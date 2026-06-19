<?php

namespace App\Http\Controllers;

use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(
        protected PasswordResetService $passwordResetService,
    ) {}

    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $result = $this->passwordResetService->sendOtp($validated['email']);

        if (!$result['success'] && $result['message'] !== 'If this email is registered, you will receive an OTP.') {
            return back()->withErrors(['email' => $result['message']]);
        }

        return redirect()
            ->route('password.verify.form', ['email' => $validated['email']])
            ->with('success', $result['message']);
    }

    public function showVerifyForm(Request $request): View
    {
        return view('auth.verify-otp', [
            'email' => $request->query('email', ''),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $result = $this->passwordResetService->verifyOtp($validated['email'], $validated['otp']);

        if (!$result['success']) {
            return back()->withErrors(['otp' => $result['message']]);
        }

        session(['otp_verified_email' => $validated['email']]);

        return redirect()
            ->route('password.reset.form', ['email' => $validated['email']])
            ->with('success', 'OTP verified. Set your new password.');
    }

    public function showResetForm(Request $request): View|RedirectResponse
    {
        $email = $request->query('email', '');

        if (session('otp_verified_email') !== $email) {
            return redirect()
                ->route('password.forgot.form')
                ->withErrors(['email' => 'Please verify your OTP first.']);
        }

        return view('auth.reset-password', [
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (session('otp_verified_email') !== $validated['email']) {
            return redirect()
                ->route('password.forgot.form')
                ->withErrors(['email' => 'Please verify your OTP first.']);
        }

        $result = $this->passwordResetService->resetPassword(
            $validated['email'],
            $validated['password'],
        );

        if (!$result['success']) {
            return back()->withErrors(['otp' => $result['message']]);
        }

        session()->forget('otp_verified_email');

        return redirect()
            ->route('login')
            ->with('success', $result['message']);
    }
}
