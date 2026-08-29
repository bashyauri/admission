<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description"
        content="Directorate of Higher Studies, Waziri Umaru Federal Polytechnic Birnin Kebbi. Explore undergraduate degree and postgraduate diploma programmes and apply today.">

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
        }
    </script>
</head>


<body class="bg-gray-50 text-gray-800 antialiased">


    {{-- ========================================================= --}}
    {{-- ADMISSION ANNOUNCEMENT --}}
    {{-- ========================================================= --}}

    <div class="bg-yellow-50 border-b border-yellow-300">

        <div class="max-w-7xl mx-auto px-4 py-3">

            <div class="flex flex-col sm:flex-row
                        items-center justify-center
                        gap-2 sm:gap-3
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


                <a href="#programs"
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



    {{-- ========================================================= --}}
    {{-- NEW PGD PROGRAMMES --}}
    {{-- ========================================================= --}}

    <section class="bg-green-700 text-white">

        <div class="max-w-6xl mx-auto px-4 py-3">

            <div class="flex flex-col md:flex-row
                        items-center justify-center
                        gap-3
                        text-center">

                <div class="flex items-center gap-2">

                    <span class="inline-flex items-center
                                 px-3 py-1
                                 rounded-full
                                 bg-white
                                 text-green-700
                                 text-xs font-extrabold
                                 uppercase
                                 tracking-wide">

                        New Programmes

                    </span>


                    <span class="hidden sm:inline
                                 text-green-100
                                 text-sm">

                        Two new PGD programmes are now available:

                    </span>

                </div>


                <div class="flex flex-wrap
                            justify-center
                            gap-2">

                    <a href="#postgraduate-courses"
                        class="px-3 py-1.5
                               rounded-lg
                               bg-green-800
                               text-white
                               border border-green-500
                               text-sm font-semibold
                               hover:bg-green-900
                               transition">

                        PGD Public Health

                    </a>


                    <a href="#postgraduate-courses"
                        class="px-3 py-1.5
                               rounded-lg
                               bg-green-800
                               text-white
                               border border-green-500
                               text-sm font-semibold
                               hover:bg-green-900
                               transition">

                        PGD Surveying & Geo-Informatics

                    </a>

                </div>

            </div>

        </div>

    </section>



    {{-- ========================================================= --}}
    {{-- PGD PHYSICAL SCREENING NOTICE --}}
    {{-- ========================================================= --}}

    <section class="bg-red-50 border-b border-red-200">

        <div class="max-w-6xl mx-auto px-4 py-4">

            <div class="flex flex-col
                        lg:flex-row
                        items-center
                        justify-center
                        gap-4
                        text-center
                        lg:text-left">


                {{-- Notice Icon --}}
                <div class="w-11 h-11
                            rounded-xl
                            bg-red-100
                            text-red-600
                            flex items-center
                            justify-center
                            flex-shrink-0">

                    <svg class="w-6 h-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01
                               M4.93 19.07a10 10 0 1114.14 0
                               A10 10 0 014.93 19.07z" />

                    </svg>

                </div>


                {{-- Notice Text --}}
                <div class="flex-1">

                    <div class="flex flex-wrap
                                items-center
                                justify-center
                                lg:justify-start
                                gap-2">

                        <span class="inline-flex
                                     px-2.5 py-1
                                     rounded-full
                                     bg-red-600
                                     text-white
                                     text-xs
                                     font-extrabold
                                     uppercase
                                     tracking-wide">

                            Important Notice

                        </span>


                        <h2 class="font-bold text-gray-900">

                            PGD Physical Screening Has Started

                        </h2>

                    </div>


                    <p class="mt-1
                              text-sm
                              text-gray-700">

                        Physical screening for Postgraduate Diploma
                        applicants is now ongoing.

                    </p>

                </div>


                {{-- Screening Details --}}
                <div class="flex flex-wrap
                            justify-center
                            gap-2
                            lg:max-w-xl">


                    {{-- Days --}}
                    <div class="inline-flex
                                items-center
                                gap-2
                                px-3 py-2
                                rounded-lg
                                bg-white
                                border border-red-200
                                text-sm
                                font-semibold
                                text-gray-800">

                        <svg class="w-4 h-4 text-red-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3M5 11h14
                                   M5 7h14a2 2 0 012 2v10
                                   a2 2 0 01-2 2H5
                                   a2 2 0 01-2-2V9
                                   a2 2 0 012-2z" />

                        </svg>

                        Monday & Thursday

                    </div>


                    {{-- Time --}}
                    <div class="inline-flex
                                items-center
                                gap-2
                                px-3 py-2
                                rounded-lg
                                bg-red-600
                                text-white
                                text-sm
                                font-bold">

                        <svg class="w-4 h-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6v6l4 2" />

                        </svg>

                        10:00 AM – 1:00 PM

                    </div>


                    {{-- Venue --}}
                    <div class="inline-flex
                                items-center
                                gap-2
                                px-3 py-2
                                rounded-lg
                                bg-white
                                border border-red-200
                                text-sm
                                font-semibold
                                text-gray-800">

                        <svg class="w-4 h-4 text-red-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17.657 16.657L13.414 21
                                   a2 2 0 01-2.828 0
                                   l-4.243-4.343
                                   a8 8 0 1111.314 0z" />

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 11a3 3 0 11-6 0
                                   3 3 0 016 0z" />

                        </svg>

                        Directorate of Higher Studies

                    </div>

                </div>

            </div>

        </div>

    </section>



    {{-- ========================================================= --}}
    {{-- HERO SECTION --}}
    {{-- ========================================================= --}}

    <section class="relative overflow-hidden
                    bg-gradient-to-br
                    from-green-800
                    via-green-700
                    to-teal-700">


        {{-- Decorative Background --}}
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
                    py-12 sm:py-16 lg:py-20">

            <div class="max-w-3xl mx-auto text-center">


                {{-- Logo --}}
                <div class="flex justify-center mb-5">

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


                {{-- Institution --}}
                <p class="text-green-200
                          font-bold
                          uppercase
                          tracking-wider
                          text-xs sm:text-sm">

                    Waziri Umaru Federal Polytechnic,
                    Birnin Kebbi

                </p>


                {{-- Heading --}}
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


                {{-- Description --}}
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


                {{-- Session --}}
                <div class="mt-5 flex justify-center">

                    <span class="inline-flex
                                 items-center
                                 gap-2
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


                {{-- CTA --}}
                <div class="mt-7
                            flex flex-col
                            sm:flex-row
                            justify-center
                            gap-3">


                    <a href="#programs"
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


                    <a href="#postgraduate-courses"
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

                        View PGD Programmes

                    </a>

                </div>

            </div>

        </div>

    </section>



    {{-- ========================================================= --}}
    {{-- QUICK INFORMATION --}}
    {{-- ========================================================= --}}

    <section class="relative -mt-7">

        <div class="max-w-6xl mx-auto px-4">

            <div class="grid grid-cols-1
                        md:grid-cols-3
                        gap-5">


                {{-- Admissions --}}
                <div class="bg-white
                            rounded-2xl
                            shadow-xl
                            border border-gray-100
                            p-6">

                    <div class="w-11 h-11
                                rounded-xl
                                bg-green-100
                                text-green-700
                                flex items-center
                                justify-center">

                        <svg class="w-6 h-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 14l9-5-9-5-9 5
                                   9 5zm0 0v6m-4 0h8" />

                        </svg>

                    </div>


                    <h3 class="mt-4
                               font-bold
                               text-lg">

                        Admissions Open

                    </h3>


                    <p class="mt-2
                              text-sm
                              leading-relaxed
                              text-gray-600">

                        Applications are open for Undergraduate Degree
                        and Postgraduate Diploma programmes.

                    </p>

                </div>


                {{-- Screening --}}
                <div class="bg-white
                            rounded-2xl
                            shadow-xl
                            border border-red-100
                            p-6">

                    <div class="w-11 h-11
                                rounded-xl
                                bg-red-100
                                text-red-600
                                flex items-center
                                justify-center">

                        <svg class="w-6 h-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M8 7V3m8 4V3
                                   M5 11h14
                                   M5 7h14a2 2 0 012 2v10
                                   a2 2 0 01-2 2H5
                                   a2 2 0 01-2-2V9
                                   a2 2 0 012-2z" />

                        </svg>

                    </div>


                    <h3 class="mt-4
                               font-bold
                               text-lg">

                        PGD Physical Screening

                    </h3>


                    <p class="mt-2
                              text-sm
                              leading-relaxed
                              text-gray-600">

                        Every
                        <strong>Monday & Thursday</strong>
                        from
                        <strong>10:00 AM – 1:00 PM.</strong>

                    </p>


                    <p class="mt-2
                              text-sm
                              font-semibold
                              text-red-600">

                        Venue: Directorate of Higher Studies

                    </p>

                </div>


                {{-- New Programmes --}}
                <div class="bg-white
                            rounded-2xl
                            shadow-xl
                            border border-gray-100
                            p-6">

                    <div class="w-11 h-11
                                rounded-xl
                                bg-teal-100
                                text-teal-700
                                flex items-center
                                justify-center">

                        <svg class="w-6 h-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 6v12m6-6H6" />

                        </svg>

                    </div>


                    <h3 class="mt-4
                               font-bold
                               text-lg">

                        New PGD Programmes

                    </h3>


                    <p class="mt-2
                              text-sm
                              leading-relaxed
                              text-gray-600">

                        PGD Public Health and PGD Surveying &
                        Geo-Informatics are now available.

                    </p>

                </div>

            </div>

        </div>

    </section>



    {{-- ========================================================= --}}
    {{-- PROGRAMMES --}}
    {{-- ========================================================= --}}

    <section id="programs"
        class="py-20 bg-white">

        <div class="max-w-6xl mx-auto
                    px-4 sm:px-6 lg:px-8">


            <div class="max-w-2xl mx-auto
                        text-center">

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

                    Select the programme that matches
                    your academic and professional goals.

                </p>

            </div>


            <div class="mt-12
                        grid grid-cols-1
                        lg:grid-cols-2
                        gap-8">


                {{-- ================================================= --}}
                {{-- UNDERGRADUATE PROGRAMME --}}
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

                        <div class="flex items-start
                                    justify-between
                                    gap-4">

                            <div>

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

                                    Undergraduate
                                    Programmes

                                </h3>

                            </div>


                            <div class="w-14 h-14
                                        rounded-2xl
                                        bg-green-100
                                        text-green-700
                                        flex items-center
                                        justify-center
                                        flex-shrink-0">

                                <svg class="w-7 h-7"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">

                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M12 3v2.25
                                           M19.5 10.5l-7.5 4.5
                                           -7.5-4.5m15 0v6.75
                                           a2.25 2.25 0 01-2.25 2.25h-9
                                           A2.25 2.25 0 013 17.25V10.5
                                           M19.5 10.5L12 6l-7.5 4.5" />

                                </svg>

                            </div>

                        </div>


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
                {{-- POSTGRADUATE PROGRAMME --}}
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

                        <div class="flex items-start
                                    justify-between
                                    gap-4">

                            <div>

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

                            </div>


                            <div class="w-14 h-14
                                        rounded-2xl
                                        bg-teal-100
                                        text-teal-700
                                        flex items-center
                                        justify-center
                                        flex-shrink-0">

                                <svg class="w-7 h-7"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">

                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M6.75 3.75h10.5
                                           A2.25 2.25 0 0119.5 6v12
                                           a2.25 2.25 0 01-2.25 2.25H6.75
                                           A2.25 2.25 0 014.5 18V6
                                           a2.25 2.25 0 012.25-2.25z
                                           M9 8.25h6M9 12h6M9 15.75h3" />

                                </svg>

                            </div>

                        </div>


                        <p class="mt-5
                                  text-gray-600
                                  leading-relaxed">

                            Apply for our Postgraduate Diploma programmes
                            and advance your academic and professional
                            qualifications.

                        </p>


                        {{-- New programmes --}}
                        <div class="mt-5 space-y-2">

                            <div class="flex items-center gap-2
                                        text-sm font-semibold">

                                <span class="w-2 h-2
                                             rounded-full
                                             bg-teal-500">
                                </span>

                                PGD Public Health

                            </div>


                            <div class="flex items-center gap-2
                                        text-sm font-semibold">

                                <span class="w-2 h-2
                                             rounded-full
                                             bg-teal-500">
                                </span>

                                PGD Surveying & Geo-Informatics

                            </div>

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


                            <a href="#postgraduate-courses"
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

                                View PGD Courses

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>



    {{-- ========================================================= --}}
    {{-- UNDERGRADUATE COURSES --}}
    {{-- ========================================================= --}}

    <section id="undergraduate-courses"
        class="py-20 bg-gray-50">

        <div class="max-w-7xl mx-auto
                    px-4 sm:px-6 lg:px-8">


            <div class="max-w-2xl mx-auto
                        text-center">

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


                <p class="mt-4
                          text-gray-600">

                    Explore available undergraduate
                    degree programmes.

                </p>

            </div>


            @if($undergraduateCourses->count() > 0)

                <div class="mt-12 space-y-10">

                    @foreach($undergraduateCourses as $department => $courses)

                        <div>

                            <div class="flex items-center
                                        gap-4 mb-5">

                                <div class="h-px
                                            flex-1
                                            bg-gray-200">
                                </div>


                                <h3 class="text-lg sm:text-xl
                                           font-bold
                                           text-gray-800
                                           text-center">

                                    {{ $department }}

                                </h3>


                                <div class="h-px
                                            flex-1
                                            bg-gray-200">
                                </div>

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

                                        <div class="flex items-start
                                                    gap-3">

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
                            bg-white
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
        class="py-20 bg-white">

        <div class="max-w-7xl mx-auto
                    px-4 sm:px-6 lg:px-8">


            <div class="max-w-3xl mx-auto
                        text-center">

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


                <p class="mt-4
                          text-gray-600">

                    Explore our available Postgraduate Diploma
                    programmes. Applications are currently open.

                </p>

            </div>


            {{-- New programmes highlight --}}
            <div class="mt-10
                        max-w-4xl
                        mx-auto">

                <div class="rounded-2xl
                            border border-green-200
                            bg-green-50
                            p-6">

                    <div class="flex flex-col
                                sm:flex-row
                                items-start
                                sm:items-center
                                gap-5">


                        <div class="w-12 h-12
                                    rounded-xl
                                    bg-green-600
                                    text-white
                                    flex items-center
                                    justify-center
                                    flex-shrink-0">

                            <svg class="w-6 h-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor">

                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4" />

                            </svg>

                        </div>


                        <div class="flex-1">

                            <div class="flex flex-wrap
                                        items-center gap-2">

                                <h3 class="font-bold text-lg">

                                    New PGD Programmes

                                </h3>


                                <span class="px-2 py-1
                                             rounded-full
                                             bg-red-600
                                             text-white
                                             text-xs
                                             font-bold">

                                    NEW

                                </span>

                            </div>


                            <p class="mt-1
                                      text-sm
                                      text-gray-600">

                                We are pleased to announce
                                the addition of:

                            </p>


                            <div class="mt-4
                                        flex flex-wrap
                                        gap-2">

                                <span class="px-4 py-2
                                             rounded-lg
                                             bg-white
                                             border border-green-200
                                             text-green-800
                                             text-sm
                                             font-semibold
                                             shadow-sm">

                                    PGD Public Health

                                </span>


                                <span class="px-4 py-2
                                             rounded-lg
                                             bg-white
                                             border border-green-200
                                             text-green-800
                                             text-sm
                                             font-semibold
                                             shadow-sm">

                                    PGD Surveying &
                                    Geo-Informatics

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            @if($postgraduateCourses->count() > 0)

                <div class="mt-12 space-y-10">

                    @foreach($postgraduateCourses as $department => $courses)

                        <div>

                            <div class="flex items-center
                                        gap-4 mb-5">

                                <div class="h-px
                                            flex-1
                                            bg-gray-200">
                                </div>


                                <h3 class="text-lg sm:text-xl
                                           font-bold
                                           text-gray-800
                                           text-center">

                                    {{ $department }}

                                </h3>


                                <div class="h-px
                                            flex-1
                                            bg-gray-200">
                                </div>

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

                                        <div class="flex items-start
                                                    gap-3">

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
                            bg-gray-50
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
    {{-- CONTACT SECTION --}}
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


                {{-- Contact Information --}}
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

                        Have questions about admission,
                        programmes or the application process?
                        Contact the Directorate of Higher Studies.

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

                                <svg class="w-5 h-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">

                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M3 21h18M5 21V5
                                           a2 2 0 012-2h10
                                           a2 2 0 012 2v16
                                           M9 7h1m4 0h1
                                           M9 11h1m4 0h1
                                           M9 15h1m4 0h1
                                           M8 21v-4h8v4" />

                                </svg>

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

                                <svg class="w-5 h-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">

                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M2.25 6.75
                                           c0 8.284 6.716 15 15 15
                                           h2.25
                                           a2.25 2.25 0 002.25-2.25
                                           v-1.372
                                           c0-.516-.351-.966-.852-1.091
                                           l-4.423-1.106
                                           c-.44-.11-.902.055-1.173.417
                                           l-.97 1.293
                                           c-.282.376-.769.542-1.21.38
                                           a12.035 12.035 0 01-7.143-7.143
                                           c-.162-.441.004-.928.38-1.21
                                           l1.293-.97
                                           c.363-.271.527-.734.417-1.173
                                           L6.963 3.102
                                           a1.125 1.125 0 00-1.091-.852H4.5
                                           A2.25 2.25 0 002.25 4.5v2.25Z" />

                                </svg>

                            </div>


                            <div>

                                <p class="font-semibold">
                                    Telephone
                                </p>

                                <p class="mt-1
                                          text-white/70
                                          leading-relaxed">

                                    08036235488<br>
                                    07033071497<br>
                                    08039273164<br>
                                    08065332564

                                </p>

                            </div>

                        </div>

                    </div>

                </div>



                {{-- Contact Form --}}
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


                    {{-- Success Message --}}
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


                    {{-- Validation Error --}}
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
                        class="space-y-5">

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


                        {{-- Submit --}}
                        <button
                            type="submit"
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

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </section>



    {{-- ========================================================= --}}
    {{-- FOOTER --}}
    {{-- ========================================================= --}}

    <footer class="bg-gray-900 text-gray-400">

        <div class="max-w-7xl mx-auto
                    px-4 sm:px-6 lg:px-8
                    py-8">

            <div class="flex flex-col
                        md:flex-row
                        items-center
                        justify-between
                        gap-4">


                <div class="text-center
                            md:text-left">

                    <p class="text-sm">

                        &copy; {{ date('Y') }}
                        Directorate of Higher Studies,
                        Waziri Umaru Federal Polytechnic,
                        Birnin Kebbi.

                    </p>


                    <p class="mt-1
                              text-xs
                              text-gray-500">

                        All rights reserved.

                    </p>

                </div>


                <div class="flex items-center
                            gap-5
                            text-sm">

                    <a href="#programs"
                        class="hover:text-white transition">

                        Programmes

                    </a>


                    <a href="#postgraduate-courses"
                        class="hover:text-white transition">

                        PGD

                    </a>


                    <a href="#contact-us"
                        class="hover:text-white transition">

                        Contact

                    </a>

                </div>

            </div>

        </div>

    </footer>



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
               transition">

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
