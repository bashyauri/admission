<div>
    <div class="flex flex-wrap -mx-3">
        <div class="w-full max-w-full px-3 shrink-0 sm:flex-0 sm:w-4/12">
            <div
                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="relative flex-auto p-4">
                    <div class="flex flex-wrap -mx-3 ">
                        <div class="w-7/12 max-w-full px-3 text-left flex-0">
                            {{-- <p class="mb-1 font-semibold leading-normal capitalize text-size-sm">School Fees</p>
                            <h5 class="mb-0 font-bold dark:text-white"></h5> --}}
                            <span class="mt-auto mb-0 font-bold leading-normal text-right text-lime-500 text-size-sm">

                                <a href="{{route('coordinator.add-course')}}"
                                    class="inline-block px-6 py-3 mt-4 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-lime leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-white">Manage
                                    Courses</a>
                            </span>
                        </div>
                        <div class="w-5/12 max-w-full px-3 flex-0">
                            <div class="relative text-right">
                                <a href="javascript:;" class="cursor-pointer" dropdown-trigger aria-expanded="false">
                                    <span
                                        class="leading-tight text-size-xs text-slate-400">{{config('remita.settings.academic_session')}}</span>
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

        <div class="w-full max-w-full px-3 mt-6 shrink-0 sm:mt-0 sm:flex-0 sm:w-4/12">
            <div
                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="relative flex-auto p-4">
                    <div class="flex flex-wrap -mx-3 ">
                        <div class="w-7/12 max-w-full px-3 text-left flex-0">
                            {{-- <p class="mb-1 font-semibold leading-normal capitalize text-size-sm">Not Recommended
                            </p> --}}
                            <h5 class="mb-0 font-bold dark:text-white"></h5>
                            <span class="mt-auto mb-0 font-bold leading-normal text-center text-lime-500 text-size-sm">

                                <a href="{{route('coordinator.department-level-units')}}"
                                    class="inline-block px-6 py-3 mt-4 font-bold text-center uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-lime leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-white">Add
                                    Max Units</a>
                            </span>
                        </div>
                        <div class="w-5/12 max-w-full px-3 flex-0">
                            <div class="relative text-center">
                                <a href="javascript:;" class="cursor-pointer" dropdown-trigger aria-expanded="false">
                                    <span
                                        class="leading-tight text-size-xs text-slate-400">{{config('remita.settings.academic_session')}}</span>
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





</div>