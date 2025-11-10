<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DHS Wufpbk</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .btn-green {
            @apply bg-green-600 text-white px-8 py-3 rounded-full shadow-lg transition-all duration-300 ease-in-out transform hover:bg-green-700 hover:scale-105;
        }

        .card {
            @apply p-6 border rounded-lg bg-gray-100 transition-shadow duration-300 ease-in-out hover:shadow-lg;
        }

        .announcement-badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>

<body class="text-gray-800 bg-gray-50">

    <section class="relative py-12 text-white bg-gradient-to-r from-green-500 via-teal-400 to-green-500">
        <div class="container px-6 py-8 mx-auto text-center">
            <img class="w-32 md:w-40 lg:w-48 h-auto p-3 mx-auto m-3" alt="Wufpbk Logo" src="{{ asset('assets') }}/img/wufp-logo.png">

            <h1 class="text-4xl font-bold leading-tight md:text-5xl">
                Welcome to <span class="text-yellow-300">Directorate of Higher Studies</span>
            </h1>
            <p class="mt-4 text-lg md:text-xl">
                Waziri Umaru Federal Polytechnic Birnin Kebbi.
            </p>
            <div class="mt-6 space-x-4">
                <a href="#contact-us"
                    class="rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Contact
                    Us</a>
                <a href="#programs" class="btn-green">Explore Programs</a>
            </div>
        </div>
    </section>

    <!-- Announcement Section -->
    <section id="announcement" class="py-8 bg-yellow-50 border-b-4 border-yellow-400">
        <div class="container px-6 mx-auto">
            <div class="flex flex-col items-center justify-between p-6 bg-white rounded-lg shadow-lg md:flex-row">
                <div class="flex items-center mb-4 md:mb-0">
                    <div class="flex items-center justify-center w-12 h-12 mr-4 text-white bg-red-600 rounded-full announcement-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-red-700">Important Announcement</h2>
                        <p class="text-gray-700">Physical Screening Exercise for BSc. Candidates - 2025/2026 Academic Session</p>
                    </div>
                </div>
                <a href="#announcement-details"
                   class="px-4 py-2 font-semibold text-white bg-red-600 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                    View Details
                </a>
            </div>
        </div>
    </section>

    <!-- Announcement Details Section -->
    <section id="announcement-details" class="py-12 bg-white">
        <div class="container px-6 mx-auto">
            <div class="max-w-4xl p-8 mx-auto bg-white border border-gray-200 rounded-lg shadow-md">
                <div class="mb-6 text-center">
                    <h2 class="text-3xl font-bold text-green-700">PUBLIC ANNOUNCEMENT</h2>
                    <p class="mt-2 text-lg text-gray-600">PHYSICAL SCREENING EXERCISE FOR BSc. CANDIDATES</p>
                    <p class="mt-1 font-semibold text-gray-700">2025/2026 ACADEMIC SESSION</p>
                </div>

                <div class="mb-6">
                    <p class="mb-4 text-gray-700">
                        This is to inform all candidates who secured admission into Waziri Umaru Federal
                        Polytechnic Birnin Kebbi (WUFPBK) Affiliated Degree Programmes through Unified
                        Tertiary Matriculation Examination (UTME) and Direct Entry (D.E.) that the physical
                        screening begins on <span class="font-bold">Monday 10th November 2025</span> at the
                        Directorate of Higher Studies (DHS) Boardroom from
                        <span class="font-bold">Mondays – Thursdays, 10:00am – 2:00pm daily</span>.
                    </p>

                    <p class="mb-4 font-semibold text-gray-700">
                        Consequently, candidates are expected to come along with:
                    </p>

                    <ul class="pl-5 mb-6 space-y-2 text-gray-700 list-disc">
                        <li>Original copies of JAMB admission letters (colored)</li>
                        <li>Post UTME online screening form</li>
                        <li>Evidence of payment of #10,000 ACCEPTANCE FEES</li>
                        <li>Original O-level certificate and primary certificate</li>
                        <li>Indigene letter, and birth certificate</li>
                        <li>Scratch card for result verification (if the result is not original)</li>
                    </ul>

                    <div class="p-4 mb-6 bg-yellow-100 border-l-4 border-yellow-500">
                        <p class="font-semibold text-gray-800">NOTE: The deadline for the screening exercise is <span class="text-red-600">Saturday 27th December, 2025</span>.</p>
                    </div>

                    <div class="p-4 bg-blue-50 rounded-lg">
                        <p class="mb-2 font-semibold text-gray-800">For more information contact:</p>
                        <div class="flex flex-wrap gap-4 text-blue-700">
                            <span>08036235488</span>
                            <span>07033071497</span>
                            <span>08039273164</span>
                            <span>08065332564</span>
                        </div>
                    </div>
                </div>

                <div class="pt-6 mt-6 text-right border-t border-gray-200">
                    <p class="font-semibold text-gray-700">(Signed)</p>
                    <p class="text-lg text-gray-800">Atiku Muhammad Bello</p>
                    <p class="text-gray-600">Registrar</p>
                    <p class="text-sm text-gray-500">27th October, 2025</p>
                </div>
            </div>
        </div>
    </section>

    <section id="programs" class="py-12 bg-white">
        <div class="container px-6 mx-auto text-center">
            <h2 class="text-3xl font-bold text-green-600">Our Programs</h2>
            <p class="mt-4 text-lg text-gray-600">
                Choose the program that's right for you!
            </p>
            <div class="mt-8 space-x-4">
                <a href="{{ route('degree-login') }}"
                    class="rounded-md px-3.5 py-2.5 text-sm font-semibold shadow-sm hover:bg-green-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Undergraduate</a>
                <a href="{{ route('login') }}"
                    class="rounded-md px-3.5 py-2.5 text-sm font-semibold shadow-sm hover:bg-green-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Postgraduate</a>

            </div>
        </div>
    </section>

    <section id="contact-us" class="px-20 py-16 text-white bg-gradient-to-r from-green-600 via-teal-400 to-green-600">
        <div class="container px-6 mx-auto">
            <h2 class="mb-8 text-4xl font-bold text-center">Start Your Journey</h2>
            <p class="mb-12 text-center sub-title">Have any questions? We'd love to hear from you!</p>

            <div class="grid items-center justify-center grid-cols-1 gap-4 lg:grid-cols-2">

                <div class="space-y-2 text-center lg:text-left">
                    <h3 class="text-2xl font-bold">Contact Information</h3>
                    <p>Feel free to reach us via the details below or by filling out the form.</p>
                    <div class="space-y-3">
                        <div class="flex items-center justify-center lg:justify-start">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                            </svg>
                            <span>Directorate of Higher Studies, Wufpbk</span>
                        </div>
                        <div class="flex items-center justify-center lg:justify-start">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                            </svg>
                            <span>08036235488, 07033071497, 08039273164, 08065332564</span>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-white rounded-lg shadow-lg text-gray-800">
                    <h3 class="mb-4 text-2xl font-bold text-center">Send us a Message</h3>
                    <form class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" id="name" name="name" class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" id="email" name="email" class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea id="message" name="message" rows="4" class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                        <div>
                            <button type="submit" class="w-full px-4 py-2 font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-6 text-white bg-gray-800">
        <div class="container px-6 mx-auto text-center">
            <p>&copy; 2025 Directorate of Higher Studies, Waziri Umaru Federal Polytechnic Birnin Kebbi. All rights reserved.</p>
        </div>
    </footer>

</body>

</html>
