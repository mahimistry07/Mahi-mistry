<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$uid = $_SESSION['user_id'];

// Save Monthly Sheet & Redirect to History
if (isset($_POST['save_sheet'])) {
    $sdate = mysqli_real_escape_string($conn, $_POST['start_date']);
    $title = mysqli_real_escape_string($conn, $_POST['sheet_title']);
    $json_data = mysqli_real_escape_string($conn, $_POST['sheet_json']);
    $sheet_id = intval($_POST['sheet_id']);

    if ($sheet_id > 0) {
        mysqli_query($conn, "UPDATE monthly_hajri SET start_date='$sdate', sheet_title='$title', sheet_data='$json_data' WHERE id=$sheet_id AND user_id=$uid");
    } else {
        mysqli_query($conn, "INSERT INTO monthly_hajri (user_id, start_date, sheet_title, sheet_data) VALUES ('$uid', '$sdate', '$title', '$json_data')");
    }
    header("Location: history.php?type=KARIGAR");
    exit();
}

// Load Existing Sheet if editing from History
$active_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$loaded_sheet = null;
if ($active_id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM monthly_hajri WHERE id=$active_id AND user_id=$uid");
    $loaded_sheet = mysqli_fetch_assoc($res);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Karigar Hajri Register - Vishvakarma Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .notebook-page {
            background-color: #ffffff;
            background-image: 
                linear-gradient(#e2e8f0 1px, transparent 1px),
                linear-gradient(90deg, #e2e8f0 1px, transparent 1px);
            background-size: 20px 20px;
            color: #1e293b;
            border: 4px solid #6e472b;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
        }
        .header-banner {
            background: linear-gradient(to right, #3d220f, #6e472b);
            color: #FFD54F;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
        }
        table.chopda-table {
            background: rgba(255, 255, 255, 0.92);
            border: 2px solid #334155 !important;
        }
        table.chopda-table th, table.chopda-table td {
            border: 1px solid #64748b !important;
            padding: 3px !important;
            text-align: center;
            vertical-align: middle;
        }
        .date-col {
            background-color: #f8fafc !important;
            font-weight: bold;
            color: #0f172a;
            min-width: 115px;
            font-size: 13px;
            border-right: 2px solid #dc2626 !important;
        }
        .karigar-header {
            background-color: #fef3c7 !important;
            min-width: 95px;
        }
        .att-cell-box {
            width: 100%;
            border: none;
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            background: transparent;
            cursor: pointer;
            padding: 2px 0;
        }
        .att-cell-box:focus {
            background: #fef08a;
            outline: 2px solid #d97706;
        }
        .val-x { color: #dc2626 !important; font-weight: 900; }
        .val-half { color: #059669 !important; font-weight: 900; }
        .val-one { color: #1e3a8a !important; }

        #quickPicker {
            position: absolute;
            display: none;
            background: #291508;
            border: 2px solid #FFD54F;
            border-radius: 10px;
            padding: 6px;
            z-index: 9999;
            box-shadow: 0 8px 20px rgba(0,0,0,0.5);
            gap: 5px;
        }
        #quickPicker button {
            font-weight: bold;
            min-width: 42px;
            border-radius: 6px;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #FFF !important; }
            .notebook-page { border: none; padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body class="wood-bg">

<!-- Quick Floating Option Box -->
<div id="quickPicker" class="no-print">
    <button type="button" class="btn btn-sm btn-primary" onclick="pickOption('1')">1</button>
    <button type="button" class="btn btn-sm btn-success" onclick="pickOption('0.5')">0.5</button>
    <button type="button" class="btn btn-sm btn-warning" onclick="pickOption('1.5')">1.5</button>
    <button type="button" class="btn btn-sm btn-danger" onclick="pickOption('X')">X</button>
</div>

<div class="container-fluid py-3 px-4">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="dashboard.php" class="btn carve-btn">⬅ Back to Dashboard</a>
        <div>
            <a href="history.php?type=KARIGAR" class="btn carve-btn me-2">📂 View Saved History</a>
            <button onclick="window.print()" class="btn carve-btn">🖨️ Print Hajri Sheet</button>
        </div>
    </div>

    <div class="notebook-page">
        <div class="header-banner mb-3">
            <h3 class="fw-bold mb-0">VISHVAKARMA FURNITURE - KARIGAR HAJRI REGISTER</h3>
            <small class="text-white">કારીગર હાજરી અને મજૂરી હિસાબ પત્રક</small>
        </div>

        <!-- Top Controls -->
        <div class="row g-2 mb-3 align-items-end no-print bg-white p-2 rounded border">
            <div class="col-md-3">
                <label class="small fw-bold">1. First Tarikh (શરૂઆતની તારીખ):</label>
                <input type="date" id="startDate" class="form-control border-dark fw-bold" onchange="generateDates()">
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">2. Register Title / સાઈટનું નામ:</label>
                <input type="text" id="sheetTitle" class="form-control border-dark" placeholder="દા.ત. ઓક્ટોબર-નવેમ્બર હાજરી">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">3. Total Days (દિવસ):</label>
                <select id="numDays" class="form-select border-dark" onchange="generateDates()">
                    <option value="30">30 Days</option>
                    <option value="31" selected>31 Days</option>
                    <option value="15">15 Days</option>
                </select>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-primary fw-bold" onclick="addKarigarCol()">+ Add Karigar (નવો કારીગર ઉમેરો)</button>
            </div>
        </div>

        <div class="alert alert-warning py-1 small no-print">
            💡 <strong>નોંધ:</strong> બધા ખાનામાં પહેલેથી <strong>1</strong> લખેલું જ આવશે. કોઈ પણ ખાના પર ક્લિક કરશો એટલે તરત <strong>1</strong>, <strong>0.5</strong>, <strong>1.5</strong>, અને <strong>X</strong> ના ઓપ્શન ખુલશે.
        </div>

        <!-- Main Chopda Style Hajri Grid -->
        <div class="table-responsive">
            <table class="table table-bordered chopda-table mb-0" id="hajriTable">
                <thead>
                    <tr id="nameRow">
                        <th class="date-col">તારીખ / Date</th>
                    </tr>
                    <tr id="rateRow" class="table-info">
                        <th class="small fw-bold text-dark">1 દિવસની હાજરી (₹)</th>
                    </tr>
                </thead>
                <tbody id="dateRowsBody">
                </tbody>
                <tfoot>
                    <tr id="totalDaysRow" class="table-secondary fw-bold">
                        <td class="text-end">કુલ હાજરી (દિવસ):</td>
                    </tr>
                    <tr id="totalPayRow" class="table-warning fw-bold fs-6">
                        <td class="text-end text-danger">કુલ મજૂરી (₹):</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Save Form -->
        <form method="POST" onsubmit="prepareSave()" class="mt-3 text-end no-print">
            <input type="hidden" name="sheet_id" value="<?php echo $active_id; ?>">
            <input type="hidden" name="start_date" id="formStartDate">
            <input type="hidden" name="sheet_title" id="formSheetTitle">
            <input type="hidden" name="sheet_json" id="formSheetJson">
            <button type="submit" name="save_sheet" class="btn carve-btn btn-lg px-5">💾 Save in History</button>
        </form>
    </div>
</div>

<script>
let sheetState = {
    startDate: "<?php echo $loaded_sheet ? $loaded_sheet['start_date'] : date('Y-m-01'); ?>",
    title: "<?php echo $loaded_sheet ? addslashes($loaded_sheet['sheet_title']) : 'Monthly Hajri Sheet'; ?>",
    days: 31,
    karigars: [
        { name: "યોગેશ", rate: 800 },
        { name: "કનુ", rate: 800 },
        { name: "સાગર", rate: 700 },
        { name: "દિલીપ", rate: 700 },
        { name: "વિજય", rate: 600 },
        { name: "દિવ્યરાજ", rate: 600 }
    ],
    attendance: {}
};

<?php if ($loaded_sheet && !empty($loaded_sheet['sheet_data'])): ?>
    sheetState = <?php echo $loaded_sheet['sheet_data']; ?>;
<?php endif; ?>

document.getElementById('startDate').value = sheetState.startDate;
document.getElementById('sheetTitle').value = sheetState.title;
document.getElementById('numDays').value = sheetState.days || 31;

let activeCellInfo = null;

function getColorClass(val) {
    let v = String(val).trim().toUpperCase();
    if (v === 'X' || v === '0') return 'val-x';
    if (v === '0.5' || v === '1.5') return 'val-half';
    return 'val-one';
}

function renderTable() {
    let nameRow = document.getElementById('nameRow');
    let rateRow = document.getElementById('rateRow');
    let tbody = document.getElementById('dateRowsBody');
    let totalDaysRow = document.getElementById('totalDaysRow');
    let totalPayRow = document.getElementById('totalPayRow');

    nameRow.innerHTML = `<th class="date-col">તારીખ / Date</th>`;
    rateRow.innerHTML = `<th class="small fw-bold text-dark">દૈનિક ભાવ (₹)</th>`;
    totalDaysRow.innerHTML = `<td class="text-end">કુલ હાજરી (દિવસ):</td>`;
    totalPayRow.innerHTML = `<td class="text-end text-danger">કુલ રકમ (₹):</td>`;
    tbody.innerHTML = "";

    sheetState.karigars.forEach((k, cIdx) => {
        nameRow.innerHTML += `
            <th class="karigar-header">
                <div class="d-flex justify-content-between align-items-center">
                    <input type="text" class="form-control form-control-sm fw-bold text-center border-0 bg-transparent" 
                           value="${k.name}" onchange="updateKarigarName(${cIdx}, this.value)" placeholder="નામ">
                    <button type="button" class="btn btn-sm text-danger p-0 no-print" onclick="removeKarigar(${cIdx})" title="Remove">×</button>
                </div>
            </th>`;

        rateRow.innerHTML += `
            <th>
                <input type="number" class="form-control form-control-sm text-center fw-bold text-primary" 
                       value="${k.rate}" oninput="updateKarigarRate(${cIdx}, this.value)" placeholder="Rate ₹">
            </th>`;

        totalDaysRow.innerHTML += `<td id="totDays_${cIdx}" class="text-primary">0</td>`;
        totalPayRow.innerHTML += `<td id="totPay_${cIdx}" class="text-danger fs-5">₹ 0</td>`;
    });

    let start = new Date(document.getElementById('startDate').value);
    if (isNaN(start.getTime())) start = new Date();
    let numDays = parseInt(document.getElementById('numDays').value) || 31;

    for (let r = 0; r < numDays; r++) {
        let curr = new Date(start);
        curr.setDate(start.getDate() + r);
        let dd = String(curr.getDate()).padStart(2, '0');
        let mm = String(curr.getMonth() + 1).padStart(2, '0');
        let yy = String(curr.getFullYear()).slice(-2);
        let dateStr = `તા. ${dd}-${mm}-${yy}`;

        let tr = document.createElement('tr');
        let rowHtml = `<td class="date-col">${dateStr}</td>`;

        sheetState.karigars.forEach((k, cIdx) => {
            let key = `${r}_${cIdx}`;
            if (sheetState.attendance[key] === undefined) {
                sheetState.attendance[key] = "1";
            }
            let val = sheetState.attendance[key];
            let cClass = getColorClass(val);

            rowHtml += `
                <td>
                    <input type="text" class="att-cell-box ${cClass}" value="${val}" 
                           onclick="openQuickPicker(event, ${r}, ${cIdx}, this)"
                           oninput="manualType(${r}, ${cIdx}, this)">
                </td>`;
        });
        tr.innerHTML = rowHtml;
        tbody.appendChild(tr);
    }
    calculateAllTotals();
}

function openQuickPicker(event, r, cIdx, inputEl) {
    event.stopPropagation();
    activeCellInfo = { r, cIdx, inputEl };
    let picker = document.getElementById('quickPicker');
    let rect = inputEl.getBoundingClientRect();
    
    picker.style.display = 'flex';
    picker.style.top = (window.scrollY + rect.bottom + 4) + 'px';
    picker.style.left = (window.scrollX + rect.left - 35) + 'px';
}

function pickOption(val) {
    if (!activeCellInfo) return;
    let { r, cIdx, inputEl } = activeCellInfo;
    inputEl.value = val;
    sheetState.attendance[`${r}_${cIdx}`] = val;
    inputEl.className = `att-cell-box ${getColorClass(val)}`;
    
    document.getElementById('quickPicker').style.display = 'none';
    calculateAllTotals();
}

function manualType(r, cIdx, inputEl) {
    let val = inputEl.value.trim();
    sheetState.attendance[`${r}_${cIdx}`] = val;
    inputEl.className = `att-cell-box ${getColorClass(val)}`;
    calculateAllTotals();
}

document.addEventListener('click', function(e) {
    let picker = document.getElementById('quickPicker');
    if (picker && !picker.contains(e.target)) {
        picker.style.display = 'none';
    }
});

function generateDates() {
    sheetState.startDate = document.getElementById('startDate').value;
    sheetState.days = parseInt(document.getElementById('numDays').value);
    renderTable();
}

function addKarigarCol() {
    let name = prompt("નવા કારીગરનું નામ લખો (Enter Karigar Name):");
    if (name && name.trim() !== "") {
        let rate = prompt(name + " ની 1 દિવસની હાજરી (Daily Rate ₹) લખો:", "800");
        sheetState.karigars.push({ name: name.trim(), rate: parseFloat(rate) || 0 });
        renderTable();
    }
}

function removeKarigar(cIdx) {
    if (confirm("આ કારીગરની કોલમ કાઢી નાખવી છે?")) {
        sheetState.karigars.splice(cIdx, 1);
        renderTable();
    }
}

function updateKarigarName(cIdx, val) {
    sheetState.karigars[cIdx].name = val;
}

function updateKarigarRate(cIdx, val) {
    sheetState.karigars[cIdx].rate = parseFloat(val) || 0;
    calculateAllTotals();
}

function calculateAllTotals() {
    let numDays = parseInt(document.getElementById('numDays').value) || 31;

    sheetState.karigars.forEach((k, cIdx) => {
        let totalDays = 0;
        for (let r = 0; r < numDays; r++) {
            let val = String(sheetState.attendance[`${r}_${cIdx}`] || "").trim().toUpperCase();
            if (val !== "" && val !== "X") {
                let num = parseFloat(val);
                if (!isNaN(num)) {
                    totalDays += num;
                }
            }
        }
        let totalPay = totalDays * (parseFloat(k.rate) || 0);
        document.getElementById(`totDays_${cIdx}`).innerText = totalDays;
        document.getElementById(`totPay_${cIdx}`).innerText = "₹ " + totalPay.toLocaleString('en-IN');
    });
}

function prepareSave() {
    sheetState.startDate = document.getElementById('startDate').value;
    sheetState.title = document.getElementById('sheetTitle').value;
    sheetState.days = parseInt(document.getElementById('numDays').value);

    document.getElementById('formStartDate').value = sheetState.startDate;
    document.getElementById('formSheetTitle').value = sheetState.title;
    document.getElementById('formSheetJson').value = JSON.stringify(sheetState);
}

renderTable();
</script>
</body>
</html>