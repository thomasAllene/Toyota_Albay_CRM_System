<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    protected $redirectTo = '/dashboard';

    public function index()
    {
        return view('index');
    }

    public function login(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false,'errors' => $validator->errors()], 200);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (auth()->attempt($credentials, $remember)) {
            if (auth()->user()->status == 'Active') {
                return response()->json(['success' => true, 'redirect' => $this->redirectTo]);
            } else {
                auth()->logout();
                return response()->json(['success' => false, 'message' => 'Your account is not active. Please contact the Admin.']);
            }
        }

        return response()->json(['success' => false, 'message' => 'Invalid credentials']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
