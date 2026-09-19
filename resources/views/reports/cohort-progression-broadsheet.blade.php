<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cohort Progression Master Broadsheet - {{ $department['name'] }} (Cohort: {{ $admission_session }})</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 10.5px;
            line-height: 1.35;
            padding: 15px;
        }

        .report-container {
            max-width: 1450px;
            margin: 0 auto;
            background: #ffffff;
            padding: 20px 25px;
            border-radius: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* Action bar for screen */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #cbd5e1;
        }

        .filter-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #ffffff;
            color: #1e293b;
            outline: none;
        }

        .filter-select:focus {
            border-color: #3b82f6;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .btn-primary { background-color: #0f172a; color: #ffffff; }
        .btn-primary:hover { background-color: #1e293b; }
        .btn-success { background-color: #059669; color: #ffffff; }
        .btn-success:hover { background-color: #047857; }
        .btn-secondary { background-color: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background-color: #cbd5e1; }
        .btn-filter { background-color: #2563eb; color: #ffffff; }
        .btn-filter:hover { background-color: #1d4ed8; }

        /* Report Header */
        .header-title-block {
            text-align: center;
            margin-bottom: 14px;
        }

        .header-title-block h1 {
            font-size: 13.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .header-title-block h2 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #334155;
            margin-bottom: 5px;
        }

        .header-title-block h3 {
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #f8fafc;
            padding: 4px 0;
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
        }

        /* Metadata Block */
        .meta-container {
            display: flex;
            justify-content: space-between;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 12px;
            line-height: 1.55;
        }

        .meta-col {
            width: 48%;
        }

        .meta-row {
            display: flex;
        }

        .meta-label {
            width: 150px;
            color: #000000;
        }

        .meta-value {
            color: #000000;
        }

        /* Summary Statistics Box */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin-bottom: 14px;
            text-align: center;
        }

        .stat-box {
            border: 1px solid #cbd5e1;
            padding: 5px 4px;
            border-radius: 4px;
            background: #f8fafc;
        }

        .stat-num {
            font-size: 12.5px;
            font-weight: 800;
            color: #0f172a;
        }

        .stat-label {
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
        }

        /* Data Table */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
            margin-bottom: 16px;
        }

        .report-table th {
            font-weight: 800;
            text-transform: uppercase;
            padding: 5px 6px;
            border-bottom: 1px solid #000000;
            background-color: #f1f5f9;
            font-size: 9px;
            vertical-align: middle;
        }

        .report-table td {
            padding: 6px 5px;
            vertical-align: top;
            border-bottom: 1px solid #cbd5e1;
        }

        .report-table tr:nth-child(even) {
            background-color: #fafafa;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .badge-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-info { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }

        /* Course Tags / Chips */
        .course-tag {
            display: inline-block;
            padding: 1.5px 4px;
            border-radius: 3px;
            font-size: 8.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .course-tag-pass { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .course-tag-fail { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-weight: 800; }
        .course-tag-repeat { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 700; }

        /* Session Progression Box */
        .session-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4px 6px;
            margin-bottom: 5px;
        }

        .session-header {
            display: flex;
            justify-content: space-between;
            font-size: 8.5px;
            font-weight: 800;
            background: #f8fafc;
            padding: 2px 4px;
            border-radius: 2px;
            margin-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Carry-Over Mini Table */
        .co-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-top: 2px;
        }

        .co-table th {
            padding: 2px 4px;
            font-size: 8px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
        }

        .co-table td {
            padding: 2px 4px;
            border: 1px solid #e2e8f0;
            font-size: 8.5px;
        }

        /* Statutory Signatures */
        .signature-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 25px;
            text-align: center;
            page-break-inside: avoid;
        }

        .signature-line {
            width: 80%;
            margin: 0 auto 4px auto;
            border-top: 1px solid #000000;
        }

        .signature-caption {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
                font-size: 9px;
            }

            .report-container {
                box-shadow: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: A3 landscape;
                margin: 8mm;
            }

            .report-table th, .report-table td {
                padding: 3px;
            }
        }
    </style>
</head>
<body>

<div class="report-container">
    {{-- Screen Action Bar --}}
    <div class="action-bar no-print">
        <div style="font-weight: 700; font-size: 12px; color: #1e293b;">
            COHORT PROGRESSION MASTER BROADSHEET &amp; CARRY-OVER AUDIT (ALL SESSIONS)
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('exam-officer.cohort-progression-broadsheet.export', [
                'department' => $department['id'], 
                'admissionSession' => str_replace('/', '-', $admission_session),
                'level' => $selectedLevelId ?? null
            ]) }}" class="btn btn-success">
                📥 Export CSV
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Print Master Broadsheet
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                ✕ Close
            </button>
        </div>
    </div>

    {{-- Interactive On-Page Filter Toolbar --}}
    <div class="filter-bar no-print">
        <form method="GET" id="filterForm" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; width: 100%;">
            <label style="font-size: 10px; font-weight: 700; color: #475569;">DEPARTMENT:</label>
            <select name="department" id="filterDepartment" class="filter-select">
                @foreach($allDepartments as $dept)
                    <option value="{{ $dept->id }}" {{ $dept->id == $department['id'] ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>

            <label style="font-size: 10px; font-weight: 700; color: #475569; margin-left: 8px;">LEVEL:</label>
            <select name="level" id="filterLevel" class="filter-select">
                <option value="">All Levels</option>
                @foreach($allLevels as $lvl)
                    <option value="{{ $lvl->id }}" {{ ($selectedLevelId ?? null) == $lvl->id ? 'selected' : '' }}>
                        {{ $lvl->level }} Level
                    </option>
                @endforeach
            </select>

            <label style="font-size: 10px; font-weight: 700; color: #475569; margin-left: 8px;">COHORT / SESSION:</label>
            <select name="cohort" id="filterCohort" class="filter-select">
                <option value="all" {{ strtolower($admission_session) === 'all' ? 'selected' : '' }}>
                    All Cohorts (Complete Department History)
                </option>
                @foreach($allCohorts as $cSession)
                    <option value="{{ $cSession }}" {{ $cSession == $admission_session ? 'selected' : '' }}>
                        {{ $cSession }} Cohort
                    </option>
                @endforeach
            </select>

            <button type="button" onclick="applyBroadsheetFilters()" class="btn btn-filter" style="margin-left: 6px;">
                🔍 Load Broadsheet
            </button>
        </form>
    </div>

    <script>
        function applyBroadsheetFilters() {
            var dept = document.getElementById('filterDepartment').value;
            var cohort = document.getElementById('filterCohort').value.replace('/', '-');
            var level = document.getElementById('filterLevel').value;

            var url = '/exam-officer/cohort-progression-broadsheet/' + dept + '/' + cohort;
            if (level) {
                url += '?level=' + level;
            }
            window.location.href = url;
        }
    </script>

    {{-- Report Header --}}
    <div class="header-title-block">
        <h1>WAZIRI UMARU FEDERAL POLYTECHNIC BIRNIN KEBBI</h1>
        <h2>IN AFFILIATION WITH FEDERAL UNIVERSITY BIRNIN KEBBI</h2>
        <h3>COHORT PROGRESSION MASTER BROADSHEET &amp; CARRY-OVER AUDIT LEDGER</h3>
    </div>

    {{-- Metadata Block --}}
    <div class="meta-container">
        <div class="meta-col">
            <div class="meta-row">
                <span class="meta-label">FACULTY / AFFILIATION:</span>
                <span class="meta-value">{{ $department['faculty'] ?? 'SCIENCE & TECHNOLOGY' }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">DEPARTMENT:</span>
                <span class="meta-value">{{ $department['name'] }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">PROGRAMME:</span>
                <span class="meta-value">{{ $programme }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">LEVEL FILTER:</span>
                <span class="meta-value">{{ $level ?? 'All Levels' }}</span>
            </div>
        </div>
        <div class="meta-col" style="text-align: right;">
            <div class="meta-row" style="justify-content: flex-end;">
                <span class="meta-label" style="text-align: right; margin-right: 12px;">ADMISSION COHORT:</span>
                <span class="meta-value">{{ strtolower($admission_session) === 'all' ? 'ALL ADMISSION COHORTS' : $admission_session . ' SET' }}</span>
            </div>
            <div class="meta-row" style="justify-content: flex-end;">
                <span class="meta-label" style="text-align: right; margin-right: 12px;">REPORT REFERENCE:</span>
                <span class="meta-value">PRINT-CPB/SENATE</span>
            </div>
            <div class="meta-row" style="justify-content: flex-end;">
                <span class="meta-label" style="text-align: right; margin-right: 12px;">DATE GENERATED:</span>
                <span class="meta-value">{{ now()->format('D, d-M-Y H:i:s') }}</span>
            </div>
        </div>
    </div>

    {{-- Summary Statistics Bar --}}
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-num">{{ $statistics['total_students'] }}</div>
            <div class="stat-label">Total Cohort</div>
        </div>
        <div class="stat-box" style="border-color: #bbf7d0; background: #f0fdf4;">
            <div class="stat-num" style="color: #166534;">{{ $statistics['clean_record_count'] }} ({{ $statistics['clean_record_percentage'] }}%)</div>
            <div class="stat-label">Clean (0 Carry-Overs)</div>
        </div>
        <div class="stat-box" style="border-color: #c7d2fe; background: #eef2ff;">
            <div class="stat-num" style="color: #3730a3;">{{ $statistics['resolved_carryover_count'] }} ({{ $statistics['resolved_carryover_percentage'] }}%)</div>
            <div class="stat-label">All Retakes Cleared</div>
        </div>
        <div class="stat-box" style="border-color: #fecaca; background: #fef2f2;">
            <div class="stat-num" style="color: #991b1b;">{{ $statistics['deficient_count'] }} ({{ $statistics['deficient_percentage'] }}%)</div>
            <div class="stat-label">Outstanding Deficiencies</div>
        </div>
        <div class="stat-box">
            <div class="stat-num" style="color: #059669;">{{ $statistics['total_carryovers_cleared'] }}</div>
            <div class="stat-label">Carry-Overs Cleared</div>
        </div>
        <div class="stat-box">
            <div class="stat-num" style="color: #dc2626;">{{ $statistics['total_carryovers_outstanding'] }}</div>
            <div class="stat-label">Carry-Overs Outstanding</div>
        </div>
    </div>

    {{-- Main Cohort Master Table --}}
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">#</th>
                <th style="width: 95px;">Matric No</th>
                <th style="width: 140px;">Candidate Full Name</th>
                <th style="width: 75px; text-align: center;">Entry Mode</th>
                <th style="width: 440px;">Session History &amp; Courses Taken (Code: Units - Score [Grade])</th>
                <th style="width: 100px; text-align: center;">Cumulative Standing</th>
                <th>Carry-Over Resolution Ledger (Incurred vs Cleared)</th>
                <th style="width: 110px;">Senate Standing</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $idx => $student)
                <tr>
                    <td style="text-align: center; font-weight: 700;">{{ $idx + 1 }}.</td>
                    <td style="font-weight: 700; white-space: nowrap;">
                        {{ $student['matric_no'] }}
                        <div style="font-size: 8px; color: #64748b; font-weight: normal;">Cohort: {{ $student['entry_session'] ?: 'N/A' }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 700; text-transform: uppercase;">{{ $student['student_name'] }}</div>
                        <div style="color: #64748b; font-size: 8.5px;">Level: {{ $student['current_level'] }}</div>
                    </td>
                    <td style="text-align: center; font-size: 8.5px; font-weight: 600;">
                        {{ $student['entry_mode'] }}
                    </td>
                    <td>
                        @if(empty($student['sessions']))
                            <div style="color: #94a3b8; font-style: italic; padding: 6px 0;">No examination results recorded</div>
                        @else
                            @foreach($student['sessions'] as $sess)
                                <div class="session-card">
                                    <div class="session-header">
                                        <span>📅 SESSION {{ $sess['session'] }}</span>
                                        <span>
                                            TCR: {{ $sess['tcr'] }} | TCP: {{ $sess['tcp'] }} | 
                                            GPA: <strong>{{ number_format((float) $sess['session_gpa'], 2) }}</strong> | 
                                            CGPA: <strong>{{ number_format((float) $sess['running_cgpa'], 2) }}</strong>
                                        </span>
                                    </div>
                                    @foreach($sess['semesters'] as $sem)
                                        <div style="margin-top: 3px;">
                                            <div style="font-size: 8px; font-weight: 800; color: #475569; margin-bottom: 2px;">
                                                {{ $sem['semester'] }} Semester (GPA: {{ number_format((float) $sem['gpa'], 2) }}):
                                            </div>
                                            <div style="display: flex; flex-wrap: wrap; gap: 3.5px;">
                                                @foreach($sem['courses'] as $c)
                                                    <span class="course-tag {{ $c['grade'] === 'F' ? 'course-tag-fail' : ($c['is_repeated'] ? 'course-tag-repeat' : 'course-tag-pass') }}"
                                                          title="{{ $c['title'] }} ({{ $c['units'] }} Units) - Total: {{ $c['score'] !== null ? number_format($c['score'], 0) : 'N/A' }}">
                                                        @if($c['is_repeated']) [R] @endif
                                                        <strong>{{ $c['code'] }}</strong>: {{ $c['units'] }}U - {{ $c['score'] !== null ? number_format($c['score'], 0) : '-' }} [{{ $c['grade'] }}]
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div style="font-weight: 800; font-size: 11px; color: #0f172a;">
                            {{ number_format((float) $student['final_cgpa'], 2) }}
                        </div>
                        <div style="font-size: 8.5px; font-weight: 700; color: #334155;">
                            {{ $student['class_of_degree'] }}
                        </div>
                        <div style="font-size: 8px; color: #64748b; margin-top: 3px;">
                            CCR: {{ $student['total_ccr'] }} | CCP: {{ $student['total_ccp'] }}
                        </div>
                    </td>
                    <td>
                        @if(empty($student['carry_overs']))
                            <span class="badge badge-success">✓ CLEAN RECORD (0 CARRY-OVERS)</span>
                        @else
                            <table class="co-table">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Incurred (Failed)</th>
                                        <th>Resolution / Retake</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($student['carry_overs'] as $co)
                                        <tr>
                                            <td style="font-weight: 700; white-space: nowrap;">
                                                {{ $co['course_code'] }} ({{ $co['units'] }}U)
                                            </td>
                                            <td>
                                                {{ $co['failed_session'] }} ({{ $co['failed_semester'] }}):
                                                <strong>{{ $co['failed_score'] !== null ? number_format($co['failed_score'], 0) : '0' }} ({{ $co['failed_grade'] }})</strong>
                                            </td>
                                            <td>
                                                @if($co['is_cleared'])
                                                    <span style="color: #166534; font-weight: 700;">
                                                        Retaken {{ $co['retake_session'] ?? 'Retake' }}: 
                                                        {{ $co['cleared_score'] !== null ? number_format($co['cleared_score'], 0) : '-' }} ({{ $co['cleared_grade'] ?? 'Pass' }})
                                                    </span>
                                                    @if($co['cleared_at'])
                                                        <div style="color: #64748b; font-size: 7.5px;">Cleared: {{ $co['cleared_at'] }}</div>
                                                    @endif
                                                @else
                                                    <span style="color: #dc2626; font-style: italic;">Not yet retaken / cleared</span>
                                                @endif
                                            </td>
                                            <td style="text-align: center;">
                                                @if($co['is_cleared'])
                                                    <span class="badge badge-success">CLEARED</span>
                                                @else
                                                    <span class="badge badge-danger">OUTSTANDING</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </td>
                    <td>
                        @if(!$student['has_outstanding_carryovers'])
                            <div style="font-weight: 800; color: #166534; font-size: 8.5px;">
                                {{ $student['standing_remark'] }}
                            </div>
                        @else
                            <div style="font-weight: 800; color: #dc2626; font-size: 8px;">
                                {{ $student['standing_remark'] }}
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px; color: #64748b;">
                        No student progression records found for cohort {{ $admission_session }} in this department.
                        <div style="margin-top: 6px;">
                            <a href="javascript:void(0)" onclick="document.getElementById('filterCohort').value='all'; applyBroadsheetFilters();" style="color: #2563eb; font-weight: 700; text-decoration: underline;">
                                Click here to view All Cohorts
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Statutory Signatures Footer --}}
    <div class="signature-grid">
        <div>
            <div class="signature-line"></div>
            <div class="signature-caption">Level Coordinator</div>
            <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Name, Signature &amp; Date</div>
        </div>
        <div>
            <div class="signature-line"></div>
            <div class="signature-caption">Head of Department (HOD)</div>
            <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Name, Signature &amp; Date</div>
        </div>
        <div>
            <div class="signature-line"></div>
            <div class="signature-caption">External Examiner / Moderator</div>
            <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Name, Signature &amp; Date</div>
        </div>
        <div>
            <div class="signature-line"></div>
            <div class="signature-caption">Senate Representative / Exam Officer</div>
            <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Name, Signature &amp; Date</div>
        </div>
    </div>
</div>

</body>
</html>
