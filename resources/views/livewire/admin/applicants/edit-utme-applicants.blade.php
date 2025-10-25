<div>


    <div class="my-4">
        <div class="flex flex-wrap -mx-3">
            <div class="w-full max-w-full px-3 mx-auto sm:flex-0 shrink-0 sm:w-10/12 md:w-8/12">
                <form wire:submit.prevent="recommendUTMEApplicant">
                    <div
                        class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border sm:my-12">
                        <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 text-center">

                            <div class="flex flex-wrap -mx-3 md:justify-between">
                                <div class="w-full max-w-full px-3 mt-auto md:flex-0 shrink-0 md:w-4/12">
                                    <h6 class="mb-0 text-left text-slate-400 dark:text-white dark:opacity-80">Name
                                    </h6>
                                    <h5 class="mb-0 text-left dark:text-white">{{$user->full_name}}</h5>
                                </div>
                                <div class="w-full max-w-full px-3 mt-auto md:flex-0 shrink-0 md:w-7/12 lg:w-5/12">
                                    <div class="flex flex-wrap mt-6 -mx-3 text-left md:mt-12 md:text-right">
                                        <div class="w-full max-w-full p-0 md:flex-0 shrink-0 md:w-6/12">
                                            <h6 class="mb-0 text-slate-400 dark:text-white dark:opacity-80">Email:</h6>
                                        </div>
                                        <div class="w-full max-w-full p-0 md:flex-0 shrink-0 md:w-6/12">
                                            <h6 class="mb-0 text-slate-700 dark:text-white">{{$user->email}}</h6>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap -mx-3 text-left md:text-right">
                                        <div class="w-full max-w-full px-3 md:flex-0 shrink-0 md:w-6/12">
                                            <h6 class="mb-0 text-slate-400 dark:text-white dark:opacity-80">Phone:
                                            </h6>
                                        </div>
                                        <div class="w-full max-w-full px-3 md:flex-0 shrink-0 md:w-6/12">
                                            <h6 class="mb-0 text-slate-700 dark:text-white">{{$user->phone}}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex-auto p-6">
                            <div class="flex flex-wrap -mx-3">
                                <div class="w-full max-w-full px-3 flex-0">
                                    <div class="overflow-x-auto">

                                        <table
                                            class="w-full mb-4 align-top border-gray-200 text-slate-500 dark:border-white/40">
                                            <thead class="align-bottom">
                                                <tr>
                                                    <th scope="col"
                                                        class="px-2 py-3 font-semibold text-left capitalize bg-transparent border-b border-solid shadow-none tracking-none whitespace-nowrap border-b-gray-200 dark:border-white/40 dark:text-white">
                                                        Subject</th>
                                                    <th scope="col"
                                                        class="px-2 py-3 pl-6 font-semibold capitalize bg-transparent border-b border-solid shadow-none tracking-none whitespace-nowrap border-b-gray-200 dark:border-white/40 dark:text-white">
                                                        Grade</th>

                                                </tr>
                                            </thead>
                                            <tbody class="border-t-2">
                                                @foreach ($user->olevelSubjectGrades as $subject)
                                                    <tr>
                                                        <td
                                                            class="p-2 text-left border-b whitespace-nowrap dark:border-white/40 dark:text-white/60">
                                                            {{ucwords($subject->subject_name)}}
                                                            <p class="mb-0 text-xs leading-tight text-slate-400">
                                                                {{ucwords($subject->exam_name)}}
                                                            </p>
                                                        </td>
                                                        <td
                                                            class="p-2 pl-6 border-b whitespace-nowrap dark:border-white/40 dark:text-white/60">
                                                            {{strtoupper($subject->grade)}}
                                                        </td>

                                                    </tr>
                                                @endforeach



                                            </tbody>

                                        </table>
                                        <div class="flex flex-wrap -mx-3">
                                            <!-- Qualification Table -->
                                            <div class="w-full px-3 md:w-6/12">
                                                <table
                                                    class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                                                    <thead class="align-bottom">
                                                        <tr>
                                                            <th class="px-3 py-3 text-sm leading-normal">Qualification
                                                            </th>
                                                            <th
                                                                class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b border-gray-200 border-solid shadow-none tracking-none whitespace-nowrap text-slate-400 opacity-70">
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($user->certificateUploads as $certificate)
                                                            <tr>
                                                                <td
                                                                    class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                                                    <div class="flex px-2 py-1">
                                                                        <div class="flex flex-col justify-center">
                                                                            <h6 class="mb-0 text-sm leading-normal">
                                                                                {{ ucwords($certificate->name) }}
                                                                            </h6>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td
                                                                    class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                                                    <p class="mb-0 text-xs font-semibold leading-tight">
                                                                        <a target="_blank"
                                                                            href="{{ asset('storage/' . $certificate->path) }}"
                                                                            aria-label="View Certificate">
                                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                                fill="none" viewBox="0 0 24 24"
                                                                                stroke-width="1.5" stroke="currentColor"
                                                                                class="w-6 h-6">
                                                                                <title>View Certificate</title>
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                                            </svg>
                                                                        </a>
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="2"
                                                                    class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                                                    <p class="mb-0 text-xs font-semibold leading-tight">No
                                                                        Record(s)</p>
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Department Dropdown -->
                                            <div class="w-full px-3 md:w-6/12">
                                                <label
                                                    class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                                                    for="department">Remark</label>
                                                <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                                                    <select wire:model="remark"
                                                        class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none">
                                                        <option value="">Select an Option</option>
                                                        <option value="merit">Merit</option>
                                                        <option value="elds">Elds</option>
                                                        <option value="catchmentarea">Catchment Area</option>
                                                        <option value="exception">Exception</option>
                                                    </select>
                                                    @error('remark')
                                                        <p class="text-red-500 text-size-sm">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="w-full px-3 md:w-4/12">
                                                <label
                                                    class="mb-2 ml-1 font-bold text-size-xs text-slate-700 dark:text-white/80"
                                                    for="comment">Comment</label>
                                                <div class="relative flex flex-wrap items-stretch w-full rounded-lg">
                                                    <input wire:model="comment"
                                                        class="focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none" />
                                                    @error('comment')
                                                        <p class="text-red-500 text-size-sm">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>





                                        </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-black/12.5 mt-6 rounded-b-2xl border-t-0 border-solid p-6 md:mt-12">
                            <div class="flex justify-end mt-6 mb-4">
                                <a href="{{route('admin.all-utme-applicants')}}"
                                    class="inline-block px-6 py-3 m-0 font-bold text-center uppercase align-middle transition-all bg-gray-200 border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 text-slate-800">Back</a>
                                <button type="submit"
                                    class="inline-block px-6 py-3 m-0 ml-2 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft {{$user->isRecommended() ? 'bg-gradient-red' : 'bg-gradient-lime'}}  shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">{{$user->isRecommended() ? 'Drop' : 'Recommend'}}</button>
                                <div wire:loading wire:target="recommendUTMEApplicant">
                                    updating status...
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>