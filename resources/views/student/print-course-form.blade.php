<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration Confirmation</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 10px;
            color: #333;
            font-size: 12px;
        }

        .confirmation-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 10px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .top-container {
            text-align: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }

        .logo-container {
            margin-bottom: 5px;
        }

        .top-container h1 {
            font-size: 18px;
            margin: 5px 0;
        }

        .top-container h3 {
            font-size: 16px;
            margin: 5px 0;
        }

        .top-container h5 {
            font-size: 14px;
            margin: 5px 0;
        }

        .top-container h6 {
            font-size: 12px;
            margin: 5px 0;
        }

        h2 {
            color: #34495e;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-top: 10px;
            font-size: 16px;
        }

        h3 {
            color: #34495e;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-top: 10px;
            font-size: 14px;
        }

        h6 {
            color: #34495e;
        }

        .top-container-title h3 {
            color: #34495e;
            padding-bottom: 5px;
            margin-top: 5px;
            font-size: 16px;
            border-bottom: none;
        }

        .student-info p {
            font-size: 12px;
            margin: 5px 0;
        }

        /* Table Styles */
        table {
            border: 1px solid #ddd;
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }

        table th,
        table td {
            padding: 4px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        table th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }

        table tr:hover {
            background-color: #f5f5f5;
        }

        /* Print Button */
        .print-section {
            text-align: center;
            margin-top: 10px;
        }

        button {
            padding: 8px 16px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        /* Print Styles */
        @media print {
            body {
                background-color: white;
                color: black;
                font-size: 10px;
            }

            .confirmation-container {
                width: 100%;
                padding: 0;
                margin: 0;
                box-shadow: none;
            }

            button {
                display: none;
            }
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
        }

        .info-item {
            /* Add any specific styling for each item if needed */
        }

        .info-item p {
            margin: 3px 0;
        }

        .info-item p strong {
            font-weight: bold;
        }

        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .signature-section .signature-block {
            width: 48%;
        }

        .signature-section p {
            margin: 3px 0;
        }

        .signature-section .info {
            text-align: left;
            margin-left: 10px;
        }

        .signature-section .student-info {
            text-align: right;
            margin-right: 10px;
        }

        .top-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }

        .logo-container {
            /* Style for individual logo containers */
            /* Add width if needed */
        }

        .top-container-title {
            text-align: center;
            flex-grow: 1;
            margin: 0 5px;
        }

        .top-container-title h1,
        .top-container-title h3,
        .top-container-title h5,
        .top-container-title h6 {
            margin: 3px 0;
        }

        .total-credits {
            margin-top: 5px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <div class="confirmation-container">
        <div class="top-container">
            <div class="logo-container">
                <img src="{{ asset('assets/img') }}/logo-ct.png" alt="logo-image" height="80">
            </div>

            <div class="top-container-title">
                <h3>WAZIRI UMARU FEDERAL POLYTECHNIC, BIRNIN KEBBI</h3>
                <h6>In Affiliation with </h6>
                <h6>Federal University Birnin Kebbi</h6>
                <h6>COURSE REGISTRATION FORM</h6>
            </div>
            <div class="logo-container">
                <img src="{{ asset('assets/img') }}/fubk-icon.jpg" alt="Right Logo" height="80">
            </div>
        </div>

        <section class="student-info">
            <h3>Student Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <p><strong>Student ID:</strong> {{ $user->academicDetail->matric_no }}</p>
                </div>
                <div class="info-item">
                    <p><strong>Full Name:</strong> {{ $user->full_name }}</p>
                </div>

                <div class="info-item">
                    <p><strong>Program:</strong> {{ $user->proposedCourse->course->name }}</p>
                </div>
                <div class="info-item">
                    <p><strong>Level:</strong> {{ $user->academicDetail->student_level_id . '00' }}</p>
                </div>
                <div class="info-item">
                    <p><strong>Session:</strong> {{ $user->academicDetail->registeredCourses[0]->academic_session }}</p>
                </div>
            </div>
        </section>

        <section class="registered-courses">
            <h3>Registered Courses</h3>
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Credits</th>
                        <th>Semester</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($courses as $course)
                        <tr>
                            <td>{{ $course->departmentCourse->studentCourse->code }}</td>
                            <td>{{ $course->departmentCourse->studentCourse->title }}</td>
                            <td>{{ $course->units }}</td>
                            <td>{{ $course->departmentCourse->studentCourse->semester == 1 ? 'FIRST' : 'SECOND' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
        <div class="total-credits">
            Total Credit Units: {{ $courses->sum('units') }}
        </div>
        <div class="signature-section">
            <div class="signature-block">

                <div class="info">
                    <p> ___________________________</p>
                    <p><strong>Student's Signature & Date</strong></p>
                </div>
            </div>
            <div class="signature-block">

                <div class="student-info">
                    <p> _______________________________</p>
                    <p><strong>Level Coordinator Signature & Date</strong></p>
                </div>
            </div>
        </div>
        <p style="color:red;tex-align:center"><strong>Valid only when Approved, Signed & Dated by the Level
                Coordinator</strong></p>
    </div>


</body>

</html>