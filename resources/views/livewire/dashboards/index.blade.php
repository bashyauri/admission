<div>
    <div class="flex flex-wrap -mx-3">

         <div class="w-full max-w-full px-3 mt-6 shrink-0 sm:mt-0 sm:flex-0 sm:w-3/12 mb-3">
            <div
                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                <form wire:submit='addStudent'>
                    <div class="relative flex-auto p-4">

                    <div x-data class="relative flex flex-wrap items-stretch w-full rounded-lg">
                            <label class="mt-6 mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                            for="MATRIC NUMBER">Matric Number</label>
                            <input wire:model.blur='form.matricNumber' x-mask="aaaa/aaa/aa/99/9999"  type="text"
                                placeholder="e.g WUFP/PGD/ED/20/0041"
                                class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                            @error('form.matricNumber')
                            <p class="text-red-500 text-size-sm">{{ $message }} </p>
                            @enderror
                    </div>
                    <div class="flex justify-end mt-6 mb-4">

                    <button type="submit"
                        class="inline-block px-6 py-3 m-0 ml-2 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft bg-gradient-fuchsia shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">Save</button>
                </div>
                </form>

                </div>
            </div>

        </div>

        <div class="w-full max-w-full px-3 mt-0 mb-6 lg:mb-0 lg:w-5/12 lg:flex-none">
            <div
                class="relative z-20 flex flex-col min-w-0 break-words bg-white border-0 border-solid dark:bg-gray-950 border-black-125 shadow-soft-xl dark:shadow-soft-dark-xl rounded-2xl bg-clip-border">
                <div class="flex-auto p-4">






                            <h5>Name: {{auth()->user()->surname .' ' . auth()->user()->firstname .' ' . auth()->user()->m_name}}</h5>

                            @php
                                $department_id = auth()->user()->proposedCourse?->department_id;
                                $course_id = auth()->user()->proposedCourse?->course_id;
                            @endphp
                          <p class="mb-4 text-sm leading-normal">Department: {{App\Models\Department::find($department_id)->name ?? 'Not Selected'}}</p>
                          <p class="mb-4 text-sm leading-normal">Course: {{App\Models\Course::find($course_id)->name ?? 'Not Selected'}}</p>
                           <p class="mb-4 text-sm leading-normal">Status: {{auth()->user()->proposedCourse->status ?? 'Pending'}}</p>
                          <div class="flex items-center justify-between">
                            <a href="{{route('profile')}}" type="button" class="inline-block px-8 py-2 mb-0 text-xs font-bold text-center text-teal-500 uppercase align-middle transition-all bg-transparent border border-teal-500 border-solid rounded-lg shadow-none cursor-pointer leading-pro ease-soft-in hover:scale-102 active:shadow-soft-xs tracking-tight-soft hover:border-teal-500 hover:bg-transparent hover:text-teal-700 hover:opacity-75 hover:shadow-none active:bg-teal-500 active:text-white active:hover:bg-transparent active:hover:text-teal-500">Application Area</a>



                      </div>
                </div>
            </div>
        </div>
        @can('generateAcceptanceInvoice', App\Models\User::class)
            <div class="w-full max-w-full px-3 shrink-0 sm:flex-0 sm:w-4/12">
            <div
                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="relative flex-auto p-4">
                    <div class="flex flex-wrap -mx-3 ">
                        <div class="w-7/12 max-w-full px-3 text-left flex-0">
                            <p class="mb-1 font-semibold leading-normal capitalize text-size-sm">Generate Invoice</p>
                            <h5 class="mb-0 font-bold dark:text-white">Acceptance Fee</h5>
                            <span class="mt-auto mb-0 font-bold leading-normal text-right text-lime-500 text-size-sm">
                            @if (Auth::user()->hasPaid(config('remita.acceptance.description')))
                                 <a href="{{route('print-acceptance')}}"
                                   class="inline-block px-8 py-2 mt-2 mb-0 text-xs font-bold text-center text-teal-500 uppercase align-middle transition-all bg-transparent border border-teal-500 border-solid rounded-lg shadow-none cursor-pointer leading-pro ease-soft-in hover:scale-102 active:shadow-soft-xs tracking-tight-soft hover:border-teal-500 hover:bg-transparent hover:text-teal-700 hover:opacity-75 hover:shadow-none active:bg-teal-500 active:text-white active:hover:bg-transparent active:hover:text-teal-500">Print Acceptance</a>
                            @else
                             <a href="{{route('acceptance-invoice')}}"
                                    class="inline-block px-6 py-3 mt-4 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-slate-500">Generate Invoice</a>

                            @endif

                            </span>
                        </div>
                        <div class="w-5/12 max-w-full px-3 flex-0">
                            <div class="relative text-right">
                                <a href="javascript:;" class="cursor-pointer" dropdown-trigger aria-expanded="false">
                                    <span class="leading-tight text-size-xs text-slate-400"></span>
                                </a>
                                <p class="hidden transform-dropdown-show"></p>
                                <ul dropdown-menu
                                    class="dark:shadow-soft-dark-xl z-100 dark:bg-gray-950 text-size-sm top-1 lg:shadow-soft-3xl duration-250 before:duration-350 before:font-awesome before:ease-soft min-w-44 before:text-5.5 transform-dropdown pointer-events-none absolute right-0 left-auto m-0 -mr-4 mt-2 list-none rounded-lg border-0 border-solid border-transparent bg-white bg-clip-padding px-0 py-2 text-left text-slate-500 opacity-0 transition-all before:absolute before:right-7 before:left-auto before:top-0 before:z-40 before:text-white before:transition-all before:content-['\f0d8']">
                                    <li>
                                        <a class="py-1.2 lg:ease-soft clear-both block w-full whitespace-nowrap rounded-lg border-0 bg-transparent px-4 text-left font-normal text-slate-500 hover:bg-gray-200 hover:text-slate-700 dark:text-white dark:hover:bg-gray-200/80 dark:hover:text-slate-700 lg:transition-colors lg:duration-300"
                                            href="javascript:;">Yesterday</a>
                                    </li>
                                    <li>
                                        <a class="py-1.2 lg:ease-soft clear-both block w-full whitespace-nowrap rounded-lg border-0 bg-transparent px-4 text-left font-normal text-slate-500 hover:bg-gray-200 hover:text-slate-700 dark:text-white dark:hover:bg-gray-200/80 dark:hover:text-slate-700 lg:transition-colors lg:duration-300"
                                            href="javascript:;">Last 7 days</a>
                                    </li>
                                    <li>
                                        <a class="py-1.2 lg:ease-soft clear-both block w-full whitespace-nowrap rounded-lg border-0 bg-transparent px-4 text-left font-normal text-slate-500 hover:bg-gray-200 hover:text-slate-700 dark:text-white dark:hover:bg-gray-200/80 dark:hover:text-slate-700 lg:transition-colors lg:duration-300"
                                            href="javascript:;">Last 30 days</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        @endcan



    </div>

    <div class="flex flex-wrap mt-6 -mx-3">
        <div class="w-full max-w-full px-3 mt-0 lg:w-7/12 lg:flex-none">
            <div class="relative flex flex-col w-full min-w-0 mb-0 break-words bg-white border-0 border-transparent border-solid shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="p-6 pb-0 mb-0 bg-white rounded-t-2xl">
                  <h6>Payments</h6>
                </div>
                <div class="flex-auto px-0 pt-0 pb-2">
                  <div class="p-0 overflow-x-auto">
                    <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                      <thead class="align-bottom">
                        <tr>
                          <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Description</th>
                          <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Status</th>

                          <th class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b border-gray-200 border-solid shadow-none tracking-none whitespace-nowrap text-slate-400 opacity-70"></th>
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

                            <td class="p-2 text-sm leading-normal text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                <span class="px-3.6 text-xs rounded-1.8 py-2.2 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white
                                {{ $transaction->status === '00' ? 'bg-gradient-to-tl from-green-600 to-lime-400' : 'bg-yellow-500' }}">
                                {{ $transaction->status === '00' ? 'success' : 'pending' }}
                            </span>

                            </td>

                            <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                              <a href="{{route('payment.status',['rrr'=>$transaction->RRR])}}" class="text-xs font-semibold leading-tight text-slate-400"> check status</a>
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


