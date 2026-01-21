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
        // Validate the request
        $request->validate([
            'contact_info' => 'required',
            'instrument' => 'required',
            'experience' => 'required',
            'wanted_duty' => 'required',
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

            // Decode JSON inputs if they are strings
            $contactInfo = is_string($request->input('contact_info')) ?
                json_decode($request->input('contact_info'), true) : $request->input('contact_info');

            $instrument = is_string($request->input('instrument')) ?
                json_decode($request->input('instrument'), true) : $request->input('instrument');

            $experience = is_string($request->input('experience')) ?
                json_decode($request->input('experience'), true) : $request->input('experience');

            $wantedDuty = is_string($request->input('wanted_duty')) ?
                json_decode($request->input('wanted_duty'), true) : $request->input('wanted_duty');

            // Create new club membership application
            ClubMember::create([
                'user_id' => Auth::id(),
                'status' => 'waiting',
                'contact_info' => $contactInfo,
                'instrument' => $instrument,
                'experience' => $experience,
                'wanted_duty' => $wantedDuty,
            ]);

            Auth::user()->notify(new ClubRegisterSentNoti());

            return redirect()->back()->with('success', 'ส่งใบสมัครเรียบร้อยแล้ว กรุณารอการอนุมัติจากผู้ดูแลชมรม');
        } catch (\Exception $e) {
            Log::error('Club registration error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
        }
    }
}
