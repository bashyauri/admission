<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProfileForm extends Form
{


    public $picture;

    public function rules(): array
    {
        return [
            'picture' => (!auth()->user() || !auth()->user()->picture) ? 'required|image|max:1024' : 'nullable|image|max:1024',
        ];
    }

    #[Validate('required')]
    public $birthday;
    #[Validate('required')]
    public $maritalStatus;
    #[Validate('required')]
    public $gender;
    #[Validate('required', as: "next of kin name")]
    public $kinName;
    #[Validate('required', as: "next of kin phone number")]
    public $kinPhone;
    #[Validate('required', as: "next of kin address")]
    public $kinAddress;
    #[Validate('required', message: "please select your state")]
    public $stateID;
    #[Validate('required', message: "please select your local government")]
    public $lgaID;
    #[Validate('required', as: "Home Address")]
    public $homeAddress;
    #[Validate('required', as: "Home Address")]
    public $corAddress;

    public function setProfile(User $profile)
    {
        $this->birthday = $profile->birthday;
        $this->maritalStatus = $profile->marital_status;
        $this->gender = $profile->gender;
        $this->kinName = $profile->kin_name;
        $this->kinPhone = $profile->kin_phone;
        $this->kinAddress = $profile->kin_address;
        $this->homeAddress = $profile->home_address;
        $this->corAddress = $profile->cor_address;
        $this->lgaID = $profile->lga_id;
        $this->stateID = $profile->state_id;
    }
    public function store()
    {
        $this->validate();

        $user = auth()->user();


        $user->update([
            'birthday' => $this->birthday,
            'marital_status' => $this->maritalStatus,
            'gender' => $this->gender,
            'kin_name' => $this->kinName,
            'kin_phone' => $this->kinPhone,
            'kin_address' => $this->kinAddress,
            'home_address' => $this->homeAddress,
            'cor_address' => $this->corAddress,
            'lga_id' => $this->lgaID,
            'state_id' => $this->stateID,
        ]);
    }
}
