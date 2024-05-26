<div>
    <div class="flex flex-wrap -mx-3">



        <div class="w-full max-w-full px-3 mt-0 mb-6 lg:mb-0 lg:w-5/12 lg:flex-none">
            <div
                class="relative z-20 flex flex-col min-w-0 break-words bg-white border-0 border-solid dark:bg-gray-950 border-black-125 shadow-soft-xl dark:shadow-soft-dark-xl rounded-2xl bg-clip-border">
                <div class="flex-auto p-4">






                            <h5>Name: {{auth()->user()->surname .' ' . auth()->user()->firstname .' ' . auth()->user()->m_name}}</h5>

                            @php
                                $department_id = auth()->user()->proposedCourse?->department_id;
                                $course_id = auth()->user()->proposedCourse?->course_id;
                            @endphp
                          <p class="mb-6 leading-normal text-sm">Department: {{App\Models\Department::find($department_id)->name}}</p>
                          <p class="mb-6 leading-normal text-sm">Course: {{App\Models\Course::find($course_id)->name}}</p>
                          <div class="flex items-center justify-between">
                            <a href="{{route('profile')}}" type="button" class="inline-block px-8 py-2 mb-0 font-bold text-center uppercase align-middle transition-all bg-transparent border border-solid rounded-lg shadow-none cursor-pointer leading-pro ease-soft-in text-xs hover:scale-102 active:shadow-soft-xs tracking-tight-soft border-teal-500 text-teal-500 hover:border-teal-500 hover:bg-transparent hover:text-teal-700 hover:opacity-75 hover:shadow-none active:bg-teal-500 active:text-white
                            active:hover:bg-transparent active:hover:text-teal-500">Fill Application Form</a>



                      </div>
                </div>
            </div>
        </div>


        {{-- <div class="w-full max-w-full px-3 mt-6 shrink-0 sm:mt-0 sm:flex-0 sm:w-4/12">
            <div
                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="relative flex-auto p-4">
                    <div class="flex flex-wrap -mx-3 ">
                        <div class="w-7/12 max-w-full px-3 text-left flex-0">
                            <p class="mb-1 font-semibold leading-normal capitalize text-size-sm">Customers</p>
                            <h5 class="mb-0 font-bold dark:text-white">3.200</h5>
                            <span class="mt-auto mb-0 font-bold leading-normal text-right text-lime-500 text-size-sm">
                                +12%
                                <span class="font-normal text-slate-400">since last month</span>
                            </span>
                        </div>
                        <div class="w-5/12 max-w-full px-3 flex-0">
                            <div class="relative text-right">
                                <a href="javascript:;" class="cursor-pointer" dropdown-trigger aria-expanded="false">
                                    <span class="leading-tight text-size-xs text-slate-400">6 May - 7 May</span>
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
        </div> --}}
        {{-- <div class="w-full max-w-full px-3 mt-6 shrink-0 sm:mt-0 sm:flex-0 sm:w-4/12">
            <div
                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="relative flex-auto p-4">
                    <div class="flex flex-wrap -mx-3 ">
                        <div class="w-7/12 max-w-full px-3 text-left flex-0">
                            <p class="mb-1 font-semibold leading-normal capitalize text-size-sm">Avg. Revenue</p>
                            <h5 class="mb-0 font-bold dark:text-white">$1.200</h5>
                            <span class="mt-auto mb-0 font-bold leading-normal text-right text-slate-400 text-size-sm">
                                +$231
                                <span class="font-normal text-slate-400">since last month</span>
                            </span>
                        </div>
                        <div class="w-5/12 max-w-full px-3 flex-0">
                            <div class="relative text-right">
                                <a href="javascript:;" class="cursor-pointer" dropdown-trigger aria-expanded="false">
                                    <span class="leading-tight text-size-xs text-slate-400">6 May - 7 May</span>
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

        </div> --}}
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
                                  <h6 class="mb-0 leading-normal text-sm">{{$transaction->resource}}</h6>
                                  <p class="mb-0 leading-tight text-xs text-slate-400">{{$transaction->RRR}}</p>
                                </div>
                              </div>
                            </td>

                            <td class="p-2 leading-normal text-center align-middle bg-transparent border-b text-sm whitespace-nowrap shadow-transparent">
                                <span class="px-3.6 text-xs rounded-1.8 py-2.2 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white
                                {{ $transaction->status === '00' ? 'bg-gradient-to-tl from-green-600 to-lime-400' : 'bg-yellow-500' }}">
                                {{ $transaction->status === '00' ? 'success' : 'pending' }}
                            </span>

                            </td>

                            <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                              <a href="{{route('payment.status',['rrr'=>$transaction->RRR])}}" class="font-semibold leading-tight text-xs text-slate-400"> check status</a>
                            </td>

                          </tr>

                        @empty
                        <div class="flex flex-col justify-center">
                            <h6 class="mb-0 leading-normal text-sm">No Transaction yet</h6>

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


