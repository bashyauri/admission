@use('Carbon\Carbon;')
@use('App\Services\AcademicSessionService;')

<!DOCTYPE html>
<html lang="en">

<head>


    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
        </script>
    <link href="{{ asset('assets/css/soft-ui-dashboard.css') }}" rel="stylesheet" />




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
            .school-section {
                page-break-before: always;
            }
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
                <h4 class="mb-2 font-weight-bolder text-success" style="letter-spacing:1px;">WAZIRI UMARU FEDERAL POLYTECHNIC, BIRNIN KEBBI</h4>
                <h5 class="mb-2 font-weight-bold text-primary" style="letter-spacing:1px;">Directorate of Higher Studies</h5>
                <h5 class="mb-3 font-weight-bold text-dark">ADMISSION SCREENING FORM</h5>
                <h6 class="font-weight-bold text-secondary">
                    {{ app(AcademicSessionService::class)->getAcademicSession(auth()->user())}} ACADEMIC SESSION
                </h6>
            </div>

            {{-- <div class="p-3 mb-3 text-center log0-container col-2 border-left border-dark">

                {!! QrCode::size(100)->generate($fullName . ' Remita:' . $rrr) !!}

            </div> --}}
        </div>

        <div class="row" style="margin:0% 3% 0% 3%; width:95%">
            <div class="span12">
                <div class="row mb-4" style="background: #f8fafc; border-radius: 12px; box-shadow: 0 2px 8px #e2e8f0; padding: 18px 0; align-items: center;">
                    <div class="col-12 col-md-8 d-flex flex-column justify-content-center align-items-start px-4" style="min-height: 100%;">
                        <p class="h6 mb-2 text-dark">
                            If your application is successful, you will be invited to present the original copies of all your credentials for screening on a specified date.
                        </p>
                        <div class="alert alert-warning py-2 px-3 mb-0" style="display:inline-block; font-size:1rem;">
                            <strong class="text-danger">Note:</strong> Any discrepancy between your online form and the original credentials will disqualify you. <span class="text-success">THANK YOU.</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 d-flex flex-column align-items-center justify-content-center px-4 mt-3 mt-md-0">
                        <div class="border rounded p-2 bg-white shadow-sm mb-2" style="width:110px;">
                            <img src="{{ auth()->user()?->profilePicture() }}" alt="Applicant Photo" height="100" width="100" class="rounded mb-1 border mx-auto d-block">
                            <div class="small text-muted text-center">Applicant Photo</div>
                        </div>
                        <span class="badge bg-primary text-white mb-1">Application Email: <strong>{{auth()->user()->email}}</strong></span>
                        <span class="badge bg-secondary text-white">Remita No: <strong>{{$RRR}}</strong></span>
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
                                <h4><b>SECTION B: SCHOOLS/COLLEGES ATTENDED</b></h4>
                                <tr>
                                    <th rowspan="2">S/N</th>
                                    <th rowspan="2">School

                                </tr>
                                <tr>
                                    <th>Certificate</th>
                                    <th>Date</th>
                                </tr>

                                @php
                                    $count = 0;
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
                                $subjectCount = 0;
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
         </tr>
         -->
                            <tr>

                                <td colspan="5">
                                    <h4><b>PROPOSED COURSES OF STUDY</b></h4>
                                </td>
                            </tr>

                            <tr>

                                <th>Department</th>
                                <th colspan="3">
                                    <h4> Course of Study</h4>
                                </th>

                            </tr>
                            <tr>
                                <td>{{ucwords($department)}}</td>
                                <td colspan="3">
                                    <h6>{{ ucwords($course) }}</h6>
                                </td>

                            </tr>




                        </table>


                    </div>
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
