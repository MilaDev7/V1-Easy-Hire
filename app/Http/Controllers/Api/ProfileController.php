<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Professional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Update Client Profile (name, email, location, profile photo)
     */
    public function updateClientProfile(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $request->validate([
            'name' => 'nullable|string|min:2|max:255',
            'email' => 'nullable|email|unique:users,email,'.$user->id,
            'location' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $updateData = [];

        if ($request->has('name') && $request->name) {
            $updateData['name'] = $request->name;
        }

        if ($request->has('email') && $request->email) {
            $updateData['email'] = $request->email;
        }

        if ($request->has('location') && $request->location !== null) {
            $updateData['location'] = $request->location;
        }

        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $path = $request->file('profile_photo')->store('profiles', 'public');
            $updateData['profile_photo'] = $path;
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'location' => $user->location,
                'profile_photo' => $user->profile_photo ? asset('storage/'.$user->profile_photo) : null,
            ],
        ]);
    }

    /**
     * Get Client Profile
     */
    public function getClientProfile()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'location' => $user->location ?? '',
                'profile_photo' => $user->profile_photo ? asset('storage/'.$user->profile_photo) : null,
            ],
        ]);
    }

    /**
     * Update Professional Profile (With Files)
     */
    public function updateProProfile(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $request->validate([
            'skill' => 'required|string',
            'experience' => 'required|integer',
            'bio' => 'required|string|min:20',
            'location' => 'required|string',
            'age' => 'nullable|integer',
            'gender' => 'nullable|string',
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'cv' => 'required|mimes:pdf|max:5120', // Max 5MB PDF
            'id_card' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'certificate' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ]);

        $pro = Professional::firstOrNew(['user_id' => $user->id]);

        if ($request->hasFile('profile_photo')) {
            $pro->profile_photo = $request->file('profile_photo')->store('profiles', 'public');
        }

        if ($request->hasFile('cv')) {
            $pro->cv = $request->file('cv')->store('documents/cvs', 'public');
        }

        if ($request->hasFile('id_card')) {
            $pro->id_card = $request->file('id_card')->store('documents/ids', 'public');
        }

        if ($request->hasFile('certificate')) {
            $pro->certificate = $request->file('certificate')->store('documents/certificates', 'public');
        }

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
            'data' => $pro,
        ]);
    }

    /**
     * Update Client Photo (Simple)
     */
    public function updateClientPhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|max:2048',
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
        $pro = Professional::firstOrNew(['user_id' => $user->id]);

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

        if ($request->hasFile('certificate')) {
            $pro->certificate = $request->file('certificate')->store('certificates', 'public');
        }

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
