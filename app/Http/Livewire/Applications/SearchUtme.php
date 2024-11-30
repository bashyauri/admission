<?php

namespace App\Http\Livewire\Applications;

use Livewire\Component;
use App\Models\PostUtmeUpload;
use App\Livewire\Forms\ProfileUpdateForm;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\ValidationException;

class SearchUtme extends Component
{
    use LivewireAlert;

    public string $jambNumber;
    public string $phoneNumber;
    public $result = null;
    public bool $showResult;


    public function search(): void
    {
        $this->validate(rules: [
            'jambNumber' => 'required|string',
        ]);


        $this->result = PostUtmeUpload::where('jamb_no', $this->jambNumber)->first();
        $this->showResult = true;

        // $this->reset('jambNumber');
    }
    public function updateProfile()
    {

        $parts = explode(' ', $this->result->name);


        [$surname, $firstname, $middlename] = array_pad($parts, 3, null);
        $this->validate(rules: [
            'phoneNumber' => 'required|string|unique:users,phone',
        ]);

        try {
            auth()->user()->update(
                attributes: [
                    'jamb_no' => $this->result->jamb_no,
                    'surname' => $surname,
                    'firstname' => $firstname,
                    'm_name' => $middlename,
                    'phone' => $this->phoneNumber
                ]

            );

            $this->alert('success', 'Profile Updated', [
                'position' => 'center',
                'timer' => 2000,
                'toast' => true,
            ]);

            return to_route('postutmescreening-invoice');
        } catch (\Exception $e) {
            report($e);
            $this->alert('error', 'Save failed.', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
        }
    }
    public function render()
    {
        return view('livewire.applications.search-utme');
    }
}
