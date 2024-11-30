<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Class PaymentService.
 */
class PaymentService
{
    public function getAdmissionResource(): string
    {
        if (auth()->user()->isUndergraduate()) {
            return config('remita.postutme.description');
        } else {
            return config('remita.admission.description');
        }
    }
}
