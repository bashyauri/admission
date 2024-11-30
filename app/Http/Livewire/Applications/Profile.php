<?php

namespace App\Http\Livewire\Applications;

use App\Models\Lga;
use App\Models\User;
use App\Models\State;
use Livewire\Component;
use App\Models\Transaction;
use Livewire\WithFileUploads;
use App\Services\PaymentService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use App\Livewire\Forms\ProfileForm;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Profile extends Component
{
    use WithFileUploads, LivewireAlert, AuthorizesRequests;



    public ProfileForm $form;
    #[Validate('required|image|max:1024')]
    public $picture;


    public function mount(PaymentService $paymentService)
    {


        $user = auth()->user();



        if (!auth()->user()->hasPaid($paymentService->getAdmissionResource())) {
            to_route('transactions');
        }
        $this->form->setProfile($user);
    }

    public function save()
    {

        $user = auth()->user();

        try {
            $this->form->store();
            $this->updatePicture($user);

            $this->alert('success', 'Your profile has been successfully updated!', [
                'position' => 'center',
                'timer' => 1000,
                'toast' => true,
            ]);
            return to_route('school-attended');
        } catch (ValidationException $e) {

            // Display validation errors
            $errorMessages = implode(' ', $e->validator->errors()->all());

            $this->alert('error', "$errorMessages", [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,


            ]);


            // Set validation errors in Livewire's error bag
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Save failed.', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
        }
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
