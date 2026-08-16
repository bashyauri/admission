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
    #[Validate('required|image|mimes:jpg,jpeg,png|max:2048')]
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
            if ($user->isUndergraduate()) { // Assuming `isUndergraduate` is a method or property
                return to_route('olevel');
            }

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
            // Additional file content validation
            $fileContent = file_get_contents($this->form->picture->getRealPath());
            if (!$this->isValidImageContent($fileContent)) {
                $this->alert('error', 'Invalid image file content.', [
                    'position' => 'center',
                    'timer' => 3000,
                    'toast' => true,
                ]);
                return;
            }

            $this->deleteOldPicture($user);
            $user->update([
                'picture' => $this->form->picture->store('profile', 'public')
            ]);
        }
    }

    private function isValidImageContent($content): bool
    {
        // Check for common image file signatures
        $jpgSignature = "\xFF\xD8\xFF";
        $pngSignature = "\x89\x50\x4E\x47";
        
        return strpos($content, $jpgSignature) === 0 || strpos($content, $pngSignature) === 0;
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