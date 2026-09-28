<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report_title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; color: #344767; background: #f8f9fa; font: 14px Arial, sans-serif; }
        .page { max-width: 1500px; margin: auto; }
        .card { background: #fff; border: 1px solid #e9ecef; border-radius: 14px; box-shadow: 0 4px 12px #34476712; padding: 22px; margin-bottom: 18px; }
        h1 { margin: 0 0 6px; font-size: 25px; color: #344767; }
        h2 { margin: 0 0 14px; font-size: 16px; }
        .muted { color: #67748e; }
        .toolbar, .filters, .stats, .summary-grid { display: flex; flex-wrap: wrap; gap: 12px; align-items: end; }
        .toolbar { justify-content: space-between; align-items: center; }
        .filters label { display: flex; flex-direction: column; gap: 5px; min-width: 145px; font-size: 12px; font-weight: 600; }
        input, select { min-height: 38px; padding: 8px 10px; border: 1px solid #d2d6da; border-radius: 7px; color: #344767; background: white; }
        .button { display: inline-block; border: 0; border-radius: 7px; padding: 10px 14px; color: #fff; background: #cb0c9f; font-weight: 700; text-decoration: none; cursor: pointer; }
        .button.secondary { background: #8392ab; }
        .stat { flex: 1 1 135px; padding: 14px; border-radius: 10px; background: #f8f9fa; }
        .stat strong { display: block; margin-top: 6px; font-size: 23px; }
        .tables { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td { padding: 10px 9px; border-bottom: 1px solid #e9ecef; text-align: left; }
        th { color: #67748e; font-size: 11px; text-transform: uppercase; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 20px; background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 700; }
        .empty { padding: 28px; text-align: center; color: #67748e; }
        .pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; }
        .pagination a { color: #cb0c9f; font-weight: 700; text-decoration: none; }
        @media (max-width: 700px) { body { padding: 10px; } .card { padding: 14px; } .toolbar { align-items: flex-start; flex-direction: column; } }
        @media print { body { padding: 0; background: white; } .no-print { display: none !important; } .card { box-shadow: none; border: 0; padding: 8px 0; } }
    </style>
</head>
<body>
<main class="page">
    <section class="card toolbar">
        <div>
            <h1>{{ $report_title }}</h1>
            <div class="muted">Undergraduate Senate withdrawal and reinstatement records · Generated {{ now()->format('d M Y, H:i') }}</div>
        </div>
        <nav class="no-print" aria-label="Report actions">
            <a class="button secondary" href="{{ route('exam-officer.withdrawal-ledger.export.csv', request()->query()) }}">Export CSV</a>
            <a class="button secondary" href="{{ route('exam-officer.withdrawal-ledger.export.pdf', request()->query()) }}">Download PDF</a>
            <button class="button" type="button" onclick="window.print()">Print</button>
        </nav>
    </section>

    <section class="card no-print">
        <form method="GET" action="{{ route('exam-officer.withdrawal-ledger') }}" class="filters">
            <label>Report
                <select name="report">
                    @foreach(['ledger' => 'Withdrawal ledger', 'senate' => 'Senate compliance', 'departmental' => 'Department summary', 'reinstatement' => 'Reinstatement tracking'] as $value => $label)
                        <option value="{{ $value }}" @selected($report === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Academic session
                <select name="academic_session">
                    <option value="">All sessions</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session }}" @selected(($filters['academic_session'] ?? '') === $session)>{{ $session }}</option>
                    @endforeach
                </select>
            </label>
            <label>Department
                <select name="department_id">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) ($filters['department_id'] ?? '') === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Programme
                <select name="programme_id">
                    <option value="">All programmes</option>
                    @foreach($programmes as $programme)
                        <option value="{{ $programme->id }}" @selected((string) ($filters['programme_id'] ?? '') === (string) $programme->id)>{{ $programme->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Withdrawal type
                <select name="withdrawal_type">
                    <option value="">All types</option>
                    @foreach($withdrawal_types as $type)
                        <option value="{{ $type->value }}" @selected(($filters['withdrawal_type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </label>
            <label>Senate status
                <select name="status">
                    <option value="">All decisions</option>
                    @foreach($senate_decisions as $decision)
                        <option value="{{ $decision }}" @selected(($filters['status'] ?? '') === $decision)>{{ str_replace('_', ' ', $decision) }}</option>
                    @endforeach
                </select>
            </label>
            <label>Effective from <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></label>
            <label>Effective to <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></label>
            <label>Reinstatement eligible
                <select name="reinstatement_eligible">
                    <option value="">Any</option>
                    <option value="1" @selected(($filters['reinstatement_eligible'] ?? '') === '1')>Yes</option>
                    <option value="0" @selected(($filters['reinstatement_eligible'] ?? '') === '0')>No</option>
                </select>
            </label>
            <label>Senate reference <input type="search" name="senate_reference" maxlength="100" value="{{ $filters['senate_reference'] ?? '' }}" placeholder="Search reference"></label>
            <button class="button" type="submit">Apply filters</button>
            <a class="button secondary" href="{{ route('exam-officer.withdrawal-ledger', ['report' => $report]) }}">Reset</a>
        </form>
    </section>

    <section class="card">
        <h2>Summary</h2>
        <div class="stats">
            @foreach($summary as $label => $value)
                <div class="stat"><span class="muted">{{ str($label)->replace('_', ' ')->title() }}</span><strong>{{ $value }}</strong></div>
            @endforeach
            @if(isset($missing_senate_reference_count))
                <div class="stat"><span class="muted">Approved without reference</span><strong>{{ $missing_senate_reference_count }}</strong></div>
            @endif
        </div>
    </section>

    @if($report === 'departmental')
        <section class="card table-wrap">
            <h2>Department breakdown</h2>
            @if($department_summary->isEmpty())
                <div class="empty">No withdrawal records match these filters. Adjust the session or department filters.</div>
            @else
                <table><thead><tr><th>Department</th><th>Total</th><th>Academic</th><th>Voluntary</th><th>Medical</th><th>Pending Senate</th><th>Reinstated</th></tr></thead>
                    <tbody>@foreach($department_summary as $row)<tr><td>{{ $row['department'] }}</td><td>{{ $row['total'] }}</td><td>{{ $row['academic'] }}</td><td>{{ $row['voluntary'] }}</td><td>{{ $row['medical'] }}</td><td>{{ $row['senate_pending'] }}</td><td>{{ $row['reinstated'] }}</td></tr>@endforeach</tbody>
                </table>
            @endif
        </section>
    @endif

    <section class="tables">
        <section class="card table-wrap"><h2>By academic session</h2>
            @forelse($by_session as $session => $count)<div>{{ $session }} <span class="badge">{{ $count }}</span></div>@empty<div class="muted">No records</div>@endforelse
        </section>
        <section class="card table-wrap"><h2>By withdrawal type</h2>
            @forelse($by_type as $type => $count)<div>{{ $type }} <span class="badge">{{ $count }}</span></div>@empty<div class="muted">No records</div>@endforelse
        </section>
        <section class="card table-wrap"><h2>By department</h2>
            @forelse($by_department as $department => $count)<div>{{ $department }} <span class="badge">{{ $count }}</span></div>@empty<div class="muted">No records</div>@endforelse
        </section>
    </section>

    <section class="card table-wrap">
        <h2>Withdrawal records</h2>
        @if($rows->isEmpty())
            <div class="empty">No withdrawal records match these filters. Try a different session, type, or date range.</div>
        @else
            <table>
                <thead><tr><th>Matric No</th><th>Student</th><th>Programme</th><th>Department</th><th>Type</th><th>Session</th><th>Effective date</th><th>Senate ref</th><th>Decision</th><th>Reinstatement</th></tr></thead>
                <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row['matric_no'] }}</td><td>{{ $row['student_name'] ?: '—' }}</td><td>{{ $row['programme'] }}</td><td>{{ $row['department'] }}</td>
                        <td>{{ $row['withdrawal_type'] }}</td><td>{{ $row['academic_session'] }}{{ $row['semester'] ? ' · Semester ' . $row['semester'] : '' }}</td>
                        <td>{{ $row['effective_date'] ?? '—' }}</td><td>{{ $row['senate_reference'] ?? '—' }}</td><td>{{ str_replace('_', ' ', $row['senate_decision'] ?: '—') }}</td>
                        <td>{{ $row['reinstatement_status'] }} @if($row['reinstatement_session'])<small>· {{ $row['reinstatement_session'] }}</small>@endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @if($rows->hasPages())
                <nav class="pagination no-print" aria-label="Withdrawal report pages">
                    @if($rows->previousPageUrl())<a href="{{ $rows->previousPageUrl() }}">← Previous</a>@else<span class="muted">← Previous</span>@endif
                    <span class="muted">Page {{ $rows->currentPage() }} of {{ $rows->lastPage() }} · {{ $rows->total() }} records</span>
                    @if($rows->nextPageUrl())<a href="{{ $rows->nextPageUrl() }}">Next →</a>@else<span class="muted">Next →</span>@endif
                </nav>
            @endif
        @endif
    </section>
</main>
</body>
</html>
