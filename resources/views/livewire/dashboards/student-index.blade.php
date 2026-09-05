@php
    use App\Enums\TransactionStatus;
    use App\Services\PaymentService;

    $paymentService = new PaymentService();
    $approved = TransactionStatus::APPROVED;
    $pending = TransactionStatus::PENDING;
@endphp

<div>

    {{-- ============================================================
        STUDENT PROFILE
        Keep the existing student picture/profile section
    ============================================================= --}}
    @livewire('student.student-profile')


    {{-- ============================================================
        DASHBOARD ACTION CARDS
    ============================================================= --}}
    <div class="flex flex-wrap -mx-3 gap-y-4">

        {{-- ========================================================
            GENERATE PAYMENT
        ========================================================= --}}
        <div class="w-full px-3 sm:w-1/2">

            <div
                class="relative flex flex-col h-full min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border dark:bg-gray-950 dark:shadow-soft-dark-xl">

                <div class="flex flex-col justify-between flex-auto p-5">

                    <div class="flex items-start gap-4">

                        {{-- Icon --}}
                        <div
                            class="flex items-center justify-center flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-lime shadow-soft-md">
                            <i class="ni ni-credit-card text-white text-lg"></i>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1">

                            <h6 class="mb-1 text-sm font-semibold text-slate-700 dark:text-white">
                                Generate Payment
                            </h6>

                            <p class="mb-0 text-xs leading-relaxed text-slate-400">
                                Pay your school fees and manage your payment invoice.
                            </p>

                        </div>

                    </div>

                    {{-- Button --}}
                    <div class="mt-5">

                        @if (auth()->user()->isUndergraduate())

                            <a href="{{ route('student.ug-school-fees', ['user' => auth()->user()->id]) }}"
                                class="inline-flex items-center justify-center w-full px-5 py-3 font-bold text-center text-white uppercase transition-all border-0 rounded-lg cursor-pointer bg-gradient-lime text-size-xs hover:scale-[1.01] hover:shadow-soft-md active:opacity-85">

                                Pay School Fees

                            </a>

                        @else

                            <a href="{{ route('student.school-fees-invoice') }}"
                                class="inline-flex items-center justify-center w-full px-5 py-3 font-bold text-center text-white uppercase transition-all border-0 rounded-lg cursor-pointer bg-gradient-lime text-size-xs hover:scale-[1.01] hover:shadow-soft-md active:opacity-85">

                                Generate Invoice

                            </a>

                        @endif

                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================
            CONDITIONAL UNDERGRADUATE ACTIONS
        ========================================================= --}}
        @if($paymentService->hasStudentPaidSchoolFees(Auth::user()->id) && Auth::user()->isUndergraduate())


            {{-- ====================================================
                MANAGE COURSES
            ===================================================== --}}
            <div class="w-full px-3 sm:w-1/2">

                <div
                    class="relative flex flex-col h-full min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border dark:bg-gray-950 dark:shadow-soft-dark-xl">

                    <div class="flex flex-col justify-between flex-auto p-5">

                        <div class="flex items-start gap-4">

                            {{-- Icon --}}
                            <div
                                class="flex items-center justify-center flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-lime shadow-soft-md">
                                <i class="ni ni-books text-white text-lg"></i>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1">

                                <h6 class="mb-1 text-sm font-semibold text-slate-700 dark:text-white">
                                    Manage Courses
                                </h6>

                                <p class="mb-0 text-xs leading-relaxed text-slate-400">
                                    Add or drop courses for your current semester.
                                </p>

                            </div>

                        </div>

                        {{-- Button --}}
                        <div class="mt-5">

                            <a href="{{ route('student.course-registration') }}"
                                class="inline-flex items-center justify-center w-full px-5 py-3 font-bold text-center text-white uppercase transition-all border-0 rounded-lg cursor-pointer bg-gradient-lime text-size-xs hover:scale-[1.01] hover:shadow-soft-md active:opacity-85">

                                Add / Drop Courses

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ====================================================
                GENERATE EXAM CARD
            ===================================================== --}}
            <div class="w-full px-3 sm:w-1/2">

                <div
                    class="relative flex flex-col h-full min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border dark:bg-gray-950 dark:shadow-soft-dark-xl">

                    <div class="flex flex-col justify-between flex-auto p-5">

                        <div class="flex items-start gap-4">

                            {{-- Icon --}}
                            <div
                                class="flex items-center justify-center flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-lime shadow-soft-md">
                                <i class="ni ni-single-copy-04 text-white text-lg"></i>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1">

                                <h6 class="mb-1 text-sm font-semibold text-slate-700 dark:text-white">
                                    Generate Exam Card
                                </h6>

                                <p class="mb-0 text-xs leading-relaxed text-slate-400">
                                    Generate and access your examination card.
                                </p>

                            </div>

                        </div>

                        {{-- Button --}}
                        <div class="mt-5">

                            <a href="{{ route('student.exam-card') }}"
                                class="inline-flex items-center justify-center w-full px-5 py-3 font-bold text-center text-white uppercase transition-all border-0 rounded-lg cursor-pointer bg-gradient-lime text-size-xs hover:scale-[1.01] hover:shadow-soft-md active:opacity-85">

                                Exam Card

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ====================================================
                ACADEMIC RESULTS
            ===================================================== --}}
            <div class="w-full px-3 sm:w-1/2">

                <div
                    class="relative flex flex-col h-full min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border dark:bg-gray-950 dark:shadow-soft-dark-xl">

                    <div class="flex flex-col justify-between flex-auto p-5">

                        <div class="flex items-start gap-4">

                            {{-- Icon --}}
                            <div
                                class="flex items-center justify-center flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-lime shadow-soft-md">
                                <i class="ni ni-chart-bar-32 text-white text-lg"></i>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1">

                                <h6 class="mb-1 text-sm font-semibold text-slate-700 dark:text-white">
                                    Academic Results
                                </h6>

                                <p class="mb-0 text-xs leading-relaxed text-slate-400">
                                    View your academic results and semester performance.
                                </p>

                            </div>

                        </div>

                        {{-- Button --}}
                        <div class="mt-5">

                            <a href="{{ route('student.my-results') }}"
                                class="inline-flex items-center justify-center w-full px-5 py-3 font-bold text-center text-white uppercase transition-all border-0 rounded-lg cursor-pointer bg-gradient-lime text-size-xs hover:scale-[1.01] hover:shadow-soft-md active:opacity-85">

                                My Results

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        @endif

    </div>


    {{-- ============================================================
        PAYMENTS
    ============================================================= --}}
    <div class="flex flex-wrap mt-6 -mx-3">

        <div class="w-full max-w-full px-3 mt-0 lg:w-7/12 lg:flex-none">

            <div
                class="relative flex flex-col w-full min-w-0 mb-0 break-words bg-white border-0 border-transparent border-solid shadow-soft-xl rounded-2xl bg-clip-border">

                {{-- Header --}}
                <div class="p-6 pb-0 mb-0 bg-white rounded-t-2xl">

                    <h6 class="mb-0">
                        Payments
                    </h6>

                </div>


                {{-- Table --}}
                <div class="flex-auto px-0 pt-0 pb-2">

                    <div class="p-0 overflow-x-auto">

                        <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">

                            <thead class="align-bottom">

                                <tr>

                                    <th
                                        class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                        Description
                                    </th>

                                    <th
                                        class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                        Status
                                    </th>

                                    <th
                                        class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b border-gray-200 border-solid shadow-none tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @forelse ($this->transactions as $transaction)

                                    <tr>

                                        {{-- Description --}}
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">

                                            <div class="flex px-2 py-1">

                                                <div class="flex flex-col justify-center">

                                                    <h6 class="mb-0 text-sm leading-normal">
                                                        {{ $transaction->resource }}
                                                    </h6>

                                                    <p class="mb-0 text-xs leading-tight text-slate-400">
                                                        {{ $transaction->RRR }}
                                                    </p>

                                                </div>

                                            </div>

                                        </td>


                                        {{-- Status --}}
                                        <td
                                            class="p-2 text-sm leading-normal text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">

                                            <span
                                                class="px-3.6 text-xs rounded-1.8 py-2.2 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white
                                                {{ $transaction->status === $approved->toString()
                                                    ? 'bg-lime-500'
                                                    : 'bg-yellow-500' }}">

                                                {{ $transaction->status === $approved->toString()
                                                    ? 'success'
                                                    : 'pending' }}

                                            </span>

                                        </td>


                                        {{-- Check Status --}}
                                        <td
                                            class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">

                                            <a href="{{ route('student.payment.status', ['rrr' => $transaction->RRR]) }}"
                                                class="text-xs font-semibold leading-tight text-slate-400 hover:text-lime-500 transition-colors">

                                                Check Status

                                            </a>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="3" class="p-6 text-center">

                                            <div class="flex flex-col items-center justify-center">

                                                <i class="ni ni-credit-card text-2xl text-slate-300 mb-2"></i>

                                                <h6 class="mb-0 text-sm leading-normal">
                                                    No Transaction yet
                                                </h6>

                                                <p class="mt-1 mb-0 text-xs text-slate-400">
                                                    Your payment transactions will appear here.
                                                </p>

                                            </div>

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

