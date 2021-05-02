<?php

namespace App\Http\Livewire\Admin\Users;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ListUsers extends Component
{
    public $state = [];

    public $user;

    public $showEditModal = false;

    public $userIDRemoval = null;

    public function addNew()
    {
        $this->state = [];
        $this->showEditModal = false;
        $this->dispatchBrowserEvent('show-form');
    }

    public function createUser()
    {
        $data = Validator::make($this->state, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ])->validate();

        $data['password'] = bcrypt($data['password']);

        User::create($data);

        //session()->flash('message','User added successfully!');

        $this->dispatchBrowserEvent('hide-form', ['message' => 'User added successfully!']);

    }

    public function edit( User $user )
    {
        $this->showEditModal = true;
        $this->user = $user;
        $this->state = $user->toArray();
        $this->dispatchBrowserEvent('show-form');
    }

    public function updateUser()
    {
        $data = Validator::make($this->state, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' .$this->user->id,
            'password' => 'sometimes|confirmed',
        ])->validate();

        if(!empty($data['password'])){
            $data['password'] = bcrypt($data['password']);
        }
        
        $this->user->update($data);
        $this->dispatchBrowserEvent('hide-form', ['message' => 'User updated successfully!']);
    }

    public function confirmUserRemoval($userID){
        $this->userIDRemoval = $userID;
        $this->dispatchBrowserEvent('show-delete-modal');
    }

    public function deleteUser(){
        $user = User::findOrFail($this->userIDRemoval);
        $user->delete();
        $this->dispatchBrowserEvent('hide-delete-modal', ['message' => 'User deleted successfully!']);
    }

    public function render()
    {
        $users = User::latest()->paginate();
        
        return view('livewire.admin.users.list-users', [
            'users' => $users,
        ]);
    }
}
