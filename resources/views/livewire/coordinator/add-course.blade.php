<div>



    <div class="flex flex-wrap -mx-3 mt-6">
        <div class="w-full max-w-full px-3 md:w-4/12 flex-0">
            <div
                class="relative flex flex-col min-w-0 mb-12 overflow-auto overflow-x-hidden break-words bg-white border-0 max-h-70-screen lg:mb-0 bg-white/80 shadow-blur dark:bg-gray-950 dark:shadow-soft-dark-xl rounded-2xl bg-clip-border">
                <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-4">
                    <h6 class="dark:text-white">Courses</h6>
                    <input type="text" placeholder="Search Contact"
                        class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none"
                        wire:model.live="search" />
                </div>
                <div class="flex justify-center items-center p-4" wire:loading>
                    <div
                        class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-fuchsia-500 dark:border-fuchsia-300">
                    </div>
                    <span class="ml-2 text-fuchsia-500 dark:text-fuchsia-300">Searching...</span>
                </div>
                <div class="flex-auto p-2">

                    @forelse ($courses as $course)

                        <div href="javascript:;" class="cursor-pointer block p-2 bg-gradient-lime rounded-xl mb-2">
                            <div class="flex p-2">

                                <div wire:click="addCourse({{ $course->id }})" class="ml-4">
                                    <div class="items-center justify-between">
                                        <h6 class="mb-0 text-white dark:text-white">
                                            {{$course->code}}
                                        </h6>
                                        <p class="mb-0 leading-normal text-white text-size-sm">{{$course->title}}</p>
                                        <input type="hidden" wire:model="form.code" value="{{$course->code}}">
                                        <input type="hidden" wire:model="form.title" value="{{$course->title}}">
                                        <input type="hidden" wire:model="form.course_id" value="{{$course->id}}">
                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                        <p>No courses found</p>
                    @endforelse

                </div>
            </div>

        </div>
        <div class="w-full max-w-full px-3 md:w-8/12 flex-0">
            <div
                class="relative flex flex-col min-w-0 mb-12 break-words bg-white border-0 max-h-70-screen lg:mb-0 bg-white/80 shadow-blur dark:bg-gray-950 dark:shadow-soft-dark-xl rounded-2xl bg-clip-border">
                <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 shadow-soft-3xl">
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full max-w-full px-3 shrink-0 md:w-10/12 md:flex-0">
                            <div class="flex items-center">
                                <img src="../../assets/img/team-2.jpg" alt="avatar-img"
                                    class="inline-flex items-center justify-center w-12 h-12 text-white transition-all duration-200 text-size-base ease-soft-in-out rounded-xl">
                                <div class="ml-4">
                                    <h6 class="block mb-0 dark:text-white">Charlie Watson</h6>
                                    <span class="leading-normal text-size-sm opacity-80 text-slate-700">last seen
                                        today at 1:53am</span>
                                </div>
                            </div>
                        </div>
                        <div class="w-1/12 max-w-full px-3 pr-0 my-auto flex-0">
                            <button type="button"
                                class="inline-block w-9 h-9 p-2.8 mb-0 text-slate-700 mr-4 sm:mr-0 font-bold text-center uppercase align-middle transition-all bg-transparent border border-transparent border-solid rounded-lg cursor-pointer leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-none bg-150 bg-x-25 active:opacity-85">
                                <i class="ni ni-camera-compact"></i>
                            </button>

                        </div>
                        <div class="w-1/12 max-w-full px-3 pl-0 my-auto flex-0">
                            <div>

                                <button type="button" dropdown-trigger aria-expanded="false"
                                    class="inline-block w-9 h-9 p-2.8 mb-0 text-slate-700 mr-4 sm:mr-0 font-bold text-center uppercase align-middle transition-all bg-transparent border border-transparent border-solid rounded-lg cursor-pointer leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-none bg-150 bg-x-25 active:opacity-85">
                                    <i class="ni ni-settings"></i>
                                </button>
                                <ul dropdown-menu
                                    class="z-100 min-w-44 text-size-sm shadow-soft-3xl duration-250 transform-dropdown before:duration-350 before:font-awesome before:ease-soft before:text-5.5 dark:bg-gray-950 pointer-events-none absolute top-6 right-16 bottom-auto left-auto m-0 mt-2 block origin-top cursor-pointer list-none rounded-lg border-0 border-solid border-transparent bg-white bg-clip-padding p-2 text-left text-slate-500 opacity-0 transition-all will-change-transform before:absolute before:top-0 before:right-5 before:left-auto before:z-40 before:text-white before:transition-all before:content-['\f0d8'] sm:-mr-6">
                                    <li class="relative"><a
                                            class="py-1.2 lg:ease-soft clear-both block w-full whitespace-nowrap rounded-lg px-4 font-normal text-slate-500 transition-colors hover:bg-gray-200 hover:text-slate-700 focus:bg-gray-200 focus:text-slate-700 dark:text-white dark:hover:bg-gray-200/80 dark:hover:text-slate-700 lg:duration-300"
                                            href="javascript:;">Profile</a></li>
                                    <li class="relative"><a
                                            class="py-1.2 lg:ease-soft clear-both block w-full whitespace-nowrap rounded-lg px-4 font-normal text-slate-500 transition-colors hover:bg-gray-200 hover:text-slate-700 focus:bg-gray-200 focus:text-slate-700 dark:text-white dark:hover:bg-gray-200/80 dark:hover:text-slate-700 lg:duration-300"
                                            href="javascript:;">Mute conversation</a></li>
                                    <li class="relative"><a
                                            class="py-1.2 lg:ease-soft clear-both block w-full whitespace-nowrap rounded-lg px-4 font-normal text-slate-500 transition-colors hover:bg-gray-200 hover:text-slate-700 focus:bg-gray-200 focus:text-slate-700 dark:text-white dark:hover:bg-gray-200/80 dark:hover:text-slate-700 lg:duration-300"
                                            href="javascript:;">Block</a></li>
                                    <li class="relative"><a
                                            class="py-1.2 lg:ease-soft clear-both block w-full whitespace-nowrap rounded-lg px-4 font-normal text-slate-500 transition-colors hover:bg-gray-200 hover:text-slate-700 focus:bg-gray-200 focus:text-slate-700 dark:text-white dark:hover:bg-gray-200/80 dark:hover:text-slate-700 lg:duration-300"
                                            href="javascript:;">Clear chat</a></li>
                                    <li class="relative">
                                        <hr class="h-px my-2 bg-gradient-horizontal-dark" />
                                    </li>
                                    <li class="relative"><a
                                            class="py-1.2 text-danger lg:ease-soft clear-both block w-full whitespace-nowrap rounded-lg px-4 font-normal text-red-600 transition-colors hover:bg-gray-200 focus:bg-gray-200 dark:hover:bg-gray-200/80 lg:duration-300"
                                            href="javascript:;">Delete chat</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-auto p-6 overflow-auto overflow-x-hidden">
                    <div class="flex flex-wrap justify-start mb-6 -mx-3">
                        <div class="w-auto max-w-full px-3 flex-0">
                            <div
                                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                                <div class="flex-auto px-4 py-2">
                                    <p class="mb-1">It contains a lot of good lessons about effective practices</p>
                                    <div class="flex items-center leading-normal text-size-sm opacity-60">
                                        <i class="mr-1 leading-normal ni ni-check-bold text-size-sm"></i>
                                        <small>3:14am</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end mb-6 -mx-3 text-right">
                        <div class="w-auto max-w-full px-3 flex-0">
                            <div
                                class="relative flex flex-col min-w-0 break-words bg-gray-200 border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                                <div class="flex-auto px-4 py-2">
                                    <p class="mb-1"> Can it generate daily design links that include essays and data
                                        visualizations ?</p>
                                    <div class="flex items-center justify-end leading-normal text-size-sm opacity-60">
                                        <i class="mr-1 leading-normal ni ni-check-bold text-size-sm"></i>
                                        <small>4:42pm</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap mt-6 -mx-3">
                        <div class="w-full max-w-full px-3 text-center shrink-0 md:flex-0 md:w-full">
                            <span
                                class="py-2.2-em px-3.6-em text-size-xs-em rounded-1.8 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-slate-700">Wed,
                                3:27pm</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-start mb-6 -mx-3">
                        <div class="w-auto max-w-full px-3 flex-0">
                            <div
                                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                                <div class="flex-auto px-4 py-2">
                                    <p class="mb-1"> Yeah! Responsive Design is geared towards those trying to build
                                        web apps </p>
                                    <div class="flex items-center leading-normal text-size-sm opacity-60">
                                        <i class="mr-1 leading-normal ni ni-check-bold text-size-sm"></i>
                                        <small>4:31pm</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end mb-6 -mx-3 text-right">
                        <div class="w-auto max-w-full px-3 flex-0">
                            <div
                                class="relative flex flex-col min-w-0 break-words bg-gray-200 border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                                <div class="flex-auto px-4 py-2">
                                    <p class="mb-1"> Excellent, I want it now ! </p>
                                    <div class="flex items-center justify-end leading-normal text-size-sm opacity-60">
                                        <i class="mr-1 leading-normal ni ni-check-bold text-size-sm"></i>
                                        <small>4:42pm</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-start mb-6 -mx-3">
                        <div class="w-auto max-w-full px-3 flex-0">
                            <div
                                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                                <div class="flex-auto px-4 py-2">
                                    <p class="mb-1"> You can easily get it; The content here is all free </p>
                                    <div class="flex items-center leading-normal text-size-sm opacity-60">
                                        <i class="mr-1 leading-normal ni ni-check-bold text-size-sm"></i>
                                        <small>4:42pm</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end mb-6 -mx-3 text-right">
                        <div class="w-auto max-w-full px-3 flex-0">
                            <div
                                class="relative flex flex-col min-w-0 break-words bg-gray-200 border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                                <div class="flex-auto px-4 py-2">
                                    <p class="mb-1">
                                        Awesome, blog is important source material for anyone who creates apps?
                                        <br>
                                        Beacuse these blogs offer a lot of information about website development.
                                    </p>
                                    <div class="flex items-center justify-end leading-normal text-size-sm opacity-60">
                                        <i class="mr-1 leading-normal ni ni-check-bold text-size-sm"></i>
                                        <small>4:42pm</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-start mb-6 -mx-3">
                        <div class="w-5/12 max-w-full px-3 flex-0">
                            <div
                                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                                <div class="flex-auto p-2">
                                    <div class="w-full max-w-full p-0 flex-0">
                                        <img class="h-auto max-w-full mb-2 rounded-xl"
                                            src="https://images.unsplash.com/photo-1602142946018-34606aa83259?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1762&q=80"
                                            alt="Rounded Image">
                                    </div>
                                    <div class="flex items-center leading-normal text-size-sm opacity-60">
                                        <i class="mr-1 leading-normal ni ni-check-bold text-size-sm"></i>
                                        <small>4:47pm</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end mb-6 -mx-3 text-right">
                        <div class="w-auto max-w-full px-3 flex-0">
                            <div
                                class="relative flex flex-col min-w-0 break-words bg-gray-200 border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                                <div class="flex-auto px-4 py-2">
                                    <p class="mb-0"> At the end of the day … the native dev apps is where users are
                                    </p>
                                    <div class="flex items-center justify-end leading-normal text-size-sm opacity-60">
                                        <i class="mr-1 leading-normal ni ni-check-bold text-size-sm"></i>
                                        <small>4:47pm</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-start -mx-3">
                        <div class="w-auto max-w-full px-3 flex-0">
                            <div
                                class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                                <div class="flex-auto px-4 py-2">
                                    <p class="mb-0">Charlie is typing... </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-black/12.5 rounded-b-2xl border-t-0 border-solid p-6 block">
                    <form class="items-center">
                        <div class="flex">
                            <input type="text" placeholder="Type here"
                                class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                            <button type="button"
                                class="inline-block px-6 py-3 mb-0 ml-2 font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs bg-gradient-fuchsia leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">
                                <i class="ni ni-send"></i>
                            </button>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>