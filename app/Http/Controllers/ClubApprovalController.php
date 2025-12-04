<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
            return redirect()->route('admin.club.approve')->with('success', 'อนุมัติสมาชิกเรียบร้อยแล้ว');
        } elseif ($action === 'reject') {
            $clubMember->reject($adminId, $reason);
            return redirect()->route('admin.club.approve')->with('success', 'ไม่อนุมัติสมาชิกเรียบร้อยแล้ว');
        }

        return back()->with('error', 'Invalid action');
    }
}
