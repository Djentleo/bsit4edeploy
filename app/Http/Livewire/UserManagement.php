<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;

class UserManagement extends Component
{
    public $addUserModal = false;
    public $name, $email, $role;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'role' => 'nullable|string|max:255',
    ];

    public function saveUser()
    {
        $this->validate();
        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => 'active',
            'password' => bcrypt('password'), // Default password, change as needed
        ]);
        $this->reset(['addUserModal', 'name', 'email', 'role']);
    }

    public function render()
    {
        return view('livewire.user-management', [
            'users' => User::all()
        ]);
    }
}
