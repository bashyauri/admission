<?php

namespace App\Http\Livewire\Applications;

use App\Models\Course;
use Livewire\Component;
use App\Models\Programme;
use Livewire\Attributes\Computed;
use App\Livewire\Forms\ProposedCourseForm;
use App\Models\CertificateUpload;
use App\Models\PostUtmeUpload;
use App\Models\ProposedCourse as ModelsProposedCourse;
use App\Services\PaymentService;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ProposedCourse extends Component
{
    use LivewireAlert;
    public ProposedCourseForm $form;
    public function mount(PaymentService $paymentService)
    {
        if (!auth()->user()->hasPaid($paymentService->getAdmissionResource())) {
            to_route('transactions');
        }
        if (CertificateUpload::where('user_id', auth()->user()->id)->count() < 2) {


            to_route('upload-certificate')->with('warning', 'Certficate for the schools attended not uploaded, please confirm that all the certificates are uploaded.');
        }
        $course = auth()->user()->proposedCourse;
        $this->form->setProposedCourse($course);
    }

    public function save()
    {
        if (auth()->user()->isUndergraduate()) {
            $jambData = PostUtmeUpload::query()->where('jamb_no', auth()->user()->jamb_no)->firstOrFail();
            $this->form->storeUndergraduate($jambData);
        } else {
            $this->form->store();
        }


        $this->alert('success', 'Saved Successfully', [
            'position' => 'center',
            'timer' => 1000,
            'toast' => true,
        ]);
    }
    #[Computed()]
    public function departments()
    {
        $program = Programme::findOrFail(auth()->user()->programme->id);

        return $program->departments;
    }

    #[Computed()]
    public function courses()
    {
        return Course::where(['department_id' => $this->form->departmentID, 'programme_id' => auth()->user()->programme_id])->get();
    }
    public function updatedDepartmentID()
    {

        $this->form->courseID = null;
    }
    public function render()
    {
        return view('livewire.applications.proposed-course');
    }
}
