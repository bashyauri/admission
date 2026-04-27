<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SendContactFormMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            SendContactFormMail::dispatch(
                $validated['name'],
                $validated['email'],
                $validated['message']
            );
            return Redirect::back()->with('success', 'Your message has been sent successfully!');
        } catch (\Exception $e) {
            Log::error('Contact form error: ' . $e->getMessage());
            return Redirect::back()->withErrors(['error' => 'Failed to send your message. Please try again later.']);
        }
    }
}
