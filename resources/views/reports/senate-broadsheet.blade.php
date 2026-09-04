<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Report for Senate - {{ $department['name'] }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            background-color: #f1f5f9;
            color: #000000;
            font-size: 11px;
            line-height: 1.35;
            padding: 20px;
        }

        .report-page {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* Action bar for screen */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #cbd5e1;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .btn-primary { background-color: #0f172a; color: #ffffff; }
        .btn-primary:hover { background-color: #1e293b; }
        .btn-secondary { background-color: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background-color: #cbd5e1; }

        /* Report Header */
        .header-title-block {
            text-align: center;
            margin-bottom: 20px;
        }

        .header-title-block h1 {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 4px;
        }

        .header-title-block h2 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .header-title-block h3 {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Metadata Block */
        .meta-container {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .meta-col-left {
            width: 48%;
        }

        .meta-col-right {
            width: 48%;
            display: flex;
            justify-content: space-between;
        }

        .meta-row {
            display: flex;
        }

        .meta-label {
            width: 110px;
            color: #000000;
        }

        .meta-value {
            color: #000000;
        }

        .print-ref {
            text-align: right;
            font-size: 10px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        /* Results Table */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
        }

        .report-table th {
            font-weight: 800;
            text-transform: uppercase;
            padding: 4px 6px;
            border-bottom: 1px solid #000000;
            font-size: 9.5px;
        }

        .report-table td {
            padding: 8px 6px;
            vertical-align: top;
            border-bottom: 1px solid #000000;
        }

        .col-sn {
            width: 30px;
            font-weight: 700;
        }

        .col-matric {
            width: 100px;
            font-weight: 700;
        }

        .col-name {
            width: 160px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .col-breakdown {
            padding-right: 15px !important;
        }

        .col-remarks {
            width: 170px;
            font-size: 9.5px;
            font-weight: 700;
        }

        /* Breakdown Interior */
        .gpa-summary-line {
            font-weight: 800;
            margin-bottom: 5px;
            letter-spacing: 0.2px;
            font-size: 9.5px;
        }

        .gpa-summary-line span {
            margin-right: 14px;
        }

        .courses-grid {
            display: flex;
            flex-wrap: wrap;
            row-gap: 3px;
            column-gap: 12px;
            font-weight: 600;
            font-size: 9.5px;
        }

        .course-tag {
            white-space: nowrap;
        }

        /* Remarks Styling */
        .remark-block {
            line-height: 1.35;
        }

        .remark-title {
            font-weight: 800;
            margin-bottom: 2px;
        }

        .status-title {
            font-weight: 800;
            margin-top: 5px;
            margin-bottom: 1px;
        }

        .status-val {
            font-weight: 800;
        }

        /* Summary Footer */
        .summary-wrapper {
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .summary-header-title {
            text-align: center;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 15px;
        }

        .summary-table th, .summary-table td {
            border: 1px solid #000000;
            padding: 4px 6px;
        }

        .summary-table th {
            font-weight: 800;
            text-transform: uppercase;
            background-color: #ffffff;
        }

        .asterisk-divider {
            text-align: center;
            letter-spacing: 1px;
            font-size: 10px;
            margin: 15px 0 35px 0;
        }

        .signature-section {
            text-align: center;
            page-break-inside: avoid;
        }

        .signature-line {
            display: inline-block;
            width: 250px;
            border-top: 1px solid #000000;
            margin-bottom: 4px;
        }

        .signature-caption {
            font-size: 10px;
            font-weight: 700;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
                font-size: 9.5px;
            }

            .report-page {
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
                margin: 8mm 10mm;
            }

            .report-table th, .report-table td {
                border-color: #000000 !important;
            }
        }
    </style>
</head>
<body>

    <div class="report-page">

        {{-- Action Bar (Screen Only) --}}
        <div class="action-bar no-print">
            <button onclick="window.history.back()" class="btn btn-secondary">
                &larr; Back to Results
            </button>
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Print Grade Report for Senate
            </button>
        </div>

        {{-- Top Institutional Heading --}}
        <div class="header-title-block">
            <h1>WAZIRI UMARU FEDERAL POLYTECHNIC BIRNIN KEBBI</h1>
            <h2>IN AFFILIATION WITH FEDERAL UNIVERSITY BIRNIN KEBBI</h2>
            <h3>GRADE REPORT FOR SENATE</h3>
        </div>

        {{-- Metadata Header --}}
        <div class="meta-container">
            <div class="meta-col-left">
                <div class="meta-row">
                    <span class="meta-label">FACULTY</span>
                    <span class="meta-value">AFFILIATION</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">DEPARTMENT</span>
                    <span class="meta-value">{{ strtoupper($department['name']) }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">PROGRAMME</span>
                    <span class="meta-value">{{ strtoupper($programme) }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">SESSION-LEVEL</span>
                    <span class="meta-value">{{ $session }} - {{ is_numeric($level) ? $level.'00 Level' : $level }}</span>
                </div>
            </div>

            <div class="meta-col-right">
                <div style="width: 50%;">
                    <div class="meta-row">
                        <span style="width: 85px;">MIN UNITS TS</span>
                        <span>15</span>
                    </div>
                    <div class="meta-row">
                        <span style="width: 85px;">MAX UNITS TS</span>
                        <span>24</span>
                    </div>
                    <div class="meta-row">
                        <span style="width: 85px;">MIN UNITS TD</span>
                        <span>0</span>
                    </div>
                    <div class="meta-row">
                        <span style="width: 85px;">MAX UNITS TD</span>
                        <span>0</span>
                    </div>
                </div>
                <div style="width: 50%; text-align: right;">
                    <div>DATE {{ now()->format('D, d-M-Y') }}</div>
                    <div>TIME {{ now()->format('H:i:s') }}</div>
                </div>
            </div>
        </div>

        <div class="print-ref">Print SR4</div>

        {{-- Student Results Table --}}
        <table class="report-table">
            <thead>
                <tr>
                    <th class="col-sn" style="text-align: left;">#</th>
                    <th class="col-matric" style="text-align: left;">Matric Number</th>
                    <th class="col-name" style="text-align: left;">Full Name</th>
                    <th class="col-breakdown" style="text-align: left;">RESULTS BREAKDOWN</th>
                    <th class="col-remarks" style="text-align: left;">REMARKS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $idx => $student)
                    <tr>
                        <td class="col-sn">{{ $idx + 1 }}.</td>
                        <td class="col-matric">{{ $student['matric_no'] }}</td>
                        <td class="col-name">{{ $student['student_name'] }}</td>
                        <td class="col-breakdown">
                            <div class="gpa-summary-line">
                                <span>UTS = {{ $student['uts'] }}</span>
                                <span>UTD = {{ $student['utd'] }}</span>
                                <span>GPTS = {{ $student['gpts'] }}</span>
                                <span>GPTD = {{ $student['gptd'] }}</span>
                                <span>CGPA LS = {{ $student['cgpa_ls'] ?? '-' }}</span>
                                <span>CGPA = {{ number_format($student['cgpa'], 2) }}</span>
                            </div>

                            <div class="courses-grid">
                                @foreach($student['course_breakdown_items'] as $item)
                                    <span class="course-tag">{{ $item }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="col-remarks">
                            <div class="remark-block">
                                @if($student['is_pass'])
                                    <div>PASS</div>
                                @else
                                    @if(!empty($student['repeat_courses']))
                                        <div class="remark-title">REPEAT:</div>
                                        <div>{{ implode(', ', $student['repeat_courses']) }}</div>
                                    @endif

                                    @if(!empty($student['status_text']))
                                        <div class="status-title">STATUS:</div>
                                        <div class="status-val">{{ $student['status_text'] }}</div>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 25px; color: #64748b;">
                            No student examination records found for this cohort in {{ $session }} ({{ ucfirst($semester) }} Semester).
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Summary Footer --}}
        <div class="summary-wrapper">
            <div class="summary-header-title">SUMMARY</div>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>PASS</th>
                        <th>PROBATION</th>
                        <th>WITHDRAWN</th>
                        <th>SPECIAL CASES</th>
                        <th>OTHERS</th>
                        <th>TOTAL NUMBER OF STUDENTS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $summary['pass_count'] }}</td>
                        <td>{{ $summary['probation_count'] }}</td>
                        <td>{{ $summary['withdrawn_count'] }}</td>
                        <td>{{ $summary['special_cases_count'] }}</td>
                        <td>{{ $summary['others_count'] }}</td>
                        <td>{{ $summary['total_students'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $summary['pass_percentage'] }}%</td>
                        <td>{{ $summary['probation_percentage'] }}%</td>
                        <td>{{ $summary['withdrawn_percentage'] }}%</td>
                        <td>{{ $summary['special_cases_percentage'] }}%</td>
                        <td>{{ $summary['others_percentage'] }}%</td>
                        <td>100%</td>
                    </tr>
                </tbody>
            </table>

            <div class="asterisk-divider">
                *********************************************************************************************
            </div>

            <div class="signature-section">
                <div class="signature-line"></div>
                <div class="signature-caption">(Chairman of the Senate)</div>
            </div>
        </div>

    </div>

</body>
</html>
