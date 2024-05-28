<?php

namespace App\Http\Livewire\Applications;

use App\Models\Course;
use Livewire\Component;
use App\Models\Programme;
use Livewire\Attributes\Computed;
use App\Livewire\Forms\ProposedCourseForm;
use App\Models\CertificateUpload;
use App\Models\ProposedCourse as ModelsProposedCourse;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ProposedCourse extends Component
{
    use LivewireAlert;
    public ProposedCourseForm $form;
    public function mount()
    {
        if (!auth()->user()->hasPaid(config('remita.admission.description'))) {
            to_route('transactions');
        }
        if (CertificateUpload::where('user_id', auth()->user()->id)->count() < 2) {
            $this->alert('warning', 'Please upload Certificate', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
                'showConfirmButton' => true,
                'onConfirmed' => '',
                'width' => '500',
                'confirmButtonText' => 'take me',
                'text' => 'Certficate for the schools attended not uploaded, please confirm that all the certificates are uploaded.',
            ]);
            to_route('upload-certificate');
        }
        $course = auth()->user()->proposedCourse;
        $this->form->setProposedCourse($course);
    }

    public function save()
    {
        $this->form->store();
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
        return Course::where(['department_id' => $this->form->departmentID, 'programme_id' => 6])->get();
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