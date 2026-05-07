<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compound Production Details - Enhanced</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f0f0f0 0%, #e8e8e8 100%);
            font-family: 'Cantarell', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, #F28018, #ff9a3d);
            border-radius: 2px;
        }

        h1 {
            color: #F28018;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(242, 128, 24, 0.2);
        }

        .subtitle {
            color: #666;
            font-size: 1.1rem;
            font-weight: 400;
        }

        .controls-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-field {
            position: relative;
        }

        .search-field input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fff;
        }

        .search-field input:focus {
            outline: none;
            border-color: #F28018;
            box-shadow: 0 0 0 3px rgba(242, 128, 24, 0.1);
            transform: translateY(-1px);
        }

        .search-field input::placeholder {
            color: #999;
        }

        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .records-per-page {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .records-per-page select {
            padding: 8px 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            background: #fff;
            font-size: 14px;
            cursor: pointer;
        }

        .records-per-page select:focus {
            outline: none;
            border-color: #F28018;
        }

        .table-info {
            color: #666;
            font-size: 14px;
        }

        .table-wrapper {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        thead {
            background: linear-gradient(135deg, #F28018 0%, #ff9a3d 100%);
            color: #fff;
        }

        thead th {
            padding: 18px 12px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 12px;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }

        thead th:last-child {
            border-right: none;
        }

        tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.001);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        tbody tr:nth-child(even):hover {
            background-color: #f1f3f4;
        }

        tbody td {
            padding: 14px 12px;
            vertical-align: middle;
            border-right: 1px solid #f1f3f4;
        }

        tbody td:last-child {
            border-right: none;
        }

        .pagination-wrapper {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }

        .pagination {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .pagination button {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            background: #fff;
            color: #666;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            min-width: 44px;
        }

        .pagination button:hover:not(:disabled) {
            border-color: #F28018;
            color: #F28018;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(242, 128, 24, 0.2);
        }

        .pagination button.active {
            background: linear-gradient(135deg, #F28018 0%, #ff9a3d 100%);
            color: #fff;
            border-color: #F28018;
            box-shadow: 0 4px 12px rgba(242, 128, 24, 0.3);
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-info {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 16px;
        }

        .no-results::before {
            content: "🔍";
            display: block;
            font-size: 48px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 20px;
            }

            h1 {
                font-size: 2rem;
            }

            .search-grid {
                grid-template-columns: 1fr;
            }

            .table-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .table-wrapper {
                overflow-x: auto;
            }

            table {
                min-width: 1000px;
            }

            .pagination-wrapper {
                flex-direction: column;
                gap: 15px;
            }
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #F28018;
            font-size: 16px;
        }

        .loading::before {
            content: "⏳";
            display: block;
            font-size: 32px;
            margin-bottom: 15px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Compound Production Details</h1>
            <p class="subtitle">Advanced Data Management System</p>
        </div>

        <div class="controls-section">
            <div class="search-grid">
                <div class="search-field">
                    <input type="text" id="inputDateFilter" placeholder="🗓️ Search by Input Date...">
                </div>
                <div class="search-field">
                    <input type="text" id="shiftFilter" placeholder="⏰ Search by Shift...">
                </div>
                <div class="search-field">
                    <input type="text" id="compoundNameFilter" placeholder="🧪 Search by Compound Name...">
                </div>
                <div class="search-field">
                    <input type="text" id="serialNumberFilter" placeholder="🔢 Search by Serial Number...">
                </div>
                <div class="search-field">
                    <input type="text" id="palletFilter" placeholder="📦 Search by Pallet...">
                </div>
            </div>

            <div class="table-controls">
                <div class="records-per-page">
                    <label for="recordsPerPage">Show:</label>
                    <select id="recordsPerPage">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100" selected>100</option>
                        <option value="200">200</option>
                        <option value="all">All</option>
                    </select>
                    <span>records per page</span>
                </div>
                <div class="table-info" id="tableInfo">
                    Loading data...
                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <table id="dataTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Input Date</th>
                        <th>Shift</th>
                        <th>Compound Name</th>
                        <th>Description</th>
                        <th>CStock</th>
                        <th>Batch</th>
                        <th>Batch2</th>
                        <th>Pallet</th>
                        <th>Created At</th>
                        <th>Weight</th>
                        <th>Serial Number</th>
                    </tr>
                </thead>
                <tbody id="dataBody">
                    <tr><td colspan="12" class="loading">Loading data...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            <div class="pagination-info" id="paginationInfo"></div>
            <div class="pagination" id="pagination"></div>
        </div>
    </div>

    <?php
    // Database connection parameters
    $host = 'localhost';
    $username = 'planatir_task_managemen';
    $password = 'Bishan@1919';
    $database = 'planatir_task_managemen';

    $data = array();
    $error = null;

    try {
        // Create connection
        $conn = new mysqli($host, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // SQL query to fetch data from both bcompound2 and bcompound76 tables, ordered by inputDate descending
        $sql = "(
            SELECT id, inputDate, shift, compound_name, description, cstock, batch, batch2, pallet, created_at, weight, serial_number 
            FROM bcompound2
        )
        UNION ALL
        (
            SELECT id, inputDate, shift, compound_name, description, cstock, batch, batch2, pallet, created_at, weight, serial_number 
            FROM bcompound76
        )
        ORDER BY inputDate DESC";

        // Perform query
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            // Fetch all data into array
            while($row = $result->fetch_assoc()) {
                $data[] = array(
                    'id' => $row['id'],
                    'inputDate' => $row['inputDate'],
                    'shift' => $row['shift'],
                    'compound_name' => $row['compound_name'],
                    'description' => $row['description'],
                    'cstock' => $row['cstock'],
                    'batch' => $row['batch'],
                    'batch2' => $row['batch2'],
                    'pallet' => $row['pallet'],
                    'created_at' => $row['created_at'],
                    'weight' => $row['weight'],
                    'serial_number' => $row['serial_number']
                );
            }
        }

        // Close connection
        $conn->close();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
    ?>

    <script>
        // PHP data injection
        const phpData = <?php echo json_encode($data); ?>;
        const phpError = <?php echo json_encode($error); ?>;

        class CompoundTable {
            constructor(data) {
                this.originalData = data || [];
                this.filteredData = [...this.originalData];
                this.currentPage = 1;
                this.recordsPerPage = 100;
                this.init();
            }

            init() {
                if (phpError) {
                    this.showError(phpError);
                    return;
                }

                if (this.originalData.length === 0) {
                    this.showNoData();
                    return;
                }

                this.setupEventListeners();
                this.render();
            }

            showError(error) {
                document.getElementById('dataBody').innerHTML = `
                    <tr><td colspan="12" class="error-message">
                        <strong>Database Error:</strong> ${error}
                    </td></tr>
                `;
                document.getElementById('tableInfo').textContent = 'Error loading data';
            }

            showNoData() {
                document.getElementById('dataBody').innerHTML = `
                    <tr><td colspan="12" class="no-results">
                        No data found in the database
                    </td></tr>
                `;
                document.getElementById('tableInfo').textContent = 'No records found';
            }

            setupEventListeners() {
                // Search filters
                const filters = ['inputDateFilter', 'shiftFilter', 'compoundNameFilter', 'serialNumberFilter', 'palletFilter'];
                filters.forEach(filterId => {
                    document.getElementById(filterId).addEventListener('input', this.debounce(() => this.applyFilters(), 300));
                });

                // Records per page
                document.getElementById('recordsPerPage').addEventListener('change', (e) => {
                    this.recordsPerPage = e.target.value === 'all' ? this.filteredData.length : parseInt(e.target.value);
                    this.currentPage = 1;
                    this.render();
                });
            }

            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            applyFilters() {
                const filters = {
                    inputDate: document.getElementById('inputDateFilter').value.toLowerCase(),
                    shift: document.getElementById('shiftFilter').value.toLowerCase(),
                    compoundName: document.getElementById('compoundNameFilter').value.toLowerCase(),
                    serialNumber: document.getElementById('serialNumberFilter').value.toLowerCase(),
                    pallet: document.getElementById('palletFilter').value.toLowerCase()
                };

                this.filteredData = this.originalData.filter(row => {
                    return (!filters.inputDate || (row.inputDate && row.inputDate.toLowerCase().includes(filters.inputDate))) &&
                           (!filters.shift || (row.shift && row.shift.toLowerCase().includes(filters.shift))) &&
                           (!filters.compoundName || (row.compound_name && row.compound_name.toLowerCase().includes(filters.compoundName))) &&
                           (!filters.serialNumber || (row.serial_number && row.serial_number.toLowerCase().includes(filters.serialNumber))) &&
                           (!filters.pallet || (row.pallet && row.pallet.toLowerCase().includes(filters.pallet)));
                });

                this.currentPage = 1;
                this.render();
            }

            render() {
                this.renderTable();
                this.renderPagination();
                this.updateInfo();
            }

            renderTable() {
                const tbody = document.getElementById('dataBody');
                const startIndex = (this.currentPage - 1) * this.recordsPerPage;
                const endIndex = this.recordsPerPage === this.filteredData.length ? this.filteredData.length : startIndex + this.recordsPerPage;
                const pageData = this.filteredData.slice(startIndex, endIndex);

                if (pageData.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="12" class="no-results">No matching records found</td></tr>';
                    return;
                }

                tbody.innerHTML = pageData.map(row => `
                    <tr>
                        <td>${row.id || ''}</td>
                        <td>${row.inputDate || ''}</td>
                        <td>${row.shift || ''}</td>
                        <td>${row.compound_name || ''}</td>
                        <td>${row.description || ''}</td>
                        <td>${row.cstock || ''}</td>
                        <td>${row.batch || ''}</td>
                        <td>${row.batch2 || ''}</td>
                        <td>${row.pallet || ''}</td>
                        <td>${row.created_at ? new Date(row.created_at).toLocaleString() : ''}</td>
                        <td>${row.weight || ''}</td>
                        <td>${row.serial_number || ''}</td>
                    </tr>
                `).join('');
            }

            renderPagination() {
                const totalPages = Math.ceil(this.filteredData.length / this.recordsPerPage);
                const pagination = document.getElementById('pagination');
                
                if (totalPages <= 1) {
                    pagination.innerHTML = '';
                    return;
                }

                let paginationHTML = '';

                // Previous button
                paginationHTML += `<button ${this.currentPage === 1 ? 'disabled' : ''} onclick="table.goToPage(${this.currentPage - 1})">‹</button>`;

                // Page numbers
                const maxVisiblePages = 7;
                let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2));
                let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

                if (endPage - startPage + 1 < maxVisiblePages) {
                    startPage = Math.max(1, endPage - maxVisiblePages + 1);
                }

                if (startPage > 1) {
                    paginationHTML += `<button onclick="table.goToPage(1)">1</button>`;
                    if (startPage > 2) {
                        paginationHTML += `<button disabled>...</button>`;
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    paginationHTML += `<button class="${i === this.currentPage ? 'active' : ''}" onclick="table.goToPage(${i})">${i}</button>`;
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        paginationHTML += `<button disabled>...</button>`;
                    }
                    paginationHTML += `<button onclick="table.goToPage(${totalPages})">${totalPages}</button>`;
                }

                // Next button
                paginationHTML += `<button ${this.currentPage === totalPages ? 'disabled' : ''} onclick="table.goToPage(${this.currentPage + 1})">›</button>`;

                pagination.innerHTML = paginationHTML;
            }

            updateInfo() {
                const startRecord = (this.currentPage - 1) * this.recordsPerPage + 1;
                const endRecord = Math.min(this.currentPage * this.recordsPerPage, this.filteredData.length);
                
                document.getElementById('tableInfo').textContent = 
                    `Showing ${startRecord}-${endRecord} of ${this.filteredData.length} records (${this.originalData.length} total)`;
                
                const totalPages = Math.ceil(this.filteredData.length / this.recordsPerPage);
                document.getElementById('paginationInfo').textContent = 
                    totalPages > 0 ? `Page ${this.currentPage} of ${totalPages}` : '';
            }

            goToPage(page) {
                const totalPages = Math.ceil(this.filteredData.length / this.recordsPerPage);
                if (page >= 1 && page <= totalPages) {
                    this.currentPage = page;
                    this.render();
                    // Smooth scroll to top of table
                    document.querySelector('.table-wrapper').scrollIntoView({ behavior: 'smooth' });
                }
            }
        }

        // Initialize the table when page loads
        let table;
        document.addEventListener('DOMContentLoaded', function() {
            table = new CompoundTable(phpData);
        });
    </script>
</body>
</html>