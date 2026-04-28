<div class="w-full max-w-full px-3 lg:flex-0 shrink-0">
    <div class="relative flex flex-col w-full min-w-0 mb-0 break-words bg-white border-0 border-transparent border-solid shadow-soft-xl rounded-2xl bg-clip-border">

        <div class="p-6 pb-0 mb-12 bg-white rounded-t-2xl">
            <h6>Subject Grade</h6>
            <div class="flex justify-end mt-4">
                <button type="button"
                        onclick="openModal()"
                        class="inline-block px-8 py-2 text-xs font-bold text-center text-teal-500 uppercase align-middle transition-all border border-teal-500 border-solid rounded-lg shadow-none cursor-pointer active:opacity-85 leading-pro ease-soft-in tracking-tight-soft bg-150 bg-x-25 hover:scale-102 hover:text-teal-900 hover:opacity-75">
                    Add Grades
                </button>
            </div>
        </div>

        <!-- Modal -->
        <div class="fixed inset-0 hidden items-center justify-center z-50" id="add-subject-grade" style="display: none;">
            <div class="relative w-full max-w-md mx-4 bg-white dark:bg-gray-950 rounded-2xl shadow-xl">

                <!-- Modal Header -->
                <div class="flex items-center justify-between p-5 border-b">
                    <h5 class="mb-0 text-lg font-semibold dark:text-white">O'level Grade</h5>
                    <button type="button"
                            onclick="closeModal()"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl leading-none">
                        ×
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <form wire:submit="save">

                        <select wire:model="form.examName"
                                class="mb-4 block w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-fuchsia-300 focus:outline-none dark:bg-gray-900 dark:text-white">
                            <option value="">Select Exam</option>
                            @forelse ($this->exams as $exam)
                                <option value="{{ strtolower($exam->exam_name . ':' . $exam->exam_number) }}">
                                    {{ ucfirst($exam->exam_name) }} / {{ $exam->exam_number }}
                                </option>
                            @empty
                                <option>No exams found</option>
                            @endforelse
                        </select>

                        <select wire:model="form.subjectName"
                                class="mb-4 block w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-fuchsia-300 focus:outline-none dark:bg-gray-900 dark:text-white">
                            <option value="">Select Subject</option>
                            @forelse ($this->subjects as $subject)
                                <option value="{{ strtolower($subject->name) }}">{{ $subject->name }}</option>
                            @empty
                                <option>No subjects found</option>
                            @endforelse
                        </select>

                        <select wire:model="form.grade"
                                class="mb-6 block w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-fuchsia-300 focus:outline-none dark:bg-gray-900 dark:text-white">
                            <option value="">Select Grade</option>
                            @foreach ($this->grades as $grade)
                                <option value="{{ strtolower($grade->name) }}">{{ ucfirst($grade->name) }}</option>
                            @endforeach
                        </select>

                        <div class="flex justify-end gap-3">
                            <button type="button"
                                    onclick="closeModal()"
                                    class="px-6 py-2.5 text-sm font-medium text-white bg-red-500 border border-gray-300 rounded-lg hover:bg-red-50">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-8 py-2.5 text-sm font-bold text-white bg-lime-500 rounded-lg hover:scale-105 transition-all">
                                Add Grade
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 hidden z-40" id="modal-backdrop" style="display: none;"></div>

        @include('livewire.applications.includes.olevel-grade-table')

        <div class="flex flex-wrap -mx-3">
            <div class="flex-auto p-6 pt-0">
                <a href="{{ route('upload-certificate') }}"
                   class="inline-block float-right px-8 py-2 mt-16 font-bold text-white uppercase bg-gradient-to-r from-slate-700 to-slate-900 rounded-lg hover:scale-105 transition-all">
                    Next
                </a>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    function openModal() {
        document.getElementById('add-subject-grade').style.display = 'flex';
        document.getElementById('modal-backdrop').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('add-subject-grade').style.display = 'none';
        document.getElementById('modal-backdrop').style.display = 'none';
        document.body.style.overflow = '';
    }

    // Close modal when Livewire successfully saves
    Livewire.on('close-modal', () => {
        closeModal();
    });

    // Optional: Close modal when clicking on backdrop
    document.getElementById('modal-backdrop').addEventListener('click', function() {
        closeModal();
    });
</script>
@endpush
