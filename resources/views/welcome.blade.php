<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta name="description"
        content="Directorate of Higher Studies, Waziri Umaru Federal Polytechnic Birnin Kebbi. Explore undergraduate degree and postgraduate diploma programmes and apply today.">

    <meta name="theme-color"
        content="#15803d">

    <title>
        Directorate of Higher Studies |
        Waziri Umaru Federal Polytechnic Birnin Kebbi
    </title>


    {{-- Alpine.js --}}
    <script
        src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"
        defer>
    </script>


    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>


    <script>

        tailwind.config = {

            theme: {

                extend: {

                    colors: {

                        primary: {

                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',

                        }

                    }

                }

            }

        };

    </script>


    <style>

        [x-cloak] {
            display: none !important;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        ::selection {
            background: #bbf7d0;
            color: #14532d;
        }

    </style>

</head>


<body class="bg-gray-50 text-gray-800 antialiased">


{{-- ========================================================= --}}
{{-- STICKY TOP NAVIGATION --}}
{{-- ========================================================= --}}

<a href="#main-content"
   class="sr-only focus:not-sr-only focus:absolute focus:z-[10000] focus:m-2 focus:px-4 focus:py-2 focus:rounded-lg focus:bg-white focus:text-green-800">
    Skip to main content
</a>

{{-- Top utility bar --}}
<div class="hidden md:block bg-green-900 text-green-100 text-xs">
    <div class="max-w-7xl mx-auto px-4 h-9 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <span>Waziri Umaru Federal Polytechnic, Birnin Kebbi</span>
            <a href="tel:07064429665" class="hover:text-white transition">07064429665</a>
        </div>
        <div class="flex items-center gap-4">
            <a href="#screening-announcement" class="inline-flex items-center gap-1.5 font-semibold text-amber-300 hover:text-amber-200 transition">
                <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span>
                Latest: BSc. Physical Screening Notice
            </a>
        </div>
    </div>
</div>

<nav x-data="{ mobileOpen: false }"
     class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-200 shadow-sm">

    <div class="max-w-7xl mx-auto px-4">

        <div class="flex items-center justify-between h-14 sm:h-16">

            {{-- Logo / Brand --}}
            <a href="/"
               class="flex items-center gap-2 sm:gap-3 flex-shrink-0">

                <img src="{{ asset('assets/img/logo-ct.png') }}"
                     alt="WUFPBK Logo"
                     class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover"
                     onerror="this.style.display='none'">

                <div class="leading-tight">
                    <span class="block text-sm sm:text-base font-extrabold text-green-800 tracking-tight">
                        DHS — WUFPBK
                    </span>
                    <span class="hidden sm:block text-[10px] text-gray-500 font-medium">
                        Directorate of Higher Studies
                    </span>
                </div>

            </a>


            {{-- Desktop Navigation Links --}}
            <div class="hidden md:flex items-center gap-1">

                <a href="{{ route('home') }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-green-700 hover:bg-green-50 transition">
                    Home
                </a>

                <a href="#programmes"
                   class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-green-700 hover:bg-green-50 transition">
                    Programmes
                </a>

                <a href="#screening-announcement"
                   class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-green-700 hover:bg-green-50 transition">
                    Notices
                </a>

                <a href="#contact-us"
                   class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-green-700 hover:bg-green-50 transition">
                    Contact
                </a>

            </div>


            {{-- Desktop Login Buttons --}}
            <div class="hidden md:flex items-center gap-2">

                <a href="{{ route('degree-login') }}"
                   class="inline-flex items-center gap-1.5
                          px-4 py-2 rounded-lg
                          bg-green-600 text-white
                          text-sm font-bold
                          hover:bg-green-700
                          shadow-sm
                          transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 14l6.16-3.422A12.083 12.083 0 0112 21a12.083 12.083 0 01-6.16-10.422L12 14z" />
                    </svg>
                    Degree Login
                </a>

                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-1.5
                          px-4 py-2 rounded-lg
                          border border-green-600
                          text-green-700
                          text-sm font-bold
                          hover:bg-green-50
                          transition">
                    PGD Login
                </a>

            </div>


            {{-- Mobile Menu Button --}}
            <button @click="mobileOpen = !mobileOpen"
                    class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition"
                    :aria-expanded="mobileOpen"
                    aria-label="Toggle navigation menu">

                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16" />
                </svg>

                <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12" />
                </svg>

            </button>

        </div>

    </div>


    {{-- Mobile Dropdown Menu --}}
    <div x-show="mobileOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="md:hidden border-t border-gray-100 bg-white">

        <div class="px-4 py-3 space-y-1">

            <a href="#programmes"
               @click="mobileOpen = false"
               class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700 transition">
                Programmes
            </a>

            <a href="#screening-announcement"
               @click="mobileOpen = false"
               class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700 transition">
                Screening
            </a>

            <a href="#contact-us"
               @click="mobileOpen = false"
               class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700 transition">
                Contact
            </a>

        </div>

        <div class="px-4 pb-4 pt-2 border-t border-gray-100 grid grid-cols-2 gap-2">

            <a href="{{ route('degree-login') }}"
               class="inline-flex items-center justify-center
                      px-4 py-2.5 rounded-lg
                      bg-green-600 text-white
                      text-sm font-bold
                      hover:bg-green-700
                      transition">
                🎓 Degree Login
            </a>

            <a href="{{ route('login') }}"
               class="inline-flex items-center justify-center
                      px-4 py-2.5 rounded-lg
                      border border-green-600
                      text-green-700
                      text-sm font-bold
                      hover:bg-green-50
                      transition">
                PGD Login
            </a>

        </div>

    </div>

</nav>



{{-- ========================================================= --}}
{{-- ADMISSION ANNOUNCEMENT --}}
{{-- ========================================================= --}}

<div class="bg-yellow-50 border-b border-yellow-300">

    <div class="max-w-7xl mx-auto px-4 py-3">

        <div class="flex flex-col sm:flex-row
                    items-center justify-center
                    gap-2 sm:gap-4
                    text-center">

            <div class="flex items-center gap-2">

                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01
                           M12 8v.01
                           M12 21a9 9 0 100-18
                           9 9 0 000 18z" />

                </svg>


                <span class="font-semibold text-yellow-900">

                    Admissions are

                    <span class="font-extrabold text-red-600">
                        OPEN
                    </span>

                </span>

            </div>


            <span class="hidden sm:block text-yellow-600">
                —
            </span>


            <span class="text-sm text-yellow-900">

                Undergraduate Degree and Postgraduate Diploma
                programmes are currently accepting applications.

            </span>


            <a href="#programmes"
                class="inline-flex items-center justify-center
                       px-4 py-1.5
                       rounded-lg
                       bg-red-600
                       text-white
                       text-sm font-bold
                       hover:bg-red-700
                       transition">

                Apply Now

            </a>

        </div>

    </div>

</div>



<main id="main-content" class="flex flex-col">

{{-- ========================================================= --}}
{{-- PUBLIC ANNOUNCEMENT: BSc. PHYSICAL SCREENING EXERCISE --}}
{{-- ========================================================= --}}

<section id="screening-announcement" class="bg-gradient-to-b from-amber-50 via-white to-gray-50 border-y border-amber-200 py-6 sm:py-8">

    <div class="max-w-6xl mx-auto px-4">

        <div class="bg-white rounded-2xl shadow-xl border border-amber-200/80 overflow-hidden">

            {{-- Announcement Header Banner --}}
            <div class="bg-gradient-to-r from-green-800 via-green-700 to-emerald-800 text-white px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold tracking-wider uppercase text-green-200">
                        Waziri Umaru Federal Polytechnic Birnin Kebbi • Office of the Registrar
                    </p>
                    <h2 class="text-lg sm:text-xl font-extrabold tracking-tight mt-0.5 text-white flex items-center gap-2">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-400 animate-pulse"></span>
                        PUBLIC ANNOUNCEMENT: PHYSICAL SCREENING EXERCISE FOR BSc. CANDIDATES
                    </h2>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-400 text-amber-950 shadow-sm">
                        2026/2027 Academic Session
                    </span>
                    <span class="text-xs font-medium text-green-100 hidden sm:inline">
                        2nd November, 2026
                    </span>
                </div>
            </div>

            <div class="p-6 sm:p-8">

                {{-- Intro Message --}}
                <div class="bg-amber-50/70 border-l-4 border-amber-500 rounded-r-xl p-4 sm:p-5 mb-6 text-gray-800">
                    <p class="text-sm sm:text-base leading-relaxed">
                        This is to inform all candidates who secured admission into
                        <strong class="font-bold text-gray-950">Waziri Umaru Federal Polytechnic Birnin Kebbi (WUFPBK)</strong>
                        Affiliated Degree Programmes through
                        <strong class="font-bold text-gray-950">Unified Tertiary Matriculation Examination (UTME)</strong>
                        and <strong class="font-bold text-gray-950">Direct Entry (D.E.)</strong>
                        that the physical screening begins on
                        <strong class="text-green-800 font-bold">Wednesday 4th November, 2026</strong>.
                    </p>
                </div>

                {{-- Key Schedule & Venue Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

                    {{-- Card 1: Commencement --}}
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 7h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z" />
                            </svg>
                            Commencement Date
                        </div>
                        <div class="text-base font-bold text-gray-900">
                            Wednesday 4th Nov, 2026
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Screening officially commences</p>
                    </div>

                    {{-- Card 2: Schedule --}}
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Daily Schedule
                        </div>
                        <div class="text-base font-bold text-gray-900">
                            Mondays – Thursdays
                        </div>
                        <p class="text-xs text-emerald-700 font-semibold mt-1">10:00am – 2:00pm daily</p>
                    </div>

                    {{-- Card 3: Venue --}}
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 21a2 2 0 01-2.828 0l-4.243-4.343a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Screening Venue
                        </div>
                        <div class="text-base font-bold text-gray-900">
                            DHS Boardroom
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Directorate of Higher Studies</p>
                    </div>

                    {{-- Card 4: Deadline --}}
                    <div class="p-4 rounded-xl bg-red-50 border border-red-200">
                        <div class="text-xs font-bold text-red-600 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Screening Deadline
                        </div>
                        <div class="text-base font-bold text-red-700">
                            Saturday 21st Nov, 2026
                        </div>
                        <p class="text-xs text-red-600 font-semibold mt-1">Strict deadline applies</p>
                    </div>

                </div>

                {{-- Requirements Checklist --}}
                <div class="mb-8">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-gray-900 mb-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-600"></span>
                        Consequently, candidates are expected to come along with:
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-gray-50 border border-gray-200/80">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-green-100 text-green-800 font-extrabold text-xs flex-shrink-0">
                                i
                            </span>
                            <span class="text-sm text-gray-800 font-medium">
                                Original copies of <strong>JAMB admission letters (coloured)</strong>
                            </span>
                        </div>

                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-gray-50 border border-gray-200/80">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-green-100 text-green-800 font-extrabold text-xs flex-shrink-0">
                                ii
                            </span>
                            <span class="text-sm text-gray-800 font-medium">
                                <strong>Post UTME online screening form</strong>
                            </span>
                        </div>

                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-green-50/70 border border-green-200">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-green-200 text-green-900 font-extrabold text-xs flex-shrink-0">
                                iii
                            </span>
                            <span class="text-sm text-gray-800 font-medium">
                                Evidence of payment of <strong class="text-green-800 font-extrabold">₦10,000 ACCEPTANCE FEES</strong>
                            </span>
                        </div>

                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-gray-50 border border-gray-200/80">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-green-100 text-green-800 font-extrabold text-xs flex-shrink-0">
                                iv
                            </span>
                            <span class="text-sm text-gray-800 font-medium">
                                <strong>Original O-level certificate</strong> and <strong>primary certificate</strong>
                            </span>
                        </div>

                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-gray-50 border border-gray-200/80">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-green-100 text-green-800 font-extrabold text-xs flex-shrink-0">
                                v
                            </span>
                            <span class="text-sm text-gray-800 font-medium">
                                <strong>Indigene letter</strong>, and <strong>birth certificate</strong>
                            </span>
                        </div>

                        <div class="flex items-start gap-3 p-3.5 rounded-xl bg-gray-50 border border-gray-200/80">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-green-100 text-green-800 font-extrabold text-xs flex-shrink-0">
                                vi
                            </span>
                            <span class="text-sm text-gray-800 font-medium">
                                <strong>Scratch card for result verification</strong> <span class="text-gray-500 text-xs font-normal">(if the result is not original)</span>
                            </span>
                        </div>

                    </div>
                </div>

                {{-- Footer: Contact Numbers & Signoff --}}
                <div class="pt-5 border-t border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 block mb-1">
                            For More Information, Contact:
                        </span>
                        <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-green-800">
                            <a href="tel:07064429665" class="hover:underline hover:text-green-950">07064429665</a>
                            <span class="text-gray-300">•</span>
                            <a href="tel:07033071497" class="hover:underline hover:text-green-950">07033071497</a>
                            <span class="text-gray-300">•</span>
                            <a href="tel:08068991818" class="hover:underline hover:text-green-950">08068991818</a>
                            <span class="text-gray-300">•</span>
                            <a href="tel:08036397597" class="hover:underline hover:text-green-950">08036397597</a>
                        </div>
                    </div>

                    <div class="text-right sm:text-right border-t md:border-t-0 pt-3 md:pt-0">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Official Notice</p>
                        <p class="text-sm font-extrabold text-gray-900">(Signed)</p>
                        <p class="text-sm font-bold text-gray-900">Mohammed Lawal Ibrahim</p>
                        <p class="text-xs font-semibold text-green-800">Registrar</p>
                    </div>
                </div>

            </div>

        </div>

    </div>

</section>



{{-- ========================================================= --}}
{{-- HERO --}}
{{-- ========================================================= --}}

<section class="order-first relative overflow-hidden
                bg-gradient-to-br
                from-green-800
                via-green-700
                to-teal-700">


    {{-- Decorative circles --}}

    <div class="absolute -top-24 -right-24
                w-72 h-72
                rounded-full
                bg-white/5">
    </div>


    <div class="absolute -bottom-32 -left-24
                w-80 h-80
                rounded-full
                bg-white/5">
    </div>


    <div class="relative max-w-5xl mx-auto
                px-4 sm:px-6 lg:px-8
                py-14 sm:py-18 lg:py-20">


        <div class="max-w-3xl mx-auto text-center">


            {{-- Logo --}}

            <div class="flex justify-center mb-6">

                <div class="p-3
                            rounded-2xl
                            bg-white/10
                            border border-white/20
                            backdrop-blur-sm">

                    <img
                        src="{{ asset('assets/img/logo-ct.png') }}"
                        alt="Waziri Umaru Federal Polytechnic Logo"
                        class="w-20 sm:w-24 lg:w-28 h-auto"
                    >

                </div>

            </div>


            <p class="text-green-200
                      font-bold
                      uppercase
                      tracking-wider
                      text-xs sm:text-sm">

                Waziri Umaru Federal Polytechnic,
                Birnin Kebbi

            </p>


            <h1 class="mt-3
                       text-3xl sm:text-4xl lg:text-5xl
                       font-extrabold
                       tracking-tight
                       text-white">

                Directorate of

                <span class="block text-green-200">
                    Higher Studies
                </span>

            </h1>


            <p class="mt-5
                      max-w-2xl
                      mx-auto
                      text-base sm:text-lg
                      leading-relaxed
                      text-white/85">

                Explore undergraduate degree and postgraduate diploma
                programmes designed to support your academic and
                professional ambitions.

            </p>


            <div class="mt-5 flex justify-center">

                <span class="inline-flex items-center gap-2
                             px-4 py-2
                             rounded-full
                             bg-white/10
                             border border-white/20
                             text-sm
                             font-semibold
                             text-white">

                    <span class="w-2 h-2
                                 rounded-full
                                 bg-green-300">
                    </span>

                    Admissions 2026/2027

                </span>

            </div>


            {{-- Primary CTA buttons --}}
            <div class="mt-8
                        flex flex-col sm:flex-row
                        justify-center
                        gap-3">

                <a href="#programmes"
                    class="inline-flex
                           items-center
                           justify-center
                           px-7 py-3
                           rounded-xl
                           bg-white
                           text-green-700
                           font-bold
                           shadow-lg
                           hover:bg-green-50
                           transition">

                    Explore Programmes

                    <svg class="w-5 h-5 ml-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />

                    </svg>

                </a>


                <a href="#pgd-requirements"
                    class="inline-flex
                           items-center
                           justify-center
                           px-7 py-3
                           rounded-xl
                           bg-green-600/30
                           border border-white/30
                           text-white
                           font-semibold
                           hover:bg-white/10
                           transition">

                    View PGD Requirements

                </a>

            </div>


            {{-- Student Login buttons --}}
            <div class="mt-5 flex flex-col sm:flex-row justify-center gap-3">

                <a href="{{ route('degree-login') }}"
                    class="inline-flex items-center justify-center gap-2
                           px-6 py-2.5 rounded-xl
                           bg-amber-400 text-amber-950
                           font-bold text-sm
                           shadow-md
                           hover:bg-amber-300
                           transition">

                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Degree Student Login

                </a>

                <a href="{{ route('login') }}"
                    class="inline-flex items-center justify-center gap-2
                           px-6 py-2.5 rounded-xl
                           bg-white/15 border border-white/40
                           text-white
                           font-bold text-sm
                           hover:bg-white/25
                           transition">

                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    PGD Student Login

                </a>

            </div>

        </div>

    </div>

</section>



{{-- ========================================================= --}}
{{-- QUICK INFORMATION --}}
{{-- ========================================================= --}}

<section class="order-first relative -mt-7 pb-4">

    <div class="max-w-6xl mx-auto px-4">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">


            {{-- Admissions --}}

            <a href="#programmes"
                class="bg-white
                        rounded-2xl
                        shadow-xl
                        border border-gray-100
                        p-6
                        block
                        hover:shadow-2xl
                        hover:border-green-200
                        transition
                        group">

                <div class="w-11 h-11
                            rounded-xl
                            bg-green-100
                            text-green-700
                            flex items-center
                            justify-center">

                    🎓

                </div>


                <h3 class="mt-4
                           font-bold
                           text-lg
                           group-hover:text-green-700
                           transition">

                    Admissions Open

                </h3>


                <p class="mt-2
                          text-sm
                          leading-relaxed
                          text-gray-600">

                    Applications are open for Undergraduate Degree
                    and Postgraduate Diploma programmes.

                </p>

            </a>



            {{-- BSc. Screening --}}

            <a href="#screening-announcement"
                class="bg-white
                        rounded-2xl
                        shadow-xl
                        border border-amber-100
                        p-6
                        block
                        hover:shadow-2xl
                        hover:border-amber-300
                        transition
                        group">

                <div class="w-11 h-11
                            rounded-xl
                            bg-amber-100
                            text-amber-700
                            flex items-center
                            justify-center">

                    📅

                </div>


                <h3 class="mt-4
                           font-bold
                           text-lg text-gray-900
                           group-hover:text-amber-700
                           transition">

                    BSc. Physical Screening

                </h3>


                <p class="mt-2
                          text-sm
                          leading-relaxed
                          text-gray-600">

                    Starts <strong>Wed 4th Nov, 2026</strong>. Runs <strong>Mondays – Thursdays</strong>
                    from <strong>10:00 AM – 2:00 PM</strong> daily.

                </p>


                <p class="mt-2
                          text-sm
                          font-semibold
                          text-amber-700">

                    Venue: DHS Boardroom

                </p>

            </a>



            {{-- Screening Requirements & Deadline --}}

            <a href="#screening-announcement"
                class="bg-white
                        rounded-2xl
                        shadow-xl
                        border border-red-100
                        p-6
                        block
                        hover:shadow-2xl
                        hover:border-red-300
                        transition
                        group">

                <div class="w-11 h-11
                            rounded-xl
                            bg-red-100
                            text-red-600
                            flex items-center
                            justify-center">

                    📋

                </div>


                <h3 class="mt-4
                           font-bold
                           text-lg text-gray-900
                           group-hover:text-red-600
                           transition">

                    Required Documents

                </h3>


                <p class="mt-2
                          text-sm
                          leading-relaxed
                          text-gray-600">

                    JAMB letter (coloured), ₦10,000 acceptance fee proof, Post-UTME form & credentials.

                </p>

                <p class="mt-2
                          text-sm
                          font-semibold
                          text-red-600">

                    Deadline: Saturday 21st Nov, 2026

                </p>

            </a>

        </div>

    </div>

</section>



{{-- ========================================================= --}}
{{-- PROGRAMMES --}}
{{-- ========================================================= --}}

<section id="programmes"
    class="py-20 bg-white">

    <div class="max-w-6xl mx-auto
                px-4 sm:px-6 lg:px-8">


        <div class="max-w-2xl mx-auto text-center">

            <span class="inline-flex
                         px-3 py-1
                         rounded-full
                         bg-green-100
                         text-green-700
                         text-sm font-bold">

                Admissions 2026/2027

            </span>


            <h2 class="mt-4
                       text-3xl sm:text-4xl
                       font-extrabold
                       text-gray-900">

                Choose Your Programme

            </h2>


            <p class="mt-4
                      text-gray-600
                      text-lg">

                Select the programme that matches your academic
                and professional goals.

            </p>

        </div>



        <div class="mt-12
                    grid grid-cols-1
                    lg:grid-cols-2
                    gap-8">


            {{-- ================================================= --}}
            {{-- UNDERGRADUATE --}}
            {{-- ================================================= --}}

            <div class="relative overflow-hidden
                        rounded-3xl
                        border border-green-200
                        bg-white
                        shadow-lg
                        hover:shadow-2xl
                        transition">

                <div class="h-1.5 bg-green-600"></div>


                <div class="p-8">

                    <span class="inline-flex
                                 px-3 py-1
                                 rounded-full
                                 bg-green-100
                                 text-green-700
                                 text-xs font-bold
                                 uppercase">

                        Degree

                    </span>


                    <h3 class="mt-4
                               text-2xl
                               font-bold">

                        Undergraduate Programmes

                    </h3>


                    <p class="mt-5
                              text-gray-600
                              leading-relaxed">

                        Explore our undergraduate degree programmes
                        and access the admission portal to apply,
                        continue your application or check your status.

                    </p>


                    <div class="mt-8
                                grid grid-cols-1
                                sm:grid-cols-2
                                gap-3">


                        <a href="{{ route('degree-login') }}"
                            class="inline-flex
                                   items-center
                                   justify-center
                                   px-5 py-3
                                   rounded-xl
                                   bg-green-600
                                   text-white
                                   font-bold
                                   hover:bg-green-700
                                   transition">

                            Degree Login

                        </a>


                        <a href="#undergraduate-courses"
                            class="inline-flex
                                   items-center
                                   justify-center
                                   px-5 py-3
                                   rounded-xl
                                   border border-green-600
                                   text-green-700
                                   font-bold
                                   hover:bg-green-50
                                   transition">

                            View Courses

                        </a>

                    </div>

                </div>

            </div>



            {{-- ================================================= --}}
            {{-- PGD --}}
            {{-- ================================================= --}}

            <div class="relative overflow-hidden
                        rounded-3xl
                        border border-teal-200
                        bg-white
                        shadow-lg
                        hover:shadow-2xl
                        transition">

                <div class="h-1.5 bg-teal-600"></div>


                <div class="p-8">

                    <div class="flex flex-wrap gap-2">

                        <span class="inline-flex
                                     px-3 py-1
                                     rounded-full
                                     bg-teal-100
                                     text-teal-700
                                     text-xs font-bold
                                     uppercase">

                            PGD

                        </span>


                        <span class="inline-flex
                                     px-3 py-1
                                     rounded-full
                                     bg-red-100
                                     text-red-700
                                     text-xs font-bold">

                            NEW

                        </span>

                    </div>


                    <h3 class="mt-4
                               text-2xl
                               font-bold">

                        Postgraduate Diploma

                    </h3>


                    <p class="mt-5
                              text-gray-600
                              leading-relaxed">

                        Advance your academic and professional
                        qualifications through our Postgraduate
                        Diploma programmes.

                    </p>


                    <div class="mt-5 space-y-3">

                        <a href="#pgd-public-health"
                            class="group flex items-center
                                   justify-between
                                   rounded-xl
                                   border border-gray-200
                                   p-4
                                   hover:border-green-300
                                   hover:bg-green-50
                                   transition">

                            <div>

                                <p class="font-bold
                                          text-gray-800
                                          group-hover:text-green-700">

                                    PGD Public Health

                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    View admission requirements
                                </p>

                            </div>


                            <span class="text-green-600">
                                →
                            </span>

                        </a>


                        <a href="#pgd-surveying"
                            class="group flex items-center
                                   justify-between
                                   rounded-xl
                                   border border-gray-200
                                   p-4
                                   hover:border-teal-300
                                   hover:bg-teal-50
                                   transition">

                            <div>

                                <p class="font-bold
                                          text-gray-800
                                          group-hover:text-teal-700">

                                    PGD Surveying & Geo-Informatics

                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    View admission requirements
                                </p>

                            </div>


                            <span class="text-teal-600">
                                →
                            </span>

                        </a>

                    </div>


                    <div class="mt-8
                                grid grid-cols-1
                                sm:grid-cols-2
                                gap-3">


                        <a href="{{ route('login') }}"
                            class="inline-flex
                                   items-center
                                   justify-center
                                   px-5 py-3
                                   rounded-xl
                                   bg-teal-600
                                   text-white
                                   font-bold
                                   hover:bg-teal-700
                                   transition">

                            PGD Login

                        </a>


                        <a href="#pgd-requirements"
                            class="inline-flex
                                   items-center
                                   justify-center
                                   px-5 py-3
                                   rounded-xl
                                   border border-teal-600
                                   text-teal-700
                                   font-bold
                                   hover:bg-teal-50
                                   transition">

                            Requirements

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



{{-- ========================================================= --}}
{{-- PGD ADMISSION REQUIREMENTS --}}
{{-- ========================================================= --}}

<section id="pgd-requirements"
    class="py-20 bg-gray-50">

    <div class="max-w-7xl mx-auto
                px-4 sm:px-6 lg:px-8">


        {{-- Header --}}

        <div class="max-w-3xl mx-auto text-center">

            <span class="inline-flex items-center gap-2
                         px-3 py-1.5
                         rounded-full
                         bg-green-100
                         text-green-700
                         text-sm font-bold">

                🎓 PGD ADMISSIONS

            </span>


            <h2 class="mt-4
                       text-3xl sm:text-4xl
                       font-extrabold
                       text-gray-900">

                Admission Requirements

            </h2>


            <p class="mt-4
                      text-gray-600
                      text-lg
                      leading-relaxed">

                Review the requirements for your preferred
                Postgraduate Diploma programme before applying.

            </p>

        </div>



        {{-- ================================================= --}}
        {{-- REQUIREMENT CARDS --}}
        {{-- ================================================= --}}

        <div class="mt-12
                    grid grid-cols-1
                    lg:grid-cols-2
                    gap-8">


            {{-- ================================================= --}}
            {{-- PUBLIC HEALTH CARD --}}
            {{-- ================================================= --}}

            <article id="pgd-public-health"
                class="bg-white
                       rounded-3xl
                       border border-green-200
                       shadow-lg
                       overflow-hidden">

                <div class="h-1.5 bg-green-600"></div>


                <div class="p-6 sm:p-8">


                    {{-- Heading --}}

                    <div class="flex items-start gap-4">

                        <div class="w-14 h-14
                                    rounded-2xl
                                    bg-green-100
                                    text-green-700
                                    flex items-center
                                    justify-center
                                    flex-shrink-0
                                    text-2xl">

                            🎓

                        </div>


                        <div class="flex-1">

                            <div class="flex flex-wrap gap-2">

                                <span class="px-2.5 py-1
                                             rounded-full
                                             bg-green-100
                                             text-green-700
                                             text-xs font-extrabold">

                                    PGD

                                </span>


                                <span class="px-2.5 py-1
                                             rounded-full
                                             bg-red-100
                                             text-red-700
                                             text-xs font-extrabold">

                                    NEW

                                </span>

                            </div>


                            <h3 class="mt-2
                                       text-2xl
                                       font-extrabold
                                       text-gray-900">

                                Public Health

                            </h3>

                        </div>

                    </div>


                    <p class="mt-5
                              text-gray-600
                              leading-relaxed">

                        Applicants seeking admission into the
                        Postgraduate Diploma in Public Health
                        must satisfy the following requirements.

                    </p>


                    {{-- Quick requirements --}}

                    <div class="mt-7 space-y-4">


                        <div class="flex gap-3">

                            <span class="w-7 h-7
                                         rounded-full
                                         bg-green-100
                                         text-green-700
                                         flex items-center
                                         justify-center
                                         text-sm font-bold
                                         flex-shrink-0">

                                ✓

                            </span>


                            <div>

                                <p class="font-bold text-gray-900">
                                    O'Level
                                </p>

                                <p class="text-sm text-gray-600 mt-1">
                                    Five credits including English
                                    Language and Mathematics.
                                </p>

                            </div>

                        </div>


                        <div class="flex gap-3">

                            <span class="w-7 h-7
                                         rounded-full
                                         bg-green-100
                                         text-green-700
                                         flex items-center
                                         justify-center
                                         text-sm font-bold
                                         flex-shrink-0">

                                ✓

                            </span>


                            <div>

                                <p class="font-bold text-gray-900">
                                    Bachelor's Degree
                                </p>

                                <p class="text-sm text-gray-600 mt-1">

                                    Minimum Third Class in Public Health
                                    or a related discipline.

                                </p>

                            </div>

                        </div>


                        <div class="flex gap-3">

                            <span class="w-7 h-7
                                         rounded-full
                                         bg-green-100
                                         text-green-700
                                         flex items-center
                                         justify-center
                                         text-sm font-bold
                                         flex-shrink-0">

                                ✓

                            </span>


                            <div>

                                <p class="font-bold text-gray-900">
                                    HND
                                </p>

                                <p class="text-sm text-gray-600 mt-1">

                                    Minimum Lower Credit in a Science
                                    or Health-related discipline.

                                </p>

                            </div>

                        </div>


                        <div class="flex gap-3">

                            <span class="w-7 h-7
                                         rounded-full
                                         bg-green-100
                                         text-green-700
                                         flex items-center
                                         justify-center
                                         text-sm font-bold
                                         flex-shrink-0">

                                ✓

                            </span>


                            <div>

                                <p class="font-bold text-gray-900">
                                    Work Experience
                                </p>

                                <p class="text-sm text-gray-600 mt-1">

                                    Relevant work experience may be
                                    considered where applicable.

                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- Duration --}}

                    <div class="mt-7
                                rounded-2xl
                                bg-green-600
                                text-white
                                p-5">

                        <p class="text-green-100 text-sm">
                            Programme Duration
                        </p>

                        <p class="mt-1
                                  text-lg
                                  font-extrabold">

                            One Year (Two Semesters)

                        </p>

                    </div>


                    {{-- Full requirements button --}}

                    <button
                        type="button"
                        @click="$dispatch(
                            'open-pgd-modal',
                            { programme: 'public-health' }
                        )"

                        class="mt-6
                               w-full
                               inline-flex
                               items-center
                               justify-center
                               gap-2
                               px-5 py-3
                               rounded-xl
                               border-2
                               border-green-600
                               text-green-700
                               font-bold
                               hover:bg-green-50
                               transition">

                        View Full Requirements

                        <svg class="w-5 h-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 5v14m7-7H5" />

                        </svg>

                    </button>

                </div>

            </article>



            {{-- ================================================= --}}
            {{-- SURVEYING CARD --}}
            {{-- ================================================= --}}

            <article id="pgd-surveying"
                class="bg-white
                       rounded-3xl
                       border border-teal-200
                       shadow-lg
                       overflow-hidden">

                <div class="h-1.5 bg-teal-600"></div>


                <div class="p-6 sm:p-8">


                    <div class="flex items-start gap-4">

                        <div class="w-14 h-14
                                    rounded-2xl
                                    bg-teal-100
                                    text-teal-700
                                    flex items-center
                                    justify-center
                                    flex-shrink-0
                                    text-2xl">

                            📐

                        </div>


                        <div class="flex-1">

                            <div class="flex flex-wrap gap-2">

                                <span class="px-2.5 py-1
                                             rounded-full
                                             bg-teal-100
                                             text-teal-700
                                             text-xs font-extrabold">

                                    PGD

                                </span>


                                <span class="px-2.5 py-1
                                             rounded-full
                                             bg-red-100
                                             text-red-700
                                             text-xs font-extrabold">

                                    NEW

                                </span>

                            </div>


                            <h3 class="mt-2
                                       text-2xl
                                       font-extrabold
                                       text-gray-900">

                                Surveying & Geo-Informatics

                            </h3>

                        </div>

                    </div>


                    <p class="mt-5
                              text-gray-600
                              leading-relaxed">

                        Applicants seeking admission into the
                        Postgraduate Diploma in Surveying &
                        Geo-Informatics must satisfy the applicable
                        ND and HND requirements, including the
                        NYSC certificate.

                    </p>


                    {{-- Requirements summary --}}

                    <div class="mt-7 space-y-4">


                        <div class="flex gap-3">

                            <span class="w-7 h-7
                                         rounded-full
                                         bg-teal-100
                                         text-teal-700
                                         flex items-center
                                         justify-center
                                         text-sm font-bold
                                         flex-shrink-0">

                                ✓

                            </span>


                            <div>

                                <p class="font-bold text-gray-900">
                                    O'Level / ND
                                </p>

                                <p class="text-sm text-gray-600 mt-1">

                                    English Language, Mathematics,
                                    Physics and any other two
                                    approved subjects.

                                </p>

                            </div>

                        </div>


                        <div class="flex gap-3">

                            <span class="w-7 h-7
                                         rounded-full
                                         bg-teal-100
                                         text-teal-700
                                         flex items-center
                                         justify-center
                                         text-sm font-bold
                                         flex-shrink-0">

                                ✓

                            </span>


                            <div>

                                <p class="font-bold text-gray-900">
                                    ND in Surveying & Geo-Informatics
                                </p>

                                <p class="text-sm text-gray-600 mt-1">

                                    Minimum Lower Credit pass.

                                </p>

                            </div>

                        </div>


                        <div class="flex gap-3">

                            <span class="w-7 h-7
                                         rounded-full
                                         bg-teal-100
                                         text-teal-700
                                         flex items-center
                                         justify-center
                                         text-sm font-bold
                                         flex-shrink-0">

                                ✓

                            </span>


                            <div>

                                <p class="font-bold text-gray-900">
                                    Cognate Work Experience
                                </p>

                                <p class="text-sm text-gray-600 mt-1">

                                    One year post-ND experience,
                                    or two years where applicable.

                                </p>

                            </div>

                        </div>


                        <div class="flex gap-3">

                            <span class="w-7 h-7
                                         rounded-full
                                         bg-teal-100
                                         text-teal-700
                                         flex items-center
                                         justify-center
                                         text-sm font-bold
                                         flex-shrink-0">

                                ✓

                            </span>


                            <div>

                                <p class="font-bold text-gray-900">
                                    NYSC Certificate
                                </p>

                                <p class="text-sm text-gray-600 mt-1">

                                    Required for the PGD applicant.

                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- Important note --}}

                    <div class="mt-7
                                rounded-2xl
                                border border-teal-200
                                bg-teal-50
                                p-5">

                        <div class="flex items-start gap-3">

                            <span class="w-9 h-9
                                         rounded-xl
                                         bg-teal-600
                                         text-white
                                         flex items-center
                                         justify-center
                                         flex-shrink-0">

                                !

                            </span>


                            <div>

                                <p class="font-bold text-teal-900">

                                    Important

                                </p>

                                <p class="mt-1
                                          text-sm
                                          text-teal-800
                                          leading-relaxed">

                                    PGD applicants must satisfy all
                                    the applicable requirements above,
                                    including the NYSC certificate.

                                </p>

                            </div>

                        </div>

                    </div>


                    <button
                        type="button"
                        @click="$dispatch(
                            'open-pgd-modal',
                            { programme: 'surveying' }
                        )"

                        class="mt-6
                               w-full
                               inline-flex
                               items-center
                               justify-center
                               gap-2
                               px-5 py-3
                               rounded-xl
                               border-2
                               border-teal-600
                               text-teal-700
                               font-bold
                               hover:bg-teal-50
                               transition">

                        View Full Requirements

                        <svg class="w-5 h-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 5v14m7-7H5" />

                        </svg>

                    </button>

                </div>

            </article>

        </div>

    </div>

</section>



{{-- ========================================================= --}}
{{-- UNDERGRADUATE COURSES --}}
{{-- ========================================================= --}}

<section id="undergraduate-courses"
    class="py-20 bg-white">

    <div class="max-w-7xl mx-auto
                px-4 sm:px-6 lg:px-8">


        <div class="max-w-2xl mx-auto text-center">

            <span class="inline-flex
                         px-3 py-1
                         rounded-full
                         bg-green-100
                         text-green-700
                         text-sm font-bold">

                Undergraduate

            </span>


            <h2 class="mt-4
                       text-3xl sm:text-4xl
                       font-extrabold">

                Undergraduate Courses

            </h2>


            <p class="mt-4 text-gray-600">

                Explore available undergraduate
                degree programmes.

            </p>

        </div>


        @if($undergraduateCourses->count() > 0)

            <div class="mt-12 space-y-10">

                @foreach($undergraduateCourses as $department => $courses)

                    <div>

                        <div class="flex items-center gap-4 mb-5">

                            <div class="h-px flex-1 bg-gray-200"></div>

                            <h3 class="text-lg sm:text-xl
                                       font-bold
                                       text-gray-800
                                       text-center">

                                {{ $department }}

                            </h3>

                            <div class="h-px flex-1 bg-gray-200"></div>

                        </div>


                        <div class="grid grid-cols-1
                                    sm:grid-cols-2
                                    lg:grid-cols-3
                                    gap-4">

                            @foreach($courses as $course)

                                <div class="group
                                            bg-white
                                            rounded-xl
                                            border border-gray-200
                                            p-5
                                            hover:border-green-300
                                            hover:shadow-lg
                                            transition">

                                    <div class="flex items-start gap-3">

                                        <div class="w-9 h-9
                                                    rounded-lg
                                                    bg-green-50
                                                    text-green-600
                                                    flex items-center
                                                    justify-center
                                                    flex-shrink-0">

                                            <svg class="w-5 h-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor">

                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M12 6v12m6-6H6" />

                                            </svg>

                                        </div>


                                        <div>

                                            <p class="font-semibold
                                                      text-gray-800
                                                      group-hover:text-green-700
                                                      transition">

                                                {{ $course->name }}

                                            </p>


                                            @if($course->programme?->name)

                                                <p class="text-sm
                                                          text-gray-500
                                                          mt-1">

                                                    {{ $course->programme->name }}

                                                </p>

                                            @endif

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <div class="mt-12
                        text-center
                        bg-gray-50
                        border border-gray-200
                        rounded-2xl
                        p-10">

                <p class="text-gray-500">

                    No undergraduate courses
                    are currently available.

                </p>

            </div>

        @endif

    </div>

</section>



{{-- ========================================================= --}}
{{-- POSTGRADUATE COURSES --}}
{{-- ========================================================= --}}

<section id="postgraduate-courses"
    class="py-20 bg-gray-50">

    <div class="max-w-7xl mx-auto
                px-4 sm:px-6 lg:px-8">


        <div class="max-w-3xl mx-auto text-center">

            <span class="inline-flex
                         px-3 py-1
                         rounded-full
                         bg-teal-100
                         text-teal-700
                         text-sm font-bold">

                Postgraduate Diploma

            </span>


            <h2 class="mt-4
                       text-3xl sm:text-4xl
                       font-extrabold">

                PGD Programmes

            </h2>


            <p class="mt-4 text-gray-600">

                Explore our available Postgraduate Diploma
                programmes and review their admission requirements.

            </p>

        </div>



        @if($postgraduateCourses->count() > 0)

            <div class="mt-12 space-y-10">

                @foreach($postgraduateCourses as $department => $courses)

                    <div>

                        <div class="flex items-center gap-4 mb-5">

                            <div class="h-px flex-1 bg-gray-200"></div>

                            <h3 class="text-lg sm:text-xl
                                       font-bold
                                       text-gray-800
                                       text-center">

                                {{ $department }}

                            </h3>

                            <div class="h-px flex-1 bg-gray-200"></div>

                        </div>


                        <div class="grid grid-cols-1
                                    sm:grid-cols-2
                                    lg:grid-cols-3
                                    gap-4">

                            @foreach($courses as $course)

                                <div class="group
                                            bg-white
                                            rounded-xl
                                            border border-gray-200
                                            p-5
                                            hover:border-teal-300
                                            hover:shadow-lg
                                            transition">

                                    <div class="flex items-start gap-3">

                                        <div class="w-9 h-9
                                                    rounded-lg
                                                    bg-teal-50
                                                    text-teal-600
                                                    flex items-center
                                                    justify-center
                                                    flex-shrink-0">

                                            <svg class="w-5 h-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor">

                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M9 12h6m-6 4h6
                                                       m2 5H7a2 2 0 01-2-2V5
                                                       a2 2 0 012-2h5.586
                                                       a1 1 0 01.707.293
                                                       l5.414 5.414
                                                       A1 1 0 0119 9.414V19
                                                       a2 2 0 01-2 2z" />

                                            </svg>

                                        </div>


                                        <div>

                                            <p class="font-semibold
                                                      text-gray-800
                                                      group-hover:text-teal-700
                                                      transition">

                                                {{ $course->name }}

                                            </p>


                                            @if($course->programme?->name)

                                                <p class="text-sm
                                                          text-gray-500
                                                          mt-1">

                                                    {{ $course->programme->name }}

                                                </p>

                                            @endif

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <div class="mt-12
                        text-center
                        bg-white
                        border border-gray-200
                        rounded-2xl
                        p-10">

                <p class="text-gray-500">

                    No postgraduate courses
                    are currently available.

                </p>

            </div>

        @endif

    </div>

</section>



{{-- ========================================================= --}}
{{-- CONTACT --}}
{{-- ========================================================= --}}

<section id="contact-us"
    class="bg-gradient-to-br
           from-green-800
           via-green-700
           to-teal-700">

    <div class="max-w-7xl mx-auto
                px-4 sm:px-6 lg:px-8
                py-20">

        <div class="grid grid-cols-1
                    lg:grid-cols-2
                    gap-12
                    items-start">


            {{-- Contact information --}}

            <div class="text-white">

                <span class="inline-flex
                             px-3 py-1
                             rounded-full
                             bg-white/10
                             border border-white/20
                             text-green-100
                             text-sm
                             font-semibold">

                    Need Assistance?

                </span>


                <h2 class="mt-5
                           text-3xl sm:text-4xl
                           font-extrabold">

                    Start Your Journey

                </h2>


                <p class="mt-5
                          text-white/80
                          text-lg
                          leading-relaxed">

                    Have questions about admission, programmes
                    or the application process? Contact the
                    Directorate of Higher Studies.

                </p>


                <div class="mt-8 space-y-6">


                    {{-- Office --}}

                    <div class="flex items-start gap-4">

                        <div class="w-11 h-11
                                    rounded-xl
                                    bg-white/10
                                    flex items-center
                                    justify-center
                                    flex-shrink-0">

                            🏢

                        </div>


                        <div>

                            <p class="font-semibold">

                                Directorate of Higher Studies

                            </p>


                            <p class="mt-1 text-white/70">

                                Waziri Umaru Federal Polytechnic,
                                Birnin Kebbi

                            </p>

                        </div>

                    </div>


                    {{-- Phone --}}

                    <div class="flex items-start gap-4">

                        <div class="w-11 h-11
                                    rounded-xl
                                    bg-white/10
                                    flex items-center
                                    justify-center
                                    flex-shrink-0">

                            📞

                        </div>


                        <div>

                            <p class="font-semibold">
                                Telephone
                            </p>


                            <p class="mt-1
                                      text-white/70
                                      leading-relaxed">

                                <a href="tel:07064429665" class="hover:text-white transition">07064429665</a><br>
                                <a href="tel:07033071497" class="hover:text-white transition">07033071497</a><br>
                                <a href="tel:08068991818" class="hover:text-white transition">08068991818</a><br>
                                <a href="tel:08036397597" class="hover:text-white transition">08036397597</a>

                            </p>

                        </div>

                    </div>

                </div>

            </div>



            {{-- Contact form --}}

            <div class="bg-white
                        rounded-3xl
                        shadow-2xl
                        p-6 sm:p-8">


                <div class="text-center mb-7">

                    <h3 class="text-2xl
                               font-bold
                               text-gray-900">

                        Send Us a Message

                    </h3>


                    <p class="mt-2
                              text-sm
                              text-gray-500">

                        We'll get back to you as soon as possible.

                    </p>

                </div>


                @if(session('success'))

                    <div class="mb-6
                                rounded-xl
                                bg-green-50
                                border border-green-200
                                p-4">

                        <p class="text-sm
                                  font-medium
                                  text-green-800">

                            {{ session('success') }}

                        </p>

                    </div>

                @endif


                @if($errors->any())

                    <div class="mb-6
                                rounded-xl
                                bg-red-50
                                border border-red-200
                                p-4">

                        <p class="text-sm
                                  font-semibold
                                  text-red-800">

                            {{ $errors->first() }}

                        </p>

                    </div>

                @endif


                <form
                    method="POST"
                    action="{{ route('contact.send') }}"
                    class="space-y-5"
                    x-data="{ submitting: false }"
                    @submit="submitting = true">

                    @csrf


                    {{-- Name --}}

                    <div>

                        <label
                            for="name"
                            class="block
                                   text-sm
                                   font-semibold
                                   text-gray-700">

                            Full Name

                        </label>


                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                            placeholder="Enter your full name"
                            class="mt-2 block w-full
                                   rounded-xl
                                   border-gray-300
                                   px-4 py-3
                                   shadow-sm
                                   focus:border-green-500
                                   focus:ring-green-500">

                    </div>


                    {{-- Email --}}

                    <div>

                        <label
                            for="email"
                            class="block
                                   text-sm
                                   font-semibold
                                   text-gray-700">

                            Email Address

                        </label>


                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            placeholder="you@example.com"
                            class="mt-2 block w-full
                                   rounded-xl
                                   border-gray-300
                                   px-4 py-3
                                   shadow-sm
                                   focus:border-green-500
                                   focus:ring-green-500">

                    </div>


                    {{-- Message --}}

                    <div>

                        <label
                            for="message"
                            class="block
                                   text-sm
                                   font-semibold
                                   text-gray-700">

                            Message

                        </label>


                        <textarea
                            id="message"
                            name="message"
                            rows="5"
                            required
                            placeholder="How can we help you?"
                            class="mt-2 block w-full
                                   rounded-xl
                                   border-gray-300
                                   px-4 py-3
                                   shadow-sm
                                   focus:border-green-500
                                   focus:ring-green-500">{{ old('message') }}</textarea>

                    </div>


                    <button
                        type="submit"
                        :disabled="submitting"
                        :class="submitting ? 'opacity-75 cursor-not-allowed' : ''"
                        class="w-full
                               inline-flex
                               items-center
                               justify-center
                               px-5 py-3.5
                               rounded-xl
                               bg-green-600
                               text-white
                               font-bold
                               hover:bg-green-700
                               focus:outline-none
                               focus:ring-2
                               focus:ring-green-500
                               focus:ring-offset-2
                               transition">

                        <template x-if="!submitting">
                            <span class="inline-flex items-center">
                                Send Message

                                <svg class="w-5 h-5 ml-2"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">

                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />

                                </svg>
                            </span>
                        </template>

                        <template x-if="submitting">
                            <span class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Sending...
                            </span>
                        </template>

                    </button>

                </form>

            </div>

        </div>

    </div>

</section>



</main>

{{-- ========================================================= --}}
{{-- FOOTER --}}
{{-- ========================================================= --}}

<footer class="bg-gray-900 text-gray-400">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

            <div class="lg:col-span-2">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/img/logo-ct.png') }}"
                         alt="WUFPBK Logo"
                         class="w-10 h-10 rounded-full object-cover"
                         onerror="this.style.display='none'">
                    <p class="font-extrabold text-white leading-tight">
                        Directorate of Higher Studies
                    </p>
                </div>
                <p class="mt-4 text-sm leading-relaxed max-w-md">
                    Waziri Umaru Federal Polytechnic, Birnin Kebbi. Affiliated degree and
                    postgraduate diploma programmes supporting your academic and professional goals.
                </p>
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-white">Quick Links</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="#programmes" class="hover:text-white transition">Programmes</a></li>
                    <li><a href="#screening-announcement" class="hover:text-white transition">Screening Notice</a></li>
                    <li><a href="#pgd-requirements" class="hover:text-white transition">PGD Requirements</a></li>
                    <li><a href="#contact-us" class="hover:text-white transition">Contact</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-white">Student Portals</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="{{ route('degree-login') }}" class="hover:text-white transition">Degree Student Login</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">PGD Student Login</a></li>
                </ul>
                <p class="mt-5 text-sm">
                    <a href="tel:07064429665" class="hover:text-white transition">07064429665</a><br>
                    <a href="tel:07033071497" class="hover:text-white transition">07033071497</a>
                </p>
            </div>

        </div>

        <div class="mt-10 pt-6 border-t border-gray-800 text-center sm:text-left">
            <p class="text-sm">
                &copy; {{ date('Y') }} Directorate of Higher Studies,
                Waziri Umaru Federal Polytechnic, Birnin Kebbi. All rights reserved.
            </p>
        </div>

    </div>

</footer>



{{-- ========================================================= --}}
{{-- PGD REQUIREMENTS MODAL --}}
{{-- ========================================================= --}}

<div
    x-data="{
        open: false,
        programme: null
    }"
    x-on:open-pgd-modal.window="
        programme = $event.detail.programme;
        open = true;
    "
    x-on:keydown.escape.window="open = false"
>
    <div
        x-cloak
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-[9999] overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >

        {{-- Backdrop --}}
        <div
            class="fixed inset-0 bg-black/60 backdrop-blur-sm"
            @click="open = false"
        ></div>

        {{-- Modal Wrapper --}}
        <div class="relative min-h-screen flex items-center justify-center p-4 sm:p-6">

            {{-- Modal --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.stop
                class="relative w-full max-w-3xl max-h-[90vh] overflow-hidden rounded-3xl bg-white shadow-2xl"
            >

                {{-- Header --}}
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-5 sm:px-7 py-4">

                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-green-600">
                            PGD Admissions
                        </p>

                        <h2 class="mt-1 text-xl sm:text-2xl font-extrabold text-gray-900">
                            <span x-show="programme === 'public-health'">
                                PGD Public Health
                            </span>

                            <span x-show="programme === 'surveying'">
                                PGD Surveying & Geo-Informatics
                            </span>
                        </h2>
                    </div>

                    <button
                        type="button"
                        @click="open = false"
                        class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition"
                        aria-label="Close"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                </div>

                {{-- Scrollable Content --}}
                <div class="overflow-y-auto max-h-[calc(90vh-80px)]">

                    {{-- PUBLIC HEALTH --}}
                    <div
                        x-show="programme === 'public-health'"
                        class="p-5 sm:p-7"
                    >

                        <div class="rounded-2xl border border-green-200 bg-green-50 p-5">
                            <div class="flex items-start gap-3">

                                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-green-600 text-white flex items-center justify-center">
                                    🎓
                                </div>

                                <div>
                                    <h3 class="font-bold text-green-900">
                                        Admission Requirements
                                    </h3>
                                    <p class="mt-1 text-sm text-green-800">
                                        PGD Public Health
                                    </p>
                                </div>

                            </div>
                        </div>

                        <div class="mt-7 space-y-6">

                            <div class="flex gap-4">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold">
                                    1
                                </span>

                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        O'Level Requirements
                                    </h4>

                                    <p class="mt-2 text-sm sm:text-base leading-relaxed text-gray-600">
                                        Five O’Level credit passes in WAEC, NECO, NABTEB, GCE or equivalent,
                                        including English Language and Mathematics.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold">
                                    2
                                </span>

                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        Bachelor's Degree
                                    </h4>

                                    <p class="mt-2 text-sm sm:text-base leading-relaxed text-gray-600">
                                        Bachelor’s Degree with at least Third Class Honours in Public Health
                                        or a related discipline.
                                    </p>

                                    <div class="mt-3 rounded-xl bg-gray-50 border border-gray-200 p-4">
                                        <p class="text-sm font-semibold text-gray-700">
                                            Related disciplines may include:
                                        </p>

                                        <p class="mt-2 text-sm leading-relaxed text-gray-600">
                                            Public Health, Microbiology, Biochemistry, Biology, Nursing,
                                            Environmental Health and other relevant Health & Life Sciences
                                            disciplines.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold">
                                    3
                                </span>

                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        HND Requirement
                                    </h4>

                                    <p class="mt-2 text-sm sm:text-base leading-relaxed text-gray-600">
                                        HND with at least Lower Credit in a Science or Health-related discipline.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold">
                                    4
                                </span>

                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        Work Experience
                                    </h4>

                                    <p class="mt-2 text-sm sm:text-base leading-relaxed text-gray-600">
                                        Relevant work experience may be considered where applicable.
                                    </p>
                                </div>
                            </div>

                        </div>

                        <div class="mt-8 rounded-2xl bg-green-600 p-5 text-white">
                            <p class="text-sm text-green-100">
                                Programme Duration
                            </p>

                            <p class="mt-1 text-lg font-extrabold">
                                One Year (Two Semesters)
                            </p>
                        </div>

                    </div>

                    {{-- SURVEYING --}}
                    <div
                        x-show="programme === 'surveying'"
                        class="p-5 sm:p-7"
                    >

                        <div class="rounded-2xl border border-teal-200 bg-teal-50 p-5">
                            <div class="flex items-start gap-3">

                                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center">
                                    📐
                                </div>

                                <div>
                                    <h3 class="font-bold text-teal-900">
                                        Admission Requirements
                                    </h3>

                                    <p class="mt-1 text-sm text-teal-800">
                                        PGD Surveying & Geo-Informatics
                                    </p>
                                </div>

                            </div>
                        </div>

                        <div class="mt-7 space-y-6">

                            <div class="flex gap-4">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold">
                                    1
                                </span>

                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        O'Level Requirements
                                    </h4>

                                    <p class="mt-2 text-sm sm:text-base leading-relaxed text-gray-600">
                                        WASC, NECO, SSCE, GCE O’Level or equivalent with credits in English
                                        Language, Mathematics, Physics and any other two approved subjects.
                                    </p>

                                    <p class="mt-3 text-sm font-semibold text-gray-700">
                                        Not more than two sittings.
                                    </p>

                                    <div class="mt-3 rounded-xl bg-gray-50 border border-gray-200 p-4">
                                        <p class="text-sm font-semibold text-gray-700">
                                            Other approved subjects include:
                                        </p>

                                        <p class="mt-2 text-sm leading-relaxed text-gray-600">
                                            Statistics, Further Mathematics, Chemistry, Technical Drawing,
                                            Basic Surveying, Geography, Economics, Biology and Agricultural Science.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold">
                                    2
                                </span>

                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        ND Requirement
                                    </h4>

                                    <p class="mt-2 text-sm sm:text-base leading-relaxed text-gray-600">
                                        ND in Surveying and Geo-Informatics with a minimum of Lower Credit.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold">
                                    3
                                </span>

                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        Cognate Work Experience
                                    </h4>

                                    <p class="mt-2 text-sm sm:text-base leading-relaxed text-gray-600">
                                        Minimum of one year post-ND cognate work experience.
                                    </p>

                                    <p class="mt-2 text-sm sm:text-base leading-relaxed text-gray-600">
                                        Applicants with a Pass may require two years post-ND cognate experience
                                        in Surveying, Geo-Informatics or a related field.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold">
                                    4
                                </span>

                                <div>
                                    <h4 class="font-bold text-gray-900">
                                        NYSC Certificate
                                    </h4>

                                    <p class="mt-2 text-sm sm:text-base leading-relaxed text-gray-600">
                                        NYSC Certificate is required for the PGD applicant.
                                    </p>
                                </div>
                            </div>

                        </div>

                        <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                            <div class="flex items-start gap-3">

                                <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold">
                                    !
                                </div>

                                <div>
                                    <h4 class="font-bold text-amber-900">
                                        Important
                                    </h4>

                                    <p class="mt-1 text-sm leading-relaxed text-amber-800">
                                        PGD applicants must satisfy all applicable requirements, including
                                        the NYSC Certificate.
                                    </p>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>

                {{-- Footer --}}
                <div class="border-t border-gray-200 bg-gray-50 px-5 sm:px-7 py-4 flex flex-col sm:flex-row gap-3 sm:justify-between sm:items-center">

                    <p class="text-xs sm:text-sm text-gray-500">
                        Please review all requirements before applying.
                    </p>

                    <button
                        type="button"
                        @click="open = false"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 transition"
                    >
                        Close
                    </button>

                </div>

            </div>
        </div>
    </div>
</div>


{{-- ========================================================= --}}
{{-- BACK TO TOP --}}
{{-- ========================================================= --}}

<a href="#"
    aria-label="Back to top"
    class="fixed bottom-5 right-5
           w-11 h-11
           rounded-full
           bg-green-600
           text-white
           flex items-center
           justify-center
           shadow-lg
           hover:bg-green-700
           transition
           z-40">

    <svg class="w-5 h-5"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor">

        <path stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M5 15l7-7 7 7" />

    </svg>

</a>


</body>

</html>