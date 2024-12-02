@use('Carbon\Carbon;')

<!DOCTYPE html>
<html lang="en">

<head>


    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <link id="pagestyle" href="../assets/css/soft-ui-dashboard.css" rel="stylesheet" />



    <style type="text/css">
    body {
        /*padding: 2% 1% 2% 1%;
      color: #111111;
        background-image:url(image/bg2.jpg);
        background-repeat:repeat;
             width: 210mm;
             height: 297mm;*/
        margin-left: auto;
        margin-right: auto;
        padding: 0px;
        ;
        color: #111111;
        background-image: url(image/bg2.jpg);
        background-repeat: repeat;
    }
    @media print {
  .school-section {page-break-before: always;}
}
    </style>
</head>

<body>
    <div class="main">

        <div class="top-container container-fluid border-bottom border-dark row">
            <div class="mt-3 mb-3 text-center log0-container col-2 border-right border-dark">
                <img src="{{ asset('assets/img') }}/logo-ct.png" alt="logo-image" height="100px" />
            </div>

            <div class="text-center top-container-title col-8">
                <h5 class="mb-4 font-weight-bolder">WAZIRI UMARU FEDERAL POLYTECHNIC, BIRNIN KEBBI</h5>
                <h5 class="mb-4 font-weight-bold">POSTUTME SCREENING FORM</h5>
                <h6 class="font-weight-bold">{{strtoupper(config('remita.settings.academic_session'))}} ACADEMIC SESSION
                </h6>
            </div>

            {{-- <div class="p-3 mb-3 text-center log0-container col-2 border-left border-dark">

                    {!! QrCode::size(100)->generate($fullName . ' Remita:' . $rrr) !!}

                </div> --}}
        </div>

        <div class="row" style="margin:0% 3% 0% 3%; width:95%">
            <div class="span12">
                <div class="row">
                    <div class="span6" style="">
                        <table class="table table-condensed">
                            <tr>
                                <td>
                                    <p class="h6">
                                        If your application is successful you will be <br>invited to present the
                                        original copies of all your credentials for screening on a specified date:
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p>
                                    <h2 style="color:red;">Note!!!</h2>
                                    Any discrepancy between your online form and the original credentials will
                                    disqualify you. THANKS!!!.
                                    </p>
                                </td>
                            </tr>

                        </table>

                    </div>
                    <div class="span12" style="">

                        <table width="504" class="table table-condensed">
                            <tbody>
                                <tr>
                                    <th>Application Number</th>
                                    <td rowspan="4"><img src="{{ asset('storage/' . auth()->user()?->picture) }}"
                                            alt="..." height="100" width="100"></td>
                                </tr>
                                <tr>
                                    <td><Strong>{{auth()->user()->id}}</Strong></td>
                                </tr>
                                <tr>
                                    <th>Remita Number</th>
                                </tr>
                                <tr>
                                    <td><Strong>{{$RRR}}</Strong></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
                <div class="row-fluid" style="padding-right:4px">
                    <div>
                        <table class="table table-condensed">
                            <h4><Strong>SECTION A: PERSONAL DETAILS</Strong></h4>
                            <tbody>
                                <tr>
                                    <th>Name in Full: </th>
                                    <td style="width:15%;">{{ auth()->user()->full_name }}</td>
                                    <th style="width:20%;">Date of Birth:</th>
                                    <td>{{ auth()->user()->birthday }}</td>
                                    <th style="width:20%;">Gender:</th>
                                    <td>{{ auth()->user()->gender }}</td>
                                </tr>
                                <tr>
                                    <th>Home Town:</th>
                                    <td>{{ ucwords(auth()->user()->home_town) }}</td>

                                    <th>L/Govt. Area:</th>
                                    <td>{{ ucwords($lga) }}</td>
                                    <th>State of Origin:</th>
                                    <td>{{ ucwords($state)}}</td>
                                </tr>
                                <tr>
                                    <th>Nationality:</th>
                                    <td>{{ ucwords(auth()->user()->nationality) }}</td>
                                    <th>Marital Status:</th>
                                    <td>{{ ucwords(auth()->user()->marital_status) }}</td>
                                    <th>Phone Number:</th>
                                    <td>{{ auth()->user()->phone }}</td>
                                </tr>
                                <tr>
                                    <th colspan="2">Permanent Home Address:</th>
                                    <td colspan="4">{{ucwords(auth()->user()->home_address) }}</td>

                                </tr>
                                <tr>
                                    <th colspan="2">Correspondence Home Address:</th>
                                    <td colspan="4">{{ ucwords(auth()->user()->cor_address) }}</td>
                                </tr>

                                <tr>

                                    <th>Next of Kin Name:</th>
                                    <td>{{ ucwords(auth()->user()->kin_name) }}</td>
                                    <th>Phone NO. of Next of Kin:</th>
                                    <td>{{ auth()->user()->kin_phone }}</td>


                                </tr>
                                <tr>
                                    <th colspan="2">Next of Kin Address:</th>
                                    <td colspan="4">{{ucwords(auth()->user()->kin_address) }}</td>

                                </tr>
                                <tr>
                                    <th colspan="2" style="color:red;">PROGRAMME APPLIED FOR:</th>


                                    <td colspan="4" style="color:red;">{{ ucwords($programme) }}</td>
                                </tr>
                            </tbody>
                            <table>
                                <hr />
                    </div>
                </div>
                @if (auth()->user()->isPostgraduate())


                <div class="row-fluid school-section">
                    <div class="span12">
                        <table class="table table-condensed ">
                            <h4 ><b>SECTION B: SCHOOLS/COLLEGES ATTENDED</b></h4>
                            <tr>
                                <th rowspan="2">S/N</th>
                                <th rowspan="2">School

                            </tr>
                            <tr>
                                <th>Certificate</th>
                                <th>Date</th>
                            </tr>

                            @php
                            $count= 0;
                            @endphp
                            @foreach (auth()->user()->schools as $school)
                            <tr>
                                <td>{{ $count = $count + 1 }}</td>
                                <td>{{ ucwords($school->school_name) }}</td>
                                <td>{{ ucwords($school->certificate_name) }}</td>
                                <td>{{ Carbon::createFromFormat('Y-m-d', $school->date_obtained)->format('Y') }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
                  @endif
                <div class="row-fluid">
                    <div class="span6">
                        <table class="table table-condensed">
                            <h4><b>SECTION C: 'O' LEVEL RESULT DETAILS</b></h4>



                            <tr>
                                <th colspan="2">Exam Name</th>
                                <th colspan="2">Exam Number</th>
                                <th colspan="2">Exam Date</th>

                            </tr>
                            @foreach (auth()->user()->olevelExams as $exam)
                            <tr>
                                <td colspan="2">{{ ucwords($exam->exam_name) }}</td>
                                <td colspan="2">{{ ucwords($exam->exam_number) }}</td>
                                <td colspan="2">{{ ucwords($exam->exam_year) }}</td>

                            </tr>
                            @endforeach
                            <tr>
                                <th>S/N</th>
                                <th>Subject</th>
                                <th>Exam</th>
                                <th>Grade</th>
                            </tr>
                            @php
                            $subjectCount=0;
                            @endphp
                            @foreach (auth()->user()->olevelSubjectGrades as $subject)
                            <tr>



                                <td>{{ $subjectCount = $subjectCount + 1 }}</td>
                                <td>{{ ucwords($subject->subject_name) }}</td>
                                <td>{{ ucwords($subject->exam_name) }}</td>
                                <td><strong>{{ ucwords($subject->grade) }}</strong></td>

                            </tr>
                            @endforeach
                        </table>
                    </div>
                    <div class="span6">

                        <table class="table table-condensed">

                            <tr>
                                <td colspan="5">
                                    <h4><b>JAMB RESULTS DETAILS</b></h4>
                                </td>
                            </tr>
                            <tr>
                                <th colspan="2">JAMB Number</th>
                                <th colspan="1">JAMB Score</th>
                            </tr>

                            <td colspan="2">{{ auth()->user()->jamb_no }}</td>
                            <td colspan="2">{{auth()->user()->proposedCourse->jamb_score }}</td>

                            </tr>
                            <!--tr>
      <th>S/N</th>
      <th colspan="2">Subject</th>
      <th colspan="2">Score</th>
     </tr>
    <tr>
     <td></td>
      <td colspan="2"></td>
      <td colspan="2"><b></b></td>
     </tr>
    <tr>
    <td colspan="5"><h4><b> Pre RESULTS DETAILS</b></h4></td>
    </tr>
     <tr>
      <th  colspan="2">Pre Type </th>
      <th colspan="2">Year</th>
      <th colspan="1">GPA</th>
     </tr>

     <td colspan="2"></td>
      <td colspan="2"></td>
      <td colspan="1"><b></b></td>
     </tr>
     <tr>
      <th>S/N</th>
      <th colspan="2">Subject</th>
      <th colspan="2">Grade</th>
     </tr>
     <tr>
     <td></td>
      <td colspan="2"></td>
      <td colspan="2"><b></b></td>
     </tr>

    <tr>
    <td colspan="5"><h4 class="text text-dark">NATIONAL DIPLOMA CERTIFICATE DETAILS</h4></td>
    </tr>

     <tr style="width:50px;">
    <th>S/N</th>
    <th>School Name</th>
     <th>Qualification</th>
     <th>Graduation Year</th>
     <th>Class Grade</th>
   </tr>
     <tr>
    <td></td>
    <td></td>
     <td></td>
     <td></td>
     <td><b></b></td>
   <tr>
    <td colspan="5"><h4><b>PROPOSED COURSES OF STUDY</b></h4></td>
    </tr-->

                            <tr>
                                <th>S/N</th>
                                <th colspan="3">
                                    <h4> Course of Study</h4>
                                </th>

                            </tr>
                            <tr>
                                <td>{{ 1 }}</td>
                                <td colspan="3">
                                    <h6>{{ $course }}</h6>
                                </td>

                            </tr>




                        </table>


                    </div>

            </div>
        </div>


        <!-- /Content -->
    </div>


    <!-- /container -->

</body>
<script>
window.print();
</script>

</html>
