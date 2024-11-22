<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>University Landing Page</title>
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
<body class="bg-gray-50 text-gray-800">

  <!-- Hero Section -->
  <section class="relative bg-gradient-to-r from-green-500 via-teal-400 to-green-500 text-white py-16">
    <div class="container mx-auto px-6 py-12 text-center">
      <h1 class="text-4xl font-bold md:text-6xl leading-tight">
        Welcome to <span class="text-yellow-300">Directorate of Higher Studies</span>
      </h1>
      <p class="mt-4 text-lg md:text-xl">
        Your gateway to quality education and a brighter future.
      </p>
      <div class="mt-6 space-x-4">
        <a href="#contact-us" class="rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Contact Us</a>
        <a href="#programs" class="btn-green">Explore Programs</a>

      </div>
    </div>
  </section>

  <!-- Programs Section -->
  <section id="programs" class="bg-white py-12">
    <div class="container mx-auto px-6 text-center">
      <h2 class="text-3xl font-bold text-green-600">Our Programs</h2>
      <p class="mt-4 text-lg text-gray-600">
        Choose the program that's right for you!
      </p>
      <div class="mt-8 space-x-4" x-data="{ showInfo: null }">
        <!-- Buttons -->
        <button
    @click="showInfo = 'postgraduate'"
    class="rounded-md bg-green-200 px-3.5 py-2.5 text-sm font-semibold shadow-sm hover:bg-green-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
    :class="showInfo === 'postgraduate' ? 'bg-green-700 text-white' : 'bg-green-500 text-white'"
>
    Postgraduate Diploma
</button>
        <button
          @click="showInfo = 'degree'"
          class="rounded-md bg-green-200 px-3.5 py-2.5 text-sm font-semibold shadow-sm hover:bg-green-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
    :class="showInfo === 'degree' ? 'bg-green-700 text-white' : 'bg-green-500 text-white'"
>

          Degree Program
        </button>

        <!-- Program Details -->
        <div x-show="showInfo === 'postgraduate'" class="mt-6 card text-left">
          <h3 class="text-xl font-bold text-green-600">Postgraduate Diploma</h3>
          <p class="mt-2 text-gray-700">
            Our postgraduate diploma program is designed for professionals who want to advance their careers and expand their knowledge.
          </p>
          <ul class="mt-4 list-disc list-inside text-gray-700">
            <li>Duration: 1 Year</li>
            <li>Eligibility: Bachelor’s degree or equivalent</li>
            <li>Specializations in multiple fields</li>
          </ul>

          <a href="/postgraduate-login" class="btn-green mt-4 inline-block text-teal-700 font-semibold">Login to Postgraduate Program</a>
        </div>

        <div x-show="showInfo === 'degree'" class="mt-6 card text-left">
          <h3 class="text-xl font-bold text-green-600">Degree Program</h3>
          <p class="mt-2 text-gray-700">
            Our degree programs provide comprehensive education, equipping you with the skills for a successful career.
          </p>
          <ul class="mt-4 list-disc list-inside text-gray-700">
            <li>Duration: 3-4 Years</li>
            <li>Eligibility: High school diploma or equivalent</li>
            <li>Wide range of disciplines</li>
          </ul>
          <a href="/degree-login" class="btn-green mt-4 inline-block text-teal-700 font-semibold">Login to Degree Program</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Us Section -->

<section id="contact-us" class="py-16 px-20 bg-gradient-to-r from-green-600 via-teal-400 to-green-600 text-white">
  <div class="container mx-auto px-6">
    <h2 class="text-4xl font-bold text-center mb-8">Start Your Journey</h2>
    <p class="sub-title text-center mb-12">Have any questions? We'd love to hear from you!</p>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-center justify-center"> <!-- Centered items -->

      <!-- Contact Info -->
      <div class="space-y-2 text-center lg:text-left"> <!-- Center text on smaller screens -->
        <h3 class="text-2xl font-bold">Contact Information</h3>
        <p>Feel free to reach us via the details below or by filling out the form.</p>
        <div class="space-y-3">
          <div class="flex items-center justify-center lg:justify-start">
            <svg class="w-6 h-6 mr-4 text-teal-300" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h7M7 21V9M7 3L3 10m18 0h-7m4 11V9m0-6l4 7M7 16h10" />
            </svg>
            <span>545 Mavis Island, Chicago, IL 99191</span>
          </div>
          <div class="flex items-center justify-center lg:justify-start">
            <svg class="w-6 h-6 mr-4 text-teal-300" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h7M7 21V9M7 3L3 10m18 0h-7m4 11V9m0-6l4 7M9 5l7 7-7 7" />
            </svg>
            <span>+1 (555) 234-5678</span>
          </div>
          <div class="flex items-center justify-center lg:justify-start">
            <svg class="w-6 h-6 mr-4 text-teal-300" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h7M7 21V9M7 3L3 10m18 0h-7m4 11V9m0-6l4 7M12 12h.01m6.445-2.137c-.81-1.444-2.445-2.137-4.445-2.137s-3.635.693-4.445 2.137c-.928 1.657-1.105 4.162-.35 6.937.707 2.553 1.888 4.347 2.886 5.363a1.042 1.042 0 001.718 0c.998-1.016 2.179-2.81 2.886-5.363.755-2.775.578-5.28-.35-6.937z" />
            </svg>
            <span>hello@example.com</span>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form class="space-y-2 text-center lg:text-left">
        <input type="text" placeholder="Your Name" class="w-full px-6 py-3 rounded-lg shadow-md text-gray-800" required>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <input type="email" placeholder="Your Email" class="w-full px-6 py-3 rounded-lg shadow-md text-gray-800" required>
          <input type="tel" placeholder="Your Phone Number" class="w-full px-6 py-3 rounded-lg shadow-md text-gray-800" required>
        </div>
        <textarea placeholder="Your Message" class="w-full px-6 py-3 rounded-lg shadow-md text-gray-800" rows="4" required></textarea>
        <button type="submit" class="block w-full rounded-md bg-green-600 px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
          Send Message
        </button>
      </form>
    </div>
  </div>
</section>




  <!-- Footer -->
  <footer class="bg-gray-800 text-white py-6">
    <div class="container mx-auto px-6 text-center">
      <p class="text-sm">
        &copy; 2024 XYZ University. All Rights Reserved.
      </p>
    </div>
  </footer>

</body>
</html>
