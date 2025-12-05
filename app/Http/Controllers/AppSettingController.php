<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\AppSetting;

class AppSettingController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_header' => 'required|string|max:255',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ], [
            'app_name.required' => 'กรุณากรอกชื่อแอปพลิเคชัน',
            'app_name.max' => 'ชื่อแอปพลิเคชันต้องไม่เกิน 255 ตัวอักษร',
            'app_header.required' => 'กรุณากรอกหัวแอป',
            'app_header.max' => 'หัวแอปต้องไม่เกิน 255 ตัวอักษร',
            'app_logo.image' => 'ไฟล์โลโก้ต้องเป็นรูปภาพ',
            'app_logo.mimes' => 'ไฟล์โลโก้ต้องเป็นไฟล์ประเภท: jpeg, png, jpg, gif, svg',
            'app_logo.max' => 'ขนาดไฟล์โลโก้ต้องไม่เกิน 4MB',
        ]);

        try {
            AppSetting::updateSetting('name', $request->input('app_name'));
            AppSetting::updateSetting('header', $request->input('app_header'));

            if ($request->hasFile('app_logo')) {
                $request->file('app_logo')->move(public_path('assets/image'), 'logo.png');
            }

            return redirect()->back()->with('success', 'บันทึกการตั้งค่าเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการบันทึกการตั้งค่า: ' . $e->getMessage());
        }
    }
}
