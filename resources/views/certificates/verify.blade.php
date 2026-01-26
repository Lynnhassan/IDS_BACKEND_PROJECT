<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        .status-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }

        .status-icon.valid {
            background-color: #10b981;
            color: white;
        }

        .status-icon.invalid {
            background-color: #ef4444;
            color: white;
        }

        h1 {
            color: #1f2937;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .status-message {
            color: #6b7280;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .certificate-info {
            background: #f9fafb;
            border-radius: 12px;
            padding: 30px;
            margin: 20px 0;
            text-align: left;
        }

        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #374151;
            min-width: 140px;
            flex-shrink: 0;
        }

        .info-value {
            color: #6b7280;
            flex: 1;
        }

        .verification-code {
            background: #667eea;
            color: white;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            letter-spacing: 2px;
            margin-top: 20px;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
        }

        .badge.valid {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge.invalid {
            background-color: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 640px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .info-row {
                flex-direction: column;
            }

            .info-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
@if($valid)
    <!-- Valid Certificate -->
    <div class="container">
        <div class="status-icon valid">
            ✓
        </div>
        <h1>Certificate Verified</h1>
        <p class="status-message">This certificate is authentic and valid</p>

        <div class="badge valid">✓ Verified Authentic</div>

        <div class="certificate-info">
            <div class="info-row">
                <div class="info-label">Student Name:</div>
                <div class="info-value">{{ $certificate['student_name'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Course Title:</div>
                <div class="info-value">{{ $certificate['course_title'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Category:</div>
                <div class="info-value">{{ $certificate['category'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Difficulty:</div>
                <div class="info-value">{{ ucfirst($certificate['difficulty']) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Instructor:</div>
                <div class="info-value">{{ $certificate['instructor'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Issued Date:</div>
                <div class="info-value">{{ $certificate['issued_date'] }}</div>
            </div>
        </div>

        <div class="verification-code">
            {{ $certificate['verification_code'] }}
        </div>

        <div class="footer">
            <p>This certificate was verified on {{ now()->format('F d, Y \a\t H:i') }}</p>
            <p style="margin-top: 10px;">Certificate authenticity confirmed via cryptographic verification</p>
        </div>
    </div>
@else
    <!-- Invalid Certificate -->
    <div class="container">
        <div class="status-icon invalid">
            ✕
        </div>
        <h1>Certificate Not Found</h1>
        <p class="status-message">This verification code does not match any certificate in our system</p>

        <div class="badge invalid">✕ Not Valid</div>

        <div class="certificate-info">
            <p style="color: #6b7280; line-height: 1.6;">
                The verification code you scanned could not be validated. This may mean:
            </p>
            <ul style="text-align: left; margin: 20px 0; padding-left: 20px; color: #6b7280; line-height: 1.8;">
                <li>The certificate has been revoked</li>
                <li>The verification code is incorrect</li>
                <li>The QR code may be damaged</li>
            </ul>
            <p style="color: #6b7280; line-height: 1.6;">
                Please contact the certificate issuer if you believe this is an error.
            </p>
        </div>

        <div class="footer">
            <p>Verification attempted on {{ now()->format('F d, Y \a\t H:i') }}</p>
        </div>
    </div>
@endif
</body>
</html>
