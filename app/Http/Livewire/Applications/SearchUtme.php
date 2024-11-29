<?php

namespace App\Http\Livewire\Applications;

use Livewire\Component;
use App\Models\PostUtmeUpload;

class SearchUtme extends Component
{
    public string $jambNumber;
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
    public function render()
    {
        return view('livewire.applications.search-utme');
    }
}