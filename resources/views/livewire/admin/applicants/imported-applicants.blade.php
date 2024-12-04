@use('Carbon\Carbon')
<div class="flex flex-wrap -mx-3">
    <div class="w-full max-w-full px-3 flex-0">
        <div
            class="relative flex flex-col min-w-0 break-words bg-white border-0 dark:bg-gray-950 dark:shadow-soft-dark-xl shadow-soft-xl rounded-2xl bg-clip-border">
            <div class="border-black/12.5 rounded-t-2xl border-b-0 border-solid p-6 pb-0">
                <div class="lg:flex">
                    <div>
                        <h5 class="mb-0 dark:text-white">All Uploaded Applicants</h5>

                    </div>

                </div>
            </div>
            <div class="flex-auto p-6 px-0 pb-0">
                <div class="overflow-x-auto table-responsive">
                    <table class="table" datatable id="products-list">
                        <thead class="thead-light">
                            <tr>
                                <th>Full Name</th>

                                <th>Jamb Number</th>
                                  <th>Course Selected</th>
                                    <th>Jamb Score</th>
                                     <th>Created</th>
                                    <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->importedApplicants as $applicant)


                            <tr>
                                <td>
                                    <div class="flex">


                                        <h6 class="my-auto ml-4 dark:text-white">{{$applicant->name}}</h6>
                                    </div>
                                </td>
                                <td class="leading-normal text-size-sm">{{$applicant->jamb_no}}</td>
                                 <td class="leading-normal text-size-sm">{{$applicant->course}}</td>
                                   <td class="leading-normal text-size-sm">{{$applicant->jamb_score}}</td>
                                 <td class="leading-normal text-size-sm">{{ $applicant->created_at->diffForHumans() }}</td>
                                 <td class="leading-normal text-size-sm">{{ $applicant->updated_at->diffForHumans() }}</td>

                            </tr>
                              @endforeach

                        </tbody>
                        <tfoot>
                            <tr>
                               <th>Jamb Number</th>

                                <th>Name</th>
                                <th>Course Selected</th>
                                    <th>Jamb Score</th>
                                     <th>Created</th>
                                    <th>Last Updated</th>

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
