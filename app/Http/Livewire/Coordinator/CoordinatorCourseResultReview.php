<?php

namespace App\Http\Livewire\Coordinator;

class CoordinatorCourseResultReview extends CoordinatorResultReview
{
    public function render()
    {
        return view(
            'livewire.coordinator.coordinator-course-result-review'
        )->layout('layouts.app');
    }
}
