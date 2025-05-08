<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination Card</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .exam-card {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border: 2px solid #003366;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background-color: #003366;
            color: white;
            padding: 15px;
            text-align: center;
            border-bottom: 4px solid #ffcc00;
        }

        .card-header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }

        .card-header h2 {
            margin: 5px 0 0;
            font-size: 16px;
            font-weight: 400;
        }

        .student-info {
            display: flex;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .student-details {
            flex: 1;
        }

        .student-photo {
            width: 120px;
            height: 150px;
            border: 1px solid #ddd;
            background-color: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 20px;
            font-size: 12px;
            color: #999;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: 600;
            width: 120px;
            color: #555;
        }

        .info-value {
            flex: 1;
        }

        .courses-section {
            padding: 15px;
        }

        .section-title {
            background-color: #f0f0f0;
            padding: 8px 15px;
            font-weight: 600;
            color: #003366;
            border-left: 4px solid #ffcc00;
            margin-bottom: 15px;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .course-code {
            background-color: #f8f9fa;
            padding: 8px;
            text-align: center;
            border-radius: 4px;
            font-weight: 500;
            border: 1px solid #e0e0e0;
        }

        .signature-area {
            display: flex;
            justify-content: space-between;
            padding: 20px 15px;
            border-top: 1px dashed #999;
            margin-top: 20px;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            height: 1px;
            background-color: #333;
            margin: 30px 0 5px;
        }

        .footer-note {
            background-color: #ffebee;
            color: #d32f2f;
            padding: 10px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
        }

        .barcode {
            text-align: center;
            padding: 10px;
            font-family: 'Libre Barcode 39', cursive;
            font-size: 28px;
            letter-spacing: 2px;
        }

        @media print {
            body {
                background-color: white;
                padding: 0;
            }

            .exam-card {
                box-shadow: none;
                border: 1px solid #000;
            }
        }
    </style>
</head>

<body>
    <div class="exam-card">
        <!-- Header Section -->
        <div class="card-header">
            <h1>WAZIRI UMARU FEDERAL POLYTECHNIC, BIRNIN KEBBI</h1>
            <h2>IN AFFILIATION WITH FEDERAL UNIVERSITY BIRNIN KEBBI</h2>
            <h2>OFFICIAL EXAMINATION CARD - 2023/2024 SESSION</h2>
        </div>

        <!-- Student Information -->
        <div class="student-info">
            <div class="student-details">
                <div class="info-row">
                    <div class="info-label">Matric Number:</div>
                    <div class="info-value">FUBK/2020/CSC/12345</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Full Name:</div>
                    <div class="info-value">ABDULLAHI MOHAMMED BELLO</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Faculty:</div>
                    <div class="info-value">Faculty of Science</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Department:</div>
                    <div class="info-value">Computer Science</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Program:</div>
                    <div class="info-value">B.Sc. Computer Science</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Level:</div>
                    <div class="info-value">300 Level</div>
                </div>
            </div>
            <div class="student-photo">
                Student Photograph
            </div>
        </div>

        <!-- Registered Courses -->
        <div class="courses-section">
            <div class="section-title">REGISTERED COURSES</div>
            <div class="courses-grid">
                <!-- Column 1 -->
                <div class="course-code">CSC 301</div>
                <div class="course-code">CSC 303</div>
                <div class="course-code">CSC 305</div>

                <!-- Column 2 -->
                <div class="course-code">CSC 307</div>
                <div class="course-code">MAT 301</div>
                <div class="course-code">STA 301</div>

                <!-- Column 3 -->
                <div class="course-code">GST 303</div>
                <div class="course-code">ENT 301</div>
                <div class="course-code">CSC 309</div>
            </div>
        </div>

        <!-- Barcode -->
        <div class="barcode">
            *FUBK2020CSC12345*
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
    </div>
</body>

</html>