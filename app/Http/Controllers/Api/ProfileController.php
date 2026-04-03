<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Professional;

class ProfileController extends Controller
{
    /**
     * Update Professional Profile (With Files)
     */
    public function updateProProfile(Request $request)
    {
        $user = Auth::user();

        // 1. Validate - Don't let them upload trash
        $request->validate([
            'skill' => 'required|string',
            'experience' => 'required|integer',
            'bio' => 'required|string|min:20',
            'location' => 'required|string',
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'cv' => 'required|mimes:pdf|max:5120', // Max 5MB PDF
            'id_card' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. Find or Create the Pro record
        $pro = Professional::firstOrNew(['user_id' => $user->id]);

        // 3. Handle File Uploads
        if ($request->hasFile('profile_photo')) {
            $pro->profile_photo = $request->file('profile_photo')->store('profiles', 'public');
        }

        if ($request->hasFile('cv')) {
            $pro->cv = $request->file('cv')->store('documents/cvs', 'public');
        }

        if ($request->hasFile('id_card')) {
            $pro->id_card = $request->file('id_card')->store('documents/ids', 'public');
        }

        // 4. Update text fields
        $pro->skill = $request->skill;
        $pro->experience = $request->experience;
        $pro->bio = $request->bio;
        $pro->age = $request->age;
        $pro->gender = $request->gender;
        $pro->location = $request->location;
        $pro->status = 'pending'; // Professional is now waiting for Admin approval
        
        $pro->save();

        return response()->json([
            'success' => true, 
            'message' => 'Profile submitted for approval!',
            'data' => $pro
        ]);
    }

    /**
     * Update Client Photo (Simple)
     */
    public function updateClientPhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|max:2048'
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $user->update(['profile_photo' => $path]);
        }

        return response()->json(['success' => true, 'path' => asset('storage/'.$path)]);
    }

    public function completeProfessionalProfile(Request $request)
{
    $user = auth()->user();
    $pro = Professional::where('user_id', $user->id)->first();

    // 1. Validate (Crucial!)
    $request->validate([
        'skill' => 'required',
        'profile_photo' => 'required|image|max:2048',
        'cv' => 'required|mimes:pdf|max:5120',
        'id_card' => 'required|image|max:2048',
    ]);

    // 2. Save Files
    $pro->profile_photo = $request->file('profile_photo')->store('profiles', 'public');
    $pro->id_card = $request->file('id_card')->store('ids', 'public');
    $pro->cv = $request->file('cv')->store('cvs', 'public');

    // 3. Save Text
    $pro->skill = $request->skill;
    $pro->experience = $request->experience;
    $pro->bio = $request->bio;
    $pro->age = $request->age;
    $pro->gender = $request->gender;
    $pro->location = $request->location;
    $pro->status = 'pending'; // For Admin review
    
    $pro->save();

    return response()->json(['message' => 'Profile Updated Successfully']);
}
}