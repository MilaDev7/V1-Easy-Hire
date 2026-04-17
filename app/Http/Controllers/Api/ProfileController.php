<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Professional;
use App\Models\ProfessionalPortfolioItem;
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
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
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
     * Get Professional Profile
     */
    public function getProProfile()
    {
        $user = Auth::user();
        $pro = Professional::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'location' => $pro ? ($pro->location ?? '') : '',
                'profile_photo' => $pro && $pro->profile_photo ? asset('storage/'.$pro->profile_photo) : ($user->profile_photo ? asset('storage/'.$user->profile_photo) : null),
            ],
        ]);
    }

    /**
     * Update Professional Profile (Simple - name, email, location, photo)
     */
    public function updateProProfileSimple(Request $request)
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
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
        ]);

        $updateData = [];

        if ($request->has('name') && $request->name) {
            $updateData['name'] = $request->name;
        }

        if ($request->has('email') && $request->email) {
            $updateData['email'] = $request->email;
        }

        $pro = Professional::where('user_id', $user->id)->first();

        $proUpdateData = [];
        if ($request->has('location') && $request->location !== null) {
            $proUpdateData['location'] = $request->location;
        }

        if ($request->hasFile('profile_photo')) {
            if ($pro && $pro->profile_photo) {
                Storage::disk('public')->delete($pro->profile_photo);
            }
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $path = $request->file('profile_photo')->store('profiles', 'public');
            $proUpdateData['profile_photo'] = $path;
            $updateData['profile_photo'] = $path;
        }

        if ($pro) {
            $pro->update($proUpdateData);
        }

        // Persist user changes after all optional fields (including photo) are prepared.
        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'location' => $pro ? $pro->location : '',
                'profile_photo' => $pro && $pro->profile_photo ? asset('storage/'.$pro->profile_photo) : ($user->profile_photo ? asset('storage/'.$user->profile_photo) : null),
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

    /**
     * Upload a professional portfolio item (image + optional description + optional linked completed job).
     */
    public function uploadPortfolioItem(Request $request)
    {
        $user = Auth::user();
        $professional = Professional::where('user_id', $user->id)->first();

        if (! $professional) {
            return response()->json([
                'success' => false,
                'message' => 'Professional profile not found',
            ], 404);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'description' => 'nullable|string|max:500',
            'linked_job_id' => 'nullable|integer|exists:job_posts,id',
        ]);

        $linkedJobId = $request->linked_job_id ? (int) $request->linked_job_id : null;

        if ($linkedJobId) {
            $isCompletedForProfessional = Application::where('professional_id', $user->id)
                ->where('job_id', $linkedJobId)
                ->where('status', 'accepted')
                ->whereHas('job', function ($query) {
                    $query->where('status', 'completed');
                })
                ->exists();

            if (! $isCompletedForProfessional) {
                return response()->json([
                    'success' => false,
                    'message' => 'Linked job must be one of your completed jobs',
                ], 422);
            }
        }

        $imagePath = $request->file('image')->store('portfolio', 'public');

        $item = ProfessionalPortfolioItem::create([
            'professional_id' => $professional->id,
            'job_id' => $linkedJobId,
            'image_path' => $imagePath,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Portfolio item uploaded',
            'data' => [
                'id' => $item->id,
                'image_url' => asset('storage/'.$item->image_path),
                'description' => $item->description,
                'linked_job_id' => $item->job_id,
            ],
        ]);
    }

    /**
     * Update one portfolio item for authenticated professional.
     */
    public function updatePortfolioItem(Request $request, $id)
    {
        $user = Auth::user();
        $professional = Professional::where('user_id', $user->id)->first();

        if (! $professional) {
            return response()->json([
                'success' => false,
                'message' => 'Professional profile not found',
            ], 404);
        }

        $item = ProfessionalPortfolioItem::where('id', $id)
            ->where('professional_id', $professional->id)
            ->first();

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Portfolio item not found',
            ], 404);
        }

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'description' => 'nullable|string|max:500',
            'linked_job_id' => 'nullable|integer|exists:job_posts,id',
        ]);

        $linkedJobId = $request->linked_job_id ? (int) $request->linked_job_id : null;

        if ($linkedJobId) {
            $isCompletedForProfessional = Application::where('professional_id', $user->id)
                ->where('job_id', $linkedJobId)
                ->where('status', 'accepted')
                ->whereHas('job', function ($query) {
                    $query->where('status', 'completed');
                })
                ->exists();

            if (! $isCompletedForProfessional) {
                return response()->json([
                    'success' => false,
                    'message' => 'Linked job must be one of your completed jobs',
                ], 422);
            }
        }

        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $item->image_path = $request->file('image')->store('portfolio', 'public');
        }

        if ($request->has('description')) {
            $item->description = $request->description;
        }

        if ($request->has('linked_job_id')) {
            $item->job_id = $linkedJobId;
        }

        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Portfolio item updated',
            'data' => [
                'id' => $item->id,
                'image_url' => asset('storage/'.$item->image_path),
                'description' => $item->description,
                'linked_job_id' => $item->job_id,
                'created_at' => $item->created_at,
            ],
        ]);
    }

    /**
     * List current professional portfolio items.
     */
    public function myPortfolioItems()
    {
        $user = Auth::user();
        $professional = Professional::where('user_id', $user->id)->first();

        if (! $professional) {
            return response()->json([
                'success' => false,
                'message' => 'Professional profile not found',
            ], 404);
        }

        $items = ProfessionalPortfolioItem::where('professional_id', $professional->id)
            ->latest()
            ->get()
            ->map(function (ProfessionalPortfolioItem $item) {
                return [
                    'id' => $item->id,
                    'image_url' => asset('storage/'.$item->image_path),
                    'description' => $item->description,
                    'linked_job_id' => $item->job_id,
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Delete one portfolio item for authenticated professional.
     */
    public function deletePortfolioItem($id)
    {
        $user = Auth::user();
        $professional = Professional::where('user_id', $user->id)->first();

        if (! $professional) {
            return response()->json([
                'success' => false,
                'message' => 'Professional profile not found',
            ], 404);
        }

        $item = ProfessionalPortfolioItem::where('id', $id)
            ->where('professional_id', $professional->id)
            ->first();

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Portfolio item not found',
            ], 404);
        }

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Portfolio item deleted',
        ]);
    }
}
