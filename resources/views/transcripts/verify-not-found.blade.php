<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transcript Verification Failed - Federal University Birnin Kebbi</title>
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
            max-width: 580px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .header {
            background: #991b1b;
            color: #ffffff;
            padding: 24px;
        }
        .warning-icon {
            width: 48px;
            height: 48px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.5rem;
            margin-bottom: 12px;
        }
        .body-content {
            padding: 28px 24px;
        }
        .body-content h2 {
            font-size: 1.15rem;
            color: #991b1b;
            margin-bottom: 8px;
        }
        .body-content p {
            font-size: 0.9rem;
            color: #475569;
            line-height: 1.5;
            margin-bottom: 16px;
        }
        .code-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 10px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 0.95rem;
            color: #334155;
            display: inline-block;
        }
        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 16px;
            font-size: 0.75rem;
            color: #64748b;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="warning-icon">&times;</div>
        <h1 style="font-size: 1.2rem; font-weight: 800; text-transform: uppercase;">Transcript Verification Unsuccessful</h1>
    </div>

    <div class="body-content">
        <h2>Unrecognized Verification Code</h2>
        <p>
            No active official transcript could be verified in the institutional database matching the submitted code:
        </p>
        <div class="code-box">{{ $code }}</div>
        <p style="margin-top: 16px; font-size: 0.85rem;">
            Please ensure you scanned the exact official QR code or entered the full reference code correctly. If you believe this is an error, contact the Office of the Registrar, Federal University Birnin Kebbi.
        </p>
    </div>

    <div class="footer">
        Federal University Birnin Kebbi &bull; Academic Affairs Directorate
    </div>
</div>

</body>
</html>
