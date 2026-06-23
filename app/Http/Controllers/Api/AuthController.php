<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\JobPost;
use App\Models\Professional;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

// Recommended for transactions

class AuthController extends Controller
{
    private function ensureBaseRoles(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('client', 'web');
        Role::findOrCreate('professional', 'web');
        Role::findOrCreate('admin', 'web');
    }

    // Get current user (works for all roles)
    public function me()
    {
        $user = auth()->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first(),
            'profile_photo' => $user->profile_photo
                ? asset('storage/'.$user->profile_photo)
                : asset('images/user1.jpg'),
        ]);
    }

    // Register Professional

    public function registerProfessional(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'phone' => 'required|digits:10|unique:users,phone',
            'password' => ['required', 'string', new StrongPassword, 'confirmed'],
        ]);

        $this->ensureBaseRoles();

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'professional',
            ]);

            $user->assignRole('professional');

            // 🔥 CREATE PROFESSIONAL PROFILE
            Professional::create([
                'user_id' => $user->id,
                'skill' => '',
                'experience' => 0,
                'bio' => '',
            ]);

            return $user;
        });

        $token = $user->createToken('auth_token')->plainTextToken;

        app(AdminNotificationService::class)->send(
            'pro_signup',
            'New professional registration: '.$user->name,
            '/admin/dashboard?view=pending-professionals'
        );

        return response()->json([
            'message' => 'Professional registered',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // Register Client
    public function registerClient(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|digits:10|unique:users,phone',
            'password' => ['required', 'string', new StrongPassword, 'confirmed'],
        ]);

        $this->ensureBaseRoles();

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'client',
            ]);

            $user->assignRole('client'); // assign Spatie role

            return $user;
        });

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Client registered',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // Login

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($user->is_suspended) {
            return response()->json([
                'message' => 'Your account is suspended',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // 🔥 check professional status
        $professional = Professional::where('user_id', $user->id)->first();

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'role' => $user->getRoleNames()->first(),
            'approval_status' => $professional ? $professional->status : null,
            'user_name' => $user->name,
        ]);

    }

    // Web login (session + token for SPA API calls)
    public function webLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->string('email')->toString();
        $throttleKey = 'login:' . mb_strtolower($email);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'message' => 'Too many login attempts. Please try again later.',
                'retry_after' => $seconds,
            ], 429);
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();
        $user = Auth::user();

        if ($user->is_suspended) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'Your account has been suspended.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $professional = Professional::where('user_id', $user->id)->first();
        $intendedUrl = $request->session()->pull('url.intended');
        $role = $user->getRoleNames()->first();

        $needsSetup = false;
        if ($role === 'client' && !$user->profile_photo) {
            $needsSetup = true;
        } elseif ($role === 'professional' && $professional && empty($professional->skill)) {
            $needsSetup = true;
        }

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'role' => $role,
            'approval_status' => $professional ? $professional->status : null,
            'user_name' => $user->name,
            'intended_url' => $intendedUrl,
            'needs_setup' => $needsSetup,
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    // Change password

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', new StrongPassword, 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    // delet accout

    public function deleteAccount()
    {
        $user = auth()->user();

        // Check for active contracts
        $activeContracts = Contract::where(function ($query) use ($user) {
            $query->where('client_id', $user->id)
                ->orWhere('professional_id', $user->id);
        })
        ->whereIn('status', ['active', 'pending_completion'])
        ->exists();

        if ($activeContracts) {
            return response()->json([
                'message' => 'You cannot delete your account while you have active contracts. Please complete or cancel them first.',
            ], 400);
        }

        // Cancel all open job posts if user is a client
        if ($user->role === 'client') {
            JobPost::where('client_id', $user->id)
                ->where('status', 'open')
                ->update(['status' => 'cancelled']);
        }

        // Remove professional from public listings if user is a professional
        if ($user->role === 'professional') {
            Professional::where('user_id', $user->id)->update(['status' => 'rejected']);
        }

        // Revoke all Sanctum tokens
        $user->tokens()->delete();

        // Soft delete the user
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully',
        ]);
    }

    // Web logout (session logout used by protected web routes)
    public function webLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
