<?php

namespace App\Http\Livewire\Applications;

use App\Models\School;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use App\Models\CertificateUpload as ModelsCertificateUpload;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\WithFileUploads;

class CertificateUpload extends Component
{

    use LivewireAlert;
    use WithFileUploads;




    #[Validate('required', message: "The certificate name required")]
    public $name;

    #[Validate('required|file|mimes:pdf|max:1024')]
    public $certificate;



    public function save()
    {
        $this->validate();
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
        try {
            ModelsCertificateUpload::find($id)->delete();
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

    public function render()
    {
        return view('livewire.applications.certificate-upload');
    }
}
