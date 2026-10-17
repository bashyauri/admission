<!-- Navbar -->
<nav navbar-main
  class="relative flex flex-wrap items-center justify-between px-0 py-2 mx-6 mt-6 transition-all shadow-none duration-250 ease-soft-in rounded-2xl lg:flex-nowrap lg:justify-start"
  navbar-scroll="true">
  <div class="flex items-center justify-between w-full px-4 py-1 mx-auto flex-wrap-inherit">
    <nav>
      <!-- breadcrumb -->
      <ol class="flex flex-wrap pt-1 mr-12 bg-transparent rounded-lg sm:mr-16">
        <li class="leading-normal text-size-sm breadcrumb-item">
          <a class="text-slate-700 opacity-30 dark:text-white" href="{{ env('APP_URL') }}">
            <svg width="12px" height="12px" class="mb-1" viewBox="0 0 45 40" version="1.1"
              xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <title>shop</title>
              <g class="dark:fill-white" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <g class="dark:fill-white" transform="translate(-1716.000000, -439.000000)" fill="#252f40"
                  fill-rule="nonzero">
                  <g class="dark:fill-white" transform="translate(1716.000000, 291.000000)">
                    <g class="dark:fill-white" transform="translate(0.000000, 148.000000)">
                      <path
                        d="M46.7199583,10.7414583 L40.8449583,0.949791667 C40.4909749,0.360605034 39.8540131,0 39.1666667,0 L7.83333333,0 C7.1459869,0 6.50902508,0.360605034 6.15504167,0.949791667 L0.280041667,10.7414583 C0.0969176761,11.0460037 -1.23209662e-05,11.3946378 -1.23209662e-05,11.75 C-0.00758042603,16.0663731 3.48367543,19.5725301 7.80004167,19.5833333 L7.81570833,19.5833333 C9.75003686,19.5882688 11.6168794,18.8726691 13.0522917,17.5760417 C16.0171492,20.2556967 20.5292675,20.2556967 23.494125,17.5760417 C26.4604562,20.2616016 30.9794188,20.2616016 33.94575,17.5760417 C36.2421905,19.6477597 39.5441143,20.1708521 42.3684437,18.9103691 C45.1927731,17.649886 47.0084685,14.8428276 47.0000295,11.75 C47.0000295,11.3946378 46.9030823,11.0460037 46.7199583,10.7414583 Z">
                      </path>
                      <path
                        d="M39.198,22.4912623 C37.3776246,22.4928106 35.5817531,22.0149171 33.951625,21.0951667 L33.92225,21.1107282 C31.1430221,22.6838032 27.9255001,22.9318916 24.9844167,21.7998837 C24.4750389,21.605469 23.9777983,21.3722567 23.4960833,21.1018359 L23.4745417,21.1129513 C20.6961809,22.6871153 17.4786145,22.9344611 14.5386667,21.7998837 C14.029926,21.6054643 13.533337,21.3722507 13.0522917,21.1018359 C11.4250962,22.0190609 9.63246555,22.4947009 7.81570833,22.4912623 C7.16510551,22.4842162 6.51607673,22.4173045 5.875,22.2911849 L5.875,44.7220845 C5.875,45.9498589 6.7517757,46.9451667 7.83333333,46.9451667 L19.5833333,46.9451667 L19.5833333,33.6066734 L27.4166667,33.6066734 L27.4166667,46.9451667 L39.1666667,46.9451667 C40.2482243,46.9451667 41.125,45.9498589 41.125,44.7220845 L41.125,22.2822926 C40.4887822,22.4116582 39.8442868,22.4815492 39.198,22.4912623 Z">
                      </path>
                    </g>
                  </g>
                </g>
              </g>
            </svg>
          </a>
        </li>
        <li
          class="text-size-sm pl-2 leading-normal before:float-left before:pr-2 before:text-gray-600 before:content-['/']">
          <span class="opacity-50 text-slate-700 dark:text-white">
            {{ getPathName() }}
          </span>
        </li>

        <li
          class="text-size-sm pl-2 capitalize leading-normal text-slate-700 before:float-left before:pr-2 before:text-gray-600 before:content-['/'] dark:text-white dark:before:text-white"
          aria-current="page">{{ str_replace('-', ' ', Route::currentRouteName()) }}</li>
      </ol>
      <h6 class="mb-0 font-bold capitalize dark:text-white">{{ str_replace('-', ' ', Route::currentRouteName()) }}</h6>
    </nav>

    <div class="flex items-center">
      <a mini-sidenav-burger href="javascript:;"
        class="hidden p-0 transition-all ease-nav-brand text-size-sm text-slate-500 xl:block" aria-expanded="false">
        <div class="w-4.5 overflow-hidden">
          <i
            class="ease-soft mb-0.75 relative block h-0.5 translate-x-[5px] rounded-sm bg-slate-500 transition-all dark:bg-white"></i>
          <i class="ease-soft mb-0.75 relative block h-0.5 rounded-sm bg-slate-500 transition-all dark:bg-white"></i>
          <i
            class="ease-soft relative block h-0.5 translate-x-[5px] rounded-sm bg-slate-500 transition-all dark:bg-white"></i>
        </div>
      </a>
    </div>

    <div class="flex items-center mt-2 grow sm:mt-0 sm:mr-6 md:mr-0 lg:flex lg:basis-auto" id="navbar">
      <div class="flex items-center md:ml-auto md:pr-4">
        <!-- pro btn  -->
        {{-- <a class="inline-block w-full px-6 py-3 mr-2 font-bold text-center text-white uppercase align-middle transition-all ease-in border-0 rounded-lg select-none shadow-soft-md bg-150 bg-x-25 leading-pro text-size-xs bg-gradient-fuchsia hover:shadow-soft-2xl hover:scale-102"
          href="https://www.creative-tim.com/product/soft-ui-dashboard-pro-tall" target="_blank"><i class="mr-2 fas fa-shopping-cart"></i>Purchase now</a> --}}
        <div class="relative flex flex-wrap items-stretch w-full transition-all rounded-lg ease-soft">
          <span
            class="text-size-sm ease-soft leading-5.6 absolute z-50 -ml-px flex h-full items-center whitespace-nowrap rounded-lg rounded-tr-none rounded-br-none border border-r-0 border-transparent bg-transparent py-2 px-2.5 text-center font-normal text-slate-500 transition-all">
            <i class="fas fa-search" aria-hidden="true"></i>
          </span>
          <input type="text"
            class="pl-9 text-size-sm focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 ease-soft w-1/100 leading-5.6 relative -ml-px block min-w-0 flex-auto rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 pr-3 text-gray-700 transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none focus:transition-shadow"
            placeholder="Type here..." />
        </div>
      </div>
      <ul class="flex flex-row justify-end pl-0 mb-0 list-none md-max:w-full">
        <!-- online builder btn  -->
        <!-- <li class="flex items-center">
              <a class="inline-block px-8 py-2 mb-0 mr-4 font-bold text-center uppercase align-middle transition-all bg-transparent border border-solid rounded-lg shadow-none cursor-pointer leading-pro border-fuchsia-500 ease-soft-in text-size-xs hover:scale-102 active:shadow-soft-xs text-fuchsia-500 hover:border-fuchsia-500 active:bg-fuchsia-500 active:hover:text-fuchsia-500 hover:text-fuchsia-500 tracking-tight-soft hover:bg-transparent hover:opacity-75 hover:shadow-none active:text-white active:hover:bg-transparent" target="_blank" href="https://www.creative-tim.com/builder/soft-ui?ref=navbar-dashboard&amp;_ga=2.76518741.1192788655.1647724933-1242940210.1644448053">Online Builder</a>
            </li> -->
        <li class="flex items-center">
          <div class="px-0 py-2">
            <livewire:auth.logout />
          </div>
        </li>
        <li class="flex items-center pl-4 xl:hidden">
          <a sidenav-trigger class="block p-0 transition-all ease-nav-brand text-size-sm text-slate-500 dark:text-white"
            href="javascript:;" aria-expanded="false">
            <div class="w-4.5 overflow-hidden">
              <i
                class="ease-soft mb-0.75 relative block h-0.5 rounded-sm bg-slate-500 transition-all dark:bg-white"></i>
              <i
                class="ease-soft mb-0.75 relative block h-0.5 rounded-sm bg-slate-500 transition-all dark:bg-white"></i>
              <i class="ease-soft relative block h-0.5 rounded-sm bg-slate-500 transition-all dark:bg-white"></i>
            </div>

          </a>
        </li>
        <li class="flex items-center px-4">
          <a href="javascript:;" class="p-0 transition-all text-size-sm ease-nav-brand text-slate-500 dark:text-white">
            <i fixed-plugin-button-nav class="cursor-pointer fa fa-cog" aria-hidden="true"></i>
            <!-- fixed-plugin-button-nav  -->
          </a>
        </li>

        <li class="relative flex items-center pr-2">
          <a href="{{ route('notifications') }}" aria-label="Open notifications"
            class="relative block p-0 transition-all text-size-sm ease-nav-brand text-slate-500 hover:text-slate-700 dark:text-white">
            <i class="cursor-pointer fa fa-bell" aria-hidden="true"></i>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
