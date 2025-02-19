<div class="dark:bg-gray-800 rounded-lg  p-3 w-full max-w-[180px]">
    <!-- Profile Picture Section -->
    <div class="flex justify-center mb-2">
        <div
            class="w-16 h-16 relative overflow-hidden rounded-full border-2 border-white dark:border-gray-700 shadow-sm mb-2">
            <img class="w-full h-full object-cover"
                src="{{ $student->profilePicture() ?? 'https://via.placeholder.com/150' }}"
                alt="{{ $student->full_name }}">
        </div>
    </div>

    <!-- Student Details Section -->
    <div class="text-center">
        <!-- Name -->
        <h2 class="text-sm font-semibold text-gray-800 dark:text-white mb-1 truncate">
            {{ $student->full_name }}
        </h2>
        <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ $student->academicDetail->matric_no ?? 'N/A' }}
        </p>

        <!-- Programme -->
        <p class="text-xs text-gray-600 dark:text-gray-300 mb-1 truncate">
            {{ $student->proposedCourse->course->name ?? 'N/A' }}
        </p>

        <!-- Academic Level -->
        <p class="text-xs text-gray-500 dark:text-gray-400">
            Level: {{ $student->academicDetail->student_level_id . '00' ?? 'N/A' }}
        </p>
    </div>
</div>