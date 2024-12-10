<div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 flex-0">
        <div
            class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
            <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0">
                <div class="lg:flex">
                    <div>
                        <h5 class="mb-0 dark:text-white">All UTME Applicants</h5>

                    </div>
                    <div class="my-auto mt-6 ml-auto lg:mt-0">
                        <div class="my-auto ml-auto">



                            <a href ="{{route('admin.export-recommended-pdf')}}" target="_blank"
                                class="inline-block px-8 py-2 font-bold text-center uppercase align-middle transition-all bg-transparent border border-solid rounded-lg shadow-none cursor-pointer active:opacity-85 leading-pro text-size-xs ease-soft-in tracking-tight-soft bg-150 bg-x-25 hover:scale-102 active:shadow-soft-xs border-fuchsia-500 text-fuchsia-500 hover:text-fuchsia-500 hover:opacity-75 hover:shadow-none active:scale-100 active:border-fuchsia-500 active:bg-fuchsia-500 active:text-white hover:active:border-fuchsia-500 hover:active:bg-transparent hover:active:text-fuchsia-500 hover:active:opacity-75">Export Recommended Applicants</a>
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
                                 <th>Jamb no</th>
                                <th>Phone</th>

                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recommendedApplicants as $applicant)


                            <tr>
                                <td>
                                    <div class="flex">

                                        <img class="ml-4 w-1/10"
                                            src="{{asset('/storage/'.$applicant->picture)}}"
                                            alt="user image">
                                        <h6 class="my-auto ml-4 dark:text-white">{{$applicant->surname.' '.$applicant->firstname.' '.$applicant->middlename}}</h6>
                                    </div>
                                </td>
                                <td class="leading-normal text-size-sm">{{$applicant->course_name}}</td>
                                 <td class="leading-normal text-size-sm">{{$applicant->jamb_no}}</td>

                                <td class="leading-normal text-size-sm">{{$applicant->phone}}</td>

                                <td>

                                   <span class="py-1.8-em px-3-em text-size-xxs-em rounded-1 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none
  {{ $applicant->status === 'shortlisted' ? 'text-green-500 bg-green-100' : 'text-gray-700 bg-gray-200' }}">
  {{ $applicant->status ?? 'Pending' }}
</span>
                                </td>
                                <td class="leading-normal text-size-sm">

                                     <a href="{{ route('admin.edit-utme-applicant', $applicant->user_id)}}" class="mx-4">

                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="ml-2 size-4">
  <path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" />
  <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z" />
</svg>

                                    </a>

                                </td>
                            </tr>
                              @endforeach

                        </tbody>
                        <tfoot>
                            <tr>

                                <th>Course</th>
                                <th>Phone</th>

                                <th>Status</th>
                                <th>Action</th>
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
