<div class="relative max-w-full p-6 mx-auto bg-white rounded-lg shadow-lg">
    <h2 class="mb-4 text-2xl font-semibold text-center text-gray-800">Search UTME Registration Number</h2>

    <form
        wire:submit.prevent="search"
        class="space-y-6"
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
                class="mt-2 p-2 focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none"
                placeholder="Enter your JAMB Number"
                aria-label="JAMB Number"
            >
            @error('jambNumber')
                <span class="block mt-2 text-xs text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="flex justify-center">
            <button
                type="submit"
                class="w-full px-4 py-2 mt-2 text-sm font-semibold text-white transition-all transform bg-teal-500 rounded-md shadow hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="isDisabled"
                wire:loading.attr="disabled"
                :class="{ 'opacity-50 cursor-not-allowed': isDisabled }"
            >
                Search
            </button>
        </div>
    </form>

    <!-- Loader -->
    <!-- Loader -->
            <div wire:loading wire:target="search" class="absolute inset-0 flex items-center justify-center p-4 bg-white bg-opacity-75">
                <svg class="w-10 h-10 text-indigo-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0116 0v5a8 8 0 01-16 0z"></path>
                </svg>
                Please wait...
            </div>

    <!-- Results Section -->
    @if ($result)
        <div class="p-4 mt-6 border border-green-300 rounded-md bg-green-50">
            @include('livewire.applications.includes.postutme-profileupdate-form')
        </div>
    @elseif ($showResult)
        <div class="p-4 mt-6 text-center border border-red-300 rounded-md bg-red-50">
            <p class="text-sm font-medium text-red-600">No results found for the entered JAMB number.</p>
        </div>
    @endif
</div>
