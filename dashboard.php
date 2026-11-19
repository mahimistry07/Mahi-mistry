<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Vishvakarma Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .wood-signboard {
            background: linear-gradient(180deg, #5e381d 0%, #3d220f 50%, #291508 100%);
            border: 6px solid #c68b59;
            border-radius: 22px;
            padding: 35px 20px;
            box-shadow: 
                inset 0 0 25px rgba(0,0,0,0.85),
                0 12px 30px rgba(0,0,0,0.7),
                0 0 0 4px #291508;
            position: relative;
        }
        .screw {
            width: 14px; height: 14px;
            background: radial-gradient(circle, #ffd54f, #b45309);
            border-radius: 50%;
            position: absolute;
            box-shadow: 1px 1px 3px #000;
        }
        .s-tl { top: 12px; left: 12px; }
        .s-tr { top: 12px; right: 12px; }
        .s-bl { bottom: 12px; left: 12px; }
        .s-br { bottom: 12px; right: 12px; }

        .carved-wood-title {
            font-family: 'Cinzel Decorative', serif;
            font-weight: 900;
            letter-spacing: 3px;
            background: linear-gradient(to bottom, #ffe082 0%, #d4a373 50%, #9c6644 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(2px 4px 2px #140903);
        }

        .option-card {
            border-radius: 18px;
            text-decoration: none;
            display: block;
            padding: 30px 15px;
            color: white;
            height: 100%;
        }
        .card-bill { background: linear-gradient(135deg, #b45309, #78350f); }
        .card-maap { background: linear-gradient(135deg, #047857, #064e3b); }
        .card-karigar { background: linear-gradient(135deg, #7e22ce, #4c1d95); }
        .card-hist { background: linear-gradient(135deg, #1d4ed8, #1e3a8a); }
    </style>
</head>
<body class="wood-bg">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <span class="fs-5">Welcome, <strong style="color:#FFD54F;"><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
        <a href="logout.php" class="btn carve-btn btn-sm px-3">Logout</a>
    </div>

    <!-- Proper Wooden Carved Signboard -->
    <div class="wood-signboard text-center mb-5">
        <div class="screw s-tl"></div><div class="screw s-tr"></div>
        <div class="screw s-bl"></div><div class="screw s-br"></div>

        <svg width="80" height="80" viewBox="0 0 100 100" class="mb-2">
            <circle cx="50" cy="50" r="45" fill="#291508" stroke="#d4a373" stroke-width="4"/>
            <path d="M25 28 L50 75 L75 28" fill="none" stroke="#ffd54f" stroke-width="8" stroke-linecap="round"/>
            <path d="M35 45 L65 45" stroke="#d4a373" stroke-width="4" stroke-linecap="round"/>
        </svg>

        <h1 class="display-5 carved-wood-title mb-2">VISHVAKARMA FURNITURE</h1>
        <p class="text-warning small text-uppercase fw-bold mb-0" style="letter-spacing: 3px;">★ Royal Woodwork • Mistry Billing • Measurement ★</p>
    </div>

    <!-- 4 Main Action Cards -->
    <div class="row g-4 text-center">
        <div class="col-md-3 col-6">
            <a href="create_bill.php" class="option-card card-bill carve-card">
                <h3>🧾 BILL</h3>
                <p class="mb-0 small">નવું બિલ બનાવો</p>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="create_maap.php" class="option-card card-maap carve-card">
                <h3>📐 MAAP</h3>
                <p class="mb-0 small">માપ (Sq.Ft) લખો</p>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="karigar_kharcho.php" class="option-card card-karigar carve-card">
                <h3>👷 KARIGAR</h3>
                <p class="mb-0 small">હાજરી અને મજૂરી</p>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="history.php" class="option-card card-hist carve-card">
                <h3>📂 HISTORY</h3>
                <p class="mb-0 small">બિલ, માપ અને હાજરી જુઓ</p>
            </a>
        </div>
    </div>
</div>
</body>
</html>