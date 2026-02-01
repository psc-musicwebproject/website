<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'name_title' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'student_id' => 'required|string|max:255|unique:users,student_id,' . $id,
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:users,email,' . $id,
            'major' => 'nullable|string|max:255',
            'class' => 'nullable|string|max:50',
            'type' => 'required|string',
            'password' => 'nullable|string|min:4',
            'reset_password_on_next_login' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Checkbox handling: if not checked, it won't be in the request, so we default to false if not present
        // However, standard HTML forms don't send anything for unchecked checkboxes.
        // We need to explicitly check using $request->has or set a default.
        // It's safer to explicitly set it based on the presence of the key in the request if using a checkbox.
        $validated['reset_password_on_next_login'] = $request->has('reset_password_on_next_login');
        $validated['is_active'] = $request->has('is_active');

        $user->update($validated);

        return redirect()->back()->with('success', 'บันทึกข้อมูลผู้ใช้เรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'ลบผู้ใช้เรียบร้อยแล้ว');
    }

    /**
     * Unbind LINE from a user (admin action).
     */
    public function unbindLine($id)
    {
        $user = User::findOrFail($id);

        if (empty($user->line_id)) {
            return redirect()->back()->with('error', 'ผู้ใช้นี้ยังไม่ได้ผูก LINE');
        }

        $user->line_id = null;
        $user->line_bound = false;
        $user->save();

        return redirect()->back()->with('success', 'ยกเลิกการผูก LINE ของผู้ใช้ ' . $user->name . ' เรียบร้อยแล้ว');
    }
}
