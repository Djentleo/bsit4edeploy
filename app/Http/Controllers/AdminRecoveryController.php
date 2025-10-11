<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AdminRecoveryController extends Controller
{
    // Show the recovery form
    public function showForm(Request $request)
    {
        // IP allow list removed; accessible from any IP
        return view('auth.recover-admin');
    }

    // Handle recovery admin creation
    public function recover(Request $request)
    {
        // IP allow list removed; accessible from any IP
        $validator = Validator::make($request->all(), [
            'recovery_key' => 'required|string',
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

        // Check recovery key
        if ($request->recovery_key !== env('RECOVERY_KEY')) {
            return back()->withErrors(['recovery_key' => 'Invalid recovery key.'])->withInput();
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

        Log::info('Admin account recovered via recovery key.');

        return redirect()->route('login')->with('status', 'Admin account recovered successfully. You can now log in.');
    }
}
