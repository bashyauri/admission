<?php

namespace App\Http\Livewire\Applications;

use App\Models\School;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\ValidationException;
use App\Models\CertificateUpload as ModelsCertificateUpload;

class CertificateUpload extends Component
{

    use LivewireAlert;
    use WithFileUploads;




    #[Validate('required', message: "The certificate name required")]
    public $name;

    #[Validate('required|file|mimes:pdf|max:1024')]
    public $certificate;

    public function mount()
    {
        if (!auth()->user()->hasPaid(config('remita.admission.description'))) {
            to_route('transactions');
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $path = $this->certificate->store('certificates', 'public');
            auth()->user()->certificateUploads()->create(
                [
                    'name' => $this->name,
                    'path' => $path
                ]
            );
            $this->alert('success', 'Saved Successfully', [
                'position' => 'center',
                'timer' => 1000,
                'toast' => true,
            ]);
            $this->reset();
        } catch (ValidationException $e) {

            // Display validation errors
            $errorMessages = implode(' ', $e->validator->errors()->all());

            $this->alert('error', "$errorMessages", [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
                'showConfirmButton' => true,
                'onConfirmed' => 'confirmed'

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

    #[Computed()]
    public function certificates()
    {
        return School::get(['certificate_name']);
    }
    #[Computed()]
    public function uploadedCertificates()
    {
        return ModelsCertificateUpload::all();
    }
    public function delete($id)
    {
        $certificate = ModelsCertificateUpload::find($id);
        try {

            $this->deleteCertificateFile($certificate->path);
            $certificate->delete();
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to delete', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
            return;
        }

        $this->alert('success', 'Deleted Successfully', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true,
        ]);
    }
    protected function deleteCertificateFile($path)
    {

        if (file_exists(storage_path('app/public/' . $path))) {
            unlink(storage_path('app/public/' . $path));
        }
    }

    public function render()
    {
        return view('livewire.applications.certificate-upload');
    }
}