<?php

namespace App\Http\Livewire\Applications;

use App\Models\PostUtmeUpload;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class SearchUtme extends Component
{
    use LivewireAlert;

    public string $jambNumber = '';
    public string $phoneNumber = '';
    public string $nin = '';

    public ?PostUtmeUpload $result = null;

    public bool $showResult = false;

    /**
     * Validation rules.
     */
    protected function rules(): array
    {
        return [
            'jambNumber' => [
                'required',
                'string',
            ],

            'phoneNumber' => [
                'required',
                'string',
                Rule::unique('users', 'phone')
                    ->ignore(auth()->id()),
            ],

            'nin' => [
                'required',
                'digits:11',
                Rule::unique('users', 'nin')
                    ->ignore(auth()->id()),
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    protected function messages(): array
    {
        return [
            'nin.required' => 'Please enter your NIN.',
            'nin.digits' => 'NIN must be exactly 11 digits.',

            'phoneNumber.required' => 'Please enter your phone number.',
            'phoneNumber.unique' => 'This phone number is already in use.',

            'jambNumber.required' => 'Enter your JAMB registration number.',
        ];
    }

    /**
     * Remove every non-digit from NIN.
     */
    public function updatedNin($value): void
    {
        $this->nin = preg_replace('/\D/', '', $value);

        $this->validateOnly('nin');
    }

    /**
     * Validate phone as user types.
     */
    public function updatedPhoneNumber(): void
    {
        $this->validateOnly('phoneNumber');
    }

    /**
     * Search for JAMB record.
     */
    public function search(): void
    {
        $this->validateOnly('jambNumber');

        $this->result = PostUtmeUpload::where(
            'jamb_no',
            trim($this->jambNumber)
        )->first();

        if (!$this->result) {

            $this->showResult = false;

            $this->alert('error', 'No record found for the supplied JAMB Number.', [
                'toast' => true,
                'position' => 'center',
            ]);

            return;
        }

        $this->showResult = true;
    }

    /**
     * Save profile.
     */
    public function updateProfile()
    {
        if (!$this->result) {

            $this->alert('error', 'Please search for your JAMB record first.');

            return;
        }

        $this->validate();

        $parts = preg_split('/\s+/', trim($this->result->name));

        [$surname, $firstname, $middlename] = array_pad($parts, 3, null);

        try {

            auth()->user()->update([

                'jamb_no'   => $this->result->jamb_no,
                'surname'   => $surname,
                'firstname' => $firstname,
                'm_name'    => $middlename,
                'phone'     => $this->phoneNumber,
                'nin'       => $this->nin,

            ]);

            $this->alert('success', 'Profile updated successfully.', [
                'toast' => true,
                'position' => 'center',
                'timer' => 2000,
            ]);

            return redirect()->route('postutmescreening-invoice');

        } catch (\Throwable $e) {

            report($e);

            $this->alert('error', 'Unable to update your profile. Please try again.', [
                'toast' => true,
                'position' => 'center',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.applications.search-utme');
    }
}
