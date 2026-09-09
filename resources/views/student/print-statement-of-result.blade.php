<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Statement of Result &mdash; {{ strtoupper($semester) }} SEMESTER {{ $academicSession }}</title>
    <style>
        /* ============================================================
           BASE & RESET
        ============================================================ */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
            line-height: 1.45;
        }

        /* ============================================================
           PAGE / DOCUMENT WRAPPER
        ============================================================ */
        .document {
            width: 210mm;
            max-width: 100%;
            margin: 0 auto;
            padding: 10mm 12mm 12mm;
            background: #fff;
            border: 1px solid #ccc;
        }

        /* ============================================================
           HEADER SECTION
        ============================================================ */
        .doc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 5mm;
            margin-bottom: 4mm;
            border-bottom: 3px double #000;
        }

        .logo-box {
            width: 22mm;
            flex-shrink: 0;
        }

        .logo-box img {
            max-width: 100%;
            height: auto;
        }

        .header-center {
            flex: 1;
            text-align: center;
            padding: 0 8mm;
        }

        .header-center .inst-name {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-center .inst-subtitle {
            font-size: 9pt;
            margin-top: 1mm;
            color: #333;
        }

        .header-center .doc-title {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 3mm;
            text-decoration: underline;
            letter-spacing: 1px;
        }

        .header-center .doc-subtitle {
            font-size: 9.5pt;
            margin-top: 1mm;
            font-style: italic;
            color: #444;
        }

        /* ============================================================
           STUDENT INFORMATION GRID
        ============================================================ */
        .info-section {
            margin-bottom: 4mm;
            border: 1px solid #aaa;
            border-radius: 2px;
            padding: 3mm 4mm;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5mm 8mm;
        }

        .info-row {
            display: flex;
            gap: 2mm;
        }

        .info-label {
            font-weight: bold;
            white-space: nowrap;
            min-width: 30mm;
            font-size: 10pt;
        }

        .info-value {
            font-size: 10pt;
            border-bottom: 1px dotted #888;
            flex: 1;
        }

        /* ============================================================
           RESULTS TABLE
        ============================================================ */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 4mm;
        }

        .results-table thead tr {
            background-color: #1a1a1a;
            color: #fff;
        }

        .results-table thead th {
            padding: 2.5mm 3mm;
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #000;
        }

        .results-table thead th:first-child,
        .results-table thead th:nth-child(2) {
            text-align: left;
        }

        .results-table tbody tr {
            border-bottom: 1px solid #ccc;
        }

        .results-table tbody tr:nth-child(even) {
            background-color: #f7f7f7;
        }

        .results-table tbody td {
            padding: 2mm 3mm;
            border: 1px solid #ccc;
            font-size: 10pt;
            vertical-align: middle;
        }

        .results-table tbody td.text-center { text-align: center; }
        .results-table tbody td.text-left   { text-align: left; }

        .grade-badge {
            display: inline-block;
            padding: 0.5mm 2.5mm;
            border: 1px solid currentColor;
            border-radius: 2px;
            font-weight: bold;
            font-size: 10pt;
        }

        .grade-a { color: #155724; }
        .grade-b { color: #0c4a6e; }
        .grade-c { color: #1d4e3f; }
        .grade-d { color: #7c4a00; }
        .grade-e { color: #92400e; }
        .grade-f { color: #7f1d1d; }

        .pass-mark  { color: #155724; font-weight: bold; }
        .fail-mark  { color: #7f1d1d; font-weight: bold; }

        /* ============================================================
           SUMMARY FOOTER ROW
        ============================================================ */
        .summary-row {
            background-color: #1a1a1a !important;
            color: #fff;
            font-weight: bold;
        }

        .summary-row td {
            border-color: #000 !important;
            color: #fff;
            font-size: 10pt;
            padding: 2.5mm 3mm;
        }

        /* ============================================================
           GPA BOX
        ============================================================ */
        .gpa-section {
            display: flex;
            gap: 4mm;
            margin-bottom: 5mm;
        }

        .gpa-box {
            flex: 1;
            border: 1px solid #aaa;
            padding: 2.5mm 4mm;
            text-align: center;
        }

        .gpa-box .gpa-label {
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #555;
        }

        .gpa-box .gpa-value {
            font-size: 16pt;
            font-weight: bold;
            margin-top: 1mm;
        }

        .gpa-box .gpa-sub {
            font-size: 8pt;
            color: #555;
            margin-top: 0.5mm;
        }

        /* ============================================================
           GRADING KEY TABLE
        ============================================================ */
        .grading-key {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 5mm;
        }

        .grading-key caption {
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 1mm;
            text-transform: uppercase;
        }

        .grading-key th, .grading-key td {
            border: 1px solid #ccc;
            padding: 1.5mm 2.5mm;
            text-align: center;
        }

        .grading-key th {
            background-color: #e8e8e8;
            font-weight: bold;
        }

        /* ============================================================
           SIGNATURES / ATTESTATION
        ============================================================ */
        .attestation {
            border-top: 2px solid #000;
            padding-top: 4mm;
            margin-top: 3mm;
        }

        .attestation-text {
            font-size: 9pt;
            font-style: italic;
            margin-bottom: 5mm;
            color: #333;
        }

        .sig-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 4mm;
        }

        .sig-box {
            text-align: center;
        }

        .sig-line {
            border-top: 1px solid #000;
            margin: 10mm 4mm 1mm;
        }

        .sig-label {
            font-size: 8.5pt;
            font-weight: bold;
        }

        /* ============================================================
           FOOTER / WATERMARK STRIP
        ============================================================ */
        .doc-footer {
            margin-top: 5mm;
            border-top: 2px solid #000;
            padding-top: 2.5mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8pt;
            color: #555;
        }

        .doc-footer .ref-number {
            font-family: 'Courier New', monospace;
            font-size: 8pt;
            font-weight: bold;
            color: #000;
        }

        .security-notice {
            text-align: center;
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #aaa;
            border: 1px dashed #ccc;
            padding: 1.5mm 3mm;
            margin-top: 3mm;
        }

        /* ============================================================
           SCREEN-ONLY: PRINT BUTTON
        ============================================================ */
        .no-print {
            display: block;
            text-align: center;
            margin: 8mm auto 4mm;
            font-family: Arial, sans-serif;
        }

        .btn-print {
            display: inline-block;
            background: #1a1a1a;
            color: #fff;
            padding: 8px 28px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-right: 8px;
        }

        .btn-back {
            display: inline-block;
            background: #f5f5f5;
            color: #333;
            padding: 8px 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }

        /* ============================================================
           PRINT MEDIA
        ============================================================ */
        @media print {
            .no-print { display: none !important; }

            body {
                background: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .document {
                border: none;
                margin: 0;
                padding: 8mm 10mm;
                width: 100%;
            }

            .results-table thead tr,
            .summary-row {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                size: A4;
                margin: 8mm 10mm;
            }
        }
    </style>
</head>
<body>

{{-- ── PRINT BUTTON (screen only) ─────────────────────────────────────── --}}
<div class="no-print">
    <a href="{{ url()->previous() }}" class="btn-back">&larr; Back</a>
    <button class="btn-print" onclick="window.print()">&#128438; Print Statement</button>
</div>

{{-- ── DOCUMENT ─────────────────────────────────────────────────────────── --}}
<div class="document">

    {{-- ── HEADER ───────────────────────────────────────────────────────── --}}
    <div class="doc-header">
        <div class="logo-box">
            <img src="{{ asset('assets/img/logo-ct.png') }}" alt="Institution Logo">
        </div>

        <div class="header-center">
            <div class="inst-name">DIRECTORATE OF HIGHER STUDIES</div>
            <div class="inst-subtitle">WAZIRI UMARU FEDERAL POLYTECHNIC, BIRNIN KEBBI<br>
                IN AFFILIATION WITH FEDERAL UNIVERSITY, BIRNIN KEBBI
            </div>
            <div class="doc-title">SEMESTER STATEMENT OF RESULT</div>
            <div class="doc-subtitle">
                {{ strtoupper($semester) }} SEMESTER &mdash; {{ $academicSession }} ACADEMIC SESSION
            </div>
        </div>

        <div class="logo-box" style="text-align:right;">
            <img src="{{ asset('assets/img/fubk-icon.jpg') }}" alt="Affiliation Logo">
        </div>
    </div>

    {{-- ── STUDENT INFORMATION ──────────────────────────────────────────── --}}
    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Student Name:</span>
                <span class="info-value">{{ $student->full_name ?? ($student->firstname . ' ' . $student->surname) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Matric. Number:</span>
                <span class="info-value">{{ $academicDetail?->matric_no ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Department:</span>
                <span class="info-value">{{ $academicDetail?->department?->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Programme:</span>
                <span class="info-value">{{ $academicDetail?->programme?->name ?? $academicDetail?->course?->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Level:</span>
                <span class="info-value">{{ $academicDetail?->studentLevel?->level ?? 'N/A' }}L</span>
            </div>
            <div class="info-row">
                <span class="info-label">Academic Session:</span>
                <span class="info-value">{{ $academicSession }} &mdash; <strong>{{ ucfirst($semester) }} Semester</strong></span>
            </div>
        </div>
    </div>

    {{-- ── RESULTS TABLE ─────────────────────────────────────────────────── --}}
    <table class="results-table">
        <thead>
            <tr>
                <th style="width:4%; text-align:center;">#</th>
                <th style="width:12%; text-align:left;">Course Code</th>
                <th style="text-align:left;">Course Title</th>
                <th style="width:7%;">Units</th>
                <th style="width:8%;">CA (40)</th>
                <th style="width:8%;">Exam (60)</th>
                <th style="width:9%;">Total (100)</th>
                <th style="width:7%;">Grade</th>
                <th style="width:6%;">GP</th>
                <th style="width:8%;">QP</th>
                <th style="width:8%;">Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $index => $res)
                @php
                    $code  = $res->course_code_snapshot
                        ?? $res->departmentCourse?->studentCourse?->code
                        ?? $res->registeredCourse?->departmentCourse?->studentCourse?->code
                        ?? 'N/A';

                    $title = $res->course_title_snapshot
                        ?? $res->departmentCourse?->studentCourse?->title
                        ?? $res->registeredCourse?->departmentCourse?->studentCourse?->title
                        ?? 'N/A';

                    $units = (int) ($res->credit_units_snapshot ?? $res->credit_units ?? $res->departmentCourse?->units ?? 0);
                    $ca    = $res->ca_score   !== null ? number_format((float)$res->ca_score,   1) : '-';
                    $exam  = $res->exam_score  !== null ? number_format((float)$res->exam_score,  1) : '-';
                    $total = $res->total_score !== null ? number_format((float)$res->total_score, 1) : '-';
                    $grade = strtoupper((string) ($res->grade ?? 'F'));
                    $gp    = (int) ($res->grade_point ?? $gradeService->calculateGradePoint($grade));
                    $qp    = $gp * $units;
                    $isPass = $grade !== 'F';

                    $gradeCssClass = match($grade) {
                        'A' => 'grade-a',
                        'B' => 'grade-b',
                        'C' => 'grade-c',
                        'D' => 'grade-d',
                        'E' => 'grade-e',
                        default => 'grade-f',
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left" style="font-weight:bold; font-family:'Courier New',monospace;">{{ $code }}</td>
                    <td class="text-left">{{ $title }}</td>
                    <td class="text-center">{{ $units }}</td>
                    <td class="text-center">{{ $ca }}</td>
                    <td class="text-center">{{ $exam }}</td>
                    <td class="text-center" style="font-weight:bold;">{{ $total }}</td>
                    <td class="text-center">
                        <span class="grade-badge {{ $gradeCssClass }}">{{ $grade }}</span>
                    </td>
                    <td class="text-center">{{ $gp }}</td>
                    <td class="text-center">{{ $qp }}</td>
                    <td class="text-center">
                        @if($isPass)
                            <span class="pass-mark">PASS</span>
                        @else
                            <span class="fail-mark">FAIL</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        {{-- Summary row --}}
        <tfoot>
            <tr class="summary-row">
                <td colspan="3" style="text-align:right; padding-right:4mm;">SEMESTER SUMMARY:</td>
                <td class="text-center">TCR: {{ $tcr }}</td>
                <td colspan="2" class="text-center">TCP: {{ $tcp }}</td>
                <td colspan="2" class="text-center">TQP: {{ $tqp }}</td>
                <td colspan="3" class="text-center">GPA: {{ number_format($semesterGpa, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ── GPA / CGPA BOXES ──────────────────────────────────────────────── --}}
    <div class="gpa-section">
        <div class="gpa-box">
            <div class="gpa-label">Semester GPA</div>
            <div class="gpa-value">{{ number_format($semesterGpa, 2) }}</div>
            <div class="gpa-sub">/ 5.00 &nbsp;(NUC Scale)</div>
        </div>
        @if($cgpa !== null)
        <div class="gpa-box">
            <div class="gpa-label">Cumulative GPA (CGPA)</div>
            <div class="gpa-value">{{ number_format($cgpa, 2) }}</div>
            <div class="gpa-sub">/ 5.00 &nbsp;(NUC Scale)</div>
        </div>
        <div class="gpa-box">
            <div class="gpa-label">Class of Degree (Current)</div>
            <div class="gpa-value" style="font-size:11pt; margin-top:2mm;">{{ $classOfDegree }}</div>
            <div class="gpa-sub">Based on current CGPA</div>
        </div>
        @endif
        <div class="gpa-box">
            <div class="gpa-label">Total Credits Earned</div>
            <div class="gpa-value">{{ $tcp }}</div>
            <div class="gpa-sub">of {{ $tcr }} registered units</div>
        </div>
    </div>

    {{-- ── NUC GRADING KEY ───────────────────────────────────────────────── --}}
    <table class="grading-key">
        <caption>NUC Undergraduate Grading Key (5-Point Scale)</caption>
        <thead>
            <tr>
                <th>Grade</th>
                <th>Score Range</th>
                <th>Grade Point</th>
                <th>Interpretation</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>A</td><td>70 – 100</td><td>5</td><td>Excellent</td></tr>
            <tr><td>B</td><td>60 – 69</td><td>4</td><td>Very Good</td></tr>
            <tr><td>C</td><td>50 – 59</td><td>3</td><td>Good</td></tr>
            <tr><td>D</td><td>45 – 49</td><td>2</td><td>Fair</td></tr>
            <tr><td>E</td><td>40 – 44</td><td>1</td><td>Pass</td></tr>
            <tr><td>F</td><td>0 – 39</td><td>0</td><td>Fail</td></tr>
        </tbody>
    </table>

    {{-- ── ATTESTATION & SIGNATURES ──────────────────────────────────────── --}}
    <div class="attestation">
        <p class="attestation-text">
            This is to certify that the result shown above is the official {{ ucfirst($semester) }} Semester result
            of {{ $academicSession }} Academic Session for the above-named undergraduate student of
            the Directorate of Higher Studies, Waziri Umaru Federal Polytechnic, Birnin Kebbi,
            in affiliation with Federal University, Birnin Kebbi. This statement is issued on
            <strong>{{ now()->format('d F Y') }}</strong>.
        </p>

        <div class="sig-grid">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Student's Signature &amp; Date</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Head of Department</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Registrar / Exam Officer</div>
            </div>
        </div>
    </div>

    {{-- ── DOCUMENT FOOTER ───────────────────────────────────────────────── --}}
    <div class="doc-footer">
        <span>Printed: {{ now()->format('d/m/Y H:i') }}</span>
        <span class="ref-number">Ref: {{ $refNumber }}</span>
        <span>Strictly Undergraduate &mdash; Confidential</span>
    </div>

    <div class="security-notice">
        OFFICIAL DOCUMENT &mdash; UNAUTHORISED REPRODUCTION IS PROHIBITED
    </div>

</div>

<script>
    // Auto-print when the page loads from the button link
    // (user can also click the Print button above)
</script>
</body>
</html>
