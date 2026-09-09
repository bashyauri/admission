<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Transcript Verification - Federal University Birnin Kebbi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 15px;
        }
        .container {
            width: 100%;
            max-width: 720px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .logo-wrap {
            margin-bottom: 12px;
        }
        .logo-wrap img {
            width: 65px;
            height: auto;
            border-radius: 8px;
            background: #fff;
            padding: 4px;
        }
        .header h1 {
            font-size: 1.25rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            font-size: 0.85rem;
            color: #a7f3d0;
            margin-top: 4px;
        }
        .verified-banner {
            background: #ecfdf5;
            border-bottom: 2px solid #10b981;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .verified-icon {
            width: 36px;
            height: 36px;
            background: #10b981;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .verified-text h2 {
            font-size: 1rem;
            color: #065f46;
            font-weight: 700;
        }
        .verified-text p {
            font-size: 0.8rem;
            color: #047857;
            margin-top: 2px;
        }
        .body-content {
            padding: 24px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }
        @media (max-width: 580px) {
            .grid { grid-template-columns: 1fr; }
        }
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
        }
        .label {
            font-size: 0.72rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #0f172a;
            margin-top: 4px;
            word-break: break-word;
        }
        .highlight-value {
            color: #065f46;
            font-weight: 800;
            font-size: 1.1rem;
        }
        .audit-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 16px 24px;
            font-size: 0.75rem;
            color: #64748b;
            text-align: center;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="logo-wrap">
            <img src="{{ asset('assets/img/fubk-icon.jpg') }}" alt="FUBK Logo">
        </div>
        <h1>Federal University Birnin Kebbi</h1>
        <p>Academic Affairs Directorate &bull; Official Credential &amp; Transcript Verification</p>
    </div>

    <div class="verified-banner">
        <div class="verified-icon">&check;</div>
        <div class="verified-text">
            <h2>Authentic Academic Transcript Verified</h2>
            <p>This academic record is authenticated against the institutional result database.</p>
        </div>
    </div>

    <div class="body-content">
        <div class="grid">
            <div class="info-card">
                <div class="label">Student Full Name</div>
                <div class="value">{{ strtoupper($student->surname) }}, {{ $student->firstname }} {{ $student->othername }}</div>
            </div>

            <div class="info-card">
                <div class="label">Matriculation Number</div>
                <div class="value font-mono">{{ $academicDetail->matric_no ?? 'N/A' }}</div>
            </div>

            <div class="info-card">
                <div class="label">Programme / Degree</div>
                <div class="value">{{ $academicDetail->course->name ?? 'Undergraduate Degree' }}</div>
            </div>

            <div class="info-card">
                <div class="label">Department</div>
                <div class="value">{{ $academicDetail->department->name ?? 'N/A' }}</div>
            </div>

            <div class="info-card">
                <div class="label">Faculty / School</div>
                <div class="value">{{ $academicDetail->department->name ?? 'N/A' }}</div>
            </div>

            <div class="info-card">
                <div class="label">Admission Session</div>
                <div class="value">{{ $academicDetail->admission_session ?? $academicDetail->acad_session ?? 'N/A' }}</div>
            </div>

            <div class="info-card">
                <div class="label">Total Earned Units</div>
                <div class="value highlight-value">{{ $summary['total_credit_units'] ?? 0 }} Units</div>
            </div>

            <div class="info-card">
                <div class="label">Cumulative GPA (CGPA)</div>
                <div class="value highlight-value">{{ number_format($summary['cgpa'] ?? 0, 2) }} / 5.00</div>
            </div>

            <div class="info-card" style="grid-column: 1 / -1;">
                <div class="label">NUC Class of Degree</div>
                <div class="value highlight-value" style="font-size: 1.15rem; color: #047857;">
                    {{ $summary['class_of_degree'] ?? 'Undergraduate In Progress' }}
                </div>
            </div>

            <div class="info-card">
                <div class="label">Verification Code</div>
                <div class="value font-mono" style="font-size: 0.85rem; color: #475569;">{{ $transcript->verification_code }}</div>
            </div>

            <div class="info-card">
                <div class="label">Transcript Request Number</div>
                <div class="value font-mono" style="font-size: 0.85rem; color: #475569;">{{ $transcript->request_number }}</div>
            </div>
        </div>
    </div>

    <div class="audit-footer">
        Verification completed on <strong>{{ $verified_at->format('d M Y, H:i:s') }} UTC</strong>.<br>
        Direct all official inquiries regarding student academic transcripts to the Office of the Registrar, Federal University Birnin Kebbi.
    </div>
</div>

</body>
</html>
