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
            return config('remita.admission.description'); //for postgraduate
        }
    }
    public function getAcceptanceResource(): string
    {
        if (auth()->user()->isUndergraduate()) {
            return config('remita.postutme.acceptance_description');
        } else {
            return config('remita.acceptance.description'); //for postgraduate
        }
    }
    public function getAcceptanceFee(): int
    {
        if (auth()->user()->isUndergraduate()) {
            return config('remita.postutme.acceptance_fee');
        } else {
            return config('remita.acceptance.fee'); //for postgraduate
        }
    }
}
