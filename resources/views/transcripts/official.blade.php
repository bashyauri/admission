<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Academic Transcript - {{ $academicDetail->matric_no ?? $student->id }}</title>
    <style>
        @page {
            margin: 14mm 12mm 14mm 12mm;
            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 8pt;
                color: #555;
            }
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #1a202c;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 35%;
            left: 8%;
            width: 84%;
            text-align: center;
            opacity: 0.045;
            font-size: 52pt;
            font-weight: 900;
            color: #000;
            transform: rotate(-32deg);
            z-index: -1000;
            text-transform: uppercase;
            letter-spacing: 4px;
        }

        /* Header layout */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            border-bottom: 2px solid #065f46;
            padding-bottom: 8px;
        }

        .header-logo {
            width: 80px;
            text-align: left;
            vertical-align: middle;
        }

        .header-logo img {
            width: 75px;
            height: auto;
        }

        .header-center {
            text-align: center;
            vertical-align: middle;
        }

        .header-center h1 {
            margin: 0;
            font-size: 15pt;
            font-weight: 800;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-center h2 {
            margin: 2px 0 0 0;
            font-size: 9.5pt;
            font-weight: 600;
            color: #1f2937;
            text-transform: uppercase;
        }

        .header-center h3 {
            margin: 2px 0 0 0;
            font-size: 8.5pt;
            font-weight: 500;
            color: #4b5563;
        }

        .header-center .doc-title {
            display: inline-block;
            margin-top: 4px;
            padding: 3px 12px;
            background: #065f46;
            color: #ffffff;
            font-size: 9pt;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-radius: 3px;
        }

        .header-qr {
            width: 85px;
            text-align: right;
            vertical-align: middle;
        }

        .header-qr img {
            width: 80px;
            height: 80px;
        }

        .qr-caption {
            font-size: 6pt;
            color: #64748b;
            text-align: center;
            margin-top: 1px;
            text-transform: uppercase;
        }

        /* Student Info Section */
        .info-panel {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
        }

        .info-panel td {
            padding: 4px 8px;
            font-size: 8pt;
            vertical-align: top;
            border: 1px solid #e2e8f0;
        }

        .info-label {
            font-weight: 700;
            color: #475569;
            width: 16%;
            text-transform: uppercase;
            font-size: 7.5pt;
        }

        .info-value {
            font-weight: 600;
            color: #0f172a;
            width: 34%;
        }

        /* Semester tables */
        .semester-block {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .semester-heading {
            background: #065f46;
            color: #ffffff;
            font-weight: 700;
            font-size: 8.5pt;
            padding: 4px 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0;
            border-top-left-radius: 2px;
            border-top-right-radius: 2px;
        }

        .courses-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        .courses-table th {
            background: #e2e8f0;
            color: #1e293b;
            font-weight: 700;
            font-size: 7.5pt;
            text-transform: uppercase;
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        .courses-table th.col-left {
            text-align: left;
        }

        .courses-table td {
            padding: 3px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            font-size: 8pt;
        }

        .courses-table tr:nth-child(even) {
            background: #f8fafc;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'Courier New', Courier, monospace; font-weight: 600; }
        .font-bold { font-weight: 700; }

        .repeat-tag {
            color: #b91c1c;
            font-weight: 700;
            font-size: 7pt;
            background: #fee2e2;
            padding: 1px 4px;
            border-radius: 2px;
            display: inline-block;
        }

        .grade-pass { color: #065f46; font-weight: 700; }
        .grade-fail { color: #dc2626; font-weight: 700; }

        /* Semester Summary Row */
        .summary-bar {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            border: 1px solid #94a3b8;
            background: #f1f5f9;
            font-size: 7.5pt;
        }

        .summary-bar td {
            padding: 3px 6px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        .summary-bar .sec-title {
            background: #334155;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 7pt;
        }

        /* Final Summary & Degree Classification */
        .final-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 10px;
            page-break-inside: avoid;
            border: 2px solid #065f46;
        }

        .final-summary-table th {
            background: #065f46;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8pt;
            padding: 5px 8px;
            text-align: center;
        }

        .final-summary-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            text-align: center;
            font-size: 8.5pt;
            background: #f8fafc;
        }

        .degree-badge {
            font-size: 11pt;
            font-weight: 800;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Legend Table */
        .legend-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .legend-table th {
            background: #475569;
            color: #fff;
            padding: 2px 4px;
            font-weight: 700;
            border: 1px solid #334155;
            text-align: center;
        }

        .legend-table td {
            padding: 2px 4px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        /* Attestation and Signature */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 33.33%;
            vertical-align: top;
            padding: 8px 12px;
            text-align: center;
        }

        .sig-line {
            border-bottom: 1px solid #1e293b;
            height: 35px;
            margin-bottom: 4px;
        }

        .sig-title {
            font-weight: 700;
            font-size: 7.5pt;
            color: #1e293b;
            text-transform: uppercase;
        }

        .sig-sub {
            font-size: 6.5pt;
            color: #64748b;
        }

        .seal-box {
            display: inline-block;
            width: 75px;
            height: 75px;
            border: 2px dashed #94a3b8;
            border-radius: 50%;
            line-height: 75px;
            font-size: 7pt;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 700;
        }

        .footer-note {
            margin-top: 8px;
            font-size: 6.5pt;
            color: #64748b;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <!-- Watermark -->
    <div class="watermark">
        {{ $official ? 'Official Transcript' : 'Student Copy - Unofficial' }}
    </div>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if($logoBase64)
                    <img src="data:image/jpeg;base64,{{ $logoBase64 }}" alt="FUBK Crest">
                @else
                    <div style="width: 75px; height: 75px; border: 1px solid #065f46; line-height: 75px; text-align: center; font-size: 9pt; font-weight: bold; color: #065f46;">FUBK</div>
                @endif
            </td>
            <td class="header-center">
                <h1>Federal University Birnin Kebbi</h1>
                <h2>Kebbi State, Nigeria</h2>
                <h3>Academic Affairs Division &bull; Directorate of Academic Planning &amp; Records</h3>
                <div class="doc-title">
                    {{ $official ? 'Official Academic Transcript' : 'Undergraduate Student Academic Record' }}
                </div>
            </td>
            <td class="header-qr">
                @if($qrCodeSvg)
                    <img src="data:image/svg+xml;base64,{{ $qrCodeSvg }}" alt="Verification QR Code">
                    <div class="qr-caption">Scan to Verify</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Student Bio & Academic Information -->
    <table class="info-panel">
        <tr>
            <td class="info-label">Student Name:</td>
            <td class="info-value">{{ strtoupper($student->surname) }}, {{ $student->firstname }} {{ $student->othername }}</td>
            <td class="info-label">Matric Number:</td>
            <td class="info-value font-mono">{{ $academicDetail->matric_no ?? 'PENDING' }}</td>
        </tr>
        <tr>
            <td class="info-label">Faculty / School:</td>
            <td class="info-value">{{ $academicDetail->department->name ?? 'Faculty of Science' }}</td>
            <td class="info-label">Department:</td>
            <td class="info-value">{{ $academicDetail->department->name ?? 'Computer Science' }}</td>
        </tr>
        <tr>
            <td class="info-label">Programme / Degree:</td>
            <td class="info-value">{{ $academicDetail->course->name ?? 'B.Sc. Computer Science' }} (Undergraduate)</td>
            <td class="info-label">Admission Session:</td>
            <td class="info-value">{{ $academicDetail->admission_session ?? $academicDetail->acad_session ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="info-label">Reference No:</td>
            <td class="info-value font-mono">{{ $transcript->request_number ?? 'TRQ-PENDING' }}</td>
            <td class="info-label">Verification Code:</td>
            <td class="info-value font-mono">{{ $transcript->verification_code ?? 'TRV-PENDING' }}</td>
        </tr>
        <tr>
            <td class="info-label">Date Issued:</td>
            <td class="info-value">{{ $generated_at->format('d F Y, H:i:s') }}</td>
            <td class="info-label">Max Units Level:</td>
            <td class="info-value">
                {{ $departmentMaxUnit ? $departmentMaxUnit . ' Units (DepartmentMaxUnit Ceiling)' : 'Per NUC Standard Limits' }}
            </td>
        </tr>
    </table>

    <!-- Academic Records Per Session / Semester -->
    @forelse($resultsBreakdown as $sem)
        <div class="semester-block">
            <div class="semester-heading">
                {{ $sem['academic_session'] }} &bull; {{ $sem['semester_title'] }}
            </div>
            <table class="courses-table">
                <thead>
                    <tr>
                        <th style="width: 14%;">Course Code</th>
                        <th class="col-left" style="width: 46%;">Course Title</th>
                        <th style="width: 8%;">Credit Units</th>
                        <th style="width: 8%;">Score</th>
                        <th style="width: 6%;">Grade</th>
                        <th style="width: 6%;">Grade Point</th>
                        <th style="width: 6%;">Quality Points</th>
                        <th style="width: 6%;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sem['courses'] as $course)
                        <tr>
                            <td class="text-center font-mono font-bold">{{ $course['code'] }}</td>
                            <td class="text-left">{{ $course['title'] }}</td>
                            <td class="text-center">{{ $course['units'] }}</td>
                            <td class="text-center font-mono">{{ number_format((float) $course['score'], 1) }}</td>
                            <td class="text-center {{ $course['grade'] === 'F' ? 'grade-fail' : 'grade-pass' }}">
                                {{ $course['grade'] }}
                            </td>
                            <td class="text-center">{{ $course['grade_point'] }}</td>
                            <td class="text-center font-mono">{{ $course['quality_points'] }}</td>
                            <td class="text-center">
                                @if($course['is_repeated'])
                                    <span class="repeat-tag" title="Repeated Course Attempt">R{{ $course['attempt_number'] }}</span>
                                @elseif($course['grade'] === 'F')
                                    <span class="repeat-tag">FAIL</span>
                                @else
                                    <span style="color: #065f46; font-size: 7pt; font-weight: 600;">PASS</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Semester & Running Cumulative Summary Strip -->
            <table class="summary-bar">
                <tr>
                    <td class="sec-title" style="width: 18%;">Semester Metrics:</td>
                    <td><strong>TCR:</strong> {{ $sem['tcr'] }}</td>
                    <td><strong>TCP:</strong> {{ $sem['tcp'] }}</td>
                    <td><strong>TQP:</strong> {{ $sem['tqp'] }}</td>
                    <td><strong>GPA:</strong> <span class="font-mono font-bold">{{ number_format($sem['gpa'], 2) }}</span></td>
                    <td class="sec-title" style="width: 20%;">Cumulative to Date:</td>
                    <td><strong>CCR:</strong> {{ $sem['ccr'] }}</td>
                    <td><strong>CCP:</strong> {{ $sem['ccp'] }}</td>
                    <td><strong>CQP:</strong> {{ $sem['cqp'] }}</td>
                    <td><strong>CGPA:</strong> <span class="font-mono font-bold" style="color: #065f46;">{{ number_format($sem['cgpa'], 2) }}</span></td>
                </tr>
            </table>
        </div>
    @empty
        <div style="padding: 20px; text-align: center; border: 1px solid #cbd5e1; background: #f8fafc; margin-bottom: 12px;">
            <p style="color: #64748b; font-size: 9pt; margin: 0;">No official released results recorded for this student.</p>
        </div>
    @endforelse

    <!-- Final Academic Summary & Class of Degree -->
    <table class="final-summary-table">
        <thead>
            <tr>
                <th style="width: 20%;">Total Credits Registered (TCUR)</th>
                <th style="width: 20%;">Total Credits Earned (TCUE)</th>
                <th style="width: 20%;">Total Quality Points (TQP)</th>
                <th style="width: 15%;">Final CGPA</th>
                <th style="width: 25%;">Class of Degree</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">{{ $summary['total_credit_units'] }}</td>
                <td class="font-bold" style="color: #065f46;">{{ $summary['total_credit_units_earned'] }}</td>
                <td class="font-bold">{{ $summary['total_grade_points'] }}</td>
                <td>
                    <span class="font-mono font-bold" style="font-size: 13pt; color: #065f46;">
                        {{ number_format($summary['final_cgpa'], 2) }}
                    </span>
                    <span style="font-size: 7.5pt; color: #64748b;"> / 5.00</span>
                </td>
                <td>
                    <span class="degree-badge">{{ $summary['class_of_degree'] }}</span>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- NUC 5-Point Grading Legend -->
    <table class="legend-table">
        <thead>
            <tr>
                <th colspan="7">National Universities Commission (NUC) Undergraduate 5-Point Grading System &amp; Degree Classification</th>
            </tr>
            <tr>
                <th>Score Range</th>
                <th>Letter Grade</th>
                <th>Grade Point (GP)</th>
                <th>Description</th>
                <th>CGPA Range</th>
                <th>Class of Degree</th>
                <th>Key Symbols</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>70% &ndash; 100%</td>
                <td><strong>A</strong></td>
                <td>5.00</td>
                <td>Excellent</td>
                <td>4.50 &ndash; 5.00</td>
                <td>First Class Honours</td>
                <td rowspan="6" style="text-align: left; vertical-align: top; padding: 2px 6px;">
                    <strong>TCR:</strong> Total Credits Registered<br>
                    <strong>TCP:</strong> Total Credits Passed<br>
                    <strong>TQP:</strong> Total Quality Points<br>
                    <strong>GPA:</strong> Grade Point Average<br>
                    <strong>CGPA:</strong> Cumulative GPA<br>
                    <strong>R#:</strong> Repeated Attempt
                </td>
            </tr>
            <tr>
                <td>60% &ndash; 69%</td>
                <td><strong>B</strong></td>
                <td>4.00</td>
                <td>Very Good</td>
                <td>3.50 &ndash; 4.49</td>
                <td>Second Class Upper Division</td>
            </tr>
            <tr>
                <td>50% &ndash; 59%</td>
                <td><strong>C</strong></td>
                <td>3.00</td>
                <td>Good</td>
                <td>2.40 &ndash; 3.49</td>
                <td>Second Class Lower Division</td>
            </tr>
            <tr>
                <td>45% &ndash; 49%</td>
                <td><strong>D</strong></td>
                <td>2.00</td>
                <td>Fair</td>
                <td>1.50 &ndash; 2.39</td>
                <td>Third Class Honours</td>
            </tr>
            <tr>
                <td>40% &ndash; 44%</td>
                <td><strong>E</strong></td>
                <td>1.00</td>
                <td>Pass</td>
                <td>1.00 &ndash; 1.49</td>
                <td>Pass Degree</td>
            </tr>
            <tr>
                <td>0% &ndash; 39%</td>
                <td><strong>F</strong></td>
                <td>0.00</td>
                <td>Fail</td>
                <td>0.00 &ndash; 0.99</td>
                <td>Fail</td>
            </tr>
        </tbody>
    </table>

    <!-- Signatures and Attestation Area -->
    <table class="signature-table">
        <tr>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Examinations Officer</div>
                <div class="sig-sub">Signature &amp; Date</div>
            </td>
            <td>
                <div class="seal-box">SEAL</div>
                <div class="sig-sub" style="margin-top: 4px;">University Official Embossed Seal</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-title">Registrar / Authorized Officer</div>
                <div class="sig-sub">Signature &amp; Date</div>
            </td>
        </tr>
    </table>

    <!-- Footer Verification Notice -->
    <div class="footer-note">
        This transcript is officially issued by Federal University Birnin Kebbi. To verify authenticity online, scan the QR code or visit
        <strong>{{ $verificationUrl }}</strong> using verification code: <strong>{{ $transcript->verification_code ?? 'N/A' }}</strong>.
        Any alteration, erasure or mutilation renders this document completely null and void.
    </div>

</body>
</html>
