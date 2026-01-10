<?php

namespace App\Http\Controllers;

use App\Models\ClubMember;
use App\Notifications\ClubRegisterSentNoti;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClubRegisterController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        // Validate the request
        $request->validate([
            'ability' => 'required|string|max:255',
        ], [
            'ability.required' => 'กรุณากรอกความสามารถที่มี',
            'ability.max' => 'ความสามารถที่มีต้องไม่เกิน 255 ตัวอักษร',
        ]);

        try {
            // Check if user is already a member or has pending application
            $existingMembership = ClubMember::where('user_id', Auth::id())
                ->whereIn('status', ['waiting', 'approved'])
                ->first();

            if ($existingMembership) {
                if ($existingMembership->status === 'approved') {
                    return redirect()->back()->with('error', 'คุณเป็นสมาชิกชมรมแล้ว');
                } else {
                    return redirect()->back()->with('error', 'คุณมีใบสมัครที่กำลังรอการอนุมัติอยู่แล้ว');
                }
            }

            // If user had a rejected application, delete it and create a new one
            ClubMember::where('user_id', Auth::id())
                ->where('status', 'rejected')
                ->delete();

            // Create new club membership application
            ClubMember::create([
                'user_id' => Auth::id(),
                'status' => 'waiting',
                'ability' => $request->input('ability'),
            ]);

            Auth::user()->notify(new ClubRegisterSentNoti());

            return redirect()->back()->with('success', 'ส่งใบสมัครเรียบร้อยแล้ว กรุณารอการอนุมัติจากผู้ดูแลชมรม');

        } catch (\Exception $e) {
            Log::error('Club registration error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
        }
    }
}
