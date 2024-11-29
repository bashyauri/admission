<div class="min-h-screen p-8 bg-gray-50">
    <div class="max-w-2xl p-8 mx-auto bg-white rounded-lg shadow-lg">
        <h2 class="mb-6 text-2xl font-bold text-gray-800">Search for JAMB Number</h2>

        <!-- Alpine.js State -->
        <form
            wire:submit.prevent="search"
            class="relative space-y-6"
            x-data="{ jambNumber: '', isDisabled: true }"
            x-init="$watch('jambNumber', value => isDisabled = !value.trim())"
        >
            <!-- Input Field -->
            <div>
                <label for="jambNumber" class="block mb-2 text-sm font-medium text-gray-700">JAMB Number</label>
                <input
                    type="text"
                    id="jambNumber"
                    wire:model="jambNumber"
                    x-model="jambNumber"
                    class="block w-full px-4 py-3 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Enter JAMB Number"
                >
                @error('jambNumber')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button
                    type="submit"
                    class="px-6 py-3 text-sm font-medium text-white bg-indigo-600 rounded-md shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    :disabled="isDisabled"
                    :class="{ 'opacity-50 cursor-not-allowed': isDisabled }"
                    wire:loading.attr="disabled"
                >
                    Search
                </button>
            </div>

            <!-- Loader -->
            <div wire:loading wire:target="search" class="absolute inset-0 flex items-center justify-center p-4 bg-white bg-opacity-75">
                <svg class="w-10 h-10 text-indigo-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0116 0v5a8 8 0 01-16 0z"></path>
                </svg>
                Please wait...
            </div>
        </form>

        <!-- Results Section -->
        @if ($result)
            @include('livewire.applications.includes.postutme-profileupdate-form')
        @elseif ($showResult)
            <div class="p-6 mt-8 bg-red-100 border border-red-300 rounded-md">
                <p class="text-red-700">No results found for the entered JAMB number.</p>
            </div>
        @endif
    </div>
</div>


</div>
