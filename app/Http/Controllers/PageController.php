<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('about');
    }

    public function contact(): View
    {
        return view('contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        ContactMessage::create([
            ...$validated,
            'status' => 'unread',
        ]);

        app(AdminNotificationService::class)->send(
            'contact',
            'New contact message from '.$validated['name'],
            '/admin/dashboard?view=contact-messages'
        );

        return redirect()
            ->route('contact')
            ->with('success', 'Your message was sent successfully. We will contact you soon.');
    }
}
