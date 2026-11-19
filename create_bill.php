<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$uid = $_SESSION['user_id'];

$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$rec_data = ['customer_name' => '', 'work_name' => '', 'grand_total' => 0, 'advance_paid' => 0, 'balance_due' => 0];
$items_data = [];

if ($edit_id > 0) {
    $q = mysqli_query($conn, "SELECT * FROM records WHERE id=$edit_id AND user_id=$uid");
    if ($r = mysqli_fetch_assoc($q)) {
        $rec_data = $r;
        $iq = mysqli_query($conn, "SELECT * FROM record_items WHERE record_id=$edit_id");
        while ($ir = mysqli_fetch_assoc($iq)) { $items_data[] = $ir; }
    }
}

if (isset($_POST['save_bill'])) {
    $cname = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $wname = mysqli_real_escape_string($conn, $_POST['work_name']);
    $gtotal = floatval($_POST['grand_total']);
    $advance = floatval($_POST['advance_paid']);
    $balance = floatval($_POST['balance_due']);

    if ($edit_id > 0) {
        mysqli_query($conn, "UPDATE records SET customer_name='$cname', work_name='$wname', grand_total='$gtotal', advance_paid='$advance', balance_due='$balance' WHERE id=$edit_id AND user_id=$uid");
        mysqli_query($conn, "DELETE FROM record_items WHERE record_id=$edit_id");
        $record_id = $edit_id;
    } else {
        mysqli_query($conn, "INSERT INTO records (user_id, record_type, customer_name, work_name, grand_total, advance_paid, balance_due) 
        VALUES ('$uid', 'BILL', '$cname', '$wname', '$gtotal', '$advance', '$balance')");
        $record_id = mysqli_insert_id($conn);
    }

    // Save ONLY non-empty lines
    for ($i = 0; $i < count($_POST['item_name']); $i++) {
        if (trim($_POST['item_name'][$i]) != "") {
            $ino = mysqli_real_escape_string($conn, $_POST['item_no'][$i]);
            $iname = mysqli_real_escape_string($conn, $_POST['item_name'][$i]);
            $size = mysqli_real_escape_string($conn, $_POST['size'][$i]);
            $qty = mysqli_real_escape_string($conn, $_POST['qty'][$i]);
            $rate = mysqli_real_escape_string($conn, $_POST['rate'][$i]);
            $tot = floatval($_POST['total_amt'][$i]);

            mysqli_query($conn, "INSERT INTO record_items (record_id, item_no, item_name, length, qty_no, quantity_rate, total_amount) 
            VALUES ('$record_id', '$ino', '$iname', '$size', '$qty', '$rate', '$tot')");
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
    <title>Create Bill - Vishvakarma Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .bill-sheet { background: #FFFDF9; color: #222; border: 4px solid #d4a373; border-radius: 15px; padding: 25px; }
        .header-banner { background: linear-gradient(to right, #5c3a21, #8c5832); color: #FFD54F; padding: 15px; border-radius: 10px; text-align: center; }
        th { background: #FEF3C7 !important; color: #78350F; text-align: center; }
    </style>
</head>
<body class="wood-bg">
<div class="container py-4">
    <div class="mb-3 d-flex justify-content-between">
        <a href="dashboard.php" class="btn carve-btn">⬅ Back to Dashboard</a>
        <a href="history.php" class="btn carve-btn">📂 View Saved History</a>
    </div>

    <div class="bill-sheet shadow-lg">
        <div class="header-banner mb-3">
            <h2 class="fw-bold mb-0">VISHVAKARMA FURNITURE</h2>
            <p class="mb-0 text-white"><?php echo ($edit_id > 0) ? '✏️ EDIT LABOUR / ESTIMATE BILL' : 'ESTIMATE / LABOUR BILL (બિલ પત્રક)'; ?></p>
        </div>

        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Customer Name / ગ્રાહકનું નામ:</label>
                    <input type="text" name="customer_name" class="form-control border-dark" value="<?php echo htmlspecialchars($rec_data['customer_name']); ?>" placeholder="ગ્રાહકનું નામ લખો" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Work Details / કામની વિગત:</label>
                    <input type="text" name="work_name" class="form-control border-dark" value="<?php echo htmlspecialchars($rec_data['work_name']); ?>" placeholder="દા.ત. ફર્નિચર મજૂરી બિલ" required>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered border-dark">
                    <thead>
                        <tr>
                            <th style="width: 7%;">No.<br>નં.</th>
                            <th style="width: 38%;">Description / કામની વિગત (ગુજરાતીમાં)</th>
                            <th style="width: 15%;">Size / માપ</th>
                            <th style="width: 12%;">Qty / નંગ-ફૂટ</th>
                            <th style="width: 12%;">Rate / ભાવ</th>
                            <th style="width: 16%;">Amount / રકમ (₹)</th>
                        </tr>
                    </thead>
                    <tbody id="billBody">
                        <?php 
                        $bIdx = 1;
                        if (count($items_data) > 0):
                            foreach ($items_data as $item): ?>
                            <tr>
                                <td><input type="text" name="item_no[]" class="form-control text-center" value="<?php echo $bIdx++; ?>"></td>
                                <td><input type="text" name="item_name[]" class="form-control" value="<?php echo htmlspecialchars($item['item_name']); ?>" oninput="checkAutoBillRow(this)"></td>
                                <td><input type="text" name="size[]" class="form-control text-center" value="<?php echo htmlspecialchars($item['length']); ?>"></td>
                                <td><input type="number" step="any" name="qty[]" class="form-control text-center qty" value="<?php echo htmlspecialchars($item['qty_no']); ?>" oninput="calcRow(this)"></td>
                                <td><input type="number" step="any" name="rate[]" class="form-control text-center rate" value="<?php echo htmlspecialchars($item['quantity_rate']); ?>" oninput="calcRow(this)"></td>
                                <td><input type="number" step="any" name="total_amt[]" class="form-control text-end amt fw-bold" value="<?php echo $item['total_amount']; ?>" oninput="calcTotal()"></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        <!-- Always keep one blank row at bottom -->
                        <tr>
                            <td><input type="text" name="item_no[]" class="form-control text-center" value="<?php echo $bIdx; ?>"></td>
                            <td><input type="text" name="item_name[]" class="form-control" placeholder="કામની વિગત લખો (નવી લાઈન આપોઆપ આવશે)..." oninput="checkAutoBillRow(this)"></td>
                            <td><input type="text" name="size[]" class="form-control text-center" placeholder="દા.ત. 6x4"></td>
                            <td><input type="number" step="any" name="qty[]" class="form-control text-center qty" oninput="calcRow(this)"></td>
                            <td><input type="number" step="any" name="rate[]" class="form-control text-center rate" oninput="calcRow(this)"></td>
                            <td><input type="number" step="any" name="total_amt[]" class="form-control text-end amt fw-bold" oninput="calcTotal()"></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end fw-bold fs-6">Total Bill (કુલ બિલ) ₹:</td>
                            <td><input type="number" step="any" name="grand_total" id="billGrandTotal" class="form-control text-end fw-bold bg-light" readonly value="<?php echo $rec_data['grand_total']; ?>"></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-bold fs-6 text-success">Advance Paid (જમા રકમ) ₹:</td>
                            <td><input type="number" step="any" name="advance_paid" id="advancePaid" class="form-control text-end fw-bold border-success" value="<?php echo $rec_data['advance_paid']; ?>" oninput="calcBalance()"></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-bold fs-5 text-danger">Balance Due (બાકી રકમ) ₹:</td>
                            <td><input type="number" step="any" name="balance_due" id="balanceDue" class="form-control text-end fw-bold bg-warning fs-5" readonly value="<?php echo $rec_data['balance_due']; ?>"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-end mt-3">
                <button type="submit" name="save_bill" class="btn carve-btn btn-lg px-5">💾 Save & Print Bill</button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-add new row when typing in last row
function checkAutoBillRow(input) {
    let tbody = document.getElementById('billBody');
    let rows = tbody.querySelectorAll('tr');
    let lastRow = rows[rows.length - 1];
    
    if (lastRow.contains(input) && input.value.trim() !== "") {
        let nextNo = rows.length + 1;
        let tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" name="item_no[]" class="form-control text-center" value="${nextNo}"></td>
            <td><input type="text" name="item_name[]" class="form-control" placeholder="કામની વિગત લખો..." oninput="checkAutoBillRow(this)"></td>
            <td><input type="text" name="size[]" class="form-control text-center"></td>
            <td><input type="number" step="any" name="qty[]" class="form-control text-center qty" oninput="calcRow(this)"></td>
            <td><input type="number" step="any" name="rate[]" class="form-control text-center rate" oninput="calcRow(this)"></td>
            <td><input type="number" step="any" name="total_amt[]" class="form-control text-end amt fw-bold" oninput="calcTotal()"></td>
        `;
        tbody.appendChild(tr);
    }
}

function calcRow(el) {
    let tr = el.parentNode.parentNode;
    let q = parseFloat(tr.querySelector('.qty').value) || 0;
    let r = parseFloat(tr.querySelector('.rate').value) || 0;
    if (q > 0 && r > 0) {
        tr.querySelector('.amt').value = (q * r).toFixed(2);
    }
    calcTotal();
}

function calcTotal() {
    let amts = document.querySelectorAll('.amt');
    let sum = 0;
    amts.forEach(a => { sum += parseFloat(a.value) || 0; });
    document.getElementById('billGrandTotal').value = sum.toFixed(2);
    calcBalance();
}

function calcBalance() {
    let total = parseFloat(document.getElementById('billGrandTotal').value) || 0;
    let adv = parseFloat(document.getElementById('advancePaid').value) || 0;
    document.getElementById('balanceDue').value = (total - adv).toFixed(2);
}
</script>
</body>
</html>