
<div>
    <div class="flex flex-wrap -mx-3">
        <div class="relative z-20 w-full max-w-full px-3 lg:flex-0 shrink-0 lg:w-7/12">
            <div
                class="relative flex flex-col min-w-0 mb-6 break-words bg-transparent border-0 border-solid shadow-none border-black-125 rounded-2xl bg-clip-border">
                <div class="flex-auto p-4">
                    <div class="flex flex-wrap -ml-3">
                        <div class="w-full max-w-full px-3 lg:flex-0 shrink-0 lg:w-6/12">
                            <div class="flex flex-col h-full">
                                <h2 class="mb-0 font-bold dark:text-white">General Statistics</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>




        </div>
    </div>
    <div class="flex flex-wrap mt-6 -mx-3">

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
                            <a href="{{route('profile')}}" type="button" class="inline-block px-8 py-2 mb-0 font-bold text-center uppercase align-middle transition-all bg-transparent border border-solid rounded-lg shadow-none cursor-pointer leading-pro ease-soft-in text-xs hover:scale-102 active:shadow-soft-xs tracking-tight-soft border-teal-500 text-teal-500 hover:border-teal-500 hover:bg-transparent hover:text-fuchsia-500 hover:opacity-75 hover:shadow-none active:bg-fuchsia-500 active:text-white
                            active:hover:bg-transparent active:hover:text-teal-500">Fill Application Form</a>



                      </div>
                </div>
            </div>
        </div>
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
                          <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Author</th>
                          <th class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Function</th>
                          <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Status</th>
                          <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Employed</th>
                          <th class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b border-gray-200 border-solid shadow-none tracking-none whitespace-nowrap text-slate-400 opacity-70"></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                            <div class="flex px-2 py-1">
                              <div>
                                <img src="../assets/img/team-2.jpg" class="inline-flex items-center justify-center mr-4 text-white transition-all duration-200 ease-soft-in-out text-sm h-9 w-9 rounded-xl" alt="user1" />
                              </div>
                              <div class="flex flex-col justify-center">
                                <h6 class="mb-0 leading-normal text-sm">John Michael</h6>
                                <p class="mb-0 leading-tight text-xs text-slate-400">john@creative-tim.com</p>
                              </div>
                            </div>
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                            <p class="mb-0 font-semibold leading-tight text-xs">Manager</p>
                            <p class="mb-0 leading-tight text-xs text-slate-400">Organization</p>
                          </td>
                          <td class="p-2 leading-normal text-center align-middle bg-transparent border-b text-sm whitespace-nowrap shadow-transparent">
                            <span class="bg-gradient-to-tl from-green-600 to-lime-400 px-3.6 text-xs rounded-1.8 py-2.2 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Online</span>
                          </td>
                          <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                            <span class="font-semibold leading-tight text-xs text-slate-400">23/04/18</span>
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                            <a href="javascript:;" class="font-semibold leading-tight text-xs text-slate-400"> Edit </a>
                          </td>
                        </tr>
                        <tr>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                            <div class="flex px-2 py-1">
                              <div>
                                <img src="../assets/img/team-3.jpg" class="inline-flex items-center justify-center mr-4 text-white transition-all duration-200 ease-soft-in-out text-sm h-9 w-9 rounded-xl" alt="user2" />
                              </div>
                              <div class="flex flex-col justify-center">
                                <h6 class="mb-0 leading-normal text-sm">Alexa Liras</h6>
                                <p class="mb-0 leading-tight text-xs text-slate-400">alexa@creative-tim.com</p>
                              </div>
                            </div>
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                            <p class="mb-0 font-semibold leading-tight text-xs">Programator</p>
                            <p class="mb-0 leading-tight text-xs text-slate-400">Developer</p>
                          </td>
                          <td class="p-2 leading-normal text-center align-middle bg-transparent border-b text-sm whitespace-nowrap shadow-transparent">
                            <span class="bg-gradient-to-tl from-slate-600 to-slate-300 px-3.6 text-xs rounded-1.8 py-2.2 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Offline</span>
                          </td>
                          <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                            <span class="font-semibold leading-tight text-xs text-slate-400">11/01/19</span>
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                            <a href="javascript:;" class="font-semibold leading-tight text-xs text-slate-400"> Edit </a>
                          </td>
                        </tr>
                        <tr>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                            <div class="flex px-2 py-1">
                              <div>
                                <img src="../assets/img/team-4.jpg" class="inline-flex items-center justify-center mr-4 text-white transition-all duration-200 ease-soft-in-out text-sm h-9 w-9 rounded-xl" alt="user3" />
                              </div>
                              <div class="flex flex-col justify-center">
                                <h6 class="mb-0 leading-normal text-sm">Laurent Perrier</h6>
                                <p class="mb-0 leading-tight text-xs text-slate-400">laurent@creative-tim.com</p>
                              </div>
                            </div>
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                            <p class="mb-0 font-semibold leading-tight text-xs">Executive</p>
                            <p class="mb-0 leading-tight text-xs text-slate-400">Projects</p>
                          </td>
                          <td class="p-2 leading-normal text-center align-middle bg-transparent border-b text-sm whitespace-nowrap shadow-transparent">
                            <span class="bg-gradient-to-tl from-green-600 to-lime-400 px-3.6 text-xs rounded-1.8 py-2.2 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Online</span>
                          </td>
                          <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                            <span class="font-semibold leading-tight text-xs text-slate-400">19/09/17</span>
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                            <a href="javascript:;" class="font-semibold leading-tight text-xs text-slate-400"> Edit </a>
                          </td>
                        </tr>
                        <tr>
                          <td class="p-2 align-middle bg-transparent border-b-0 whitespace-nowrap shadow-transparent">
                            <div class="flex px-2 py-1">
                              <div>
                                <img src="../assets/img/team-4.jpg" class="inline-flex items-center justify-center mr-4 text-white transition-all duration-200 ease-soft-in-out text-sm h-9 w-9 rounded-xl" alt="user6" />
                              </div>
                              <div class="flex flex-col justify-center">
                                <h6 class="mb-0 leading-normal text-sm">Miriam Eric</h6>
                                <p class="mb-0 leading-tight text-xs text-slate-400">miriam@creative-tim.com</p>
                              </div>
                            </div>
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b-0 whitespace-nowrap shadow-transparent">
                            <p class="mb-0 font-semibold leading-tight text-xs">Programtor</p>
                            <p class="mb-0 leading-tight text-xs text-slate-400">Developer</p>
                          </td>
                          <td class="p-2 leading-normal text-center align-middle bg-transparent border-b-0 text-sm whitespace-nowrap shadow-transparent">
                            <span class="bg-gradient-to-tl from-green-600 to-lime-400 px-3.6 text-xs rounded-1.8 py-2.2 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Online</span>
                          </td>
                          <td class="p-2 text-center align-middle bg-transparent border-b-0 whitespace-nowrap shadow-transparent">
                            <span class="font-semibold leading-tight text-xs text-slate-400">14/09/20</span>
                          </td>
                          <td class="p-2 align-middle bg-transparent border-b-0 whitespace-nowrap shadow-transparent">
                            <a href="javascript:;" class="font-semibold leading-tight text-xs text-slate-400"> Edit </a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

        </div>
    </div>

    <div class="flex flex-wrap -mx-3">
        <div class="w-full max-w-full px-3 flex-0">
            <div id="globe" class="absolute right-0 mt-24 lg-max:overflow-hidden top-1/10 sm:mt-4 lg:mr-24">
                <canvas width="700" height="600"
                    class="w-3/4 -mr-48 h-3/4 lg-max:w-full lg:mt-12 lg:mr-0 lg:w-full"></canvas>
            </div>
        </div>
    </div>
</div>

@push('js')

<script src="{{ asset('assets') }}/js/plugins/threejs.js"></script>

@endpush
