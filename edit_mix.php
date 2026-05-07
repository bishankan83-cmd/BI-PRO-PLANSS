<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Database Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .header .sync-status {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 5px 15px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .filter-section {
            padding: 30px;
            background: #f8f9ff;
            border-bottom: 1px solid #e0e6ed;
        }

        .filter-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .form-group input, .form-group select {
            padding: 12px 15px;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #28a745;
            color: white;
            padding: 8px 15px;
            font-size: 0.8rem;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            font-size: 0.8rem;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
            padding: 8px 15px;
            font-size: 0.8rem;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
            padding: 8px 15px;
            font-size: 0.8rem;
        }

        .btn-info:hover {
            background: #138496;
        }

        .table-section {
            padding: 30px;
        }

        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-controls .left-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .table-controls .right-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .pagination-info {
            color: #666;
            font-size: 0.9rem;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-height: 600px;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 10px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            font-size: 0.9rem;
            cursor: pointer;
            user-select: none;
        }

        th:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        th.sortable::after {
            content: ' ↕️';
            font-size: 0.7rem;
        }

        th.sort-asc::after {
            content: ' ↑';
            color: #ffeb3b;
        }

        th.sort-desc::after {
            content: ' ↓';
            color: #ffeb3b;
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid #e0e6ed;
            vertical-align: middle;
        }

        tr:hover {
            background-color: #f8f9ff;
        }

        tr.selected {
            background-color: #e3f2fd !important;
        }

        td input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        td input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        }

        td input.changed {
            border-color: #ffc107;
            background-color: #fff3cd;
        }

        .actions-cell {
            white-space: nowrap;
        }

        .row-checkbox {
            cursor: pointer;
        }

        .status-message {
            padding: 15px;
            border-radius: 10px;
            margin: 20px 30px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .no-results {
            text-align: center;
            padding: 50px;
            color: #666;
            font-size: 1.2rem;
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9ff;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            margin-top: 5px;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 15px;
            width: 80%;
            max-width: 500px;
            text-align: center;
        }

        .modal h3 {
            color: #333;
            margin-bottom: 20px;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }

            .table-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .table-controls .left-controls,
            .table-controls .right-controls {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $message = "";
    $messageType = "";

    // Function to update database
    function updateRecord($conn, $id, $column, $value) {
        $value = $conn->real_escape_string($value);
        $column = $conn->real_escape_string($column);
        $sql = "UPDATE bcompound2 SET " . $column . " = '" . $value . "' WHERE id = " . intval($id);
        
        if ($conn->query($sql) === TRUE) {
            // Insert into mixing table
            $insertSql = "INSERT INTO mixing (id, " . $column . ") VALUES (" . intval($id) . ", '$value') 
                         ON DUPLICATE KEY UPDATE " . $column . " = VALUES(" . $column . ")";
            $conn->query($insertSql);
            return true;
        } else {
            return false;
        }
    }

    // Function to delete record
    function deleteRecord($conn, $id) {
        $id = intval($id);
        $sql = "DELETE FROM bcompound2 WHERE id = " . $id;
        
        if ($conn->query($sql) === TRUE) {
            // Also delete from mixing table if exists
            $deleteMixingSql = "DELETE FROM mixing WHERE id = " . $id;
            $conn->query($deleteMixingSql);
            return true;
        } else {
            return false;
        }
    }

    // Function to bulk delete records
    function bulkDeleteRecords($conn, $ids) {
        $idList = implode(',', array_map('intval', $ids));
        $sql = "DELETE FROM bcompound2 WHERE id IN ($idList)";
        
        if ($conn->query($sql) === TRUE) {
            $deleteMixingSql = "DELETE FROM mixing WHERE id IN ($idList)";
            $conn->query($deleteMixingSql);
            return true;
        } else {
            return false;
        }
    }

    // Handle AJAX requests
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
        if ($_POST['action'] == 'edit') {
            $id = $_POST["id"];
            $column = $_POST["column"];
            $value = $_POST["value"];

            if (updateRecord($conn, $id, $column, $value)) {
                echo json_encode(['status' => 'success', 'message' => 'Record updated successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update record.']);
            }
            exit;
        } elseif ($_POST['action'] == 'delete') {
            $id = $_POST["id"];

            if (deleteRecord($conn, $id)) {
                echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete record.']);
            }
            exit;
        } elseif ($_POST['action'] == 'bulk_delete') {
            $ids = json_decode($_POST["ids"], true);

            if (bulkDeleteRecords($conn, $ids)) {
                echo json_encode(['status' => 'success', 'message' => 'Records deleted successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete records.']);
            }
            exit;
        }
    }

    // Build SQL query based on filters
    $sql = "SELECT * FROM bcompound2 WHERE 1=1";
    $totalRecords = 0;

    // Get total records count
    $totalResult = $conn->query("SELECT COUNT(*) as count FROM bcompound2");
    if ($totalResult) {
        $totalRecords = $totalResult->fetch_assoc()['count'];
    }

    // Add filters
    if (!empty($_POST['id'])) {
        $sql .= " AND id LIKE '%" . $conn->real_escape_string($_POST['id']) . "%'";
    }
    if (!empty($_POST['inputDate'])) {
        $sql .= " AND inputDate = '" . $conn->real_escape_string($_POST['inputDate']) . "'";
    }
    if (!empty($_POST['shift'])) {
        $sql .= " AND shift LIKE '%" . $conn->real_escape_string($_POST['shift']) . "%'";
    }
    if (!empty($_POST['compound_name'])) {
        $sql .= " AND compound_name LIKE '%" . $conn->real_escape_string($_POST['compound_name']) . "%'";
    }
    if (!empty($_POST['description'])) {
        $sql .= " AND description LIKE '%" . $conn->real_escape_string($_POST['description']) . "%'";
    }
    if (!empty($_POST['cstock'])) {
        $sql .= " AND cstock LIKE '%" . $conn->real_escape_string($_POST['cstock']) . "%'";
    }
    if (!empty($_POST['batch'])) {
        $sql .= " AND batch LIKE '%" . $conn->real_escape_string($_POST['batch']) . "%'";
    }
    if (!empty($_POST['batch2'])) {
        $sql .= " AND batch2 LIKE '%" . $conn->real_escape_string($_POST['batch2']) . "%'";
    }
    if (!empty($_POST['pallet'])) {
        $sql .= " AND pallet LIKE '%" . $conn->real_escape_string($_POST['pallet']) . "%'";
    }
    if (!empty($_POST['created_at'])) {
        $sql .= " AND created_at = '" . $conn->real_escape_string($_POST['created_at']) . "'";
    }
    if (!empty($_POST['weight'])) {
        $sql .= " AND weight LIKE '%" . $conn->real_escape_string($_POST['weight']) . "%'";
    }
    if (!empty($_POST['serial_number'])) {
        $sql .= " AND serial_number LIKE '%" . $conn->real_escape_string($_POST['serial_number']) . "%'";
    }

    // Add sorting
    $sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'id';
    $sortOrder = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY " . $conn->real_escape_string($sortColumn) . " " . $sortOrder;

    $result = $conn->query($sql);
    $filteredRecords = $result ? $result->num_rows : 0;
    ?>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Bulk Delete Modal -->
    <div id="bulkDeleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Bulk Delete</h3>
            <p>Are you sure you want to delete <span id="deleteCount">0</span> selected records?</p>
            <p style="color: #dc3545; font-size: 0.9rem;">This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="btn btn-danger" onclick="confirmBulkDelete()">Delete</button>
                <button class="btn btn-secondary" onclick="closeBulkDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <div class="sync-status">🔄 Synced</div>
            <h1>Enhanced Database Management System</h1>
            <p>Advanced interface for compound data management</p>
        </div>

        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalRecords; ?></div>
                <div class="stat-label">Total Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $filteredRecords; ?></div>
                <div class="stat-label">Filtered Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo date('H:i:s'); ?></div>
                <div class="stat-label">Last Updated</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="selectedCount">0</div>
                <div class="stat-label">Selected Records</div>
            </div>
        </div>

        <div class="filter-section">
            <h2 class="filter-title">
                🔍 Filter & Search
            </h2>
            <form method="POST" action="">
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="id">ID</label>
                        <input type="text" name="id" id="id" placeholder="Enter ID" value="<?php echo isset($_POST['id']) ? htmlspecialchars($_POST['id']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="inputDate">Input Date</label>
                        <input type="date" name="inputDate" id="inputDate" value="<?php echo isset($_POST['inputDate']) ? htmlspecialchars($_POST['inputDate']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="shift">Shift</label>
                        <input type="text" name="shift" id="shift" placeholder="Enter shift" value="<?php echo isset($_POST['shift']) ? htmlspecialchars($_POST['shift']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="compound_name">Compound Name</label>
                        <input type="text" name="compound_name" id="compound_name" placeholder="Enter compound name" value="<?php echo isset($_POST['compound_name']) ? htmlspecialchars($_POST['compound_name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" name="description" id="description" placeholder="Enter description" value="<?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="cstock">CStock</label>
                        <input type="text" name="cstock" id="cstock" placeholder="Enter cstock" value="<?php echo isset($_POST['cstock']) ? htmlspecialchars($_POST['cstock']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="batch">Batch</label>
                        <input type="text" name="batch" id="batch" placeholder="Enter batch" value="<?php echo isset($_POST['batch']) ? htmlspecialchars($_POST['batch']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="batch2">Batch2</label>
                        <input type="text" name="batch2" id="batch2" placeholder="Enter batch2" value="<?php echo isset($_POST['batch2']) ? htmlspecialchars($_POST['batch2']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="pallet">Pallet</label>
                        <input type="text" name="pallet" id="pallet" placeholder="Enter pallet" value="<?php echo isset($_POST['pallet']) ? htmlspecialchars($_POST['pallet']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="weight">Weight</label>
                        <input type="text" name="weight" id="weight" placeholder="Enter weight" value="<?php echo isset($_POST['weight']) ? htmlspecialchars($_POST['weight']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="serial_number">Serial Number</label>
                        <input type="text" name="serial_number" id="serial_number" placeholder="Enter serial number" value="<?php echo isset($_POST['serial_number']) ? htmlspecialchars($_POST['serial_number']) : ''; ?>">
                    </div>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">🔍 Filter Data</button>
                    <button type="button" class="btn btn-secondary" onclick="clearFilters()">🗑️ Clear Filters</button>
                    <button type="button" class="btn btn-secondary" onclick="exportData()">📊 Export Data</button>
                    <button type="button" class="btn btn-warning" onclick="refreshData()">🔄 Refresh</button>
                </div>
            </form>
        </div>

        <div class="table-section">
            <div class="table-controls">
                <div class="left-controls">
                    <button class="btn btn-danger" onclick="showBulkDeleteModal()" id="bulkDeleteBtn" disabled>
                        🗑️ Delete Selected (<span id="selectedCountBtn">0</span>)
                    </button>
                    <button class="btn btn-info" onclick="selectAllRecords()">📋 Select All</button>
                    <button class="btn btn-secondary" onclick="clearSelection()">❌ Clear Selection</button>
                </div>
                <div class="right-controls">
                    <div class="pagination-info">
                        Showing <?php echo $filteredRecords; ?> of <?php echo $totalRecords; ?> records
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                            <th class="sortable" onclick="sortTable('id')">ID</th>
                            <th class="sortable" onclick="sortTable('inputDate')">Input Date</th>
                            <th class="sortable" onclick="sortTable('shift')">Shift</th>
                            <th class="sortable" onclick="sortTable('compound_name')">Compound Name</th>
                            <th class="sortable" onclick="sortTable('description')">Description</th>
                            <th class="sortable" onclick="sortTable('cstock')">CStock</th>
                            <th class="sortable" onclick="sortTable('batch')">Batch</th>
                            <th class="sortable" onclick="sortTable('batch2')">Batch2</th>
                            <th class="sortable" onclick="sortTable('pallet')">Pallet</th>
                            <th class="sortable" onclick="sortTable('created_at')">Created At</th>
                            <th class="sortable" onclick="sortTable('weight')">Weight</th>
                            <th class="sortable" onclick="sortTable('serial_number')">Serial Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr data-id='" . $row["id"] . "'>";
                                echo "<td><input type='checkbox' class='row-checkbox' value='" . $row["id"] . "' onchange='updateSelection()'></td>";
                                echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                echo "<td><input type='date' name='inputDate' value='" . htmlspecialchars($row["inputDate"]) . "' onchange='updateRecord(" . $row["id"] . ", \"inputDate\", this.value)'></td>";
                                echo "<td><input type='text' name='shift' value='" . htmlspecialchars($row["shift"]) . "' onchange='updateRecord(" . $row["id"] . ", \"shift\", this.value)'></td>";
                                echo "<td><input type='text' name='compound_name' value='" . htmlspecialchars($row["compound_name"]) . "' onchange='updateRecord(" . $row["id"] . ", \"compound_name\", this.value)'></td>";
                                echo "<td><input type='text' name='description' value='" . htmlspecialchars($row["description"]) . "' onchange='updateRecord(" . $row["id"] . ", \"description\", this.value)'></td>";
                                echo "<td><input type='text' name='cstock' value='" . htmlspecialchars($row["cstock"]) . "' onchange='updateRecord(" . $row["id"] . ", \"cstock\", this.value)'></td>";
                                echo "<td><input type='text' name='batch' value='" . htmlspecialchars($row["batch"]) . "' onchange='updateRecord(" . $row["id"] . ", \"batch\", this.value)'></td>";
                                echo "<td><input type='text' name='batch2' value='" . htmlspecialchars($row["batch2"]) . "' onchange='updateRecord(" . $row["id"] . ", \"batch2\", this.value)'></td>";
                                echo "<td><input type='text' name='pallet' value='" . htmlspecialchars($row["pallet"]) . "' onchange='updateRecord(" . $row["id"] . ", \"pallet\", this.value)'></td>";
                                echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>";
                                echo "<td><input type='text' name='weight' value='" . htmlspecialchars($row["weight"]) . "' onchange='updateRecord(" . $row["id"] . ", \"weight\", this.value)'></td>";
                                echo "<td><input type='text' name='serial_number' value='" . htmlspecialchars($row["serial_number"]) . "' onchange='updateRecord(" . $row["id"] . ", \"serial_number\", this.value)'></td>";
                                echo "<td class='actions-cell'>";
                                echo "<button class='btn btn-danger' onclick='deleteRecord(" . $row["id"] . ")'>Delete</button>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='14' class='no-results'>No records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Show loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        // Update record via AJAX
        function updateRecord(id, column, value) {
            showLoading();
            const input = event.target;
            input.classList.add('changed');

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=edit&id=${id}&column=${column}&value=${encodeURIComponent(value)}`
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                input.classList.remove('changed');
                showMessage(data.message, data.status);
            })
            .catch(error => {
                hideLoading();
                input.classList.remove('changed');
                showMessage('Error updating record.', 'error');
            });
        }

        // Delete record via AJAX
        function deleteRecord(id) {
            if (!confirm('Are you sure you want to delete this record?')) return;
            showLoading();

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                showMessage(data.message, data.status);
                if (data.status === 'success') {
                    document.querySelector(`tr[data-id="${id}"]`).remove();
                    updateStats();
                }
            })
            .catch(error => {
                hideLoading();
                showMessage('Error deleting record.', 'error');
            });
        }

        // Show bulk delete modal
        function showBulkDeleteModal() {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) return;
            document.getElementById('deleteCount').textContent = selectedIds.length;
            document.getElementById('bulkDeleteModal').style.display = 'block';
        }

        // Close bulk delete modal
        function closeBulkDeleteModal() {
            document.getElementById('bulkDeleteModal').style.display = 'none';
        }

        // Confirm bulk delete
        function confirmBulkDelete() {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) {
                closeBulkDeleteModal();
                return;
            }

            showLoading();
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=bulk_delete&ids=${encodeURIComponent(JSON.stringify(selectedIds))}`
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                closeBulkDeleteModal();
                showMessage(data.message, data.status);
                if (data.status === 'success') {
                    selectedIds.forEach(id => {
                        document.querySelector(`tr[data-id="${id}"]`).remove();
                    });
                    updateSelection();
                    updateStats();
                }
            })
            .catch(error => {
                hideLoading();
                closeBulkDeleteModal();
                showMessage('Error deleting records.', 'error');
            });
        }

        // Get selected record IDs
        function getSelectedIds() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            return Array.from(checkboxes).map(cb => cb.value);
        }

        // Update selection count and button state
        function updateSelection() {
            const selectedIds = getSelectedIds();
            const count = selectedIds.length;
            document.getElementById('selectedCount').textContent = count;
            document.getElementById('selectedCountBtn').textContent = count;
            document.getElementById('bulkDeleteBtn').disabled = count === 0;
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                const row = cb.closest('tr');
                if (cb.checked) {
                    row.classList.add('selected');
                } else {
                    row.classList.remove('selected');
                }
            });
        }

        // Select all records
        function selectAllRecords() {
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.checked = true;
                cb.closest('tr').classList.add('selected');
            });
            updateSelection();
        }

        // Clear selection
        function clearSelection() {
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.checked = false;
                cb.closest('tr').classList.remove('selected');
            });
            updateSelection();
        }

        // Toggle select all checkbox
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            if (selectAll.checked) {
                selectAllRecords();
            } else {
                clearSelection();
            }
        }

        // Clear filters
        function clearFilters() {
            document.querySelectorAll('.filter-grid input').forEach(input => {
                input.value = '';
            });
            document.forms[0].submit();
        }

        // Refresh data
        function refreshData() {
            window.location.reload();
        }

        // Export data as CSV
        function exportData() {
            const headers = ['ID', 'Input Date', 'Shift', 'Compound Name', 'Description', 'CStock', 'Batch', 'Batch2', 'Pallet', 'Created At', 'Weight', 'Serial Number'];
            const rows = Array.from(document.querySelectorAll('tbody tr')).map(row => {
                return Array.from(row.querySelectorAll('td')).slice(1, -1).map(cell => {
                    const input = cell.querySelector('input');
                    return input ? `"${input.value}"` : `"${cell.textContent.trim()}"`;
                }).join(',');
            });
            const csvContent = headers.join(',') + '\n' + rows.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'compound_data.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Show message
        function showMessage(message, type) {
            const existingMessage = document.querySelector('.status-message');
            if (existingMessage) existingMessage.remove();

            const messageDiv = document.createElement('div');
            messageDiv.className = `status-message ${type}`;
            messageDiv.innerHTML = `
                <span class="icon">
                    ${type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️'}
                </span>
                ${message}
            `;
            document.querySelector('.container').insertBefore(messageDiv, document.querySelector('.stats-section'));
            setTimeout(() => messageDiv.remove(), 5000);
        }

        // Update stats (for filtered and total records)
        function updateStats() {
            const filteredCount = document.querySelectorAll('tbody tr').length;
            document.querySelector('.stat-card:nth-child(2) .stat-number').textContent = filteredCount;
            document.querySelector('.pagination-info').textContent = `Showing ${filteredCount} of <?php echo $totalRecords; ?> records`;
        }

        // Sort table
        function sortTable(column) {
            const currentSort = '<?php echo $sortColumn; ?>';
            const currentOrder = '<?php echo $sortOrder; ?>';
            const newOrder = (column === currentSort && currentOrder === 'ASC') ? 'desc' : 'asc';
            window.location.href = `?sort=${column}&order=${newOrder}`;
        }

        // Initialize table sorting indicators
        document.addEventListener('DOMContentLoaded', () => {
            const sortColumn = '<?php echo $sortColumn; ?>';
            const sortOrder = '<?php echo $sortOrder; ?>';
            const th = document.querySelector(`th[onclick="sortTable('${sortColumn}')"]`);
            if (th) {
                th.classList.add(sortOrder === 'ASC' ? 'sort-asc' : 'sort-desc');
            }
        });
    </script>

    <?php $conn->close(); ?>
</body>
</html>