<div class="max-w-full p-6 mx-auto bg-white rounded-lg shadow-lg">
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
                class="block w-full px-4 py-3 text-sm placeholder-gray-400 border border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500"
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
                class="w-full px-4 py-2 text-sm font-semibold text-white transition-all transform bg-teal-600 rounded-md shadow hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="isDisabled"
                wire:loading.attr="disabled"
                :class="{ 'opacity-50 cursor-not-allowed': isDisabled }"
            >
                Search
            </button>
        </div>

        <!-- Loader -->
        <div
            wire:loading
            wire:target="search"
            class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-50 rounded-lg"
        >
            <div class="w-8 h-8 border-4 border-teal-500 rounded-full border-t-transparent animate-spin"></div>
        </div>
    </form>

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
