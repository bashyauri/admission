<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin\Applicants;

use App\Imports\PostUtmeImport;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class UploadApplicants extends Component
{
    use WithFileUploads, LivewireAlert;
    public $file;





    public function save()
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx|max:2048',
        ]);

        try {
            // Attempt to import the file

            Excel::import(new PostUtmeImport, $this->file->getRealPath());


            // Reset the file input
            $this->reset('file');

            // Provide feedback to the user
            $this->alert('success', 'Saved Successfully', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => true,
            ]);
        } catch (\Exception $e) {
            // Log the error message and stack trace for debugging
            Log::error('File upload failed: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString(),
            ]);

            // Show an error alert to the user
            $this->alert('error', 'An error occurred while uploading the file.', [
                'position' => 'center',
                'timer' => 5000,
                'toast' => true,
            ]);
        }
    }
    public function render()
    {
        return view('livewire.admin.applicants.upload-applicants');
    }
}
