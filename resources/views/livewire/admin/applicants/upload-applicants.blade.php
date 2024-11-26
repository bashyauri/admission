<div>



    <div class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-center text-gray-800">Upload Student Data</h1>
        <p class="mt-1 text-sm text-center text-gray-600">Upload an Excel file (.xlsx) with student data.</p>

        <form wire:submit.prevent="save" class="mt-6">

            <!-- File Input -->
            <div
                class="flex flex-col items-center justify-center p-4 border-2 border-gray-300 border-dashed rounded-lg bg-gray-50">
                <label
                    for="file-upload"
                    class="flex flex-col items-center justify-center text-gray-600 transition cursor-pointer hover:text-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7l9 6 9-6m-9 6v10M21 7l-9 6-9-6" />
                    </svg>
                    <span class="text-sm font-medium">Drag and drop your file here</span>
                    <span class="mt-1 text-sm text-gray-500">or click to select</span>
                </label>
                <input id="file-upload" type="file" wire:model="file" accept=".xlsx" class="hidden">
            </div>

            @error('file')
                <span class="text-sm text-red-600">{{ $message }}</span>
            @enderror

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full px-4 py-2 mt-6 font-semibold text-white transition bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                Upload File
            </button>
        </form>

        <!-- Success Message -->
        @if (session()->has('success'))
            <div class="mt-4 text-center">
                <p class="text-sm text-green-600">{{ session('success') }}</p>
            </div>
        @endif

        <!-- UX Enhancements -->
        <div class="mt-6 text-sm text-center text-gray-500">
            <p>Need help? <a href="#" class="text-blue-500 hover:underline">Contact Support</a></p>
        </div>
    </div>
</div>

</div>

