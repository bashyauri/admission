@use('App\Models\StudentCourse')

<div x-data="{ focused: false }">
    <!-- Enhanced PIN Section -->
    <div class="w-full max-w-full px-3 mb-6">
        <div class="bg-gradient-to-r from-fuchsia-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-white/20 rounded-full p-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">Coordinator Approval Status</h3>
                        <p class="text-fuchsia-100 text-sm">
                            @if(!$student->approval?->isPinUsed())
                                @if($student->approval?->pin)
                                    PIN: <span class="font-mono font-bold">{{ $student->approval->pin }}</span>
                                @else
                                    <span class="text-yellow-200">PIN not generated - Contact your coordinator</span>
                                @endif
                            @else
                                <span class="text-green-200">✓ Approved - You can register courses</span>
                            @endif
                        </p>
                    </div>
                </div>
                @if ($student->approval?->pin && !$student->approval?->isPinUsed())
                    <button wire:click.prevent="usePin" 
                        class="bg-white text-fuchsia-600 px-6 py-2 rounded-lg font-semibold hover:bg-fuchsia-50 transition-colors">
                        Activate PIN
                    </button>
                @endif
            </div>
            <div class="flex justify-center items-center mt-2" wire:loading wire:target="usePin">
                <span class="text-white/80 text-sm">Activating PIN...</span>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        @if ($student->approval?->isPinUsed())
            <!-- Main Content Area -->
            <div class="w-full lg:w-5/12 order-2 lg:order-1">
                <!-- Available Courses Section -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Header with Filters and Search -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col gap-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h6 class="dark:text-white text-lg font-semibold">Available Courses</h6>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Click on a course to add it</p>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-fuchsia-100 text-fuchsia-800 dark:bg-fuchsia-900 dark:text-fuchsia-200">
                                    {{ $courses->count() }} courses
                                </span>
                            </div>

                            <!-- Course Filters -->
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="filterBySemester('all')" 
                                    class="px-4 py-2 rounded-lg font-medium text-sm transition-colors {{ $semesterFilter === 'all' ? 'bg-fuchsia-100 text-fuchsia-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    All Courses
                                </button>
                                <button wire:click="filterBySemester('1')" 
                                    class="px-4 py-2 rounded-lg font-medium text-sm transition-colors {{ $semesterFilter === '1' ? 'bg-fuchsia-100 text-fuchsia-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    First Semester
                                </button>
                                <button wire:click="filterBySemester('2')" 
                                    class="px-4 py-2 rounded-lg font-medium text-sm transition-colors {{ $semesterFilter === '2' ? 'bg-fuchsia-100 text-fuchsia-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    Second Semester
                                </button>
                            </div>

                            <!-- Search -->
                            <div class="relative" x-data="{ focused: false }">
                                <div class="relative" :class="{ 'ring-2 ring-fuchsia-500 rounded-lg': focused }">
                                    <input type="text" wire:model.live.debounce.300ms="searchCourse" placeholder="Search courses..."
                                        x-ref="searchInput" @focus="focused = true" @blur="focused = false"
                                        @keydown.window="if (event.key === '/' && !focused) { event.preventDefault(); $refs.searchInput.focus(); }"
                                        class="w-full pl-11 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none bg-white dark:bg-gray-700 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200">
                                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    @if($searchCourse)
                                        <button wire:click="clearSearch('available')"
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-2">
                                    <span class="font-medium">Keyboard shortcuts:</span> 
                                    <kbd class="bg-gray-100 px-2 py-1 rounded">/</kbd> to search, 
                                    <kbd class="bg-gray-100 px-2 py-1 rounded">Esc</kbd> to clear
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search Results Info -->
                    @if($searchCourse)
                        <div class="px-6 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-800">
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    Showing {{ $courses->count() }} result(s) for "<span class="font-medium">"{{ $searchCourse }}"</span>"
                                </p>
                                <button wire:click="clearSearch('available')"
                                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-medium">
                                    Clear
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Enhanced Loading State -->
                    <div class="flex justify-center items-center p-8" wire:loading wire:target="addCourse">
                        <div class="flex flex-col items-center space-y-3">
                            <div class="relative">
                                <div class="w-12 h-12 border-4 border-fuchsia-200 rounded-full"></div>
                                <div class="w-12 h-12 border-4 border-fuchsia-500 rounded-full absolute top-0 left-0 border-t-transparent animate-spin"></div>
                            </div>
                            <span class="text-fuchsia-600 font-medium">Adding course...</span>
                        </div>
                    </div>

                    <!-- Course Cards -->
                    <div class="p-6 max-h-[70vh] overflow-y-auto">
                        @if($courses->isNotEmpty())
                            <div class="grid gap-4">
                                @foreach ($courses as $course)
                                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-lg hover:border-fuchsia-300 dark:hover:border-fuchsia-600 transition-all duration-300 cursor-pointer group
                                        {{ $isActive ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        wire:click="addCourse({{ $course->id }})" wire:loading.attr="disabled" wire:target="addCourse">
                                        
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-1">
                                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">{{ $course->code }}</span>
                                                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">Semester {{ $course->semester }}</span>
                                                </div>
                                                <h4 class="font-semibold text-gray-900 dark:text-white text-base">{{ $course->title }}</h4>
                                            </div>
                                            <div class="flex flex-col items-end space-y-2">
                                                <span class="bg-fuchsia-100 text-fuchsia-700 text-sm font-bold px-3 py-1 rounded-full">
                                                    {{ $course->units }} Units
                                                </span>
                                                <span class="text-xs text-gray-500">Level {{ $course->student_level_id }}00</span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                                            <div class="flex items-center space-x-4 text-xs text-gray-500">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                    </svg>
                                                    {{ $course->units }} Credit Hours
                                                </span>
                                            </div>
                                            <div class="flex items-center space-x-2 text-fuchsia-600 font-medium text-sm group-hover:translate-x-1 transition-transform">
                                                <span>Add Course</span>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <!-- Enhanced Empty State -->
                            <div class="text-center py-12">
                                <div class="w-20 h-20 mx-auto mb-4 text-gray-300 dark:text-gray-600">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                @if($searchCourse)
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No courses found</h3>
                                    <p class="text-gray-600 dark:text-gray-400 mb-4">No courses match "<span class="font-medium">"{{ $searchCourse }}"</span>"</p>
                                    <button wire:click="clearSearch('available')"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-fuchsia-700 bg-fuchsia-100 hover:bg-fuchsia-200 dark:bg-fuchsia-900 dark:text-fuchsia-200 transition-colors">
                                        Clear search
                                    </button>
                                @else
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No courses available</h3>
                                    <p class="text-gray-600 dark:text-gray-400">All courses for your level have been registered.</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Registered Courses Section -->
            <div class="w-full lg:w-7/12 order-1 lg:order-2">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Header with Stats -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col gap-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h6 class="dark:text-white text-lg font-semibold mb-1">Registered Courses</h6>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Your current course registrations</p>
                                </div>
                                
                                <!-- Quick Stats -->
                                <div class="flex space-x-6 text-sm">
                                    <div class="text-center">
                                        <span class="block font-bold text-gray-900 dark:text-white">{{ $courses->count() }}</span>
                                        <span class="text-gray-500">Available</span>
                                    </div>
                                    <div class="text-center">
                                        <span class="block font-bold text-gray-900 dark:text-white">{{ $registeredCourses->count() }}</span>
                                        <span class="text-gray-500">Registered</span>
                                    </div>
                                    <div class="text-center">
                                        <span class="block font-bold text-fuchsia-600">{{ $registeredCourses->sum('units') }}</span>
                                        <span class="text-gray-500">Units</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced Unit Progress Bar -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Unit Usage</span>
                                    <span class="text-sm font-bold {{ $registeredCourses->sum('units') > $maxUnits ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-300' }}">
                                        {{ $registeredCourses->sum('units') }} / {{ $maxUnits }} units
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-3">
                                    @php
                                        $percentage = min(($registeredCourses->sum('units') / $maxUnits) * 100, 100);
                                        $progressColor = $percentage >= 90 ? 'bg-red-500' : ($percentage >= 75 ? 'bg-amber-500' : 'bg-green-500');
                                    @endphp
                                    <div class="h-3 rounded-full transition-all duration-500 ease-out {{ $progressColor }}" style="width: {{ $percentage }}%"></div>
                                </div>
                                @if($percentage >= 90)
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-2 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        You've reached the unit limit
                                    </p>
                                @elseif($percentage >= 75)
                                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-2 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        Approaching unit limit
                                    </p>
                                @endif
                            </div>

                            <!-- Search -->
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="searchRegistered"
                                    placeholder="Search registered courses..."
                                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-fuchsia-500 dark:bg-gray-700 dark:text-white">
                                <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                @if($searchRegistered)
                                    <button wire:click="clearSearch('registered')"
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Print Buttons -->
                        @if ($registeredCourses->count() && $student->approval?->isPinUsed())
                            <div class="flex flex-wrap justify-center gap-3 mt-4">
                                <a href="{{ route('student.print-course-form', ['user' => $student->user_id]) }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white bg-fuchsia-500 rounded-lg hover:bg-fuchsia-600 focus:outline-none focus:ring-2 focus:ring-fuchsia-400 focus:ring-offset-2 transition-colors duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015-1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                    </svg>
                                    Print Course Form
                                </a>
                                <a href="{{ route('student.course-history') }}"
                                    class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-fuchsia-600 bg-white border border-fuchsia-400 rounded-lg hover:bg-fuchsia-50 focus:outline-none focus:ring-2 focus:ring-fuchsia-400 focus:ring-offset-2 transition-colors duration-200 dark:bg-gray-800 dark:text-fuchsia-300 dark:border-fuchsia-500 dark:hover:bg-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Previous Sessions
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Enhanced Loading State -->
                    <div class="flex justify-center items-center p-8" wire:loading wire:target="deleteCourse">
                        <div class="flex flex-col items-center space-y-3">
                            <div class="relative">
                                <div class="w-12 h-12 border-4 border-red-200 rounded-full"></div>
                                <div class="w-12 h-12 border-4 border-red-500 rounded-full absolute top-0 left-0 border-t-transparent animate-spin"></div>
                            </div>
                            <span class="text-red-600 font-medium">Removing course...</span>
                        </div>
                    </div>

                    <!-- Registered Course Cards -->
                    <div class="p-6 max-h-[70vh] overflow-y-auto">
                        @if ($student->approval?->isPinUsed())
                            @if($registeredCourses->isNotEmpty())
                                <div class="grid gap-4">
                                    @foreach ($registeredCourses as $pickedCourse)
                                        <div wire:key="course-{{ $pickedCourse->id }}"
                                            class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200 dark:border-green-800 p-5 hover:shadow-md transition-all duration-200">
                                            
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-2 mb-1">
                                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">{{ $pickedCourse->departmentCourse->studentCourse->code }}</span>
                                                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">✓ Registered</span>
                                                    </div>
                                                    <h4 class="font-semibold text-gray-900 dark:text-white text-base">{{ $pickedCourse->departmentCourse->studentCourse->title }}</h4>
                                                </div>
                                                <div class="flex items-center space-x-3">
                                                    <span class="bg-green-200 text-green-800 text-sm font-bold px-3 py-1 rounded-full">
                                                        {{ $pickedCourse->units }} Units
                                                    </span>
                                                    <button wire:click="deleteCourse({{ $pickedCourse->id }})"
                                                        wire:confirm="Remove {{ $pickedCourse->departmentCourse->studentCourse->code }}?"
                                                        wire:loading.attr="disabled"
                                                        class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition-colors"
                                                        title="Remove course">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center space-x-6 text-xs text-gray-600 dark:text-gray-400 pt-3 border-t border-green-200 dark:border-green-800">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Level {{ $pickedCourse->student_level_id }}00
                                                </span>
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    Semester {{ $pickedCourse->departmentCourse->studentCourse->semester }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <!-- Enhanced Empty State -->
                                <div class="text-center py-12">
                                    <div class="w-20 h-20 mx-auto mb-4 text-gray-300 dark:text-gray-600">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    @if($searchRegistered)
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No registered courses found</h3>
                                        <p class="text-gray-600 dark:text-gray-400 mb-4">No registered courses match "<span class="font-medium">"{{ $searchRegistered }}"</span>"</p>
                                        <button wire:click="clearSearch('registered')"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-fuchsia-700 bg-fuchsia-100 hover:bg-fuchsia-200 dark:bg-fuchsia-900 dark:text-fuchsia-200 transition-colors">
                                            Clear search
                                        </button>
                                    @else
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No courses registered yet</h3>
                                        <p class="text-gray-600 dark:text-gray-400">Start by adding courses from the available courses list.</p>
                                    @endif
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @else
            <!-- No PIN Applied State -->
            <div class="w-full">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <div class="w-20 h-20 mx-auto mb-4 text-fuchsia-500">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">PIN Required for Course Registration</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">You need to activate your coordinator-issued PIN before you can register for courses.</p>
                    <div class="inline-flex items-center px-4 py-2 bg-fuchsia-100 text-fuchsia-700 rounded-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Contact your coordinator to generate your PIN
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Enhanced JavaScript for Better UX -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Keyboard shortcut for search
            document.addEventListener('keydown', (e) => {
                if (e.key === '/' && !e.target.matches('input, textarea, [contenteditable]')) {
                    e.preventDefault();
                    const searchInput = document.querySelector('[x-ref="searchInput"]');
                    if (searchInput) searchInput.focus();
                }
                
                // Escape to clear search
                if (e.key === 'Escape') {
                    const searchInput = document.querySelector('[x-ref="searchInput"]');
                    if (searchInput && document.activeElement === searchInput) {
                        searchInput.blur();
                    }
                }
            });

            // Enhanced loading states
            Livewire.hook('message.sent', ({ component }) => {
                // Add body loading state
                document.body.classList.add('livewire-loading');
            });

            Livewire.hook('message.failed', ({ component }) => {
                // Remove body loading state
                document.body.classList.remove('livewire-loading');
            });

            Livewire.hook('message.received', ({ component }) => {
                // Remove body loading state
                document.body.classList.remove('livewire-loading');
            });

            // Smooth animations for course cards
            Livewire.hook('morph.dom', ({ component, to, from }) => {
                // Add fade-in animation to new elements
                setTimeout(() => {
                    const newElements = to.querySelectorAll('[wire\\:key]');
                    newElements.forEach(el => {
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(10px)';
                        setTimeout(() => {
                            el.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            el.style.opacity = '1';
                            el.style.transform = 'translateY(0)';
                        }, 50);
                    });
                }, 100);
            });
        });
    </script>

    <style>
        /* Custom scrollbar for better UX */
        .max-h-\\[70vh\\]::-webkit-scrollbar {
            width: 8px;
        }
        
        .max-h-\\[70vh\\]::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .max-h-\\[70vh\\]::-webkit-scrollbar-thumb {
            background: #c4b5fd;
            border-radius: 4px;
        }
        
        .max-h-\\[70vh\\]::-webkit-scrollbar-thumb:hover {
            background: #a78bfa;
        }
        
        /* Loading state styles */
        .livewire-loading {
            cursor: wait;
        }
        
        /* Focus styles for accessibility */
        button:focus-visible,
        input:focus-visible,
        a:focus-visible {
            outline: 2px solid #c026d3;
            outline-offset: 2px;
        }
    </style>
</div>