<?php

namespace App\Livewire;

use App\Services\FirebaseUserService;
use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;

class UserManagement extends Component
{
    public $addUserModal = false;
    public $editMode = false;
    public $userId;
    public $name, $email, $password, $mobile, $role, $responder_type, $position, $assigned_area;

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'mobile' => 'required|string|max:20',
            'role' => 'required|in:admin,responder,cctv',
            'position' => 'required|string|max:255',
            'assigned_area' => 'required|string|max:255',
        ];
        if ($this->role === 'responder') {
            $rules['responder_type'] = 'required|in:police,fire,medical,tanod';
        }
        return $rules;
    }


    public function openModal()
    {
    $this->reset(['name', 'email', 'mobile', 'role', 'responder_type', 'position', 'assigned_area', 'userId']);
        $this->password = $this->generatePassword();
        $this->editMode = false;
        $this->addUserModal = true;
    }

    public function editUser($id)
    {
    $user = User::findOrFail($id);
    $this->userId = $user->id;
    $this->name = $user->name;
    $this->email = $user->email;
    $this->mobile = $user->mobile;
    $this->role = $user->role;
    $this->responder_type = $user->responder_type;
    $this->position = $user->position;
    $this->assigned_area = $user->assigned_area;
    $this->password = '********'; // Not editable
    $this->editMode = true;
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
    $this->editMode = false;
    $this->reset(['name', 'email', 'password', 'mobile', 'role', 'responder_type', 'position', 'assigned_area', 'userId']);
    }

    public function saveUser()
    {
        $this->validate($this->rules());
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'mobile' => $this->mobile,
            'role' => $this->role,
            'responder_type' => $this->role === 'responder' ? $this->responder_type : null,
            'position' => $this->position,
            'assigned_area' => $this->assigned_area,
            'status' => 'active',
        ]);

        // Sync to Firebase Auth and Realtime Database only if role is 'cctv'
        if ($this->role === 'cctv') {
            try {
                $firebaseService = new FirebaseUserService();
                $firebaseService->createUser(
                    $this->email,
                    $this->password,
                    $this->name,
                    $this->role,
                    [
                        'mobile' => $this->mobile,
                        'position' => $this->position,
                        'assigned_area' => $this->assigned_area,
                        'status' => 'active',
                    ]
                );
            } catch (\Exception $e) {
                // Log or handle Firebase error
            }
        }

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

    public function updateUser()
    {
        $user = User::findOrFail($this->userId);
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'required|string|max:20',
            'role' => 'required|in:admin,responder,cctv',
            'position' => 'required|string|max:255',
            'assigned_area' => 'required|string|max:255',
        ];
        if ($this->role === 'responder') {
            $rules['responder_type'] = 'required|in:police,fire,medical,tanod';
        }
        $this->validate($rules);
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'role' => $this->role,
            'responder_type' => $this->role === 'responder' ? $this->responder_type : null,
            'position' => $this->position,
            'assigned_area' => $this->assigned_area,
        ]);
        $this->closeModal();
        $this->dispatch('user-updated');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();
        $this->dispatch('user-status-toggled');
    }

    public function confirmDeleteUser($id)
    {
        $this->dispatch('confirm-delete', $id);
    }

    public function deleteUser($id)
    {
        logger('Deleting user: ' . $id);
        $user = User::findOrFail($id);
        // If user is CCTV, delete from Firebase
        if ($user->role === 'cctv') {
            $firebaseService = new FirebaseUserService();
            $firebaseService->deleteUserByEmail($user->email);
        }
        $user->delete();
        $this->dispatch('user-deleted');
    }

    public function render()
    {
        return view('livewire.user-management', [
            'users' => User::all()
        ]);
    }
}
