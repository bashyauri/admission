<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senate Graduation Broadsheet - {{ $department['name'] }} ({{ $session }})</title>
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
            font-size: 10.5px;
            line-height: 1.35;
            padding: 20px;
        }

        .report-page {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 6px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* Action bar for screen view */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            padding-bottom: 14px;
            border-bottom: 1px solid #cbd5e1;
        }

        .action-group {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-primary { background-color: #0f172a; color: #ffffff; }
        .btn-primary:hover { background-color: #1e293b; }
        .btn-secondary { background-color: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background-color: #cbd5e1; }
        .btn-success { background-color: #059669; color: #ffffff; }
        .btn-success:hover { background-color: #047857; }

        /* Institutional Title Block */
        .header-title-block {
            text-align: center;
            margin-bottom: 22px;
        }

        .header-title-block h1 {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .header-title-block h2 {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #334155;
            margin-bottom: 8px;
        }

        .header-title-block h3 {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
            display: inline-block;
            padding: 4px 18px;
            margin-top: 4px;
        }

        /* Metadata Block */
        .meta-container {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 16px;
            line-height: 1.6;
        }

        .meta-col-left {
            width: 55%;
        }

        .meta-col-right {
            width: 40%;
            text-align: right;
        }

        .meta-row {
            display: flex;
        }

        .meta-label {
            width: 140px;
            color: #475569;
        }

        .meta-value {
            color: #000000;
            font-weight: 800;
        }

        /* Graduands Table */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            border-top: 1.5px solid #000000;
            border-bottom: 1.5px solid #000000;
            margin-bottom: 24px;
        }

        .report-table th {
            font-weight: 800;
            text-transform: uppercase;
            padding: 6px 5px;
            border-bottom: 1.5px solid #000000;
            background-color: #f8fafc;
            color: #000000;
        }

        .report-table td {
            padding: 6px 5px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }

        .report-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .col-sn { width: 30px; text-align: center; font-weight: 700; }
        .col-matric { width: 95px; font-weight: 800; font-family: monospace; }
        .col-name { width: 170px; font-weight: 700; text-transform: uppercase; }
        .col-entry { width: 75px; text-align: center; }
        .col-mode { width: 90px; text-align: center; font-size: 9px; }
        .col-prog { width: 130px; text-transform: uppercase; }
        .col-units { width: 45px; text-align: center; font-weight: 700; }
        .col-cqp { width: 45px; text-align: center; font-weight: 700; }
        .col-cgpa { width: 50px; text-align: center; font-weight: 800; font-size: 10px; }
        .col-class { width: 130px; font-weight: 800; text-transform: uppercase; }
        .col-status { width: 110px; font-weight: 700; }
        .col-remarks { width: 130px; font-size: 9px; }

        .badge-cleared {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-weight: 800;
            font-size: 8.5px;
        }

        .badge-qualified {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            font-weight: 800;
            font-size: 8.5px;
        }

        .badge-deficient {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            background-color: #fff1f2;
            color: #9f1239;
            border: 1px solid #fecdd3;
            font-weight: 800;
            font-size: 8.5px;
        }

        /* Institutional Summary Box */
        .summary-wrapper {
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .summary-header-title {
            text-align: center;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 25px;
        }

        .summary-table th, .summary-table td {
            border: 1px solid #000000;
            padding: 6px 8px;
        }

        .summary-table th {
            font-weight: 800;
            text-transform: uppercase;
            background-color: #f8fafc;
        }

        /* Signatory Section */
        .signature-grid {
            display: flex;
            justify-content: space-between;
            margin-top: 35px;
            padding-top: 15px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 22%;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000000;
            margin-bottom: 5px;
            height: 35px;
        }

        .signature-title {
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .signature-sub {
            font-size: 8.5px;
            color: #64748b;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
                font-size: 9px;
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
                size: A4 landscape;
                margin: 8mm 10mm;
            }

            .report-table th, .report-table td {
                border-color: #000000 !important;
            }

            .summary-table th, .summary-table td {
                border-color: #000000 !important;
            }
        }
    </style>
</head>
<body>

    <div class="report-page">

        {{-- Action Bar (Screen View Only) --}}
        <div class="action-bar no-print">
            <div class="action-group">
                <a href="{{ route('exam-officer.graduation-audit') }}" class="btn btn-secondary">
                    &larr; Back to Graduation Audit
                </a>
            </div>
            <div class="action-group">
                <a href="{{ route('exam-officer.senate-graduation-broadsheet.export', ['session' => str_replace('/', '-', $session), 'department' => $department['id'] ?? null]) }}" class="btn btn-success">
                    📥 Export Broadsheet CSV
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    🖨️ Print Senate Graduation Broadsheet
                </button>
            </div>
        </div>

        {{-- Top Institutional Heading --}}
        <div class="header-title-block">
            <h1>WAZIRI UMARU FEDERAL POLYTECHNIC BIRNIN KEBBI</h1>
            <h2>IN AFFILIATION WITH FEDERAL UNIVERSITY BIRNIN KEBBI</h2>
            <h3>OFFICIAL SENATE GRADUATION BROADSHEET & DEGREE CONFERMENT LIST</h3>
        </div>

        {{-- Metadata Header --}}
        <div class="meta-container">
            <div class="meta-col-left">
                <div class="meta-row">
                    <span class="meta-label">FACULTY / DIVISION:</span>
                    <span class="meta-value">{{ strtoupper($department['faculty']) }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">DEPARTMENT:</span>
                    <span class="meta-value">{{ strtoupper($department['name']) }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">DEGREE PROGRAMME:</span>
                    <span class="meta-value">{{ strtoupper($programme) }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">GRADUATING SESSION:</span>
                    <span class="meta-value">{{ $session }}</span>
                </div>
            </div>

            <div class="meta-col-right">
                <div>DATE GENERATED: {{ now()->format('D, d-M-Y') }}</div>
                <div>TIME: {{ now()->format('H:i:s') }}</div>
                <div style="margin-top: 4px; font-weight: 800;">REGULATORY STANDARD: NUC 5-POINT SCALE</div>
            </div>
        </div>

        {{-- Graduands Table --}}
        <table class="report-table">
            <thead>
                <tr>
                    <th class="col-sn">#</th>
                    <th class="col-matric">Matric Number</th>
                    <th class="col-name">Candidate Name</th>
                    <th class="col-entry">Entry Year</th>
                    <th class="col-mode">Mode of Entry</th>
                    <th class="col-prog">Programme</th>
                    <th class="col-units">Req. Units</th>
                    <th class="col-units">Earned Units</th>
                    <th class="col-cqp">CQP</th>
                    <th class="col-cgpa">Final CGPA</th>
                    <th class="col-class">Class of Degree</th>
                    <th class="col-status">Status</th>
                    <th class="col-remarks">Senate Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($graduands as $idx => $g)
                    <tr>
                        <td class="col-sn">{{ $idx + 1 }}.</td>
                        <td class="col-matric">{{ $g['matric_no'] }}</td>
                        <td class="col-name">{{ $g['student_name'] }}</td>
                        <td class="col-entry">{{ $g['entry_session'] }}</td>
                        <td class="col-mode">{{ $g['mode_of_entry'] }}</td>
                        <td class="col-prog">{{ $g['programme_name'] }}</td>
                        <td class="col-units">{{ $g['total_units_required'] }}</td>
                        <td class="col-units">{{ $g['total_units_earned'] }}</td>
                        <td class="col-cqp">{{ $g['cqp'] }}</td>
                        <td class="col-cgpa">{{ number_format($g['final_cgpa'], 2) }}</td>
                        <td class="col-class">{{ $g['class_of_degree'] }}</td>
                        <td class="col-status">
                            @if($g['is_cleared'])
                                <span class="badge-cleared">CLEARED</span>
                            @elseif($g['meets_requirements'])
                                <span class="badge-qualified">QUALIFIED</span>
                            @else
                                <span class="badge-deficient">DEFICIENT</span>
                            @endif
                        </td>
                        <td class="col-remarks">{{ $g['remarks'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" style="text-align: center; padding: 25px; color: #64748b;">
                            No graduand records or graduation audit records found for this cohort in academic session {{ $session }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Institutional Degree Award Summary --}}
        <div class="summary-wrapper">
            <div class="summary-header-title">SENATE DEGREE CLASSIFICATION DISTRIBUTION SUMMARY</div>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>FIRST CLASS</th>
                        <th>SECOND CLASS (UPPER)</th>
                        <th>SECOND CLASS (LOWER)</th>
                        <th>THIRD CLASS</th>
                        <th>PASS DEGREE</th>
                        <th>DEFICIENT / PENDING</th>
                        <th>TOTAL CANDIDATES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $summary['first_class_count'] }}</td>
                        <td>{{ $summary['second_upper_count'] }}</td>
                        <td>{{ $summary['second_lower_count'] }}</td>
                        <td>{{ $summary['third_class_count'] }}</td>
                        <td>{{ $summary['pass_count'] }}</td>
                        <td>{{ $summary['deficient_count'] }}</td>
                        <td>{{ $summary['total_graduands'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $summary['first_class_percentage'] }}%</td>
                        <td>{{ $summary['second_upper_percentage'] }}%</td>
                        <td>{{ $summary['second_lower_percentage'] }}%</td>
                        <td>{{ $summary['third_class_percentage'] }}%</td>
                        <td>{{ $summary['pass_percentage'] }}%</td>
                        <td>{{ $summary['deficient_percentage'] }}%</td>
                        <td>100%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Official Senate Signatory Approval Panel --}}
        <div class="signature-grid">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Head of Department</div>
                <div class="signature-sub">Date & Stamp</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Dean of Faculty</div>
                <div class="signature-sub">Date & Stamp</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Examination Officer</div>
                <div class="signature-sub">Academic Affairs Directorate</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Vice-Chancellor / Senate Chair</div>
                <div class="signature-sub">Approval of Degree Conferment</div>
            </div>
        </div>

    </div>

</body>
</html>
