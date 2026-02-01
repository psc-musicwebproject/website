<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManagerController extends Controller
{
    /**
     * CSV column headers for import/export.
     * This is the single source of truth for CSV format.
     */
    public const CSV_COLUMNS = [
        'student_id', 'username', 'title', 'name', 'surname', 'type',
        'major', 'class', 'phone', 'nickname', 'password', 'force_reset',
        'email', 'is_active'
    ];

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|string|unique:users,student_id',
            'username' => 'required|string|unique:users,username',
            'password' => 'nullable|string|min:8',
            'name_title' => 'nullable|string',
            'name' => 'required|string',
            'surname' => 'required|string',
            'nickname' => 'nullable|string',
            'type' => 'required|string',
            'phone_number' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email',
            // Conditional validation: Class and Major required for students
            'major' => 'nullable|string|required_if:type,student',
            'class' => 'nullable|string|required_if:type,student',
            'reset_password_on_next_login' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['password'])) {
            $generatedPassword = Str::random(8); // Auto-generate
            $validated['password'] = Hash::make($generatedPassword);
            $message = "เพิ่มผู้ใช้สำเร็จ รหัสผ่านคือ: <strong>{$generatedPassword}</strong>";
        } else {
            $validated['password'] = Hash::make($validated['password']);
            $message = 'เพิ่มผู้ใช้สำเร็จ';
        }

        // Checkbox handling
        $validated['reset_password_on_next_login'] = $request->has('reset_password_on_next_login');
        $validated['is_active'] = $request->has('is_active');

        User::create($validated);

        return redirect()->back()->with('success', $message);
    }

    /**
     * Import users from CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'reset_password_on_next_login' => 'nullable|boolean',
        ]);

        $shouldReset = $request->has('reset_password_on_next_login');

        $file = $request->file('csv_file');
        // Use closure to pass parameters to str_getcsv for PHP 8.4 compatibility
        $data = array_map(function ($line) {
            return str_getcsv($line, ",", "\"", "\\");
        }, file($file->getRealPath()));

        $header = array_shift($data); // Remove header row

        // Initialize array to keep track of new credentials
        $generatedCredentials = [];
        $skippedRows = [];

        $count = 0;
        foreach ($data as $index => $row) {
            $lineNum = $index + 2; // header is 1, index 0 is 2

            // Skip empty rows
            if (empty($row) || count($row) < 5) continue;

            // Mapping based on index (matches CSV_COLUMNS order)
            // 0:student_id, 1:username, 2:title, 3:name, 4:surname, 5:type,
            // 6:major, 7:class, 8:phone, 9:nickname, 10:password, 11:force_reset,
            // 12:email, 13:is_active
            $userData = [
                'student_id' => $row[0] ?? null,
                'username'   => $row[1] ?? null,
                'name_title' => $row[2] ?? null,
                'name'       => $row[3] ?? null,
                'surname'    => $row[4] ?? null,
                'type'       => $row[5] ?? 'student',
                'major'      => $row[6] ?? null,
                'class'      => $row[7] ?? null,
                'phone_number' => $row[8] ?? null,
                'nickname'   => $row[9] ?? null,
                'email'      => !empty($row[12]) ? trim($row[12]) : null,
            ];

            // CSV Column 11: force_reset
            // Logic: If present in CSV, use it. If not, use global checkbox ($shouldReset).
            $csvForceReset = isset($row[10]) ? ($row[10] /* password */) : null; // Wait, row[10] is password. We need row[11].

            // Re-map:
            // 0: student_id
            // ...
            // 10: password
            // 11: force_reset (optional)

            $rawForceReset = $row[11] ?? null;
            $finalResetValue = $shouldReset; // Default to global

            if ($rawForceReset !== null && $rawForceReset !== '') {
                $lowerVal = strtolower(trim($rawForceReset));
                if (in_array($lowerVal, ['1', 'true', 'yes', 'y'])) {
                    $finalResetValue = true;
                } elseif (in_array($lowerVal, ['0', 'false', 'no', 'n'])) {
                    $finalResetValue = false;
                }
            }

            $userData['reset_password_on_next_login'] = $finalResetValue;

            // Parse is_active (column 13) - defaults to true if not specified
            $rawIsActive = $row[13] ?? null;
            $finalIsActive = true; // Default to active
            if ($rawIsActive !== null && $rawIsActive !== '') {
                $lowerVal = strtolower(trim($rawIsActive));
                if (in_array($lowerVal, ['0', 'false', 'no', 'n'])) {
                    $finalIsActive = false;
                }
            }
            $userData['is_active'] = $finalIsActive;

            // Validation: Student requires Major and Class
            $missing = [];
            if ($userData['type'] === 'student') {
                if (empty($userData['major'])) $missing[] = 'Major';
                if (empty($userData['class'])) $missing[] = 'Class';
            }

            if (!empty($missing)) {
                $skippedRows[] = [
                    'line' => $lineNum,
                    'sid' => $userData['student_id'] ?? '-',
                    'name' => $userData['name'] ?? '-',
                    'raw_data' => implode(', ', array_filter([$userData['student_id'], $userData['name'], $userData['username']])),
                    'missing' => implode(', ', $missing)
                ];
                continue;
            }

            // Check for existing fields to prevent error (including email if provided)
            $existsQuery = User::where('student_id', '=', $userData['student_id'])
                ->orWhere('username', '=', $userData['username']);
            if (!empty($userData['email'])) {
                $existsQuery->orWhere('email', '=', $userData['email']);
            }
            if ($existsQuery->exists()) {
                $skippedRows[] = [
                    'line' => $lineNum,
                    'sid' => $userData['student_id'] ?? '-',
                    'name' => $userData['name'] ?? '-',
                    'raw_data' => 'Duplicate ID/Username/Email',
                    'missing' => 'Already exists'
                ];
                continue;
            }

            // Validate email format if provided
            if (!empty($userData['email']) && !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                $skippedRows[] = [
                    'line' => $lineNum,
                    'sid' => $userData['student_id'] ?? '-',
                    'name' => $userData['name'] ?? '-',
                    'raw_data' => 'Invalid email: ' . $userData['email'],
                    'missing' => 'Valid email format'
                ];
                continue;
            }

            // Password logic for CSV
            $rawPassword = null;
            if (!empty($row[10])) {
                $rawPassword = $row[10];
                $userData['password'] = Hash::make($rawPassword);
            } else {
                $rawPassword = Str::random(8);
                $userData['password'] = Hash::make($rawPassword);

                // Add to list for export (ONLY if not duplicate)
                $generatedCredentials[] = [
                    'student_id' => $userData['student_id'],
                    'username' => $userData['username'],
                    'name' => $userData['name_title'] . $userData['name'] . ' ' . $userData['surname'],
                    'password' => $rawPassword
                ];
            }

            User::create($userData);
            $count++;
        }

        $msg = "นำเข้าผู้ใช้สำเร็จ $count รายการ";
        $sessionData = ['success' => $msg];

        if (!empty($skippedRows)) {
            $sessionData['import_errors'] = $skippedRows;
            \Illuminate\Support\Facades\Log::info('Import Skipped Rows:', $skippedRows);
        }

        // If we have generated credentials, Cache them and setup download trigger
        if (count($generatedCredentials) > 0) {
            $downloadId = Str::random(40);
            \Illuminate\Support\Facades\Cache::put('csv_creds_' . $downloadId, $generatedCredentials, now()->addMinutes(10));
            $sessionData['download_id'] = $downloadId;
            \Illuminate\Support\Facades\Log::info('Generated Credentials Download ID: ' . $downloadId);
        }

        \Illuminate\Support\Facades\Log::info('Import Completed. Putting data to session (manual flash). Session ID: ' . \Illuminate\Support\Facades\Session::getId(), $sessionData);

        // Manual flash using put (will be pulled in view)
        \Illuminate\Support\Facades\Session::put('success', $sessionData['success']);

        if (isset($sessionData['import_errors'])) {
            \Illuminate\Support\Facades\Session::put('import_errors', $sessionData['import_errors']);
        }

        if (isset($sessionData['download_id'])) {
            \Illuminate\Support\Facades\Session::put('download_id', $sessionData['download_id']);
        }

        // Force save session
        \Illuminate\Support\Facades\Session::save();

        return redirect()->route('admin.usersetting');
    }

    public function downloadGeneratedCredentials($id)
    {
        $generatedCredentials = \Illuminate\Support\Facades\Cache::get('csv_creds_' . $id);

        if (!$generatedCredentials) {
            return abort(404, 'Download link expired or not found.');
        }

        $callback = function () use ($generatedCredentials) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            fputcsv($file, ['Student ID', 'Username', 'Name', 'Password'], ",", "\"", "\\");

            foreach ($generatedCredentials as $row) {
                fputcsv($file, $row, ",", "\"", "\\");
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=new_users_credentials-" . date('Y-m-d-His') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    /**
     * Download CSV Template.
     */
    public function downloadTemplate()
    {
        $columns = self::CSV_COLUMNS;
        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            // BOM for Excel
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            fputcsv($file, $columns, ",", "\"", "\\");

            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=user_import_template.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }
}
