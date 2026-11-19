<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$uid = $_SESSION['user_id'];

$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$rec_data = ['customer_name' => '', 'work_name' => '', 'grand_total' => 0];
$items_data = [];

if ($edit_id > 0) {
    $q = mysqli_query($conn, "SELECT * FROM records WHERE id=$edit_id AND user_id=$uid");
    if ($r = mysqli_fetch_assoc($q)) {
        $rec_data = $r;
        $iq = mysqli_query($conn, "SELECT * FROM record_items WHERE record_id=$edit_id");
        while ($ir = mysqli_fetch_assoc($iq)) { $items_data[] = $ir; }
    }
}

if (isset($_POST['save_maap'])) {
    $cname = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $wname = mysqli_real_escape_string($conn, $_POST['work_name']);
    $gtotal = floatval($_POST['grand_total']);

    if ($edit_id > 0) {
        mysqli_query($conn, "UPDATE records SET customer_name='$cname', work_name='$wname', grand_total='$gtotal' WHERE id=$edit_id AND user_id=$uid");
        mysqli_query($conn, "DELETE FROM record_items WHERE record_id=$edit_id");
        $record_id = $edit_id;
    } else {
        mysqli_query($conn, "INSERT INTO records (user_id, record_type, customer_name, work_name, grand_total) VALUES ('$uid', 'MAAP', '$cname', '$wname', '$gtotal')");
        $record_id = mysqli_insert_id($conn);
    }

    // Save ONLY non-empty lines
    for ($i = 0; $i < count($_POST['item_name']); $i++) {
        if (trim($_POST['item_name'][$i]) != "") {
            $ino = mysqli_real_escape_string($conn, $_POST['item_no'][$i]);
            $iname = mysqli_real_escape_string($conn, $_POST['item_name'][$i]);
            $no = mysqli_real_escape_string($conn, $_POST['qty_no'][$i]);
            $len = mysqli_real_escape_string($conn, $_POST['length'][$i]);
            $brd = mysqli_real_escape_string($conn, $_POST['breadth'][$i]);
            $dep = mysqli_real_escape_string($conn, $_POST['depth'][$i]);
            $qty = mysqli_real_escape_string($conn, $_POST['quantity'][$i]);
            $tot = floatval($_POST['total_qty'][$i]);

            mysqli_query($conn, "INSERT INTO record_items (record_id, item_no, item_name, qty_no, length, breadth, depth, quantity_rate, total_amount) 
            VALUES ('$record_id', '$ino', '$iname', '$no', '$len', '$brd', '$dep', '$qty', '$tot')");
        }
    }
    header("Location: view_print.php?id=$record_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Measurement Form - Vishvakarma Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .form-sheet { background: #FFFDF9; color: #222; border: 4px solid #d4a373; border-radius: 15px; padding: 25px; }
        .header-banner { background: linear-gradient(to right, #3d220f, #6e472b); color: #FFD54F; padding: 15px; border-radius: 10px; text-align: center; }
        th { background: #EFEBE9 !important; color: #3E2723; font-size: 13px; text-align: center; vertical-align: middle; }
        input.form-control { font-size: 14px; padding: 6px; }
    </style>
</head>
<body class="wood-bg">
<div class="container-fluid py-4 px-4">
    <div class="mb-3 d-flex justify-content-between">
        <a href="dashboard.php" class="btn carve-btn">⬅ Back to Dashboard</a>
        <a href="history.php" class="btn carve-btn">📂 View Saved History</a>
    </div>

    <div class="form-sheet shadow-lg">
        <div class="header-banner mb-3">
            <h2 class="fw-bold mb-0">VISHVAKARMA FURNITURE</h2>
            <p class="mb-0 text-white"><?php echo ($edit_id > 0) ? '✏️ EDIT MEASUREMENT FORM' : 'MEASUREMENT FORM / મેઝરમેન્ટ ફોર્મ (માપ પત્રક)'; ?></p>
        </div>

        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Name / નામ (Customer / Site):</label>
                    <input type="text" name="customer_name" class="form-control border-dark" value="<?php echo htmlspecialchars($rec_data['customer_name']); ?>" placeholder="દા.ત. ક્રિષ્ના કેન્સર હોસ્પિટલ" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Name of Work / કામનું નામ:</label>
                    <input type="text" name="work_name" class="form-control border-dark" value="<?php echo htmlspecialchars($rec_data['work_name']); ?>" placeholder="દા.ત. ફર્નિચર અને રિનોવેશન કામ" required>
                </div>
            </div>

            <div class="alert alert-info py-1 small">
                💡 <strong>Smart Tip:</strong> નીચેના ખાનામાં લખવાનું ચાલુ કરશો એટલે નવી લાઈન આપોઆપ ઉમેરાઈ જશે! Length (ઈંચ) અને Breadth (ઈંચ) નાખતા જ <strong>Sq.Ft (ચોરસ ફૂટ)</strong> આપોઆપ ગણાઈ જશે.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered border-dark" id="maapTable">
                    <thead>
                        <tr>
                            <th style="width: 6%;">No.<br>નંબર</th>
                            <th style="width: 30%;">Items<br>આઈટમ (ગુજરાતીમાં)</th>
                            <th style="width: 7%;">Qty<br>નંગ</th>
                            <th style="width: 9%;">Length<br>લંબાઈ (Inch)</th>
                            <th style="width: 9%;">Breadth<br>પહોળાઈ (Inch)</th>
                            <th style="width: 10%;">Auto Sq.Ft<br>ચોરસ ફૂટ</th>
                            <th style="width: 13%;">Quantity<br>માપ</th>
                            <th style="width: 16%;">Total Quantity<br>કુલ માપ</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php 
                        $idx = 1;
                        if (count($items_data) > 0):
                            foreach ($items_data as $item): ?>
                            <tr>
                                <td><input type="text" name="item_no[]" class="form-control text-center" value="<?php echo $idx++; ?>"></td>
                                <td><input type="text" name="item_name[]" class="form-control item-input" value="<?php echo htmlspecialchars($item['item_name']); ?>" oninput="checkAutoAdd(this)"></td>
                                <td><input type="text" name="qty_no[]" class="form-control text-center" value="<?php echo htmlspecialchars($item['qty_no']); ?>"></td>
                                <td><input type="number" step="any" name="length[]" class="form-control text-center len-inp" value="<?php echo htmlspecialchars($item['length']); ?>" oninput="calcSqFt(this)"></td>
                                <td><input type="number" step="any" name="breadth[]" class="form-control text-center brd-inp" value="<?php echo htmlspecialchars($item['breadth']); ?>" oninput="calcSqFt(this)"></td>
                                <td><input type="text" name="depth[]" class="form-control text-center sqft-out bg-light fw-bold" value="<?php echo htmlspecialchars($item['depth']); ?>" readonly></td>
                                <td><input type="text" name="quantity[]" class="form-control text-center" value="<?php echo htmlspecialchars($item['quantity_rate']); ?>"></td>
                                <td><input type="number" step="any" name="total_qty[]" class="form-control text-end row-total fw-bold" value="<?php echo $item['total_amount']; ?>" oninput="calcGrandTotal()"></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        <!-- Always keep one blank row at bottom -->
                        <tr>
                            <td><input type="text" name="item_no[]" class="form-control text-center" value="<?php echo $idx; ?>"></td>
                            <td><input type="text" name="item_name[]" class="form-control item-input" placeholder="આઈટમ લખો (નવી લાઈન આપોઆપ આવશે)..." oninput="checkAutoAdd(this)"></td>
                            <td><input type="text" name="qty_no[]" class="form-control text-center"></td>
                            <td><input type="number" step="any" name="length[]" class="form-control text-center len-inp" oninput="calcSqFt(this)"></td>
                            <td><input type="number" step="any" name="breadth[]" class="form-control text-center brd-inp" oninput="calcSqFt(this)"></td>
                            <td><input type="text" name="depth[]" class="form-control text-center sqft-out bg-light fw-bold" readonly></td>
                            <td><input type="text" name="quantity[]" class="form-control text-center"></td>
                            <td><input type="number" step="any" name="total_qty[]" class="form-control text-end row-total fw-bold" oninput="calcGrandTotal()"></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" class="text-end fw-bold fs-5">GRAND TOTAL / કુલ ટોટલ:</td>
                            <td><input type="number" step="any" name="grand_total" id="grandTotal" class="form-control text-end fw-bold bg-warning fs-5" readonly value="<?php echo $rec_data['grand_total']; ?>"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-end mt-3">
                <button type="submit" name="save_maap" class="btn carve-btn btn-lg px-5">💾 Save & Print Maap</button>
            </div>
        </form>
    </div>
</div>

<script>
// Automatically add new row when typing in the last row
function checkAutoAdd(input) {
    let tbody = document.getElementById('tableBody');
    let rows = tbody.querySelectorAll('tr');
    let lastRow = rows[rows.length - 1];
    
    if (lastRow.contains(input) && input.value.trim() !== "") {
        let nextNo = rows.length + 1;
        let tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="item_no[]" class="form-control text-center" value="${nextNo}"></td>
            <td><input type="text" name="item_name[]" class="form-control item-input" placeholder="આઈટમ લખો..." oninput="checkAutoAdd(this)"></td>
            <td><input type="text" name="qty_no[]" class="form-control text-center"></td>
            <td><input type="number" step="any" name="length[]" class="form-control text-center len-inp" oninput="calcSqFt(this)"></td>
            <td><input type="number" step="any" name="breadth[]" class="form-control text-center brd-inp" oninput="calcSqFt(this)"></td>
            <td><input type="text" name="depth[]" class="form-control text-center sqft-out bg-light fw-bold" readonly></td>
            <td><input type="text" name="quantity[]" class="form-control text-center"></td>
            <td><input type="number" step="any" name="total_qty[]" class="form-control text-end row-total fw-bold" oninput="calcGrandTotal()"></td>
        `;
        tbody.appendChild(tr);
    }
}

// Auto Square Feet Calculator: (Length * Breadth) / 144
function calcSqFt(el) {
    let tr = el.parentNode.parentNode;
    let l = parseFloat(tr.querySelector('.len-inp').value) || 0;
    let b = parseFloat(tr.querySelector('.brd-inp').value) || 0;
    let sqftBox = tr.querySelector('.sqft-out');
    if (l > 0 && b > 0) {
        let sqft = (l * b) / 144;
        sqftBox.value = sqft.toFixed(2);
    } else {
        sqftBox.value = "";
    }
}

function calcGrandTotal() {
    let totals = document.querySelectorAll('.row-total');
    let sum = 0;
    totals.forEach(input => { sum += parseFloat(input.value) || 0; });
    document.getElementById('grandTotal').value = sum.toFixed(2);
}
</script>
</body>
</html>