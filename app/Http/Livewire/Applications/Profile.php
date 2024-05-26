<?php

namespace App\Http\Livewire\Applications;

use App\Models\Lga;
use App\Models\User;
use App\Models\State;
use Livewire\Component;
use App\Models\Transaction;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use App\Livewire\Forms\ProfileForm;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Profile extends Component
{
    use WithFileUploads, LivewireAlert, AuthorizesRequests;



    public ProfileForm $form;
    #[Validate('required|image|max:1024')]
    public $picture;


    public function mount()
    {


        $user = auth()->user();



        if (!auth()->user()->hasPaid(config('remita.admission.description'))) {
            to_route('transactions');
        }
        $this->form->setProfile($user);
    }

    public function save()
    {

        $user = auth()->user();
        $this->form->store();

        $this->updatePicture($user);

        $this->alert('success', 'Your profile has been successfully updated!', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true,
        ]);
        return to_route('school-attended');
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
