<?php

namespace App\Http\Controllers;

use App\Models\ClubMember;
use App\Notifications\ClubRegisterSentNoti;
use App\Notifications\Admin\NewClubRegisterNotice;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ClubRegisterController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        // Validate the request
        $request->validate([
            'contact_info' => 'required',
            'instrument' => 'required',
            'experience' => 'required',
            'wanted_duty' => 'required',
            'image_file' => 'nullable|image|max:2048',
            'image_base64' => 'required_without:image_file|string|nullable', // Require at least one
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

            // Decode JSON inputs
            $contactInfo = is_string($request->input('contact_info')) ?
                json_decode($request->input('contact_info'), true) : $request->input('contact_info');

            $instrument = is_string($request->input('instrument')) ?
                json_decode($request->input('instrument'), true) : $request->input('instrument');

            $experience = is_string($request->input('experience')) ?
                json_decode($request->input('experience'), true) : $request->input('experience');

            $wantedDuty = is_string($request->input('wanted_duty')) ?
                json_decode($request->input('wanted_duty'), true) : $request->input('wanted_duty');

            // Handle Image Upload
            $imagePath = null;
            $destinationPath = 'private/club_member/application/profile_pic';

            if ($request->has('image_base64') && !empty($request->input('image_base64'))) {
                // Handle Base64 from Cropper
                $image_parts = explode(";base64,", $request->input('image_base64'));
                if (count($image_parts) >= 2) {
                    $image_base64 = base64_decode($image_parts[1]);
                    $filename = (string) \Illuminate\Support\Str::uuid() . '.jpg';
                    $imagePath = $destinationPath . '/' . $filename;
                    \Illuminate\Support\Facades\Storage::put($imagePath, $image_base64);
                }
            } elseif ($request->hasFile('image_file')) {
                // Handle Normal File Upload (Fallback)
                $file = $request->file('image_file');
                $filename = (string) \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->storeAs($destinationPath, $filename);
            }

            // Refine Club Member Save Mechanics to make notification don't need to use latest()->first()
            // Make it less confused for the system
            // 
            // ClubMember::create([
            //     'user_id' => Auth::id(),
            //     'status' => 'waiting',
            //     'contact_info' => $contactInfo,
            //     'instrument' => $instrument,
            //     'experience' => $experience,
            //     'wanted_duty' => $wantedDuty,
            //     'image' => $imagePath,
            // ]);

            $clubMember = new ClubMember();
            $clubMember->user_id = Auth::id();
            $clubMember->status = 'waiting';
            $clubMember->contact_info = $contactInfo;
            $clubMember->instrument = $instrument;
            $clubMember->experience = $experience;
            $clubMember->wanted_duty = $wantedDuty;
            $clubMember->image = $imagePath;
            $clubMember->save();

            Auth::user()->notify(new ClubRegisterSentNoti($clubMember));

            $admin = \App\Models\User::where('type', 'admin')->get();
            if ($admin->isNotEmpty()) {
                Notification::send($admin, new NewClubRegisterNotice($clubMember));
            }

            return redirect()->back()->with('success', 'ส่งใบสมัครเรียบร้อยแล้ว กรุณารอการอนุมัติจากผู้ดูแลชมรม');
        } catch (\Exception $e) {
            Log::error('Club registration error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
        }
    }
}
