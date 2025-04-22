@php
  use App\Enums\TransactionStatus;
  $approved = TransactionStatus::APPROVED;
  $pending = TransactionStatus::PENDING;
@endphp

<div>
  @livewire('student.student-profile')


  <div class="flex flex-wrap -mx-3">

    @include('flash-messages')



    <div class="w-full max-w-full px-3 shrink-0 sm:flex-0 sm:w-4/12 mb-2">
      <div
        class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
        <div class="relative flex-auto p-4">
          <div class="flex flex-wrap -mx-3 ">
            <div class="w-7/12 max-w-full px-3 text-left flex-0">
              <p class="mb-1 font-semibold leading-normal capitalize text-size-sm">Generate Payment</p>
              <span class="mt-auto mb-0 font-bold leading-normal text-right text-lime-500 text-size-sm">

                @if (auth()->user()->isUndergraduate())

          <a href="{{ route('student.ug-school-fees', ['user' => auth()->user()->id]) }}"
            class="inline-flex items-center px-6 py-3 mt-4 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-lime leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-white">

            <!-- Icon positioned left -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-5 h-5 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
            </svg>

            Pay School Fees
          </a>
        @else
      <a href="{{route('student.school-fees-invoice')}}"
        class="inline-flex items-center px-6 py-3 mt-4 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-lime leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-white">
        <!-- Icon positioned left -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
        stroke="currentColor" class="w-5 h-5 mr-2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
        </svg>

        Generate Invoice
      </a>
    @endif
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="w-full max-w-full px-3 shrink-0 sm:flex-0 sm:w-4/12 mb-2">
      <div
        class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
        <div class="relative flex-auto p-4">
          <div class="flex flex-wrap -mx-3 ">
            <div class="w-7/12 max-w-full px-3 text-left flex-0">
              <p class="mb-1 font-semibold leading-normal capitalize text-size-sm">Manage Courses</p>
              <span class="mt-auto mb-0 font-bold leading-normal text-right text-lime-500 text-size-sm">
                <a href="{{route('student.course-registration')}}"
                  class="inline-flex items-center px-6 py-3 mt-4 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-lime leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-white">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="h-5 w-5 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                  </svg>


                  Add/drop
                </a>
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
            <div class="w-6/12 max-w-full px-3 text-left flex-0">
              <p class="mb-1 font-semibold leading-normal capitalize text-size-sm">Generate Exam Card</p>
              <span class="mt-auto mb-0 font-bold leading-normal text-right text-lime-500 text-size-sm">
                <a href="{{route('student.exam-card')}}"
                  class="inline-flex items-center px-6 py-3 mt-4 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-lime leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-white">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                  </svg>


                  Exam Card
                </a>
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