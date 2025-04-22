@use('App\Models\Department')
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination Card</title>
    <style>
        /* Base Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
            background-color: #fff;
            font-size: 12px;
            line-height: 1.4;
        }

        /* Card Container */
        .exam-card {
            width: 210mm;
            max-width: 100%;
            margin: 0 auto;
            padding: 10mm;
            background-color: #fff;
            border: 2px solid #000;
            box-sizing: border-box;
        }

        /* Header Section */
        .card-header {
            text-align: center;
            padding-bottom: 5mm;
            margin-bottom: 5mm;
            border-bottom: 2px solid #000;
        }

        .card-header h1 {
            font-size: 16pt;
            margin: 0 0 3mm 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .card-header h2 {
            font-size: 12pt;
            margin: 2mm 0;
            font-weight: normal;
        }

        /* Add to your existing styles */
        .header-with-logos {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .left-logo,
        .right-logo {
            flex: 0 0 auto;
            width: 100px;
            /* Adjust as needed */
        }

        .header-title {
            flex: 1;
            text-align: center;
            padding: 0 15px;
        }

        .header-title h1 {
            margin: 0 0 5px 0;
            font-size: 16pt;
            font-weight: bold;
        }

        .header-title h2 {
            margin: 5px 0;
            font-size: 12pt;
            font-weight: normal;
        }

        /* Student Information */
        .student-info {
            display: flex;
            margin-bottom: 5mm;
        }

        .student-details {
            flex: 1;
        }

        .student-photo {
            width: 35mm;
            height: 45mm;
            border: 1px solid #000;
            margin-left: 5mm;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }

        .info-row {
            display: flex;
            margin-bottom: 2mm;
        }

        .info-label {
            font-weight: bold;
            width: 40mm;
        }

        /* Courses Section */
        .section-title {
            font-weight: bold;
            font-size: 14pt;
            margin: 5mm 0 3mm 0;
            text-decoration: underline;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 3mm 5mm;
            margin-bottom: 5mm;
        }

        .course-code {
            font-weight: bold;
            text-align: center;
            padding: 2mm 0;
            border-bottom: 1px dotted #000;
        }

        /* Signature Area */
        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 10mm;
            padding-top: 5mm;
            border-top: 1px solid #000;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            height: 1px;
            border-top: 1px solid #000;
            margin: 15mm 10mm 2mm 10mm;
        }

        /* Footer Elements */
        .barcode {
            font-family: 'Libre Barcode 39', monospace;
            font-size: 24pt;
            text-align: center;
            margin: 5mm 0;
            letter-spacing: 2px;
        }

        .footer-note {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            margin-top: 5mm;
            padding: 2mm;
            border: 1px solid #000;
        }

        /* Print Specific Styles */
        @media print {
            body {
                background: none;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .exam-card {
                border: none;
                padding: 0;
                margin: 0;
                width: 100%;
                box-shadow: none;
            }

            .student-photo {
                -webkit-print-color-adjust: exact;
                background-color: #fff !important;
            }

            /* Ensure the content fits on one page */
            @page {
                size: A4;
                margin: 10mm;
            }
        }

        /* Hide from screen but show when printing */
        .print-only {
            display: none;
        }

        @media print {
            .print-only {
                display: block;
            }
        }
    </style>
</head>

<body>
    <div class="exam-card">
        <!-- Header Section -->
        <div class="header-with-logos">
            <div class="left-logo">
                <img src="{{ asset('assets/img') }}/logo-ct.png" alt="Institution Logo" style="height: 80px;">
            </div>
            <div class="header-title">
                <h1>WAZIRI UMARU FEDERAL POLYTECHNIC, BIRNIN KEBBI</h1>
                <h2>IN AFFILIATION WITH FEDERAL UNIVERSITY BIRNIN KEBBI</h2>
                <h2>OFFICIAL EXAMINATION CARD - {{ $session }} SESSION</h2>
            </div>
            <div class="right-logo">
                <img src="{{ asset('assets/img') }}/fubk-icon.jpg" alt="Affiliation Logo" style="height: 80px;">
            </div>
        </div>


        <!-- Student Information -->
        <div class="student-info">
            <div class="student-details">
                <div class="info-row">
                    <div class="info-label">Reg Number:</div>
                    <div class="info-value">{{$student->academicDetail->matric_no}}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Full Name:</div>
                    <div class="info-value">{{$student->full_name}}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Faculty:</div>
                    <div class="info-value">Directorate for Higher Studies</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Department:</div>
                    <div class="info-value">{{$student->proposedCourse->department->name}}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Program:</div>
                    <div class="info-value">{{$student->proposedCourse->course->name}}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Level:</div>
                    <div class="info-value">
                        {{ str_pad($student->academicDetail->student_level_id, 3, '0', STR_PAD_RIGHT) }}
                    </div>
                </div>
            </div>
            <div class="student-photo">
                [Student Photograph]
            </div>
        </div>

        <!-- Registered Courses -->
        <div class="courses-section">
            <div class="section-title">REGISTERED COURSES</div>
            <div class="courses-grid">

                @foreach ($courses as $course)


                    <div class="course-code">{{ $course->code }}</div>

                @endforeach
                <!-- Column 1 -->
                {{-- <div class="course-code">CSC 301</div>
                <div class="course-code">CSC 303</div>
                <div class="course-code">CSC 305</div>

                <!-- Column 2 -->
                <div class="course-code">CSC 307</div>
                <div class="course-code">MAT 301</div>
                <div class="course-code">STA 301</div>

                <!-- Column 3 -->
                <div class="course-code">GST 303</div>
                <div class="course-code">ENT 301</div>
                <div class="course-code">CSC 309</div> --}}
            </div>
        </div>

        <!-- Barcode -->
        <div class="barcode">
            FIRST SEMESTER {{ $session }} SESSION
        </div>

        <!-- Signature Area -->
        <div class="signature-area">
            <div class="signature-box">
                <div class="signature-line"></div>
                <p>Student's Signature & Date</p>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <p>HOD/Coordinator's Signature & Date</p>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="footer-note">
            THIS CARD MUST BE PRESENTED FOR ADMISSION TO EXAMINATION HALLS
        </div>

        <!-- Print Instructions (only visible when printing) -->
        <div class="print-only" style="margin-top: 5mm; font-size: 8pt; text-align: center;">
            Official Document - Do Not Duplicate
        </div>
    </div>
    <script>
        window.print();
    </script>

</body>

</html>