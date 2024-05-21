<div class="w-full max-w-full px-3 lg:flex-0 shrink-0">
    <div class="relative flex flex-col w-full min-w-0 mb-0 break-words bg-white border-0 border-transparent border-solid shadow-soft-xl rounded-2xl bg-clip-border">
        <div class="p-6 pb-0 mb-0 bg-white rounded-t-2xl mb-12">
            <h6>Subject Grade</h6>
            <div class="mt-4 flex justify-end">
              <button type="button" data-toggle="modal" data-target="#add-subject-grade" class="inline-block px-8 py-2 font-bold text-center uppercase align-middle transition-all  border border-solid rounded-lg shadow-none cursor-pointer active:opacity-85 leading-pro text-xs ease-soft-in tracking-tight-soft bg-150 bg-x-25 hover:scale-102 active:shadow-soft-xs border-teal-500 text-teal-500 hover:text-teal-900 hover:opacity-75 hover:shadow-none active:scale-100 active:border-fuchsia-500 active:bg-fuchsia-500 active:text-white hover:active:border-fuchsia-500 hover:active:bg-transparent hover:active:text-fuchsia-500 hover:active:opacity-75">
                Add Grades
              </button>
            </div>
        </div>



        <div class="fixed top-0 left-0 hidden w-full h-full overflow-x-hidden overflow-y-auto transition-opacity ease-linear opacity-0 z-sticky outline-0" id="add-subject-grade" aria-hidden="true">
            <div class="relative w-auto m-2 transition-transform duration-300 pointer-events-none sm:m-7 sm:max-w-125 sm:mx-auto lg:mt-48 ease-soft-out -translate-y-13">
                <div class="relative flex flex-col w-full bg-white border border-solid pointer-events-auto dark:bg-gray-950 bg-clip-padding border-black/20 rounded-xl outline-0">
                <div class="flex items-center justify-between p-4 border-b border-solid shrink-0 border-slate-100 rounded-t-xl">
                    <h5 class="mb-0 leading-normal dark:text-white" id="ModalLabel">O'level Grade</h5>

                    <button type="button" data-toggle="modal" data-target="#add-subject-grade" class="fa fa-close w-4 h-4 ml-auto box-content p-2 text-black dark:text-white border-0 rounded-1.5 opacity-50 cursor-pointer -m-2 " data-dismiss="modal"></button>
                </div>
                <div class="relative flex-auto p-4">
                    <form wire:submit="save">

                    <select wire:model="form.examName"
                            class="mb-4 focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none"
                             >
                             <option value="">Select Exam</option>
                            @forelse ($this->exams as $exam)

                                <option value="{{$exam->exam_name.'/'.$exam->exam_number}}">{{$exam->exam_name.'/'.$exam->exam_number}}</option>

                            @empty
                                No records found
                            @endforelse


                    </select>
                    <select wire:model="form.subjectName"
                    class="mb-4 focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none"
                     >
                     <option value="">Select Subject</option>
                    @forelse ($this->subjects as $subject)

                        <option value="{{$subject->name}}">{{$subject->name}}</option>

                    @empty
                        No records found
                    @endforelse


            </select>
            <select wire:model="form.grade"
            class="mb-4 focus:shadow-soft-primary-outline dark:bg-gray-950 dark:placeholder:text-white/80 dark:text-white/80 text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 outline-none transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none">
            <option value="">Select Grade</option>
            @foreach ($this->grades as $grade)
            <option value="{{$grade->name}}">{{$grade->name}}</option>
            @endforeach

    </select>






                </div>
                <div class="flex flex-wrap items-center justify-end p-3 border-t border-solid shrink-0 border-slate-100 rounded-b-xl">
                    <button type="button" data-toggle="modal" data-target="#add-subject-grade" class="inline-block px-8 py-2 m-1 mb-4 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft bg-gradient-to-tl from-slate-600 to-slate-300 shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">Close</button>
                    <button type="submit"  class="inline-block px-8 py-2 m-1 mb-4 text-xs font-bold text-center text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer ease-soft-in leading-pro tracking-tight-soft bg-gradient-to-tl from-purple-700 to-pink-500 shadow-soft-md bg-150 bg-x-25 hover:scale-102 active:opacity-85">Add</button>
                </div>
                </form>
                </div>
            </div>
        </div>


                {{-- @foreach ($this->schools as $school) --}}


                {{-- @include('livewire.applications.includes.school-card') --}}

                {{-- @endforeach --}}

                <div class="flex flex-wrap -mx-3">
                    <div class="flex-auto p-6 pt-0">
                        <a href ="{{route('olevel')}}"
                            class="inline-block float-right px-8 py-2 mt-16 mb-0 font-bold text-right text-white uppercase align-middle transition-all border-0 rounded-lg cursor-pointer hover:scale-102 active:opacity-85 hover:shadow-soft-xs dark:bg-gradient-neutral bg-gradient-dark-gray leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25">Next</a>
                    </div>
                </div>
    </div>
        </div>
      </div>





@push('js')
<script src="{{ asset('assets') }}/js/plugins/choices.min.js"></script>
<script src="{{ asset('assets') }}/js/plugins/flatpickr.min.js"></script>
@endpush

