<!-- sidenav  -->
@if (in_array(request()->route()->getName(), getCategoriesArray('dashboards', 'virtual-reality')))
    <aside mini="false"
        class="dark:bg-gray-950 xl:animate-fade-up xl:scale-60 ease-soft-in-out z-990 max-w-64 xl:shadow-soft-xl fixed inset-y-0 left-0 xl:ml-4 block w-full -translate-x-full flex-wrap items-center justify-between overflow-y-auto rounded-2xl border-0 bg-white p-0 shadow-none transition-all duration-200 xl:left-[18%] xl:mt-6 xl:translate-x-0 xl:bg-white"
        id="sidenav-main">
@else
    <aside mini="false"
        class="fixed inset-y-0 left-0 flex-wrap items-center justify-between block w-full p-0 my-4 overflow-y-auto transition-all duration-200 -translate-x-full bg-white border-0 shadow-none xl:ml-4 dark:bg-gray-950 ease-soft-in-out z-990 max-w-64 rounded-2xl xl:translate-x-0 xl:bg-transparent"
        id="sidenav-main">
@endif

    <!-- header -->
    <div class="h-20">
        <i class="absolute top-0 right-0 p-4 opacity-50 cursor-pointer fas fa-times text-slate-400 dark:text-white xl:hidden"
            aria-hidden="true" sidenav-close-btn></i>
        <a class="block px-8 py-6 m-0 text-size-sm whitespace-nowrap text-slate-700 dark:text-white"
            href="{{ env('APP_URL') }}">
            <img src="{{ asset('assets') }}/img/logo-ct.png"
                class="inline-block h-full max-w-full transition-all duration-200 ease-soft-in-out max-h-8 dark:hidden"
                alt="main_logo" />
            <span class="ml-1 font-semibold transition-all duration-200 ease-soft-in-out">{{auth()->user()->firstname}},</span>
        </a>
    </div>
    <hr class="h-px mt-0 bg-transparent bg-gradient-horizontal-dark dark:bg-gradient-horizontal-light" />

    <div class="items-center block w-full h-auto grow basis-full" id="sidenav-collapse-main">
        <ul class="flex flex-col pl-0 mb-0 list-none">

            <!-- Exam Officer Dashboard -->
            <li class="mt-0.5 w-full">
                <a class="ease-soft-in-out py-2.7 text-size-sm my-0 mx-4 flex items-center whitespace-nowrap px-4 {{ Route::currentRouteName() == 'exam-officer.dashboard' ? 'font-semibold text-slate-700 xl:shadow-soft-xl rounded-lg bg-white' : 'font-medium text-slate-500 shadow-none' }} transition-colors dark:text-white dark:opacity-80"
                    href="{{ route('exam-officer.dashboard') }}">
                    <div class="stroke-none mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center fill-current p-2.5 text-center text-black {{ Route::currentRouteName() == 'exam-officer.dashboard' ? 'shadow-soft-sm bg-gradient-fuchsia' : 'shadow-soft-2xl' }}">
                        {{-- Shield / Exam Officer icon --}}
                        <svg width="12px" height="12px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <path class="{{ Route::currentRouteName() == 'exam-officer.dashboard' ? '' : 'fill-slate-800' }}" d="M20,2 L4,9 L4,20 C4,29.4 11.2,38.2 20,40 C28.8,38.2 36,29.4 36,20 L36,9 L20,2 Z" opacity="0.6"/>
                                <path class="{{ Route::currentRouteName() == 'exam-officer.dashboard' ? '' : 'fill-slate-800' }}" d="M20,6 L7,12 L7,20 C7,27.8 12.8,35 20,37 C27.2,35 33,27.8 33,20 L33,12 L20,6 Z"/>
                                <polyline points="13,20 18,25 28,15" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            </g>
                        </svg>
                    </div>
                    <span class="ml-1 duration-300 opacity-100 pointer-events-none ease-soft">Dashboard</span>
                </a>
            </li>

            <!-- Result Auditing & Release -->
            <li class="mt-0.5 w-full">
                <a class="ease-soft-in-out py-2.7 text-size-sm my-0 mx-4 flex items-center whitespace-nowrap px-4 {{ Route::currentRouteName() == 'exam-officer.results-review' ? 'font-semibold text-slate-700 xl:shadow-soft-xl rounded-lg bg-white' : 'font-medium text-slate-500 shadow-none' }} transition-colors dark:text-white dark:opacity-80"
                    href="{{ route('exam-officer.results-review') }}">
                    <div class="stroke-none mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center fill-current p-2.5 text-center text-black {{ Route::currentRouteName() == 'exam-officer.results-review' ? 'shadow-soft-sm bg-gradient-fuchsia' : 'shadow-soft-2xl' }}">
                        {{-- Checklist / Document icon --}}
                        <svg width="12px" height="12px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect class="{{ Route::currentRouteName() == 'exam-officer.results-review' ? '' : 'fill-slate-800' }}" x="4" y="2" width="32" height="36" rx="3" opacity="0.6"/>
                                <line x1="10" y1="12" x2="30" y2="12" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>
                                <line x1="10" y1="20" x2="30" y2="20" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>
                                <line x1="10" y1="28" x2="22" y2="28" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round"/>
                            </g>
                        </svg>
                    </div>
                    <span class="ml-1 duration-300 opacity-100 pointer-events-none ease-soft">Result Auditing & Release</span>
                </a>
            </li>

            <!-- Roles Switcher (only shown if user has extra capabilities) -->
            @if(auth()->user()->canActAsAdmin() || auth()->user()->canActAsCit() || auth()->user()->canActAsHod() || auth()->user()->canActAsLecturer())
            <li class="w-full mt-4">
                <h6 class="pl-6 ml-2 font-bold leading-tight uppercase text-size-xs opacity-60 dark:text-white">Roles</h6>
            </li>
            @endif

            @if(auth()->user()->canActAsAdmin())
            <li class="mt-0.5 w-full">
                <a class="ease-soft-in-out py-2.7 text-size-sm my-0 mx-4 flex items-center whitespace-nowrap px-4 font-medium text-slate-500 shadow-none transition-colors dark:text-white dark:opacity-80 hover:bg-slate-100 rounded-lg"
                    href="{{ route('admin.dashboard') }}">
                    <div class="stroke-none mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center fill-current p-2.5 text-center text-black shadow-soft-2xl">
                        <svg width="12px" height="12px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <circle class="fill-slate-800" cx="20" cy="12" r="8"/>
                                <path class="fill-slate-800" d="M6,36 C6,28 12,24 20,24 C28,24 34,28 34,36 L6,36 Z"/>
                            </g>
                        </svg>
                    </div>
                    <span class="ml-1 duration-300 opacity-100 pointer-events-none ease-soft">Switch to Admin</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->canActAsCit())
            <li class="mt-0.5 w-full">
                <a class="ease-soft-in-out py-2.7 text-size-sm my-0 mx-4 flex items-center whitespace-nowrap px-4 font-medium text-slate-500 shadow-none transition-colors dark:text-white dark:opacity-80 hover:bg-slate-100 rounded-lg"
                    href="{{ route('cit.dashboard') }}">
                    <div class="stroke-none mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center fill-current p-2.5 text-center text-black shadow-soft-2xl">
                        {{-- Computer / CIT icon --}}
                        <svg width="12px" height="12px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect class="fill-slate-800" x="4" y="6" width="32" height="20" rx="3"/>
                                <rect fill="#FFFFFF" x="7" y="9" width="26" height="14" rx="1"/>
                                <path class="fill-slate-800" d="M14,26 L26,26 L28,32 L12,32 Z"/>
                                <rect class="fill-slate-800" x="10" y="32" width="20" height="3" rx="1"/>
                            </g>
                        </svg>
                    </div>
                    <span class="ml-1 duration-300 opacity-100 pointer-events-none ease-soft">Switch to CIT</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->canActAsHod())
            <li class="mt-0.5 w-full">
                <a class="ease-soft-in-out py-2.7 text-size-sm my-0 mx-4 flex items-center whitespace-nowrap px-4 font-medium text-slate-500 shadow-none transition-colors dark:text-white dark:opacity-80 hover:bg-slate-100 rounded-lg"
                    href="{{ route('hod.dashboard') }}">
                    <div class="stroke-none mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center fill-current p-2.5 text-center text-black shadow-soft-2xl">
                        {{-- Org chart / HOD icon --}}
                        <svg width="12px" height="12px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect class="fill-slate-800" x="14" y="2" width="12" height="8" rx="2"/>
                                <rect class="fill-slate-800" x="2" y="18" width="10" height="8" rx="2" opacity="0.6"/>
                                <rect class="fill-slate-800" x="15" y="18" width="10" height="8" rx="2" opacity="0.6"/>
                                <rect class="fill-slate-800" x="28" y="18" width="10" height="8" rx="2" opacity="0.6"/>
                                <line x1="20" y1="10" x2="20" y2="18" stroke="#8392AB" stroke-width="1.5"/>
                                <line x1="7" y1="18" x2="33" y2="18" stroke="#8392AB" stroke-width="1.5"/>
                                <line x1="7" y1="18" x2="7" y2="14" stroke="#8392AB" stroke-width="1.5"/>
                                <line x1="33" y1="18" x2="33" y2="14" stroke="#8392AB" stroke-width="1.5"/>
                            </g>
                        </svg>
                    </div>
                    <span class="ml-1 duration-300 opacity-100 pointer-events-none ease-soft">Switch to HOD</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->canActAsLecturer())
            <li class="mt-0.5 w-full">
                <a class="ease-soft-in-out py-2.7 text-size-sm my-0 mx-4 flex items-center whitespace-nowrap px-4 font-medium text-slate-500 shadow-none transition-colors dark:text-white dark:opacity-80 hover:bg-slate-100 rounded-lg"
                    href="{{ route('lecturer.dashboard') }}">
                    <div class="stroke-none mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center fill-current p-2.5 text-center text-black shadow-soft-2xl">
                        {{-- Chalkboard / Teaching icon --}}
                        <svg width="12px" height="12px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect class="fill-slate-800" x="0" y="0" width="40" height="28" rx="2"/>
                                <rect fill="#FFFFFF" x="2" y="2" width="36" height="24" rx="1"/>
                                <rect class="fill-slate-800" x="17" y="28" width="6" height="6"/>
                                <rect class="fill-slate-800" x="10" y="34" width="20" height="3" rx="1"/>
                                <line x1="8" y1="10" x2="32" y2="10" stroke="#8392AB" stroke-width="2" stroke-linecap="round"/>
                                <line x1="8" y1="16" x2="24" y2="16" stroke="#8392AB" stroke-width="2" stroke-linecap="round"/>
                            </g>
                        </svg>
                    </div>
                    <span class="ml-1 duration-300 opacity-100 pointer-events-none ease-soft">Switch to Lecturer</span>
                </a>
            </li>
            @endif

        </ul>
    </div>
</aside>
