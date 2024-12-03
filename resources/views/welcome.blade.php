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
    .btn-green:hover {
      @apply bg-green-700; /* Darker shade on hover */
    }
    .card {
      @apply p-6 border rounded-lg bg-gray-100 transition-shadow duration-300 ease-in-out hover:shadow-lg;
    }
  </style>
</head>
<body class="text-gray-800 bg-gray-50">

  <!-- Hero Section -->
  <section class="relative py-16 text-white bg-gradient-to-r from-green-500 via-teal-400 to-green-500">
    <div class="container px-6 py-12 mx-auto text-center">
      <h1 class="text-4xl font-bold leading-tight md:text-6xl">
        Welcome to <span class="text-yellow-300">Directorate of Higher Studies</span>
      </h1>
      <p class="mt-4 text-lg md:text-xl">
        Waziri Umaru Federal Polytechnic Birnin Kebbi.
      </p>
      <div class="mt-6 space-x-4">
        <a href="#contact-us" class="rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Contact Us</a>
        <a href="#programs" class="btn-green">Explore Programs</a>

      </div>
    </div>
  </section>

  <!-- Programs Section -->
  <section id="programs" class="py-12 bg-white">
    <div class="container px-6 mx-auto text-center">
      <h2 class="text-3xl font-bold text-green-600">Our Programs</h2>
      <p class="mt-4 text-lg text-gray-600">
        Choose the program that's right for you!
      </p>
      <div class="mt-8 space-x-4" x-data="{ showInfo: null }">
        <!-- Buttons -->
        <a
         href={{route('degree-login')}}
          class="rounded-md  px-3.5 py-2.5 text-sm font-semibold shadow-sm hover:bg-green-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Undergraduate</a>
        {{-- <a href
          @click="showInfo = 'degree'"
          class="rounded-md  px-3.5 py-2.5 text-sm font-semibold shadow-sm hover:bg-green-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
    :class="showInfo === 'degree' ? 'bg-green-700 text-white' : ''"
>

          Undergraduate Program
        </a> --}}
        {{-- <button
    @click="showInfo = 'postgraduate'"
    class="rounded-md  px-3.5 py-2.5 text-sm font-semibold shadow-sm hover:bg-green-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
    :class="showInfo === 'postgraduate' ? 'bg-green-700 text-white' : ''"
>
    Postgraduate Diploma
</button> --}}
<a
         href={{route('login')}}
          class="rounded-md  px-3.5 py-2.5 text-sm font-semibold shadow-sm hover:bg-green-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Postgraduate</a>

        <!-- Program Details -->
        <div x-show="showInfo === 'postgraduate'" class="mt-6 text-left card">
          <h3 class="text-xl font-bold text-green-600">Postgraduate Diploma</h3>
          <p class="mt-2 text-gray-700">
            Our postgraduate diploma program is designed for professionals who want to advance their careers and expand their knowledge.
          </p>
          <ul class="mt-4 text-gray-700 list-disc list-inside">
            <li>Duration: 1 Year</li>
            <li>Eligibility: Bachelor’s degree or equivalent</li>
            <li>Specializations in multiple fields</li>
          </ul>

          <a href="/sign-in" class="inline-block mt-4 font-semibold text-teal-700 btn-green">Login to Postgraduate Program</a>
        </div>

        <div x-show="showInfo === 'degree'" class="mt-6 text-left card">
          <h3 class="text-xl font-bold text-green-600">Degree Program</h3>
          <p class="mt-2 text-gray-700">
            Our degree programs provide comprehensive education, equipping you with the skills for a successful career.
          </p>
          <ul class="mt-4 text-gray-700 list-disc list-inside">
            <li>Duration: 3-4 Years</li>
            <li>Eligibility: High school diploma or equivalent</li>
            <li>Wide range of disciplines</li>
          </ul>
          <a href="/degree-signin" class="inline-block mt-4 font-semibold text-teal-700 btn-green">Login to Degree Program</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Us Section -->

<section id="contact-us" class="px-20 py-16 text-white bg-gradient-to-r from-green-600 via-teal-400 to-green-600">
  <div class="container px-6 mx-auto">
    <h2 class="mb-8 text-4xl font-bold text-center">Start Your Journey</h2>
    <p class="mb-12 text-center sub-title">Have any questions? We'd love to hear from you!</p>

    <!-- Grid Layout -->
    <div class="grid items-center justify-center grid-cols-1 gap-4 lg:grid-cols-2"> <!-- Centered items -->

      <!-- Contact Info -->
      <div class="space-y-2 text-center lg:text-left"> <!-- Center text on smaller screens -->
        <h3 class="text-2xl font-bold">Contact Information</h3>
        <p>Feel free to reach us via the details below or by filling out the form.</p>
        <div class="space-y-3">
          <div class="flex items-center justify-center lg:justify-start">
           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
</svg>

            <span>545 Mavis Island, Chicago, IL 99191</span>
          </div>
          <div class="flex items-center justify-center lg:justify-start">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
</svg>

            <span class="ml-1">08038272560</span>
          </div>
          <div class="flex items-center justify-center lg:justify-start">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
</svg>

            <span>bumar@wufpbk.edu.ng</span>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form class="space-y-2 text-center lg:text-left">
        <input type="text" placeholder="Your Name" class="w-full px-6 py-3 text-gray-800 rounded-lg shadow-md" required>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <input type="email" placeholder="Your Email" class="w-full px-6 py-3 text-gray-800 rounded-lg shadow-md" required>
          <input type="tel" placeholder="Your Phone Number" class="w-full px-6 py-3 text-gray-800 rounded-lg shadow-md" required>
        </div>
        <textarea placeholder="Your Message" class="w-full px-6 py-3 text-gray-800 rounded-lg shadow-md" rows="4" required></textarea>
        <button type="submit" class="block w-full rounded-md bg-green-600 px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
          Send Message
        </button>
      </form>
    </div>
  </div>
</section>




  <!-- Footer -->
  <footer class="py-6 text-white bg-gray-800">
    <div class="container px-6 mx-auto text-center">
      <p class="text-sm">
        &copy; {{date('Y')}} CIT Wufpbk. All Rights Reserved.
      </p>
    </div>
  </footer>

</body>
</html>
