<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Course Score Sheet - {{ $course['code'] }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.4;
            padding: 20px;
        }

        .sheet-container {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }

        /* Action bar for screen view */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #e2e8f0;
            color: #334155;
        }

        .btn-secondary:hover {
            background-color: #cbd5e1;
        }

        /* Institutional Header */
        .institution-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #0f172a;
        }

        .institution-header h1 {
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .institution-header h2 {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            color: #334155;
            margin-bottom: 2px;
        }

        .institution-header h3 {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 6px;
        }

        .report-badge {
            display: inline-block;
            background-color: #0f172a;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 4px 12px;
            border-radius: 4px;
            margin-top: 4px;
        }

        /* Metadata Grid */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 16px;
        }

        .meta-item {
            font-size: 11px;
        }

        .meta-label {
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            font-size: 9px;
            display: block;
            margin-bottom: 2px;
        }

        .meta-val {
            font-weight: 800;
            color: #0f172a;
            font-size: 12px;
        }

        /* Table */
        .score-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .score-table th, .score-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }

        .score-table th {
            background-color: #f1f5f9;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 10px;
            color: #1e293b;
            text-align: center;
        }

        .score-table td.text-center {
            text-align: center;
        }

        .score-table td.text-right {
            text-align: right;
        }

        .score-table tr:nth-child(even) {
            background-color: #fafafa;
        }

        .grade-pill {
            font-weight: 900;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 10px;
            display: inline-block;
        }

        .grade-A { background-color: #dcfce7; color: #15803d; }
        .grade-B { background-color: #e0e7ff; color: #4338ca; }
        .grade-C { background-color: #fef9c3; color: #a16207; }
        .grade-D { background-color: #ffedd5; color: #c2410c; }
        .grade-F { background-color: #fee2e2; color: #b91c1c; }

        /* Statistics Summary Cards */
        .stats-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .stats-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: #334155;
            margin-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }

        .stat-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px;
            text-align: center;
        }

        .stat-box .stat-label {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        .stat-box .stat-number {
            font-size: 14px;
            font-weight: 900;
            color: #0f172a;
            margin-top: 2px;
        }

        /* Grade Frequency Table */
        .grade-freq-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .grade-freq-table th, .grade-freq-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            text-align: center;
        }

        .grade-freq-table th {
            background-color: #f8fafc;
            font-weight: 700;
        }

        /* Signatures Grid */
        .signatures-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 30px;
            padding-top: 10px;
            page-break-inside: avoid;
        }

        .signature-box {
            border-top: 1px dashed #475569;
            padding-top: 6px;
            text-align: center;
        }

        .signature-box .role-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
        }

        .signature-box .sub-text {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
                font-size: 10px;
            }

            .sheet-container {
                box-shadow: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            .score-table th {
                background-color: #e2e8f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .grade-pill {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <div class="sheet-container">

        {{-- Action Bar (Screen Only) --}}
        <div class="action-bar no-print">
            <button onclick="window.history.back()" class="btn btn-secondary">
                &larr; Back to Review
            </button>
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Print Score Sheet
            </button>
        </div>

        {{-- Institution Header --}}
        <div class="institution-header">
            <h1>{{ config('app.name', 'UNIVERSITY ACADEMIC PORTAL') }}</h1>
            @if(!empty($course['faculty_name']) && $course['faculty_name'] !== 'N/A')
                <h2>FACULTY OF {{ $course['faculty_name'] }}</h2>
            @endif
            <h3>DEPARTMENT OF {{ $course['department_name'] }}</h3>
            <div class="report-badge">Official Course Score Sheet</div>
        </div>


        {{-- Metadata Grid --}}
        <div class="meta-grid">
            <div class="meta-item">
                <span class="meta-label">Course Code & Title</span>
                <span class="meta-val">{{ $course['code'] }} - {{ $course['title'] }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Credit Units</span>
                <span class="meta-val">{{ $course['credit_units'] }} Unit(s)</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Academic Period</span>
                <span class="meta-val">{{ $course['session'] }} · {{ ucfirst($course['semester']) }} Semester</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Course Lecturer</span>
                <span class="meta-val">{{ $course['lecturer_name'] }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Level / Cohort</span>
                <span class="meta-val">{{ $course['level_id'] ? $course['level_id'].'00 Level' : 'All Cohorts' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Date Generated</span>
                <span class="meta-val">{{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>

        {{-- Student Results Table --}}
        <table class="score-table">
            <thead>
                <tr>
                    <th style="width: 35px;">S/N</th>
                    <th style="width: 110px; text-align: left;">Matric Number</th>
                    <th style="text-align: left;">Student Full Name</th>
                    <th style="width: 60px;">CA (40)</th>
                    <th style="width: 65px;">Exam (60)</th>
                    <th style="width: 65px;">Total (100)</th>
                    <th style="width: 50px;">Grade</th>
                    <th style="width: 50px;">Points</th>
                    <th style="width: 50px;">QP (GP&times;U)</th>
                    <th style="width: 80px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td style="font-weight: 700;">{{ $row['matric_no'] }}</td>
                        <td>{{ $row['student_name'] }}</td>
                        <td class="text-center">{{ $row['ca_score'] !== null ? number_format($row['ca_score'], 1) : '-' }}</td>
                        <td class="text-center">{{ $row['exam_score'] !== null ? number_format($row['exam_score'], 1) : '-' }}</td>
                        <td class="text-center" style="font-weight: 800;">
                            {{ $row['total_score'] !== null ? number_format($row['total_score'], 1) : '-' }}
                        </td>
                        <td class="text-center">
                            @if($row['grade'])
                                <span class="grade-pill grade-{{ $row['grade'] }}">{{ $row['grade'] }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">{{ $row['grade_point'] }}</td>
                        <td class="text-center" style="font-weight: 700;">{{ $row['quality_points'] }}</td>
                        <td class="text-center" style="font-size: 9px; text-transform: uppercase;">
                            {{ str_replace('_', ' ', $row['status']) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 24px; color: #64748b;">
                            No registered student results found for this course and session.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Statistics Summary --}}
        <div class="stats-section">
            <div class="stats-title">Performance & Distribution Summary</div>
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-label">Total Students</div>
                    <div class="stat-number">{{ $statistics['total_students'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Sat Exam</div>
                    <div class="stat-number">{{ $statistics['sat_exam'] }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Passed (&ge;45)</div>
                    <div class="stat-number" style="color: #16a34a;">{{ $statistics['passed'] }} ({{ $statistics['pass_percentage'] }}%)</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Failed (&lt;45)</div>
                    <div class="stat-number" style="color: #dc2626;">{{ $statistics['failed'] }} ({{ $statistics['fail_percentage'] }}%)</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Highest Score</div>
                    <div class="stat-number">{{ number_format($statistics['highest_score'], 1) }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Average Score</div>
                    <div class="stat-number">{{ number_format($statistics['average_score'], 1) }}</div>
                </div>
            </div>

            {{-- Grade Frequency Breakdown --}}
            <table class="grade-freq-table">
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>A (70–100%)</th>
                        <th>B (60–69%)</th>
                        <th>C (50–59%)</th>
                        <th>D (45–49%)</th>
                        <th>F (0–44%)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight: 700;">Count</td>
                        <td>{{ $statistics['grade_distribution']['A'] }}</td>
                        <td>{{ $statistics['grade_distribution']['B'] }}</td>
                        <td>{{ $statistics['grade_distribution']['C'] }}</td>
                        <td>{{ $statistics['grade_distribution']['D'] }}</td>
                        <td>{{ $statistics['grade_distribution']['F'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Signatures / Endorsements --}}
        <div class="signatures-grid">
            <div class="signature-box">
                <div class="role-title">Course Lecturer</div>
                <div class="sub-text">Name, Sign & Date</div>
            </div>
            <div class="signature-box">
                <div class="role-title">Course Coordinator</div>
                <div class="sub-text">Name, Sign & Date</div>
            </div>
            <div class="signature-box">
                <div class="role-title">Head of Department (HOD)</div>
                <div class="sub-text">Name, Sign & Date</div>
            </div>
            <div class="signature-box">
                <div class="role-title">Faculty Exam Officer</div>
                <div class="sub-text">Name, Sign & Date</div>
            </div>
        </div>

    </div>

</body>
</html>
