<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ward Inventory – Choose your view</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .container {
            width: 100%;
            max-width: 520px;
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            padding: 36px 32px;
            text-align: center;
        }
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #2563eb;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            padding: 4px 12px;
            border-radius: 9999px;
            margin-bottom: 12px;
        }
        h1 {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }
        p.subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 28px;
        }
        .roles-grid {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .role-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            padding: 16px 20px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #0f172a;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.15s ease-in-out;
        }
        .role-btn:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .role-btn:active {
            transform: translateY(0);
        }
        .role-name {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .role-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        .admin-dot { background-color: #4f46e5; }
        .nurse-dot { background-color: #0d9488; }
        .ms-dot { background-color: #e11d48; }
        .pharmacist-dot { background-color: #d97706; }

        .role-arrow {
            color: #94a3b8;
            font-size: 18px;
            font-weight: 700;
        }
        .footer-note {
            margin-top: 24px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <span class="badge">Hospital Information System</span>
        <h1>Ward Inventory</h1>
        <p class="subtitle">Choose your view</p>

        <div class="roles-grid">
            <a href="/admin" class="role-btn">
                <span class="role-name">
                    <span class="role-dot admin-dot"></span>
                    Admin
                </span>
                <span class="role-arrow">&rarr;</span>
            </a>
            <a href="/nurse" class="role-btn">
                <span class="role-name">
                    <span class="role-dot nurse-dot"></span>
                    Nurse
                </span>
                <span class="role-arrow">&rarr;</span>
            </a>
            <a href="/ms" class="role-btn">
                <span class="role-name">
                    <span class="role-dot ms-dot"></span>
                    Medical Superintendent (MS)
                </span>
                <span class="role-arrow">&rarr;</span>
            </a>
            <a href="/pharmacist" class="role-btn">
                <span class="role-name">
                    <span class="role-dot pharmacist-dot"></span>
                    Pharmacist
                </span>
                <span class="role-arrow">&rarr;</span>
            </a>
        </div>

        <p class="footer-note">Temporary role chooser until login is added</p>
    </div>
</body>
</html>
