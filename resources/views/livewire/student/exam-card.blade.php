<div>
    <div class="dataTable-wrapper dataTable-loading no-footer sortable searchable fixed-columns mb-10">


        <div class="dataTable-container">
            <table class="table dataTable-table" datatable="">
                <thead class="thead-light">
                    <tr>
                        <th data-sortable="" style="width: 46.2993%;"><a href="#">Session</a>
                        </th>
                        <th data-sortable="" style="width: 9.70395%;"><a href="#">Semester</a>
                        </th>
                        <th data-sortable="" style="width: 7.56579%;"><a href="#">Level</a>
                        </th>
                        <th data-sortable="" style="width: 9.86842%;"><a href="#">Status</a>
                        </th>

                    </tr>
                </thead>
                <tbody>
                    @forelse ($examCards as $examCard)



                        <tr>
                            <td>
                                <div class="flex">


                                    <h6 class="my-auto ml-2 dark:text-white">{{$examCard->academic_session}}</h6>
                                </div>
                            </td>

                            <td class="leading-normal text-size-sm">
                                {{ $examCard->semester == 1 ? 'First' : 'Second' }}
                            </td>
                            <td class="leading-normal text-size-sm">
                                {{ str_pad($examCard->student_level_id, 3, '0', STR_PAD_RIGHT) }}
                            </td>

                            <td>
                                <a href={{ route('student.print-exam-card', ['session' => str_replace('/', '-', $examCard->academic_session), 'semester' => $examCard->semester == 1 ? 'first' : 'second']) }}
                                    class="py-1.8-em px-3-em text-size-xxs-em rounded-1 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-lime-700 bg-lime-200 hover:cursor-pointer">Download</a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="flex flex-col justify-center">
                                    <h6 class="mb-0 text-sm leading-normal">No Transaction yet</h6>

                                </div>
                            </td>
                        </tr>
                    @endforelse


                </tbody>

            </table>
        </div>

    </div>
</div>