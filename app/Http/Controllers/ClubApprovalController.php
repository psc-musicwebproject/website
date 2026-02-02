<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\User\ClubRegister\ApprovedUserNotify;
use App\Notifications\User\ClubRegister\DeniedUserNotify;
use Illuminate\Support\Facades\Notification;

class ClubApprovalController extends Controller
{
    public function update(Request $request, $id)
    {
        $clubMember = \App\Models\ClubMember::where('member_id', $id)->firstOrFail();
        $action = $request->input('action');
        $reason = $request->input('approve_reason');
        $adminId = \Illuminate\Support\Facades\Auth::id();

        if ($action === 'approve') {
            $clubMember->approve($adminId, $reason);
            $clubMember->user->notify(new ApprovedUserNotify($clubMember));

            return redirect()->route('admin.club.approve')->with('success', 'อนุมัติสมาชิกเรียบร้อยแล้ว');
        } elseif ($action === 'reject') {
            $clubMember->reject($adminId, $reason);
            $clubMember->user->notify(new DeniedUserNotify($clubMember));

            return redirect()->route('admin.club.approve')->with('success', 'ไม่อนุมัติสมาชิกเรียบร้อยแล้ว');
        }

        return back()->with('error', 'Invalid action');
    }
}
