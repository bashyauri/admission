<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination Card</title>
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

        .card-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 10px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #3498db;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 2px solid #3498db;
            margin-bottom: 10px;
        }

        .logo {
            height: 80px;
        }

        .header-title {
            text-align: center;
            flex-grow: 1;
        }

        .header-title h2 {
            margin: 3px 0;
            font-size: 18px;
            color: #34495e;
        }

        .header-title h4 {
            margin: 3px 0;
            font-size: 14px;
            color: #34495e;
        }

        .student-photo {
            width: 100px;
            height: 120px;
            border: 1px solid #ddd;
            margin-left: 10px;
        }

        .info-section {
            margin-bottom: 15px;
        }

        .info-section h3 {
            background-color: #3498db;
            color: white;
            padding: 5px;
            font-size: 14px;
            margin: 5px 0;
            border-radius: 3px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
            margin-bottom: 10px;
        }

        .info-item p {
            margin: 3px 0;
            font-size: 12px;
        }

        .info-item p strong {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 12px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th {
            background-color: #3498db;
            color: white;
            padding: 5px;
            text-align: left;
        }

        td {
            padding: 5px;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #333;
        }

        .signature-block {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin: 30px 0 5px;
        }

        .footer-note {
            text-align: center;
            font-size: 10px;
            color: red;
            margin-top: 5px;
            font-weight: bold;
        }

        .barcode {
            text-align: center;
            margin: 10px 0;
            font-family: 'Libre Barcode 39', cursive;
            font-size: 24px;
        }

        @media print {
            body {
                background-color: white;
            }

            .card-container {
                box-shadow: none;
                border: 1px solid #000;
            }
        }
    </style>
</head>

<body>
    <div class="card-container">
        <!-- Header with logos and title -->
        <div class="header">
            <img src="https://via.placeholder.com/80x80?text=WUFPBK" alt="Institution Logo" class="logo">

            <div class="header-title">
                <h2>WAZIRI UMARU FEDERAL POLYTECHNIC, BIRNIN KEBBI</h2>
                <h4>IN AFFILIATION WITH FEDERAL UNIVERSITY BIRNIN KEBBI</h4>
                <h4>OFFICIAL EXAMINATION CARD</h4>
                <h4>2023/2024 ACADEMIC SESSION</h4>
            </div>

            <img src="https://via.placeholder.com/100x120?text=PHOTO" alt="Student Photo" class="student-photo">
        </div>

        <!-- Student information section -->
        <div class="info-section">
            <h3>STUDENT INFORMATION</h3>
            <div class="info-grid">
                <div class="info-item">
                    <p><strong>Matric Number:</strong> FUBK/2020/12345</p>
                    <p><strong>Full Name:</strong> ABDULLAHI MOHAMMED BELLO</p>
                    <p><strong>Gender:</strong> Male</p>
                </div>
                <div class="info-item">
                    <p><strong>Faculty:</strong> Science</p>
                    <p><strong>Department:</strong> Computer Science</p>
                    <p><strong>Program:</strong> B.Sc. Computer Science</p>
                </div>
                <div class="info-item">
                    <p><strong>Level:</strong> 300</p>
                    <p><strong>Phone:</strong> 08012345678</p>
                    <p><strong>Email:</strong> abello@student.fubk.edu.ng</p>
                </div>
            </div>
        </div>

        <!-- Registered courses section -->
        <div class="info-section">
            <h3>REGISTERED COURSES</h3>
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Units</th>
                        <th>Semester</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>CSC 301</td>
                        <td>Data Structures and Algorithms</td>
                        <td>3</td>
                        <td>First</td>
                    </tr>
                    <tr>
                        <td>CSC 303</td>
                        <td>Computer Architecture</td>
                        <td>2</td>
                        <td>First</td>
                    </tr>
                    <tr>
                        <td>CSC 305</td>
                        <td>Operating Systems</td>
                        <td>3</td>
                        <td>First</td>
                    </tr>
                    <tr>
                        <td>MAT 301</td>
                        <td>Numerical Analysis</td>
                        <td>2</td>
                        <td>First</td>
                    </tr>
                    <tr>
                        <td>STA 301</td>
                        <td>Probability Theory</td>
                        <td>2</td>
                        <td>First</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong>TOTAL CREDIT UNITS</strong></td>
                        <td colspan="2"><strong>12</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Barcode section -->
        <div class="barcode">
            *FUBK/2020/12345*
        </div>

        <!-- Signature section -->
        <div class="signature-section">
            <div class="signature-block">
                <div class="signature-line"></div>
                <p><strong>Student's Signature & Date</strong></p>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <p><strong>HOD/Coordinator's Signature & Date</strong></p>
            </div>
        </div>

        <!-- Footer note -->
        <div class="footer-note">
            THIS CARD MUST BE PRESENTED AT ALL EXAMINATION VENUES
        </div>
    </div>
</body>

</html>