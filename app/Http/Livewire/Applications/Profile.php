<?php

namespace App\Http\Livewire\Applications;

use App\Models\Lga;
use App\Models\User;
use App\Models\State;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

class Profile extends Component
{
    use WithFileUploads;



    #[Validate('required|image|max:1024')]
    public $picture;
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


    public function mount()
    {
        $user = auth()->user();
        $this->birthday = $user->birthday;
        $this->maritalStatus = $user->marital_status;
        $this->gender = $user->gender;
        $this->kinName = $user->kin_name;
        $this->kinPhone = $user->kin_phone;
        $this->kinAddress = $user->kin_address;
        $this->homeAddress = $user->home_address;
        $this->corAddress = $user->cor_address;
        $this->lgaID = $user->lga_id;
        $this->stateID = $user->state_id;
    }

    public function save()
    {
        $this->validate();

        $user = auth()->user();

        $this->updatePicture($user);

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

        return back()->withStatus('Your profile has been successfully updated!');
    }

    protected function updatePicture($user)
    {
        if ($this->picture) {
            $this->deleteOldPicture($user);
            $user->update([
                'picture' => $this->picture->store('profile', 'public')
            ]);
        }
    }

    protected function deleteOldPicture($user)
    {
        $currentAvatar = $user->picture;
        $validAvatars = ['profile/team-1.jpg', 'profile/team-2.jpg', 'profile/team-3.jpg'];

        if (!in_array($currentAvatar, $validAvatars) && !empty($currentAvatar)) {
            unlink(storage_path('app/public/' . $currentAvatar));
        }
    }

    public function updatedStateID()
    {
        $this->lgaID = null;
    }
    #[Computed()]
    public function states()
    {
        return State::all();
    }
    #[Computed()]
    public function lgas()
    {
        return Lga::where('state_id', $this->stateID)->get();
    }
    public function render()
    {
        return view('livewire.applications.profile');
    }
}