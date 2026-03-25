<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Professional; 

use Illuminate\Support\Facades\DB; // Recommended for transactions


class AuthController extends Controller
{
    // Register Professional


public function registerProfessional(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
         'password' => 'required|string|min:6|confirmed',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email'=> $request->email,
        'password'=> Hash::make($request->password),
        'role' => 'professional',
    ]);

    // ✅ assign role
    $user->assignRole('professional');

    // 🔥 CREATE PROFESSIONAL PROFILE
       Professional::create([
            'user_id' => $user->id,
            'skill' => '',
            'experience' => 0,
            'bio' => '',
        ]);

    $token = $user->createToken('auth_token')->plainTextToken;

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
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // password_confirmation needed
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client',
        ]);

        $user->assignRole('client'); // assign Spatie role

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message'      => 'Client registered',
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
    }

    // Login


public function login(Request $request)
{
    $request->validate([
        'email'=>'required|email',
        'password'=>'required',
    ]);

    $user = User::where('email',$request->email)->first();

    if (!$user || !Hash::check($request->password,$user->password)) {
        return response()->json(['message'=>'Invalid credentials'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    // 🔥 check professional status
    $professional = Professional::where('user_id', $user->id)->first();

    return response()->json([
        'message'=>'Login successful',
        'access_token'=>$token,
        'token_type'=>'Bearer',
        'role' => $user->getRoleNames()->first(),
        'approval_status' => $professional ? $professional->status : null
    ]);
}

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    //delet accout

    public function deleteAccount()
{
    $user = auth()->user();

    $user->delete();

    return response()->json([
        'message' => 'Account deleted successfully'
    ]);
}
}
