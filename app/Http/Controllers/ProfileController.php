<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function index()
    {
        if(Auth::check()){
            $user = Auth::user();
            return view('profile.profile', compact('user'));
        }

        return redirect()->route('login');
    }

    public function show()
    {
        $user = Auth::user();
        return response()->json([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'team' => $user->team->name ?? 'N/A',
            'position' => $user->usertype->name ?? 'N/A'
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->email = $validated['email'];
        $user->updated_by = Auth::id();

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'team' => $user->team->name ?? 'N/A',
                'position' => $user->usertype->name ?? 'N/A',
                'updated_by' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                'updated_at' => Carbon::parse($user->updated_at)->format('M d, Y h:i A')
            ]
        ]);
    }
}
