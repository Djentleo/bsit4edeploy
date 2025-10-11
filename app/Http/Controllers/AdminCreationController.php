<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AdminCreationController extends Controller
{
    // Show the admin creation form only if no admin exists
    public function showForm()
    {
        // Only check for users with role 'admin'
        $adminCount = User::where('role', 'admin')->count();
        // Debug: log the count
        Log::info('Admin count: ' . $adminCount);
        if ($adminCount > 0) {
            return redirect()->route('login')->with('status', 'Admin account already exists.');
        }
        // Debug: if no admin, show view
        return view('auth.create-admin');
    }

    // Handle admin creation
    public function create(Request $request)
    {
        $adminExists = User::where('role', 'admin')->exists();
        if ($adminExists) {
            return redirect()->route('login')->with('status', 'Admin account already exists.');
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|alpha_dash|min:3|max:50|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'mobile' => 'nullable|string|max:20',
            'assigned_area' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        User::create([
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'mobile' => $request->mobile,
            'assigned_area' => $request->assigned_area,
            'status' => $request->status,
        ]);

        return redirect()->route('login')->with('status', 'Admin account created successfully. You can now log in.');
    }
}
