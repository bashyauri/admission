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

    <!-- Announcement Bar -->
    <div class="w-full bg-yellow-100 border-b-2 border-yellow-400 py-3 px-4 flex items-center justify-center gap-3 text-yellow-900 font-semibold text-base md:text-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 8v.01" />
        </svg>
        <span>
            Postgraduate Admission is <span class="font-bold text-red-600">OPEN</span>
            <a href="{{ route('register') }}" class="inline-block ml-2 px-3 py-1 rounded bg-red-600 text-white font-bold text-sm shadow hover:bg-red-700 transition focus:outline-none focus:ring-2 focus:ring-red-400">Apply Now!</a>
        </span>
    </div>


    <section class="relative py-10 bg-gradient-to-r from-green-600 via-teal-500 to-green-700">
        <div class="absolute inset-0 bg-black bg-opacity-30"></div>
        <div class="relative z-10 max-w-3xl mx-auto px-6 py-8 text-center rounded-2xl shadow-2xl bg-white/10 backdrop-blur-md">
            <img class="w-20 md:w-28 lg:w-32 h-auto mx-auto mb-3" alt="Wufpbk Logo" src="{{ asset('assets') }}/img/logo-ct.png">
            <h1 class="text-3xl md:text-5xl font-extrabold leading-tight text-white drop-shadow-lg">
                Directorate of Higher Studies
            </h1>
            <p class="mt-3 text-base md:text-xl text-white/90 font-medium">
                Waziri Umaru Federal Polytechnic Birnin Kebbi
            </p>
            <div class="mt-6 flex flex-col md:flex-row justify-center gap-4">
                <a href="#programs" class="btn-green">Explore Programs</a>
                <a href="#contact-us" class="rounded-md bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow hover:bg-gray-100 focus:outline-none">Contact Us</a>
            </div>
        </div>
    </section>

    <!-- Quick Info Grid -->
    <section class="max-w-5xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center border-t-4 border-green-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m-4 0h8" />
            </svg>
            <h3 class="font-bold text-lg mb-1">Admissions</h3>
            <p class="text-gray-600 text-sm">Applications for Postgraduate and Degree programs are open. Check requirements and deadlines.</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center border-t-4 border-yellow-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z" />
            </svg>
            <h3 class="font-bold text-lg mb-1">General Updates</h3>
            <p class="text-gray-600 text-sm">Stay tuned for important news, deadlines, and reminders. Check your portal regularly for the latest updates.</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center border-t-4 border-blue-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 01-8 0M12 14v7m-4 0h8" />
            </svg>
            <h3 class="font-bold text-lg mb-1">Student Support</h3>
            <p class="text-gray-600 text-sm">Need help? Contact the Directorate or use the message form below for assistance.</p>
        </div>
    </section>

    <!-- Programs Section moved to top -->
    <section id="programs" class="py-12 bg-white">
        <div class="container px-6 mx-auto text-center">
            <h2 class="text-3xl font-bold text-green-600">Our Programs</h2>
            <p class="mt-4 text-lg text-gray-600">
                Choose the program that's right for you!
            </p>
            <div class="mt-10 flex flex-col md:flex-row justify-center gap-8">
                <!-- Degree Card -->
                <div class="flex-1 max-w-xs mx-auto bg-white rounded-xl shadow-lg border border-green-200 p-6 flex flex-col items-center hover:shadow-2xl transition-all">
                    <div class="mb-4 text-green-600">
                        <!-- Heroicons Academic Cap -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25M19.5 10.5l-7.5 4.5-7.5-4.5m15 0v6.75a2.25 2.25 0 01-2.25 2.25h-9A2.25 2.25 0 013 17.25V10.5m16.5 0L12 6l-7.5 4.5" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Undergraduate (Degree)</h3>
                    <p class="text-gray-600 mb-4">For BSc. and affiliated degree programs. Access your portal to continue your application or check your status.</p>
                    <a href="{{ route('degree-login') }}" class="w-full inline-block rounded-md px-4 py-2.5 text-base font-semibold text-white bg-green-600 shadow-md hover:bg-green-700 transition">Degree Login</a>
                </div>
                <!-- Postgraduate Card -->
                <div class="flex-1 max-w-xs mx-auto bg-white rounded-xl shadow-lg border border-teal-200 p-6 flex flex-col items-center hover:shadow-2xl transition-all relative">
                    <div class="absolute -top-4 right-4 animate-pulse">
                        <span class="inline-block bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow">Admission Open!</span>
                    </div>
                    <div class="mb-4 text-teal-600">
                        <!-- Heroicons Document Text -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-6.75A2.25 2.25 0 0017.25 5.25h-10.5A2.25 2.25 0 004.5 7.5v9A2.25 2.25 0 006.75 18h10.5A2.25 2.25 0 0019.5 15.75v-1.5m-6-6.75v6.75m0 0l-2.25-2.25m2.25 2.25l2.25-2.25" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Postgraduate (PG)</h3>
                    <p class="text-gray-600 mb-4">Apply for or access your Postgraduate (PG) program. New admissions are currently ongoing!</p>
                    <a href="{{ route('login') }}" class="w-full inline-block rounded-md px-4 py-2.5 text-base font-semibold text-white bg-teal-600 shadow-md hover:bg-teal-700 transition">PG Login</a>
                </div>
            </div>
        </div>

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
            <p>&copy; {{ date('Y') }} Directorate of Higher Studies, Waziri Umaru Federal Polytechnic Birnin Kebbi. All rights reserved.</p>
        </div>
    </footer>

</body>

</html>
