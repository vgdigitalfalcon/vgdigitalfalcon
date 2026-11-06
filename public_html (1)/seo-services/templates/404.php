<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | VG Digital Falcon</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #0A0F1E;
            color: #CBD5E1;
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 20px;
        }
        h1 { font-family: 'Syne', sans-serif; font-size: clamp(3rem, 10vw, 7rem); color: #F59E0B; line-height: 1; margin-bottom: 16px; }
        h2 { font-family: 'Syne', sans-serif; font-size: 1.5rem; color: #fff; margin-bottom: 12px; }
        p { color: #94A3B8; margin-bottom: 32px; }
        a {
            display: inline-flex; align-items: center; gap: 8px;
            background: #F59E0B; color: #0A0F1E; padding: 14px 28px;
            border-radius: 8px; font-weight: 700; text-decoration: none;
            transition: all 0.2s;
        }
        a:hover { background: #FCD34D; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div>
        <div style="font-size:4rem;margin-bottom:20px;">🦅</div>
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>The page you are looking for doesn't exist or has been moved.</p>
        <a href="<?= SITE_URL ?>">← Go Home</a>
    </div>
</body>
</html>
