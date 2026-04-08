<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class PrintIdCard extends Controller
{
    public function __invoke(User $user): View
    {
        $user->load(['academicDetail.department']);
        $matric = optional($user->academicDetail)->matric_no ?? '—';
        $qrPayload = sprintf(
            'MATRIC:%s|NAME:%s %s %s|PHONE:%s|ID:%s',
            $matric,
            $user->surname ?? '',
            $user->firstname ?? '',
            $user->m_name ?? '',
            $user->phone ?? '',
            $user->id
        );

        return view('print.id-card', [
            'student' => $user,
            'matric'  => $matric,
            'dept'    => optional($user->academicDetail->department)->name ?? '—',
            'photo'   => $user->picture ? $user->profilePicture() : asset('images/avatar.png'),
            'qrPayload' => $qrPayload,
        ]);
    }
}
