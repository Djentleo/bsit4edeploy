<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;

class UserManagement extends Component
{
    public $addUserModal = false;
    public $name, $email, $password, $mobile, $role, $position, $assigned_area;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
        'mobile' => 'required|string|max:20',
        'role' => 'required|in:admin,responder',
        'position' => 'required|string|max:255',
        'assigned_area' => 'required|string|max:255',
    ];

    public function openModal()
    {
        $this->reset(['name', 'email', 'mobile', 'role', 'position', 'assigned_area']);
        $this->password = $this->generatePassword();
        $this->addUserModal = true;
    }

    private function generatePassword($length = 10)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle(str_repeat($chars, $length)), 0, $length);
    }

    public function closeModal()
    {
        $this->addUserModal = false;
    }

    public function saveUser()
    {
        $this->validate();
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'mobile' => $this->mobile,
            'role' => $this->role,
            'position' => $this->position,
            'assigned_area' => $this->assigned_area,
            'status' => 'active',
        ]);

        // Send welcome email with credentials
        try {
            Mail::to($user->email)->send(new WelcomeUserMail($user, $this->password));
        } catch (\Exception $e) {
            // Log or handle mail error
        }

        $this->closeModal();
        $this->reset(['name', 'email', 'password', 'mobile', 'role', 'position', 'assigned_area']);
        $this->dispatch('user-added');
    }

    public function render()
    {
        return view('livewire.user-management', [
            'users' => User::all()
        ]);
    }
}
