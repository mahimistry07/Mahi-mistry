<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$uid = $_SESSION['user_id'];

// Auto-create monthly_hajri table if not exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS monthly_hajri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    start_date DATE NOT NULL,
    sheet_title VARCHAR(255) DEFAULT 'Monthly Hajri Sheet',
    sheet_data LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Delete Bill / Maap
if (isset($_GET['del'])) {
    $did = intval($_GET['del']);
    mysqli_query($conn, "DELETE FROM records WHERE id=$did AND user_id=$uid");
    header("Location: history.php"); exit();
}

// Delete Karigar Hajri Sheet
if (isset($_GET['del_karigar'])) {
    $kid = intval($_GET['del_karigar']);
    mysqli_query($conn, "DELETE FROM monthly_hajri WHERE id=$kid AND user_id=$uid");
    header("Location: history.php?type=KARIGAR"); exit();
}

$type_filter = isset($_GET['type']) ? $_GET['type'] : 'ALL';
$search_name = isset($_GET['search_name']) ? mysqli_real_escape_string($conn, $_GET['search_name']) : '';
$search_date = isset($_GET['search_date']) ? mysqli_real_escape_string($conn, $_GET['search_date']) : '';

$history_list = [];

// 1. Fetch Bills & Maap
if ($type_filter == 'ALL' || $type_filter == 'BILL' || $type_filter == 'MAAP') {
    $where = "WHERE user_id=$uid";
    if ($type_filter == 'BILL' || $type_filter == 'MAAP') {
        $where .= " AND record_type='$type_filter'";
    }
    if ($search_name != "") {
        $where .= " AND (customer_name LIKE '%$search_name%' OR work_name LIKE '%$search_name%')";
    }
    if ($search_date != "") {
        $where .= " AND DATE(created_at) = '$search_date'";
    }
    $q1 = mysqli_query($conn, "SELECT * FROM records $where ORDER BY id DESC");
    if ($q1) {
        while ($row = mysqli_fetch_assoc($q1)) {
            $history_list[] = [
                'id' => $row['id'],
                'date' => date('Y-m-d', strtotime($row['created_at'])),
                'type' => $row['record_type'],
                'title' => $row['customer_name'],
                'subtitle' => $row['work_name'],
                'total' => number_format($row['grand_total'], 2),
                'balance' => ($row['record_type'] == 'BILL') ? '₹ ' . number_format($row['balance_due'], 2) : '-'
            ];
        }
    }
}

// 2. Fetch Karigar Hajri Sheets
if ($type_filter == 'ALL' || $type_filter == 'KARIGAR') {
    $k_where = "WHERE user_id=$uid";
    if ($search_name != "") {
        $k_where .= " AND sheet_title LIKE '%$search_name%'";
    }
    if ($search_date != "") {
        $k_where .= " AND (start_date = '$search_date' OR DATE(created_at) = '$search_date')";
    }
    $q2 = mysqli_query($conn, "SELECT * FROM monthly_hajri $k_where ORDER BY id DESC");
    if ($q2) {
        while ($k = mysqli_fetch_assoc($q2)) {
            $data = json_decode($k['sheet_data'], true);
            $grand_pay = 0;
            if (is_array($data) && isset($data['karigars']) && isset($data['attendance'])) {
                $days = intval($data['days'] ?? 31);
                foreach ($data['karigars'] as $cIdx => $kar) {
                    $tDays = 0;
                    for ($dayIndex = 0; $dayIndex < $days; $dayIndex++) {
                        $cellKey = $dayIndex . "_" . $cIdx;
                        $val = isset($data['attendance'][$cellKey]) ? strtoupper(trim($data['attendance'][$cellKey])) : "";
                        if ($val !== "" && $val !== "X" && is_numeric($val)) {
                            $tDays += floatval($val);
                        }
                    }
                    $grand_pay += ($tDays * floatval($kar['rate'] ?? 0));
                }
            }
            $history_list[] = [
                'id' => $k['id'],
                'date' => $k['start_date'],
                'type' => 'KARIGAR',
                'title' => $k['sheet_title'],
                'subtitle' => 'Start Date: ' . date('d-m-Y', strtotime($k['start_date'])),
                'total' => '₹ ' . number_format($grand_pay, 2),
                'balance' => '-'
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>History - Vishvakarma Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="wood-bg">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold" style="color:#FFD54F;">📂 Saved History (Bill, Maap & Karigar Hajri)</h3>
        <a href="dashboard.php" class="btn carve-btn">⬅ Back to Dashboard</a>
    </div>

    <!-- Search & Category Filter Bar -->
    <div class="teak-panel p-3 mb-4">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="small text-warning fw-bold">Select Category (શું શોધવું છે?):</label>
                <select name="type" class="form-select fw-bold border-warning">
                    <option value="ALL" <?php if($type_filter=='ALL') echo 'selected'; ?>>📋 All (બધું જ જુઓ)</option>
                    <option value="BILL" <?php if($type_filter=='BILL') echo 'selected'; ?>>🧾 Bill (બિલ)</option>
                    <option value="MAAP" <?php if($type_filter=='MAAP') echo 'selected'; ?>>📐 Maap (માપ પત્રક)</option>
                    <option value="KARIGAR" <?php if($type_filter=='KARIGAR') echo 'selected'; ?>>👷 Karigar Hajri (હાજરી)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="small text-warning fw-bold">Search Name / Site (નામ લખો):</label>
                <input type="text" name="search_name" class="form-control" placeholder="નામ અથવા સાઈટ શોધો..." value="<?php echo htmlspecialchars($search_name); ?>">
            </div>
            <div class="col-md-3">
                <label class="small text-warning fw-bold">Date (તારીખ):</label>
                <input type="date" name="search_date" class="form-control" value="<?php echo htmlspecialchars($search_date); ?>">
            </div>
            <div class="col-md-2 d-flex gap-2 mt-4">
                <button type="submit" class="btn carve-btn w-100">🔍 Search</button>
                <a href="history.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card shadow-lg border-warning">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Customer / Register Name</th>
                        <th>Work / Details</th>
                        <th>Total</th>
                        <th>Balance Due</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($history_list) == 0): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No records found!</td></tr>
                    <?php endif; ?>
                    <?php foreach($history_list as $item): ?>
                    <tr>
                        <td><?php echo date('d-M-Y', strtotime($item['date'])); ?></td>
                        <td>
                            <?php if($item['type'] == 'BILL'): ?>
                                <span class="badge bg-warning text-dark">🧾 BILL</span>
                            <?php elseif($item['type'] == 'MAAP'): ?>
                                <span class="badge bg-success">📐 MAAP</span>
                            <?php else: ?>
                                <span class="badge bg-primary">👷 KARIGAR</span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></td>
                        <td><?php echo htmlspecialchars($item['subtitle']); ?></td>
                        <td class="fw-bold"><?php echo $item['total']; ?></td>
                        <td class="fw-bold text-danger"><?php echo $item['balance']; ?></td>
                        <td>
                            <?php if($item['type'] == 'KARIGAR'): ?>
                                <a href="karigar_kharcho.php?id=<?php echo $item['id']; ?>" class="btn btn-sm carve-btn">👁️ Open / Edit / Print</a>
                                <a href="history.php?del_karigar=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this Hajri Register?')">🗑️</a>
                            <?php else: 
                                $edit_url = ($item['type'] == 'BILL') ? 'create_bill.php?edit=' . $item['id'] : 'create_maap.php?edit=' . $item['id'];
                            ?>
                                <a href="view_print.php?id=<?php echo $item['id']; ?>" class="btn btn-sm carve-btn">👁️ Print</a>
                                <a href="<?php echo $edit_url; ?>" class="btn btn-sm btn-warning fw-bold">✏️ Edit</a>
                                <a href="history.php?del=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">🗑️</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>