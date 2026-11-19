<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$id = intval($_GET['id']);
$rec = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM records WHERE id=$id"));
$items = mysqli_query($conn, "SELECT * FROM record_items WHERE record_id=$id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print - Vishvakarma Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .print-area { background: #FFF; color: #000; max-width: 850px; margin: 20px auto; padding: 35px; border: 3px solid #4a2e1b; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        
        /* Round Custom Wooden Stamp Style */
        .wooden-stamp {
            width: 135px;
            height: 135px;
            border: 4px double #6e472b;
            border-radius: 50%;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #5c3a21;
            transform: rotate(-12deg);
            text-align: center;
            padding: 8px;
            background: rgba(212, 163, 115, 0.08);
            user-select: none;
        }
        .stamp-title { font-size: 11px; font-weight: 900; letter-spacing: 1px; border-bottom: 1px solid #6e472b; padding-bottom: 2px; }
        .stamp-mid { font-size: 13px; font-weight: bold; margin: 3px 0; color: #8c5832; }
        .stamp-sub { font-size: 9px; font-weight: bold; border-top: 1px solid #6e472b; padding-top: 2px; }

        @media print {
            .no-print { display: none !important; }
            body { background: #FFF !important; }
            .print-area { border: 2px solid #000; margin: 0; padding: 15px; max-width: 100%; box-shadow: none; }
        }
    </style>
</head>
<body class="wood-bg">

<div class="text-center py-3 no-print">
    <a href="dashboard.php" class="btn carve-btn me-2">🏠 Home</a>
    <a href="history.php" class="btn carve-btn me-2">📂 History</a>
    <button onclick="window.print()" class="btn carve-btn btn-lg px-4">🖨️ PRINT / SAVE PDF</button>
</div>

<div class="print-area">
    <div class="text-center border-bottom border-dark pb-2 mb-3">
        <h2 class="fw-bold mb-0" style="color:#4a2e1b;">VISHVAKARMA FURNITURE</h2>
        <p class="mb-0 fw-bold"><?php echo ($rec['record_type'] == 'MAAP') ? 'MEASUREMENT FORM / મેઝરમેન્ટ ફોર્મ' : 'FURNITURE WORK BILL / કામનું બિલ'; ?></p>
    </div>

    <div class="row mb-3">
        <div class="col-7"><strong>Name / નામ:</strong> <?php echo htmlspecialchars($rec['customer_name']); ?></div>
        <div class="col-5 text-end"><strong>Date / તારીખ:</strong> <?php echo date('d-m-Y', strtotime($rec['created_at'])); ?></div>
        <div class="col-12 mt-1"><strong>Name of Work / કામનું નામ:</strong> <?php echo htmlspecialchars($rec['work_name']); ?></div>
    </div>

    <table class="table table-bordered border-dark table-sm">
        <thead>
            <?php if($rec['record_type'] == 'MAAP'): ?>
            <tr class="text-center table-secondary">
                <th>No.<br>નંબર</th>
                <th>Items<br>આઈટમ</th>
                <th>No.<br>નંગ</th>
                <th>Length<br>લંબાઈ</th>
                <th>Breadth<br>પહોળાઈ</th>
                <th>Depth<br>ઉંડાઈ</th>
                <th>Quantity<br>માપ</th>
                <th>Total Qty<br>કુલ માપ</th>
            </tr>
            <?php else: ?>
            <tr class="text-center table-secondary">
                <th>No.</th>
                <th>Description / વિગત</th>
                <th>Size / માપ</th>
                <th>Qty / નંગ</th>
                <th>Rate / ભાવ</th>
                <th>Amount / રકમ (₹)</th>
            </tr>
            <?php endif; ?>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($items)): ?>
                <?php if($rec['record_type'] == 'MAAP'): ?>
                <tr>
                    <td class="text-center"><?php echo $row['item_no']; ?></td>
                    <td><?php echo $row['item_name']; ?></td>
                    <td class="text-center"><?php echo $row['qty_no']; ?></td>
                    <td class="text-center"><?php echo $row['length']; ?></td>
                    <td class="text-center"><?php echo $row['breadth']; ?></td>
                    <td class="text-center"><?php echo $row['depth']; ?></td>
                    <td class="text-center"><?php echo $row['quantity_rate']; ?></td>
                    <td class="text-end fw-bold"><?php echo $row['total_amount']; ?></td>
                </tr>
                <?php else: ?>
                <tr>
                    <td class="text-center"><?php echo $row['item_no']; ?></td>
                    <td><?php echo $row['item_name']; ?></td>
                    <td class="text-center"><?php echo $row['length']; ?></td>
                    <td class="text-center"><?php echo $row['qty_no']; ?></td>
                    <td class="text-center"><?php echo $row['quantity_rate']; ?></td>
                    <td class="text-end fw-bold"><?php echo $row['total_amount']; ?></td>
                </tr>
                <?php endif; ?>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <?php if($rec['record_type'] == 'MAAP'): ?>
            <tr>
                <td colspan="7" class="text-end fw-bold">GRAND TOTAL / કુલ ટોટલ:</td>
                <td class="text-end fw-bold fs-6"><?php echo number_format($rec['grand_total'], 2); ?></td>
            </tr>
            <?php else: ?>
            <tr>
                <td colspan="5" class="text-end fw-bold">Total Bill (કુલ બિલ):</td>
                <td class="text-end fw-bold">₹ <?php echo number_format($rec['grand_total'], 2); ?></td>
            </tr>
            <tr>
                <td colspan="5" class="text-end fw-bold text-success">Advance Paid (જમા રકમ):</td>
                <td class="text-end fw-bold text-success">₹ <?php echo number_format($rec['advance_paid'], 2); ?></td>
            </tr>
            <tr class="table-warning">
                <td colspan="5" class="text-end fw-bold fs-6">Balance Due (બાકી રકમ):</td>
                <td class="text-end fw-bold fs-6">₹ <?php echo number_format($rec['balance_due'], 2); ?></td>
            </tr>
            <?php endif; ?>
        </tfoot>
    </table>

    <!-- Stamp & Digital Signature Section -->
    <div class="row mt-4 pt-3 align-items-end">
        <div class="col-5">
            <p class="mb-0 border-top border-dark d-inline-block pt-1 px-3">Customer Signature</p>
        </div>
        <div class="col-7 text-end">
            <div class="d-inline-flex align-items-center gap-3">
                <!-- Round Wooden Digital Stamp -->
                <div class="wooden-stamp">
                    <span class="stamp-title">VISHVAKARMA</span>
                    <span class="stamp-mid">★ FURNITURE ★</span>
                    <span class="stamp-sub">VERIFIED & APPROVED</span>
                </div>
                
                <!-- Signature Area -->
                <div class="text-center">
                    <?php if(file_exists('signature.png')): ?>
                        <img src="signature.png" alt="Signature" style="max-height: 55px; display:block; margin: 0 auto 5px;">
                    <?php else: ?>
                        <div style="font-family: 'Brush Script MT', cursive; font-size: 26px; color: #3b2314; margin-bottom: -5px;">Vishvakarma</div>
                    <?php endif; ?>
                    <p class="mb-0 fw-bold border-top border-dark pt-1 px-2">For, Vishvakarma Furniture</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>