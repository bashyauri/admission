<div>
 <div class="flex flex-wrap -mx-3">
        <div class="w-full max-w-full px-3 shrink-0 sm:flex-0 sm:w-4/12">
            <div
                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="relative flex-auto p-4">
                    <div class="flex flex-wrap -mx-3 ">
                        <div class="w-7/12 max-w-full px-3 text-left flex-0">
                            <p class="mb-1 font-semibold leading-normal capitalize text-size-sm">Total UTME Applicants Uploaded</p>
                            <h5 class="mb-0 font-bold dark:text-white">{{$this->totalApplicantUploded}}</h5>
                            <span class="mt-auto mb-0 font-bold leading-normal text-right text-lime-500 text-size-sm">

                                <a href="{{route('admin.imported-applicants')}}"
                                    class="inline-block px-6 py-3 mt-4 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-slate-800">View More</a>
                            </span>
                        </div>
                        <div class="w-5/12 max-w-full px-3 flex-0">
                            <div class="relative text-right">
                                <a href="javascript:;" class="cursor-pointer" dropdown-trigger aria-expanded="false">
                                    <span class="leading-tight text-size-xs text-slate-400">{{config('remita.settings.academic_session')}}</span>
                                </a>
                                <p class="hidden transform-dropdown-show"></p>
                                <ul dropdown-menu
                                    class="dark:shadow-soft-dark-xl z-100 dark:bg-gray-950 text-size-sm top-1 lg:shadow-soft-3xl duration-250 before:duration-350 before:font-awesome before:ease-soft min-w-44 before:text-5.5 transform-dropdown pointer-events-none absolute right-0 left-auto m-0 -mr-4 mt-2 list-none rounded-lg border-0 border-solid border-transparent bg-white bg-clip-padding px-0 py-2 text-left text-slate-500 opacity-0 transition-all before:absolute before:right-7 before:left-auto before:top-0 before:z-40 before:text-white before:transition-all before:content-['\f0d8']">
                                    <li>
                                        <a class="py-1.2 lg:ease-soft clear-both block w-full whitespace-nowrap rounded-lg border-0 bg-transparent px-4 text-left font-normal text-slate-500 hover:bg-gray-200 hover:text-slate-700 dark:text-white dark:hover:bg-gray-200/80 dark:hover:text-slate-700 lg:transition-colors lg:duration-300"
                                            href="javascript:;">Yesterday</a>
                                    </li>
                                    <li>
                                        <a class="py-1.2 lg:ease-soft clear-both block w-full whitespace-nowrap rounded-lg border-0 bg-transparent px-4 text-left font-normal text-slate-500 hover:bg-gray-200 hover:text-slate-700 dark:text-white dark:hover:bg-gray-200/80 dark:hover:text-slate-700 lg:transition-colors lg:duration-300"
                                            href="javascript:;">Last 7 days</a>
                                    </li>
                                    <li>
                                        <a class="py-1.2 lg:ease-soft clear-both block w-full whitespace-nowrap rounded-lg border-0 bg-transparent px-4 text-left font-normal text-slate-500 hover:bg-gray-200 hover:text-slate-700 dark:text-white dark:hover:bg-gray-200/80 dark:hover:text-slate-700 lg:transition-colors lg:duration-300"
                                            href="javascript:;">Last 30 days</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>



    </div>


    <div class="flex items-center justify-center min-h-screen">
    <div class="max-w-md p-6 bg-white rounded-lg shadow-lg">
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

                <div wire:loading>
        Please Wait...
    </div>
            <button
                type="submit"
                class="w-full px-4 py-2 mt-6 font-semibold text-white transition bg-indigo-700 rounded-lg shadow-md hover:bg-lime-500 focus:outline-none focus:ring-2 focus:ring-blue-400">
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

