<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-4">
    <!-- Student Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 p-3">
        <!-- Profile Picture -->
        <div class="flex justify-center mb-3">
            <div class="w-full h-32 relative overflow-hidden rounded-lg shadow-sm">
                <img class="w-36 h-36 object-contain"
                    src="{{ $student->profilePicture() ?? 'https://via.placeholder.com/150' }}"
                    alt="{{ $student->full_name }}">
            </div>
        </div>

        <!-- Student Details -->
        <div class="text-left">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-white mb-1 truncate">
                {{ $student->full_name }}
            </h2>
            <p class="text-xs text-gray-600 dark:text-gray-300 mb-1 truncate">
                {{ $student->academicDetail->matric_no ?? 'N/A' }}
            </p>
            <p class="text-xs text-gray-600 dark:text-gray-300 mb-1 truncate">
                {{ $student->programme->name ?? 'N/A' }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Level: {{ $student->academicDetail->student_level_id . '00' ?? 'N/A' }}
            </p>
        </div>
    </div>

    <!-- Repeat for other students -->
</div>