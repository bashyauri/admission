<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senate Official Pass List - {{ $department['name'] }} ({{ $session }})</title>
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
            line-height: 1.4;
            padding: 20px;
        }

        .report-page {
            max-width: 1100px;
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

        /* Class of Degree Section */
        .class-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .class-header {
            background-color: #f8fafc;
            border: 2px solid #0f172a;
            padding: 12px 16px;
            margin-bottom: 12px;
            text-align: center;
        }

        .class-header h4 {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            letter-spacing: 0.5px;
        }

        .class-header .count-badge {
            display: inline-block;
            background-color: #0f172a;
            color: #ffffff;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 8px;
        }

        /* Pass List Table */
        .pass-list-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            border-top: 1.5px solid #000000;
            border-bottom: 1.5px solid #000000;
            margin-bottom: 20px;
        }

        .pass-list-table th {
            font-weight: 800;
            text-transform: uppercase;
            padding: 8px 10px;
            border-bottom: 1.5px solid #000000;
            background-color: #f8fafc;
            color: #000000;
            font-size: 9.5px;
        }

        .pass-list-table td {
            padding: 8px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }

        .pass-list-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .col-sn { width: 35px; text-align: center; font-weight: 700; }
        .col-matric { width: 110px; font-weight: 800; font-family: monospace; }
        .col-name { width: 200px; font-weight: 700; text-transform: uppercase; }
        .col-programme { width: 180px; text-transform: uppercase; }
        .col-cgpa { width: 60px; text-align: center; font-weight: 800; }
        .col-entry { width: 90px; text-align: center; }
        .col-dept { width: 150px; }

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
            font-size: 10px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 25px;
        }

        .summary-table th, .summary-table td {
            border: 1px solid #000000;
            padding: 8px 12px;
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
                font-size: 10px;
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
                margin: 10mm;
            }

            .pass-list-table th, .pass-list-table td {
                border-color: #000000 !important;
            }

            .summary-table th, .summary-table td {
                border-color: #000000 !important;
            }

            .class-section {
                page-break-inside: avoid;
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
                <a href="{{ route('exam-officer.senate-pass-list.export', ['session' => str_replace('/', '-', $session), 'department' => $department['id'] ?? null]) }}" class="btn btn-success">
                    📥 Export NYSC Mobilization CSV
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    🖨️ Print Senate Pass List
                </button>
            </div>
        </div>

        {{-- Top Institutional Heading --}}
        <div class="header-title-block">
            <h1>WAZIRI UMARU FEDERAL POLYTECHNIC BIRNIN KEBBI</h1>
            <h2>IN AFFILIATION WITH FEDERAL UNIVERSITY BIRNIN KEBBI</h2>
            <h3>OFFICIAL SENATE PASS LIST (GRADUATING CLASS)</h3>
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

        {{-- Pass List Grouped by Class of Degree --}}
        @forelse($grouped_by_class as $className => $graduands)
            @if(!empty($graduands))
                <div class="class-section">
                    <div class="class-header">
                        <h4>{{ $className }} <span class="count-badge">{{ count($graduands) }} CANDIDATES</span></h4>
                    </div>

                    <table class="pass-list-table">
                        <thead>
                            <tr>
                                <th class="col-sn">#</th>
                                <th class="col-matric">Matric Number</th>
                                <th class="col-name">Candidate Name</th>
                                <th class="col-programme">Programme</th>
                                <th class="col-dept">Department</th>
                                <th class="col-entry">Mode of Entry</th>
                                <th class="col-cgpa">Final CGPA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($graduands as $idx => $g)
                                <tr>
                                    <td class="col-sn">{{ $idx + 1 }}.</td>
                                    <td class="col-matric">{{ $g['matric_no'] }}</td>
                                    <td class="col-name">{{ $g['student_name'] }}</td>
                                    <td class="col-programme">{{ $g['programme_name'] }}</td>
                                    <td class="col-dept">{{ $g['department_name'] }}</td>
                                    <td class="col-entry">{{ $g['mode_of_entry'] }}</td>
                                    <td class="col-cgpa">{{ number_format((float) $g['final_cgpa'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @empty
            <div style="text-align: center; padding: 40px; color: #64748b; font-weight: 700;">
                No graduating candidates found for Senate Pass List in academic session {{ $session }}.
                <div style="margin-top: 10px; font-weight: normal; font-size: 10px;">
                    Ensure graduation eligibility audit has been run and candidates have been cleared and staged into the graduation list.
                </div>
            </div>
        @endforelse

        {{-- Institutional Degree Award Summary --}}
        <div class="summary-wrapper">
            <div class="summary-header-title">SENATE PASS LIST SUMMARY STATISTICS</div>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>FIRST CLASS</th>
                        <th>SECOND CLASS (UPPER)</th>
                        <th>SECOND CLASS (LOWER)</th>
                        <th>THIRD CLASS</th>
                        <th>PASS DEGREE</th>
                        <th>TOTAL GRADUANDS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $summary['first_class_count'] }}</td>
                        <td>{{ $summary['second_upper_count'] }}</td>
                        <td>{{ $summary['second_lower_count'] }}</td>
                        <td>{{ $summary['third_class_count'] }}</td>
                        <td>{{ $summary['pass_count'] }}</td>
                        <td>{{ $summary['total_graduands'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $summary['first_class_percentage'] }}%</td>
                        <td>{{ $summary['second_upper_percentage'] }}%</td>
                        <td>{{ $summary['second_lower_percentage'] }}%</td>
                        <td>{{ $summary['third_class_percentage'] }}%</td>
                        <td>{{ $summary['pass_percentage'] }}%</td>
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