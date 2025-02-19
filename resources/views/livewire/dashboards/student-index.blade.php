@php
  use App\Enums\TransactionStatus;
  $approved = TransactionStatus::APPROVED;
  $pending = TransactionStatus::PENDING;
@endphp

<div>
  @livewire('student.student-profile')


  <div class="flex flex-wrap -mx-3">

    @include('flash-messages')




    <div class="w-full max-w-full px-3 shrink-0 sm:flex-0 sm:w-4/12">
      <div
        class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
        <div class="relative flex-auto p-4">
          <div class="flex flex-wrap -mx-3 ">
            <div class="w-7/12 max-w-full px-3 text-left flex-0">
              <p class="mb-1 font-semibold leading-normal capitalize text-size-sm">Generate Payment</p>

              <span class="mt-auto mb-0 font-bold leading-normal text-right text-lime-500 text-size-sm">
                @if (auth()->user()->isUndergraduate())
          <a href="{{route('student.ug-school-fees', ['user' => auth()->user()->id])}}"
            class="inline-block px-6 py-3 mt-4 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-lime leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-white">Pay
            School Fees</a>
        @else
      <a href="{{route('student.school-fees-invoice')}}"
        class="inline-block px-6 py-3 mt-4 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-lime leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-white">Generate
        Invoice</a>
    @endif


              </span>
            </div>


          </div>
        </div>
      </div>
    </div>
    <div class="w-full max-w-full px-3 shrink-0 sm:flex-0 sm:w-4/12">
      <div
        class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
        <div class="relative flex-auto p-4">
          <div class="flex flex-wrap -mx-3 ">
            <div class="w-7/12 max-w-full px-3 text-left flex-0">
              <p class="mb-1 font-semibold leading-normal capitalize text-size-sm">Manage Courses</p>

              <span class="mt-auto mb-0 font-bold leading-normal text-right text-lime-500 text-size-sm">

                <a href="{{route('student.course-registration')}}"
                  class="inline-block px-6 py-3 mt-4 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-lime leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-white">Add/drop</a>



              </span>
            </div>


          </div>
        </div>
      </div>
    </div>



  </div>

  <div class="flex flex-wrap mt-6 -mx-3">
    <div class="w-full max-w-full px-3 mt-0 lg:w-7/12 lg:flex-none">
      <div
        class="relative flex flex-col w-full min-w-0 mb-0 break-words bg-white border-0 border-transparent border-solid shadow-soft-xl rounded-2xl bg-clip-border">
        <div class="p-6 pb-0 mb-0 bg-white rounded-t-2xl">
          <h6>Payments </h6>
        </div>
        <div class="flex-auto px-0 pt-0 pb-2">
          <div class="p-0 overflow-x-auto">
            <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
              <thead class="align-bottom">
                <tr>
                  <th
                    class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                    Description</th>
                  <th
                    class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">
                    Status</th>

                  <th
                    class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b border-gray-200 border-solid shadow-none tracking-none whitespace-nowrap text-slate-400 opacity-70">
                  </th>
                </tr>
              </thead>
              <tbody>
                @forelse ($this->transactions as $transaction)
              <tr>
                <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                <div class="flex px-2 py-1">

                  <div class="flex flex-col justify-center">
                  <h6 class="mb-0 text-sm leading-normal">{{$transaction->resource}}</h6>
                  <p class="mb-0 text-xs leading-tight text-slate-400">{{$transaction->RRR}}</p>
                  </div>
                </div>
                </td>

                <td
                class="p-2 text-sm leading-normal text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">

                <span
                  class="px-3.6 text-xs rounded-1.8 py-2.2 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white
            {{ $transaction->status === $approved->toString() ? 'bg-gradient-to-tl from-green-600 to-lime-400' : 'bg-yellow-500' }}">
                  {{ $transaction->status === $approved->toString() ? 'success' : 'pending' }}
                </span>

                </td>

                <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                <a href="{{route('student.payment.status', ['rrr' => $transaction->RRR])}}"
                  class="text-xs font-semibold leading-tight text-slate-400"> check status</a>
                </td>

              </tr>

        @empty
      <div class="flex flex-col justify-center">
        <h6 class="mb-0 text-sm leading-normal">No Transaction yet</h6>

      </div>

    @endforelse

              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>

</div>