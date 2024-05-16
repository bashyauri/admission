<?php

namespace App\Http\Livewire\Applications;

use App\Livewire\Forms\ProfileForm;
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



    public ProfileForm $form;
    #[Validate('required|image|max:1024')]
    public $picture;


    public function mount()
    {
        $user = auth()->user();
        $this->form->setProfile($user);
    }

    public function save()
    {

        $user = auth()->user();

        $this->updatePicture($user);

        $this->form->store();

        return back()->withStatus('Your profile has been successfully updated!');
    }

    protected function updatePicture($user)
    {
        if ($this->form->picture) {
            $this->deleteOldPicture($user);
            $user->update([
                'picture' => $this->form->picture->store('profile', 'public')
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
        $this->form->lgaID = null;
    }
    #[Computed()]
    public function states()
    {
        return State::all();
    }
    #[Computed()]
    public function lgas()
    {
        return Lga::where('state_id', $this->form->stateID)->get();
    }
    public function render()
    {
        return view('livewire.applications.profile');
    }
}
