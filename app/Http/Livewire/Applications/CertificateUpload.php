<?php

namespace App\Http\Livewire\Applications;

use App\Models\School;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\PaymentService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use App\Models\OlevelSubjectGrade;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\ValidationException;
use App\Models\CertificateUpload as ModelsCertificateUpload;
use App\Models\PostUtmeCredentials;

class CertificateUpload extends Component
{

    use LivewireAlert;
    use WithFileUploads;




    #[Validate('required', message: "The certificate name required")]
    public $name;

    #[Validate('required|file|mimes:pdf|max:1024')]
    public $certificate;

    public function mount(PaymentService $paymentService)
    {
        if (!auth()->user()->hasPaid($paymentService->getAdmissionResource())) {
            to_route('transactions');
        }
        if (OlevelSubjectGrade::where('user_id', auth()->id())->count() === 0) {

            to_route('olevel-grade')->with('info', 'Please Select Subject');
        }
    }

    public function save(): void
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


            $errorMessages = implode(' ', $e->validator->errors()->all());

            $this->alert('error', (string)$errorMessages, [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
                'showConfirmButton' => true,
                'onConfirmed' => 'confirmed'

            ]);



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
    public function certificates(): Collection
    {
        if (auth()->user()->isUndergraduate()) {
            return PostUtmeCredentials::query()->get(['certificate_name']);
        }
        return School::where('user_id', auth()->user()->id)->get(['certificate_name']);
    }
    #[Computed()]
    public function uploadedCertificates(): Collection
    {
        return ModelsCertificateUpload::where('user_id', auth()->user()->id)->get();
    }
    public function delete($id): void
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
    protected function deleteCertificateFile($path): void
    {

        if (file_exists(storage_path("app/public/  {$path}"))) {
            unlink(storage_path("app/public/  {$path}"));
        }
    }

    public function render(): View
    {
        return view('livewire.applications.certificate-upload');
    }
}
