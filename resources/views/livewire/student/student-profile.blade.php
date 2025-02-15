<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 w-full max-w-xs mb-12">
    <div class="flex items-center space-x-3">
        <!-- Profile Picture -->
        <div class="flex-shrink-0">
            <img class="h-12 w-12 rounded-full object-cover"
                src="{{ $student->profilePicture() ?? 'https://via.placeholder.com/150' }}"
                alt="{{ $student->full_name }}">
        </div>

        <!-- Student Details -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $student->full_name }}</h2>
            <p class="text-xs text-gray-600 dark:text-gray-400">{{ $student->email }}</p>
            <p class="text-xs text-gray-600 dark:text-gray-400">Programme: {{ $student->programme->name ?? 'N/A' }}</p>
            <p class="text-xs text-gray-600 dark:text-gray-400">Level: {{ $student->academic_level ?? 'N/A' }}</p>
        </div>
    </div>
</div>