<div>


    <div class="flex flex-wrap mt-6 -mx-3">
        <div class="w-full max-w-full px-3 shrink-0 md:flex-0 md:w-full lg:w-7/12">
            <div class="relative  flex flex-col w-full min-w-0 mb-0 break-words bg-white border-0 border-transparent border-solid shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="p-6 pb-0 mb-0 bg-white rounded-t-2xl mb-12">
                    <h6>School List</h6>
                    <div class="mt-4 flex justify-end">
                      <button type="button" data-toggle="modal" data-target="#add-school" class="inline-block px-8 py-2 font-bold text-center uppercase align-middle transition-all  border border-solid rounded-lg shadow-none cursor-pointer active:opacity-85 leading-pro text-xs ease-soft-in tracking-tight-soft bg-150 bg-x-25 hover:scale-102 active:shadow-soft-xs border-teal-500 text-teal-500 hover:text-teal-900 hover:opacity-75 hover:shadow-none active:scale-100 active:border-fuchsia-500 active:bg-fuchsia-500 active:text-white hover:active:border-fuchsia-500 hover:active:bg-transparent hover:active:text-fuchsia-500 hover:active:opacity-75">
                        Add SSCE
                      </button>
                    </div>
                </div>



                <div class="fixed top-0 left-0 hidden w-full h-full overflow-x-hidden overflow-y-auto transition-opacity ease-linear opacity-0 z-sticky outline-0" id="add-school" aria-hidden="true">
                    <div class="relative w-auto m-2 transition-transform duration-300 pointer-events-none sm:m-7 sm:max-w-125 sm:mx-auto lg:mt-48 ease-soft-out -translate-y-13">
                        <div class="relative flex flex-col w-full bg-white border border-solid pointer-events-auto dark:bg-gray-950 bg-clip-padding border-black/20 rounded-xl outline-0">
                        <div class="flex items-center justify-between p-4 border-b border-solid shrink-0 border-slate-100 rounded-t-xl">
                            <h5 class="mb-0 leading-normal dark:text-white" id="ModalLabel">Add SSCE</h5>

                            <button type="button" data-toggle="modal" data-target="#add-school" class="fa fa-close w-4 h-4 ml-auto box-content p-2 text-black dark:text-white border-0 rounded-1.5 opacity-50 cursor-pointer -m-2 " data-dismiss="modal"></button>
                        </div>
                        <div class="relative flex-auto p-4">
                            <form wire:submit="save">
                            <input wire:model="form.schoolName" type="text"  class="dark:bg-gray-950 mb-4 focus:shadow-soft-success-outline dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-teal-300 focus:outline-none" placeholder="Exam No....">

                            <select wire:model="form.certificateName"
                                    class="mb-4 focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none"
                                    name="choices-country" id="choices-country" >
                                    <option value="">Exam Type</option>
                                    <option value="national diploma">National Diploma</option>
                                    <option value="secondary certificate">Secondary Certificate</option>

                            </select>
                            <input wire:model="form.dateObtained"  datetimepicker class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" type="text" placeholder="e.g 2015.." />





                        </div>
                        <div class="flex flex-wrap items-center justify-end p-3 border-t border-solid shrink-0 border-slate-100 rounded-b-xl">
                            <button type="button" data-toggle="modal" data-target="#add-school" class="inline-block px-8 py-2 m-1 mb-4 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft bg-gradient-to-tl from-slate-600 to-slate-300 shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">Close</button>
                            <button type="submit"  class="inline-block px-8 py-2 m-1 mb-4 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft bg-gradient-to-tl from-purple-700 to-pink-500 shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">Add</button>
                        </div>
                        </form>
                        </div>
                    </div>
                </div>


                        {{-- @foreach ($this->schools as $school) --}}


                        {{-- @include('livewire.applications.includes.olevel-card') --}}

                        {{-- @endforeach --}}

                        <div class="flex flex-wrap -mx-3">
                            <div class="flex-auto p-6 pt-0">
                                <a href ="{{route('olevel')}}"
                                    class="inline-block float-right px-8 py-2 mt-16 mb-0 font-bold text-right text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs dark:bg-gradient-neutral bg-gradient-dark-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">Next</a>
                            </div>
                        </div>
            </div>
        </div>
        <div class="w-full max-w-full px-3 mt-6 shrink-0 md:flex-0 md:w-full lg:w-5/12 lg:mt-0">
            <div
                class="relative flex flex-col h-full min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-4 pb-0">
                    <div class="flex items-center">
                        <h6 class="mb-0 dark:text-white">Referrals</h6>
                        <button type="button"
                            class="active:shadow-soft-xs active:opacity-85 ease-soft-in leading-pro text-size-xs bg-150 bg-x-25 rounded-3.5xl p-1.2 h-6 w-6 mb-0 ml-auto flex cursor-pointer items-center justify-center border border-solid border-slate-400 bg-transparent text-center align-middle font-bold text-slate-400 shadow-none transition-all hover:bg-transparent hover:text-slate-400 hover:opacity-75 hover:shadow-none active:bg-slate-400 active:text-black hover:active:bg-transparent hover:active:text-slate-400 hover:active:opacity-75 hover:active:shadow-none"
                            data-target="tooltip_trigger">
                            <i class="fas fa-info text-size-3xs"></i>
                        </button>
                        <div class="z-50 hidden px-2 py-1 text-center text-white bg-black rounded-lg max-w-46 text-size-sm"
                            id="tooltip" role="tooltip" data-popper-placement="bottom">
                            See wich websites are sending traffic to your website
                            <div id="arrow"
                                class="invisible absolute h-2 w-2 bg-inherit before:visible before:absolute before:h-2 before:w-2 before:rotate-45 before:bg-inherit before:content-['']"
                                data-popper-arrow></div>
                        </div>
                    </div>
                </div>

                <div class="flex-auto p-4">
                    <div class="flex flex-wrap -mx-3">
                        <div class="w-full max-w-full px-3 text-center flex-0 lg:w-5/12">
                            <div class="mt-12">
                                <canvas id="chart-doughnut-referrals" height="200"></canvas>
                            </div>
                            <a href="javascript:;"
                                class="inline-block px-8 py-2 m-0 mt-6 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft bg-gradient-slate shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">See
                                all referrals</a>
                        </div>
                        <div class="w-full max-w-full px-3 flex-0 lg:w-7/12">
                            <div class="overscroll-x-auto">
                                <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                                    <tbody class="align-top">
                                        <tr>
                                            <td
                                                class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                                <div class="flex px-2 py-1">
                                                    <div>
                                                        <img class="inline-flex items-center justify-center text-white transition-all duration-200 text-size-sm ease-soft-in-out h-9 w-9 rounded-xl"
                                                            src="../../assets/img/small-logos/logo-xd.svg"
                                                            alt="xd logo" />
                                                    </div>
                                                    <div class="flex flex-col justify-center">
                                                        <h6 class="mb-0 leading-normal dark:text-white text-size-sm">
                                                            Adobe</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td
                                                class="p-2 leading-normal text-center align-middle border-b border-gray-200 border-solid text-size-sm whitespace-nowrap">
                                                <span
                                                    class="font-semibold leading-tight dark:text-white text-size-xs">25%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td
                                                class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                                <div class="flex px-2 py-1">
                                                    <div>
                                                        <img class="inline-flex items-center justify-center text-white transition-all duration-200 text-size-sm ease-soft-in-out h-9 w-9 rounded-xl"
                                                            src="../../assets/img/small-logos/logo-atlassian.svg"
                                                            alt="atlassian logo" />
                                                    </div>
                                                    <div class="flex flex-col justify-center">
                                                        <h6 class="mb-0 leading-normal dark:text-white text-size-sm">
                                                            Atlassian</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td
                                                class="p-2 leading-normal text-center align-middle border-b border-gray-200 border-solid text-size-sm whitespace-nowrap">
                                                <span
                                                    class="font-semibold leading-tight dark:text-white text-size-xs">3%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td
                                                class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                                <div class="flex px-2 py-1">
                                                    <div>
                                                        <img class="inline-flex items-center justify-center text-white transition-all duration-200 text-size-sm ease-soft-in-out h-9 w-9 rounded-xl"
                                                            src="../../assets/img/small-logos/logo-slack.svg"
                                                            alt="slack logo" />
                                                    </div>
                                                    <div class="flex flex-col justify-center">
                                                        <h6 class="mb-0 leading-normal dark:text-white text-size-sm">
                                                            Slack</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td
                                                class="p-2 leading-normal text-center align-middle border-b border-gray-200 border-solid text-size-sm whitespace-nowrap">
                                                <span
                                                    class="font-semibold leading-tight dark:text-white text-size-xs">12%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td
                                                class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                                <div class="flex px-2 py-1">
                                                    <div>
                                                        <img class="inline-flex items-center justify-center text-white transition-all duration-200 text-size-sm ease-soft-in-out h-9 w-9 rounded-xl"
                                                            src="../../assets/img/small-logos/logo-spotify.svg"
                                                            alt="spotify logo" />
                                                    </div>
                                                    <div class="flex flex-col justify-center">
                                                        <h6 class="mb-0 leading-normal dark:text-white text-size-sm">
                                                            Spotify</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td
                                                class="p-2 leading-normal text-center align-middle border-b border-gray-200 border-solid text-size-sm whitespace-nowrap">
                                                <span
                                                    class="font-semibold leading-tight dark:text-white text-size-xs">7%</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td
                                                class="p-2 align-middle border-b-0 border-solid whitespace-nowrap border-inherit">
                                                <div class="flex px-2 py-1">
                                                    <div>
                                                        <img class="inline-flex items-center justify-center text-white transition-all duration-200 text-size-sm ease-soft-in-out h-9 w-9 rounded-xl"
                                                            src="../../assets/img/small-logos/logo-jira.svg"
                                                            alt="jira logo" />
                                                    </div>
                                                    <div class="flex flex-col justify-center">
                                                        <h6 class="mb-0 leading-normal dark:text-white text-size-sm">
                                                            Jira</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td
                                                class="p-2 leading-normal text-center align-middle border-b-0 border-gray-200 border-solid text-size-sm whitespace-nowrap">
                                                <span
                                                    class="font-semibold leading-tight dark:text-white text-size-xs">10%</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap mt-6 -mx-3">
        <div class="w-full max-w-full px-3 shrink-0 sm:flex-0 sm:w-6/12">
            <div
                class="relative flex flex-col h-full min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-4 pb-0">
                    <div class="flex items-center">
                        <h6 class="mb-0 dark:text-white">Social</h6>
                        <button type="button"
                            class="active:shadow-soft-xs active:opacity-85 ease-soft-in leading-pro text-size-xs bg-150 bg-x-25 rounded-3.5xl p-1.2 h-6 w-6 mb-0 ml-auto flex cursor-pointer items-center justify-center border border-solid border-slate-400 bg-transparent text-center align-middle font-bold text-slate-400 shadow-none transition-all hover:bg-transparent hover:text-slate-400 hover:opacity-75 hover:shadow-none active:bg-slate-400 active:text-black hover:active:bg-transparent hover:active:text-slate-400 hover:active:opacity-75 hover:active:shadow-none"
                            data-target="tooltip_trigger">
                            <i class="fas fa-info text-size-3xs"></i>
                        </button>
                        <div class="z-50 hidden px-2 py-1 text-center text-white bg-black rounded-lg max-w-46 text-size-sm"
                            id="tooltip" role="tooltip" data-popper-placement="bottom">
                            See how much traffic do you get from social media
                            <div id="arrow"
                                class="invisible absolute h-2 w-2 bg-inherit before:visible before:absolute before:h-2 before:w-2 before:rotate-45 before:bg-inherit before:content-['']"
                                data-popper-arrow></div>
                        </div>
                    </div>
                </div>

                <div class="flex-auto p-4">
                    <ul class="flex flex-col pl-0 mb-0 rounded-lg">
                        <li
                            class="border-black/12.5 rounded-t-inherit relative border-solid py-2 text-inherit border-0 flex items-center px-0 mb-2">
                            <div class="w-full">
                                <div class="flex items-center mb-2">
                                    <a
                                        class="inline-block p-0 font-bold text-center text-blue-800 uppercase align-middle transition-all bg-transparent border-0 rounded-lg shadow-none cursor-pointer leading-pro text-size-xs ease-soft-in tracking-tight-soft hover:scale-102 active:opacity-85">
                                        <i
                                            class="fab fa-facebook text-5.3-em leading-3-em font-normal align-[-0.0667em]"></i>
                                    </a>
                                    <span
                                        class="mx-2 font-semibold leading-normal capitalize text-size-sm">Facebook</span>
                                    <span class="ml-auto font-semibold leading-normal text-size-sm">80%</span>
                                </div>
                                <div>
                                    <div
                                        class="h-0.75 text-size-xs flex overflow-visible rounded-lg bg-gray-200 dark:bg-gradient-dark-gray">
                                        <div
                                            class="dark:bg-gray-200 dark:bg-none bg-gradient-dark-gray w-4/5 transition-width duration-600 ease-soft rounded-1 -mt-0.4 -ml-px flex h-1.5 flex-col justify-center overflow-hidden whitespace-nowrap text-center text-white">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li
                            class="border-black/12.5 relative border-t-0 border-solid py-2 text-inherit border-0 flex items-center px-0 mb-2">
                            <div class="w-full">
                                <div class="flex items-center mb-2">
                                    <a
                                        class="inline-block p-0 font-bold text-center uppercase align-middle transition-all bg-transparent border-0 rounded-lg shadow-none cursor-pointer text-sky-600 leading-pro text-size-xs ease-soft-in tracking-tight-soft hover:scale-102 active:opacity-85">
                                        <i
                                            class="fab fa-twitter text-5.3-em leading-3-em font-normal align-[-0.0667em]"></i>
                                    </a>
                                    <span
                                        class="mx-2 font-semibold leading-normal capitalize text-size-sm">Twitter</span>
                                    <span class="ml-auto font-semibold leading-normal text-size-sm">40%</span>
                                </div>
                                <div>
                                    <div
                                        class="h-0.75 text-size-xs flex overflow-visible rounded-lg bg-gray-200 dark:bg-gradient-dark-gray">
                                        <div
                                            class="dark:bg-gray-200 dark:bg-none bg-gradient-dark-gray w-2/5 transition-width duration-600 ease-soft rounded-1 -mt-0.4 -ml-px flex h-1.5 flex-col justify-center overflow-hidden whitespace-nowrap text-center text-white">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li
                            class="border-black/12.5 relative border-t-0 border-solid py-2 text-inherit border-0 flex items-center px-0 mb-2">
                            <div class="w-full">
                                <div class="flex items-center mb-2">
                                    <a
                                        class="inline-block p-0 font-bold text-center uppercase align-middle transition-all bg-transparent border-0 rounded-lg shadow-none cursor-pointer text-orange-650 leading-pro text-size-xs ease-soft-in tracking-tight-soft hover:scale-102 active:opacity-85">
                                        <i
                                            class="fab fa-reddit text-5.3-em leading-3-em font-normal align-[-0.0667em]"></i>
                                    </a>
                                    <span
                                        class="mx-2 font-semibold leading-normal capitalize text-size-sm">Reddit</span>
                                    <span class="ml-auto font-semibold leading-normal text-size-sm">30%</span>
                                </div>
                                <div>
                                    <div
                                        class="h-0.75 text-size-xs flex overflow-visible rounded-lg bg-gray-200 dark:bg-gradient-dark-gray">
                                        <div
                                            class="dark:bg-gray-200 dark:bg-none bg-gradient-dark-gray w-3/10 transition-width duration-600 ease-soft rounded-1 -mt-0.4 -ml-px flex h-1.5 flex-col justify-center overflow-hidden whitespace-nowrap text-center text-white">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li
                            class="border-black/12.5 relative border-t-0 border-solid py-2 text-inherit border-0 flex items-center px-0 mb-2">
                            <div class="w-full">
                                <div class="flex items-center mb-2">
                                    <a
                                        class="inline-block p-0 font-bold text-center uppercase align-middle transition-all bg-transparent border-0 rounded-lg shadow-none cursor-pointer text-red-650 leading-pro text-size-xs ease-soft-in tracking-tight-soft hover:scale-102 active:opacity-85">
                                        <i
                                            class="fab fa-youtube text-5.3-em leading-3-em font-normal align-[-0.0667em]"></i>
                                    </a>
                                    <span
                                        class="mx-2 font-semibold leading-normal capitalize text-size-sm">Youtube</span>
                                    <span class="ml-auto font-semibold leading-normal text-size-sm">25%</span>
                                </div>
                                <div>
                                    <div
                                        class="h-0.75 text-size-xs flex overflow-visible rounded-lg bg-gray-200 dark:bg-gradient-dark-gray">
                                        <div
                                            class="dark:bg-gray-200 dark:bg-none bg-gradient-dark-gray w-1/4 transition-width duration-600 ease-soft rounded-1 -mt-0.4 -ml-px flex h-1.5 flex-col justify-center overflow-hidden whitespace-nowrap text-center text-white">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li
                            class="border-black/12.5 rounded-b-inherit relative border-t-0 border-solid py-2 text-inherit border-0 flex items-center px-0 mb-2">
                            <div class="w-full">
                                <div class="flex items-center mb-2">
                                    <a
                                        class="inline-block p-0 font-bold text-center uppercase align-middle transition-all bg-transparent border-0 rounded-lg shadow-none cursor-pointer text-teal-550 leading-pro text-size-xs ease-soft-in tracking-tight-soft hover:scale-102 active:opacity-85">
                                        <i
                                            class="fab fa-slack text-5.3-em leading-3-em font-normal align-[-0.0667em]"></i>
                                    </a>
                                    <span class="mx-2 font-semibold leading-normal capitalize text-size-sm">Slack</span>
                                    <span class="ml-auto font-semibold leading-normal text-size-sm">15%</span>
                                </div>
                                <div>
                                    <div
                                        class="h-0.75 text-size-xs flex overflow-visible rounded-lg bg-gray-200 dark:bg-gradient-dark-gray">
                                        <div
                                            class="dark:bg-gray-200 dark:bg-none bg-gradient-dark-gray w-15/100 transition-width duration-600 ease-soft rounded-1 -mt-0.4 -ml-px flex h-1.5 flex-col justify-center overflow-hidden whitespace-nowrap text-center text-white">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="w-full max-w-full px-3 shrink-0 sm:flex-0 sm:w-6/12">
            <div
                class="relative flex flex-col h-full min-w-0 mt-6 break-words bg-white border-0 md:mt-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
                <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-4 pb-0">
                    <div class="flex items-center">
                        <h6 class="dark:text-white">Pages</h6>
                        <button type="button"
                            class="active:shadow-soft-xs active:opacity-85 ease-soft-in leading-pro text-size-xs bg-150 bg-x-25 rounded-3.5xl p-1.2 h-6 w-6 mb-0 ml-auto flex cursor-pointer items-center justify-center border border-solid border-lime-500 bg-transparent text-center align-middle font-bold text-lime-500 shadow-none transition-all hover:bg-transparent hover:text-lime-500 hover:opacity-75 hover:shadow-none active:bg-lime-500 active:text-black hover:active:bg-transparent hover:active:text-lime-500 hover:active:opacity-75 hover:active:shadow-none"
                            data-target="tooltip_trigger">
                            <i class="fas fa-check text-size-3xs"></i>
                        </button>
                        <div class="z-50 hidden px-2 py-1 text-center text-white bg-black rounded-lg max-w-46 text-size-sm"
                            id="tooltip" role="tooltip" data-popper-placement="bottom">
                            See how much traffic do you get from social media
                            <div id="arrow"
                                class="invisible absolute h-2 w-2 bg-inherit before:visible before:absolute before:h-2 before:w-2 before:rotate-45 before:bg-inherit before:content-['']"
                                data-popper-arrow></div>
                        </div>
                    </div>
                </div>

                <div class="flex-auto px-4 pb-2">
                    <div class="p-0 overflow-x-auto">
                        <table
                            class="items-center justify-center w-full mb-0 align-top border-gray-200 dark:text-white/80 text-slate-500">
                            <thead class="align-bottom">
                                <tr>
                                    <th
                                        class="py-3 pl-2 pr-6 font-bold uppercase align-middle border-b border-gray-200 border-solid text-size-xxs text-slate-400 opacity-70 whitespace-nowrap tracking-none">
                                        Page</th>
                                    <th
                                        class="py-3 pl-2 pr-6 font-bold uppercase align-middle border-b border-gray-200 border-solid text-size-xxs text-slate-400 opacity-70 whitespace-nowrap tracking-none">
                                        Page Views</th>
                                    <th
                                        class="py-3 pl-2 pr-6 font-bold uppercase align-middle border-b border-gray-200 border-solid text-size-xxs text-slate-400 opacity-70 whitespace-nowrap tracking-none">
                                        Avg. Time</th>
                                    <th
                                        class="py-3 pl-2 pr-6 font-bold uppercase align-middle border-b border-gray-200 border-solid text-size-xxs text-slate-400 opacity-70 whitespace-nowrap tracking-none">
                                        Bounce Rate</th>
                                </tr>
                            </thead>
                            <tbody class="align-top">
                                <tr>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">1./bits</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">345</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">00:17:07</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">40.91%</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">2.
                                            /pages/argon-dashboard</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">520</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">00:23:13</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">30.14%</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">3.
                                            /pages/soft-ui-dashboard</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">122</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">00:3:10</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">54.10%</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">4.
                                            /bootstrap-themes</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">1,900</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">00:30:42</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">20.93%</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">5. /react-themes
                                        </p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">1,442</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">00:31:50</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">34.98%</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">6.
                                            /product/argon-dashboard-angular</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">201</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">00:12:42</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b border-solid dark:border-white/40 whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">21.4%</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="p-2 align-middle border-b-0 border-solid whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">7.
                                            /product/material-dashboard-pro</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b-0 border-solid whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">2,115</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b-0 border-solid whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">00:50:11</p>
                                    </td>
                                    <td
                                        class="p-2 align-middle border-b-0 border-solid whitespace-nowrap border-inherit">
                                        <p class="mb-0 font-semibold leading-normal text-size-sm">34.98%</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="{{ asset('assets') }}/js/plugins/fullcalendar.min.js"></script>
<script src="{{ asset('assets') }}/js/plugins/chartjs.min.js"></script>
@endpush
