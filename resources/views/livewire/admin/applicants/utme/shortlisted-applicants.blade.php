@php
use App\Enums\ApplicationStatus;
$shortlisted = ApplicationStatus::SHORTLISTED;

@endphp
<div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 flex-0">
        <div
            class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
            <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0">
                <div class="lg:flex">
                    <div>
                        <h5 class="mb-0 dark:text-white">Shortlisted Applicants</h5>

                    </div>
                    <div class="my-auto mt-6 ml-auto lg:mt-0">
                        <div class="my-auto ml-auto">



                            <button export-button-products data-type="csv"
                                class="inline-block px-8 py-2 font-bold text-center uppercase align-middle transition-all bg-transparent border border-solid rounded-lg shadow-none cursor-pointer active:opacity-85 leading-pro text-size-xs ease-soft-in tracking-tight-soft bg-150 bg-x-25 hover:scale-102 active:shadow-soft-xs border-fuchsia-500 text-fuchsia-500 hover:text-fuchsia-500 hover:opacity-75 hover:shadow-none active:scale-100 active:border-fuchsia-500 active:bg-fuchsia-500 active:text-white hover:active:border-fuchsia-500 hover:active:bg-transparent hover:active:text-fuchsia-500 hover:active:opacity-75">Export</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex-auto p-6 px-0 pb-0">
                <div class="overflow-x-auto table-responsive">
                    <table class="table" datatable id="products-list">
                        <thead class="thead-light">
                            <tr>
                                <th>Full Name</th>

                                <th>Course</th>
                                <th>Phone</th>

                                <th>Status</th>
                                <th>Drop</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($shortlistedApplicants as $applicant)


                                                            <tr>
                                                                <td>
                                                                    <div class="flex">


                                                                        <h6 class="my-auto ml-4 dark:text-white">{{$applicant->surname . ' ' . $applicant->firstname . ' ' . $applicant->middlename}}</h6>
                                                                    </div>
                                                                </td>
                                                                <td class="leading-normal text-size-sm">{{$applicant->course_name}}</td>

                                                                <td class="leading-normal text-size-sm">{{$applicant->phone}}</td>

                                                                <td>

                                                                   <span class="py-1.8-em px-3-em text-size-xxs-em rounded-1 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none
                                  {{ $applicant->status === 'shortlisted' ? 'text-lime-500 bg-green-100' : 'text-gray-700 bg-gray-200' }}">
                                  {{ $applicant->status ?? 'Pending' }}
                                </span>
                                                                </td>
                                                                <td class="leading-normal text-size-sm">
                                                                    <div class="min-h-6 mb-0.5 flex items-center">
                                                                        <!-- Refactored checkbox -->
                                                                        <label class="inline-flex items-center cursor-pointer">
                                                                            <input type="checkbox"
                                                                                class="w-5 h-5 transition duration-300 ease-in-out border-gray-300 form-checkbox text-slate-800 focus:ring-slate-800"
                                                                                wire:click="drop({{$applicant->id}})">
                                                                        </label>
                                                                    </div>
                                                                </td>

                                                            </tr>
                              @endforeach

                        </tbody>
                        <tfoot>
                            <tr>
                                 <th>Full Name</th>

                                <th>Course</th>
                                <th>Phone</th>

                                <th>Status</th>
                                <th>Drop</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="{{ asset('assets') }}/js/plugins/datatables.min.js"></script>
@endpush

