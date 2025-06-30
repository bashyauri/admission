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
            <!-- x i -->
            <i class="absolute top-0 right-0 p-4 opacity-50 cursor-pointer fas fa-times text-slate-400 dark:text-white xl:hidden"
                aria-hidden="true" sidenav-close-btn></i>

            <a class="block px-8 py-6 m-0 text-size-sm whitespace-nowrap text-slate-700 dark:text-white"
                href="{{ env('APP_URL') }}">
                {{-- target="_blank" --}}
                <img src="{{ asset('assets') }}/img/logo-ct.png"
                    class="inline-block h-full max-w-full transition-all duration-200 ease-soft-in-out max-h-8 dark:hidden"
                    alt="main_logo" />
                <img src="{{ asset('assets') }}/img/logo-ct.png"
                    class="hidden h-full max-w-full transition-all duration-200 ease-soft-in-out max-h-8 dark:inline-block"
                    alt="main_logo" />
                <span
                    class="ml-1 font-semibold transition-all duration-200 ease-soft-in-out">{{auth()->user()->firstname}},</span>
                {{-- <span class="ml-1 font-semibold transition-all duration-200 ease-soft-in-out">Wufpbk DHS</span>
                --}}
            </a>

        </div>

        <!-- //---------hr----------// -->

        <hr class="h-px mt-0 bg-transparent bg-gradient-horizontal-dark dark:bg-gradient-horizontal-light" />

        <div class="items-center block w-full h-auto grow basis-full" id="sidenav-collapse-main">
            <!-- primary list  -->

            <ul class="flex flex-col pl-0 mb-0 list-none">
                <!-- primary list item -->

                <li class="mt-0.5 w-full">
                    <!-- primary anchor  -->
                    <a {{ in_array_r(request()->route()->getName(), getCategoriesArray('dashboards')) ? 'active_primary
            aria-expanded=true' :
    'aria-expanded=false' }} collapse_trigger="primary" href="javascript:;"
                        class="ease-soft-in-out
            text-size-sm
            py-2.7 active after:ease-soft-in-out after:font-awesome-5-free my-0 mx-4 flex items-center whitespace-nowrap
            px-4 {{ in_array_r(request()->route()->getName(), getCategoriesArray('dashboards')) ? 'font-semibold
            text-slate-700
            xl:shadow-soft-xl rounded-lg bg-white transition-all after:text-slate-800 after:rotate-180' : 'font-medium
            text-slate-500 shadow-none transition-colors after:text-slate-800/50 dark:after:text-white/50
            dark:after:text-white' }} after:ml-auto after:inline-block after:font-bold after:antialiased
            after:transition-all after:duration-200 after:content-['\f107'] dark:text-white dark:opacity-80">

                        <!-- big anchor expandable -->
                        <div
                            class="stroke-none mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center fill-current p-2.5 text-center text-black {{ in_array_r(request()->route()->getName(), getCategoriesArray('dashboards')) ? 'shadow-soft-sm bg-gradient-fuchsia' : 'shadow-soft-2xl' }}">

                            <!-- icon -->

                            <svg width="12px" height="12px" viewBox="0 0 45 40" version="1.1"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <title>shop</title>
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g transform="translate(-1716.000000, -439.000000)" fill="#FFFFFF"
                                        fill-rule="nonzero">
                                        <g transform="translate(1716.000000, 291.000000)">
                                            <g transform="translate(0.000000, 148.000000)">
                                                <path
                                                    class="{{ in_array_r(request()->route()->getName(), getCategoriesArray('dashboards')) ? '' : 'fill-slate-800' }}"
                                                    d="M46.7199583,10.7414583 L40.8449583,0.949791667 C40.4909749,0.360605034 39.8540131,0 39.1666667,0 L7.83333333,0 C7.1459869,0 6.50902508,0.360605034 6.15504167,0.949791667 L0.280041667,10.7414583 C0.0969176761,11.0460037 -1.23209662e-05,11.3946378 -1.23209662e-05,11.75 C-0.00758042603,16.0663731 3.48367543,19.5725301 7.80004167,19.5833333 L7.81570833,19.5833333 C9.75003686,19.5882688 11.6168794,18.8726691 13.0522917,17.5760417 C16.0171492,20.2556967 20.5292675,20.2556967 23.494125,17.5760417 C26.4604562,20.2616016 30.9794188,20.2616016 33.94575,17.5760417 C36.2421905,19.6477597 39.5441143,20.1708521 42.3684437,18.9103691 C45.1927731,17.649886 47.0084685,14.8428276 47.0000295,11.75 C47.0000295,11.3946378 46.9030823,11.0460037 46.7199583,10.7414583 Z"
                                                    opacity="0.598981585"></path>
                                                <path
                                                    class="{{ in_array_r(request()->route()->getName(), getCategoriesArray('dashboards')) ? '' : 'fill-slate-800' }}"
                                                    d="M39.198,22.4912623 C37.3776246,22.4928106 35.5817531,22.0149171 33.951625,21.0951667 L33.92225,21.1107282 C31.1430221,22.6838032 27.9255001,22.9318916 24.9844167,21.7998837 C24.4750389,21.605469 23.9777983,21.3722567 23.4960833,21.1018359 L23.4745417,21.1129513 C20.6961809,22.6871153 17.4786145,22.9344611 14.5386667,21.7998837 C14.029926,21.6054643 13.533337,21.3722507 13.0522917,21.1018359 C11.4250962,22.0190609 9.63246555,22.4947009 7.81570833,22.4912623 C7.16510551,22.4842162 6.51607673,22.4173045 5.875,22.2911849 L5.875,44.7220845 C5.875,45.9498589 6.7517757,46.9451667 7.83333333,46.9451667 L19.5833333,46.9451667 L19.5833333,33.6066734 L27.4166667,33.6066734 L27.4166667,46.9451667 L39.1666667,46.9451667 C40.2482243,46.9451667 41.125,45.9498589 41.125,44.7220845 L41.125,22.2822926 C40.4887822,22.4116582 39.8442868,22.4815492 39.198,22.4912623 Z">
                                                </path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </div>

                        <!-- primary span -->

                        <span
                            class="ml-1 duration-300 opacity-100 pointer-events-none ease-soft {{ in_array_r(request()->route()->getName(), getCategoriesArray('dashboards')) ? 'text-slate-700' : '' }}">Dashboards</span>
                    </a>

                    <div class="h-auto overflow-hidden transition-all duration-200 ease-soft-in-out max-h-0"
                        id="dashboardsExamples">
                        <!-- primary collapsable list -->
                        <ul
                            class="flex flex-wrap pl-4 mb-0 ml-6 list-none transition-all duration-200 ease-soft-in-out">
                            <!-- medium list item  -->

                            <li class="w-full">
                                <a class="ease-soft-in-out py-1.6 ml-5.4 pl-4 text-size-sm relative my-0 mr-4 flex items-center whitespace-nowrap bg-transparent pr-4 shadow-none transition-colors before:absolute before:top-1/2 before:-translate-y-1/2 before:rounded-3xl before:content-[''] dark:text-white dark:before:bg-white dark:before:opacity-80 {{ Route::currentRouteName() == 'analytics' ? 'before:-left-5 rounded-lg font-semibold text-slate-800 before:h-2 before:w-2 before:bg-slate-800 dark:opacity-100' : 'before:-left-4.5 before:h-1.25 before:w-1.25 font-medium text-slate-800/50 before:bg-slate-800/50 dark:opacity-60' }}"
                                    href="{{ route('student.dashboard') }}">
                                    <span
                                        class="w-0 text-center transition-all duration-200 opacity-0 pointer-events-none ease-soft-in-out">
                                        D
                                    </span>
                                    <span class="transition-all duration-100 pointer-events-none ease-soft"> Dashboard
                                    </span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>



                {{-- <li class="w-full mt-4">
                    <h6 class="pl-6 ml-2 font-bold leading-tight uppercase text-size-xs opacity-60 dark:text-white">
                        ADMISSION</h6>
                </li> --}}
                <!-- Transactions -->

                {{-- <li class="mt-0.5 w-full">
                    <a {{ in_array_r(request()->route()->getName(), getCategoriesArray('remita-transactions')) ?
                        'active_primary
                        aria-expanded=true' :
                        'aria-expanded=false' }} collapse_trigger="primary"
                        href="javascript:;" class="ease-soft-in-out
                        text-size-sm
                        py-2.7 active after:ease-soft-in-out after:font-awesome-5-free my-0 mx-4 flex items-center
                        whitespace-nowrap
                        px-4 {{ in_array_r(request()->route()->getName(), getCategoriesArray('remita-transactions')) ?
                        'font-semibold
                        text-slate-700
                        xl:shadow-soft-xl rounded-lg bg-white transition-all after:text-slate-800 after:rotate-180' :
                        'font-medium
                        text-slate-500 shadow-none transition-colors after:text-slate-800/50 dark:after:text-white/50
                        dark:after:text-white' }} after:ml-auto after:inline-block after:font-bold after:antialiased
                        after:transition-all after:duration-200 after:content-['\f107'] dark:text-white
                        dark:opacity-80">

                        <div
                            class="stroke-none mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center fill-current p-2.5 text-center text-black {{ in_array_r(request()->route()->getName(), getCategoriesArray('remita-transactions')) ? 'shadow-soft-sm bg-gradient-fuchsia' : 'shadow-soft-2xl' }}">

                            <svg width="12px" height="12px" viewBox="0 0 40 40" version="1.1"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <title>settings</title>
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g transform="translate(-2020.000000, -442.000000)" fill="#FFFFFF"
                                        fill-rule="nonzero">
                                        <g transform="translate(1716.000000, 291.000000)">
                                            <g transform="translate(304.000000, 151.000000)">
                                                <polygon
                                                    class="{{ in_array_r(request()->route()->getName(), getCategoriesArray('applications')) ? '' : 'fill-slate-800' }}"
                                                    opacity="0.596981957"
                                                    points="18.0883333 15.7316667 11.1783333 8.82166667 13.3333333 6.66666667 6.66666667 0 0 6.66666667 6.66666667 13.3333333 8.82166667 11.1783333 15.315 17.6716667">
                                                </polygon>
                                                <path
                                                    class="{{ in_array_r(request()->route()->getName(), getCategoriesArray('remita-transactions')) ? '' : 'fill-slate-800' }}"
                                                    d="M31.5666667,23.2333333 C31.0516667,23.2933333 30.53,23.3333333 30,23.3333333 C29.4916667,23.3333333 28.9866667,23.3033333 28.48,23.245 L22.4116667,30.7433333 L29.9416667,38.2733333 C32.2433333,40.575 35.9733333,40.575 38.275,38.2733333 L38.275,38.2733333 C40.5766667,35.9716667 40.5766667,32.2416667 38.275,29.94 L31.5666667,23.2333333 Z"
                                                    opacity="0.596981957"></path>
                                                <path
                                                    class="{{ in_array_r(request()->route()->getName(), getCategoriesArray('remita-transactions')) ? '' : 'fill-slate-800' }}"
                                                    d="M33.785,11.285 L28.715,6.215 L34.0616667,0.868333333 C32.82,0.315 31.4483333,0 30,0 C24.4766667,0 20,4.47666667 20,10 C20,10.99 20.1483333,11.9433333 20.4166667,12.8466667 L2.435,27.3966667 C0.95,28.7083333 0.0633333333,30.595 0.00333333333,32.5733333 C-0.0583333333,34.5533333 0.71,36.4916667 2.11,37.89 C3.47,39.2516667 5.27833333,40 7.20166667,40 C9.26666667,40 11.2366667,39.1133333 12.6033333,37.565 L27.1533333,19.5833333 C28.0566667,19.8516667 29.01,20 30,20 C35.5233333,20 40,15.5233333 40,10 C40,8.55166667 39.685,7.18 39.1316667,5.93666667 L33.785,11.285 Z">
                                                </path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </div>

                        <span
                            class="ml-1 duration-300 opacity-100 pointer-events-none ease-soft {{ in_array_r(request()->route()->getName(), getCategoriesArray('remita-transactions')) ? 'text-slate-700' : '' }}">Transactions</span>
                    </a>

                    <div class="h-auto overflow-hidden transition-all duration-200 ease-soft-in-out max-h-0"
                        id="applicationsExamples">
                        <ul
                            class="flex flex-wrap pl-4 mb-0 ml-6 list-none transition-all duration-200 ease-soft-in-out">
                            <li class="w-full">
                                <a class="ease-soft-in-out py-1.6 ml-5.4 pl-4 text-size-sm relative my-0 mr-4 flex items-center whitespace-nowrap bg-transparent pr-4 shadow-none transition-colors before:absolute before:top-1/2 before:-translate-y-1/2 before:rounded-3xl before:content-[''] dark:text-white dark:before:bg-white dark:before:opacity-80 {{ Route::currentRouteName() == 'transactions' ? 'before:-left-5 rounded-lg font-semibold text-slate-800 before:h-2 before:w-2 before:bg-slate-800 dark:opacity-100' : 'before:-left-4.5 before:h-1.25 before:w-1.25 font-medium text-slate-800/50 before:bg-slate-800/50 dark:opacity-60' }}"
                                    href="{{ route('transactions') }}">
                                    <span
                                        class="w-0 text-center transition-all duration-200 opacity-0 pointer-events-none ease-soft-in-out">
                                        T
                                    </span>
                                    <span class="transition-all duration-100 pointer-events-none ease-soft">
                                        Transactions </span>
                                </a>
                            </li>
                            <li class="w-full">
                                <a class="ease-soft-in-out py-1.6 ml-5.4 pl-4 text-size-sm relative my-0 mr-4 flex items-center whitespace-nowrap bg-transparent pr-4 shadow-none transition-colors before:absolute before:top-1/2 before:-translate-y-1/2 before:rounded-3xl before:content-[''] dark:text-white dark:before:bg-white dark:before:opacity-80 {{ Route::currentRouteName() == 'admission-invoice' ? 'before:-left-5 rounded-lg font-semibold text-slate-800 before:h-2 before:w-2 before:bg-slate-800 dark:opacity-100' : 'before:-left-4.5 before:h-1.25 before:w-1.25 font-medium text-slate-800/50 before:bg-slate-800/50 dark:opacity-60' }}"
                                    href="{{ route('admission-invoice') }}">
                                    <span
                                        class="w-0 text-center transition-all duration-200 opacity-0 pointer-events-none ease-soft-in-out">
                                        AI
                                    </span>
                                    <span class="transition-all duration-100 pointer-events-none ease-soft">Admission
                                        Invoice </span>
                                </a>
                            </li>
                            <li class="w-full">
                                <a class="ease-soft-in-out py-1.6 ml-5.4 pl-4 text-size-sm relative my-0 mr-4 flex items-center whitespace-nowrap bg-transparent pr-4 shadow-none transition-colors before:absolute before:top-1/2 before:-translate-y-1/2 before:rounded-3xl before:content-[''] dark:text-white dark:before:bg-white dark:before:opacity-80 {{ Route::currentRouteName() == 'admission-invoice' ? 'before:-left-5 rounded-lg font-semibold text-slate-800 before:h-2 before:w-2 before:bg-slate-800 dark:opacity-100' : 'before:-left-4.5 before:h-1.25 before:w-1.25 font-medium text-slate-800/50 before:bg-slate-800/50 dark:opacity-60' }}"
                                    href="{{ route('payment') }}">
                                    <span
                                        class="w-0 text-center transition-all duration-200 opacity-0 pointer-events-none ease-soft-in-out">
                                        AI
                                    </span>
                                    <span class="transition-all duration-100 pointer-events-none ease-soft">Payment
                                    </span>
                                </a>
                            </li>


                        </ul>
                    </div>
                </li> --}}
                {{-- pAYMENT --}}



                <!-- Applications -->

                <li class="mt-0.5 w-full">
                    <a {{ in_array_r(request()->route()->getName(), getCategoriesArray('applications')) ?
    'active_primary
                        aria-expanded=true' :
    'aria-expanded=false' }} collapse_trigger="primary"
                        href="javascript:;" class="ease-soft-in-out
                        text-size-sm
                        py-2.7 active after:ease-soft-in-out after:font-awesome-5-free my-0 mx-4 flex items-center
                        whitespace-nowrap
                        px-4 {{ in_array_r(request()->route()->getName(), getCategoriesArray('applications')) ?
    'font-semibold
                        text-slate-700
                        xl:shadow-soft-xl rounded-lg bg-white transition-all after:text-slate-800 after:rotate-180' :
    'font-medium
                        text-slate-500 shadow-none transition-colors after:text-slate-800/50 dark:after:text-white/50
                        dark:after:text-white' }} after:ml-auto after:inline-block after:font-bold after:antialiased
                        after:transition-all after:duration-200 after:content-['\f107'] dark:text-white
                        dark:opacity-80">

                        <div
                            class="stroke-none mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white bg-center fill-current p-2.5 text-center text-black {{ in_array_r(request()->route()->getName(), getCategoriesArray('applications')) ? 'shadow-soft-sm bg-gradient-fuchsia' : 'shadow-soft-2xl' }}">

                            <svg width="12px" height="12px" viewBox="0 0 40 40" version="1.1"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <title>settings</title>
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g transform="translate(-2020.000000, -442.000000)" fill="#FFFFFF"
                                        fill-rule="nonzero">
                                        <g transform="translate(1716.000000, 291.000000)">
                                            <g transform="translate(304.000000, 151.000000)">
                                                <polygon
                                                    class="{{ in_array_r(request()->route()->getName(), getCategoriesArray('applications')) ? '' : 'fill-slate-800' }}"
                                                    opacity="0.596981957"
                                                    points="18.0883333 15.7316667 11.1783333 8.82166667 13.3333333 6.66666667 6.66666667 0 0 6.66666667 6.66666667 13.3333333 8.82166667 11.1783333 15.315 17.6716667">
                                                </polygon>
                                                <path
                                                    class="{{ in_array_r(request()->route()->getName(), getCategoriesArray('applications')) ? '' : 'fill-slate-800' }}"
                                                    d="M31.5666667,23.2333333 C31.0516667,23.2933333 30.53,23.3333333 30,23.3333333 C29.4916667,23.3333333 28.9866667,23.3033333 28.48,23.245 L22.4116667,30.7433333 L29.9416667,38.2733333 C32.2433333,40.575 35.9733333,40.575 38.275,38.2733333 L38.275,38.2733333 C40.5766667,35.9716667 40.5766667,32.2416667 38.275,29.94 L31.5666667,23.2333333 Z"
                                                    opacity="0.596981957"></path>
                                                <path
                                                    class="{{ in_array_r(request()->route()->getName(), getCategoriesArray('applications')) ? '' : 'fill-slate-800' }}"
                                                    d="M33.785,11.285 L28.715,6.215 L34.0616667,0.868333333 C32.82,0.315 31.4483333,0 30,0 C24.4766667,0 20,4.47666667 20,10 C20,10.99 20.1483333,11.9433333 20.4166667,12.8466667 L2.435,27.3966667 C0.95,28.7083333 0.0633333333,30.595 0.00333333333,32.5733333 C-0.0583333333,34.5533333 0.71,36.4916667 2.11,37.89 C3.47,39.2516667 5.27833333,40 7.20166667,40 C9.26666667,40 11.2366667,39.1133333 12.6033333,37.565 L27.1533333,19.5833333 C28.0566667,19.8516667 29.01,20 30,20 C35.5233333,20 40,15.5233333 40,10 C40,8.55166667 39.685,7.18 39.1316667,5.93666667 L33.785,11.285 Z">
                                                </path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </div>

                        <span
                            class="ml-1 duration-300 opacity-100 pointer-events-none ease-soft {{ in_array_r(request()->route()->getName(), getCategoriesArray('applications')) ? 'text-slate-700' : '' }}">Admission</span>
                    </a>

                    <div class="h-auto overflow-hidden transition-all duration-200 ease-soft-in-out max-h-0"
                        id="applicationsExamples">
                        <ul
                            class="flex flex-wrap pl-4 mb-0 ml-6 list-none transition-all duration-200 ease-soft-in-out">








                            <li class="w-full">
                                <a class="ease-soft-in-out py-1.6 ml-5.4 pl-4 text-size-sm relative my-0 mr-4 flex items-center whitespace-nowrap bg-transparent pr-4 shadow-none transition-colors before:absolute before:top-1/2 before:-translate-y-1/2 before:rounded-3xl before:content-[''] dark:text-white dark:before:bg-white dark:before:opacity-80 {{ Route::currentRouteName() == 'student.print-acceptance' ? 'before:-left-5 rounded-lg font-semibold text-slate-800 before:h-2 before:w-2 before:bg-slate-800 dark:opacity-100' : 'before:-left-4.5 before:h-1.25 before:w-1.25 font-medium text-slate-800/50 before:bg-slate-800/50 dark:opacity-60' }}"
                                    target="_blank" href="{{ route('student.profile') }}">
                                    <span
                                        class="w-0 text-center transition-all duration-200 opacity-0 pointer-events-none ease-soft-in-out">
                                        R
                                    </span>
                                    <span class="transition-all duration-100 pointer-events-none ease-soft">
                                        Profile
                                    </span>
                                </a>
                            </li>

                            <li class="w-full">
                                <a class="ease-soft-in-out py-1.6 ml-5.4 pl-4 text-size-sm relative my-0 mr-4 flex items-center whitespace-nowrap bg-transparent pr-4 shadow-none transition-colors before:absolute before:top-1/2 before:-translate-y-1/2 before:rounded-3xl before:content-[''] dark:text-white dark:before:bg-white dark:before:opacity-80 {{ Route::currentRouteName() == 'student.print-form' ? 'before:-left-5 rounded-lg font-semibold text-slate-800 before:h-2 before:w-2 before:bg-slate-800 dark:opacity-100' : 'before:-left-4.5 before:h-1.25 before:w-1.25 font-medium text-slate-800/50 before:bg-slate-800/50 dark:opacity-60' }}"
                                    target="_blank" href="{{ route('student.print-form') }}">
                                    <span
                                        class="w-0 text-center transition-all duration-200 opacity-0 pointer-events-none ease-soft-in-out">
                                        R
                                    </span>
                                    <span class="transition-all duration-100 pointer-events-none ease-soft">Print Form
                                    </span>
                                </a>
                            </li>
                            @if (auth()->user()->isPostgraduate())
                                <li class="w-full">
                                    <a class="ease-soft-in-out py-1.6 ml-5.4 pl-4 text-size-sm relative my-0 mr-4 flex items-center whitespace-nowrap bg-transparent pr-4 shadow-none transition-colors before:absolute before:top-1/2 before:-translate-y-1/2 before:rounded-3xl before:content-[''] dark:text-white dark:before:bg-white dark:before:opacity-80 {{ Route::currentRouteName() == 'student.print-acceptance' ? 'before:-left-5 rounded-lg font-semibold text-slate-800 before:h-2 before:w-2 before:bg-slate-800 dark:opacity-100' : 'before:-left-4.5 before:h-1.25 before:w-1.25 font-medium text-slate-800/50 before:bg-slate-800/50 dark:opacity-60' }}"
                                        target="_blank" href="{{ route('student.print-acceptance') }}">
                                        <span
                                            class="w-0 text-center transition-all duration-200 opacity-0 pointer-events-none ease-soft-in-out">
                                            R
                                        </span>
                                        <span class="transition-all duration-100 pointer-events-none ease-soft">Print
                                            Acceptance
                                        </span>
                                    </a>
                                </li>
                            @endif

                        </ul>
                    </div>
                </li>

                <!-- Ecommerce  -->

            </ul>
        </div>

        <div class="pt-4 mx-4 mt-4">
            <form method="POST" action="{{ route('logout') }}" class="inline-block w-full">
                @csrf
                <button type="submit"
                    class="w-full px-6 py-3 my-4 font-bold text-center text-white uppercase align-middle transition-all ease-in border-0 rounded-lg select-none shadow-soft-md bg-150 bg-x-25 leading-pro text-size-xs bg-gradient-fuchsia hover:shadow-soft-2xl hover:scale-102">
                    Logout
                </button>
            </form>

        </div>
    </aside>