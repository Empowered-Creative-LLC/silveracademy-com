<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ParentCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ParentSignupController extends Controller
{
    /**
     * Display the parent signup page (email + parent code).
     */
    public function create(Request $request): Response
    {
        $justCreated = $request->boolean('created');
        $sandboxPassword = $request->session()->get('sandbox_password');
        if ($justCreated && ParentCodeService::inSandbox() && blank($sandboxPassword)) {
            $sandboxPassword = (string) config('portal.sandbox_password', 'Sandbox123!');
        }

        return Inertia::render('Auth/ParentSignup', [
            'sandboxPassword' => $sandboxPassword,
            'sandboxEmail' => $request->session()->get('sandbox_email') ?: $request->query('email'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Create the parent account from the signup form.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|string|lowercase|email|max:255',
            'code' => 'required|string|max:32',
        ]);

        $result = ParentCodeService::signup($validated['email'], $validated['code']);
        if (! $result['ok']) {
            return back()->withErrors(['code' => $result['message']])->withInput();
        }

        return redirect()
            ->route('parent.signup', array_filter([
                'created' => 1,
                'email' => $result['email'],
            ]))
            ->with('status', $result['message'])
            ->with('sandbox_password', $result['password'])
            ->with('sandbox_email', $result['email']);
    }
}
