<?php

namespace App\Http\Controllers;

use App\Models\ClubMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClubPhotoController extends Controller
{
    /**
     * Show the profile photo of a club member.
     *
     * @param string $member_id
     * @return StreamedResponse
     */
    public function show($member_id)
    {
        $member = ClubMember::where('member_id', $member_id)->firstOrFail();

        // Access Control: Only admins or the owner can view the photo
        if (!Auth::guard('admin')->check() && Auth::id() !== $member->user_id) {
            abort(403, 'Unauthorized access to this photo.');
        }

        if (!$member->image || !Storage::exists($member->image)) {
            abort(404, 'Photo not found.');
        }

        return Storage::response($member->image);
    }
}
