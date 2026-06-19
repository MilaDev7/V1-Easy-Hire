<?php

namespace App\Services;

use App\Mail\SendOtpMail;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class PasswordResetService
{
    public const OTP_LENGTH = 6;
    public const OTP_EXPIRY_MINUTES = 10;
    public const RATE_LIMIT_ATTEMPTS = 3;
    public const RATE_LIMIT_DECAY_SECONDS = 60;

    public function sendOtp(string $email): array
    {
        $key = 'send-otp:' . $email;

        if (RateLimiter::tooManyAttempts($key, self::RATE_LIMIT_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            return [
                'success' => false,
                'message' => "Too many requests. Try again in {$seconds} seconds.",
            ];
        }

        RateLimiter::hit($key, self::RATE_LIMIT_DECAY_SECONDS);

        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'If this email is registered, you will receive an OTP.',
            ];
        }

        PasswordOtp::where('email', $email)->where('used', false)->update(['used' => true]);

        $otp = str_pad((string) random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);

        PasswordOtp::create([
            'email' => $email,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'used' => false,
        ]);

        try {
            Mail::to($email)->send(new SendOtpMail($otp, $user->name));
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email: '.$e->getMessage());
        }

        return [
            'success' => true,
            'message' => 'If this email is registered, you will receive an OTP.',
            'email' => $email,
        ];
    }

    public function verifyOtp(string $email, string $otp): array
    {
        $key = 'verify-otp:' . $email;

        if (RateLimiter::tooManyAttempts($key, self::RATE_LIMIT_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            return [
                'success' => false,
                'message' => "Too many attempts. Try again in {$seconds} seconds.",
            ];
        }

        RateLimiter::hit($key, self::RATE_LIMIT_DECAY_SECONDS);

        $otpRecord = PasswordOtp::validFor($email)->latest()->first();

        if (!$otpRecord) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ];
        }

        if (!Hash::check($otp, $otpRecord->otp)) {
            return [
                'success' => false,
                'message' => 'Invalid OTP. Please try again.',
            ];
        }

        return [
            'success' => true,
            'message' => 'OTP verified.',
            'email' => $email,
        ];
    }

    public function resetPassword(string $email, string $password): array
    {
        $otpRecord = PasswordOtp::validFor($email)->latest()->first();

        if (!$otpRecord) {
            return [
                'success' => false,
                'message' => 'No valid OTP found. Please request a new one.',
            ];
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
            ];
        }

        $user->password = Hash::make($password);
        $user->save();

        $otpRecord->update(['used' => true]);

        return [
            'success' => true,
            'message' => 'Password reset successfully. You can now login.',
        ];
    }
}
