<?php
/**
 * Complete Attendance Data Processing System
 * Captures ALL attendance data without any filtering or data loss
 * Updated with Corrected Employee Number Formatting Rules
 */

class CompleteAttendanceProcessor {
    private $pdo;
    
    public function __construct($host = 'localhost', $dbname = 'planatir_task_managemen', $username = 'planatir_task_managemen', $password = 'Bishan@1919') {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        } 
    }
    
    /**
     * Format employee number according to business rules
     * - Numbers starting with "30": extract digits after "30" and remove ALL leading zeros (e.g., 30000004 -> 4, 30000037 -> 37, 30000401 -> 401)
     * - Numbers starting with "31": extract digits after "31", remove ALL leading zeros, and add "M-" prefix (e.g., 31000006 -> M-6, 31000045 -> M-45, 31000344 -> M-344)
     * - Numbers starting with "4" (but not "40"): add "M-" prefix (e.g., 4001 -> M-4001)
     */
    private function formatEmployeeNumber($employeeNo) {
        // Remove any existing whitespace
        $employeeNo = trim($employeeNo);
        
        // Check if starts with "30"
        if (substr($employeeNo, 0, 2) === '30') {
            // Extract everything after "30"
            $afterThirty = substr($employeeNo, 2);
            
            // Remove ALL leading zeros from the front
            $cleanNumber = ltrim($afterThirty, '0');
            
            // If result is empty (was all zeros), return '0'
            return $cleanNumber !== '' ? $cleanNumber : '0';
        }
        
        // Check if starts with "31" - CORRECTED LOGIC
        if (substr($employeeNo, 0, 2) === '31') {
            // Extract everything after "31"
            $afterThirtyOne = substr($employeeNo, 2);
            
            // Remove ALL leading zeros from the front
            $cleanNumber = ltrim($afterThirtyOne, '0');
            
            // If result is empty (was all zeros), use '0'
            $finalNumber = $cleanNumber !== '' ? $cleanNumber : '0';
            
            // Add "M-" prefix
            return 'M-' . $finalNumber;
        }
        
        // Check if starts with "4" (but not "40" to avoid conflicts)
        if (substr($employeeNo, 0, 1) === '4' && substr($employeeNo, 0, 2) !== '40') {
            return 'M-' . $employeeNo;
        }
        
        // Return original if no rules apply
        return $employeeNo;
    }
    
    /**
     * Create the complete processed attendance table with all time entries
     */
    public function createProcessedTable() {
        // Main processed attendance table
        $sql = "CREATE TABLE IF NOT EXISTS processed_attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            department VARCHAR(100) NOT NULL,
            name VARCHAR(100) NOT NULL,
            employee_no VARCHAR(50) NOT NULL,
            original_employee_no VARCHAR(50) NOT NULL,
            attendance_date DATE NOT NULL,
            first_entry_time TIME,
            last_entry_time TIME,
            total_entries INT DEFAULT 0,
            total_work_hours DECIMAL(6,2) DEFAULT 0,
            total_break_hours DECIMAL(6,2) DEFAULT 0,
            net_work_hours DECIMAL(6,2) DEFAULT 0,
            all_time_entries TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
        
        // Detailed time entries table to store ALL individual clock-ins/outs
        $sql2 = "CREATE TABLE IF NOT EXISTS detailed_time_entries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            department VARCHAR(100) NOT NULL,
            name VARCHAR(100) NOT NULL,
            employee_no VARCHAR(50) NOT NULL,
            original_employee_no VARCHAR(50) NOT NULL,
            attendance_date DATE NOT NULL,
            entry_time TIME NOT NULL,
            entry_datetime DATETIME NOT NULL,
            entry_sequence INT NOT NULL,
            entry_type ENUM('IN', 'OUT', 'UNKNOWN') DEFAULT 'UNKNOWN',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql2);
        
        return "Complete attendance tables created successfully with corrected employee number formatting support!";
    }
    
    /**
     * Get ALL attendance records without any filtering
     */
    public function getAllAttendanceRecords($startDate = null, $endDate = null) {
        $whereClause = "";
        $params = [];
        
        // Only apply date filter if BOTH dates are explicitly provided
        if (!empty($startDate) && !empty($endDate)) {
            $whereClause = "WHERE DATE(date_time) BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        
        $sql = "SELECT department, name, employee_no, 
                       DATE(date_time) as attendance_date,
                       TIME(date_time) as time_only, 
                       date_time,
                       HOUR(date_time) as hour_24, 
                       MINUTE(date_time) as minute_val, 
                       SECOND(date_time) as second_val
                FROM attendance_records 
                $whereClause
                ORDER BY employee_no, DATE(date_time), date_time";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div style='background-color: #e7f3ff; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>Data Capture Status:</strong> Retrieved " . count($records) . " total attendance records";
        if (!empty($startDate) && !empty($endDate)) {
            echo " (filtered from $startDate to $endDate)";
        } else {
            echo " (ALL records - no date filtering applied)";
        }
        echo "</div>";
        
        return $records;
    }
    
    /**
     * Process ALL attendance data with complete time tracking and corrected employee number formatting
     */
    public function processAllAttendanceData($startDate = null, $endDate = null) {
        $records = $this->getAllAttendanceRecords($startDate, $endDate);
        
        if (empty($records)) {
            return [];
        }
        
        // Group by employee_no and date for unique identification
        $grouped = [];
        
        foreach ($records as $record) {
            $originalEmployeeNo = $record['employee_no'];
            $formattedEmployeeNo = $this->formatEmployeeNumber($originalEmployeeNo);
            
            $key = $formattedEmployeeNo . '_' . $record['attendance_date'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'department' => $record['department'],
                    'name' => $record['name'],
                    'employee_no' => $formattedEmployeeNo,
                    'original_employee_no' => $originalEmployeeNo,
                    'attendance_date' => $record['attendance_date'],
                    'times' => [],
                    'datetimes' => []
                ];
            }
            
            // Convert to 24-hour format
            $time24h = sprintf('%02d:%02d:%02d', 
                $record['hour_24'], 
                $record['minute_val'], 
                $record['second_val']
            );
            
            $grouped[$key]['times'][] = $time24h;
            $grouped[$key]['datetimes'][] = $record['date_time'];
        }
        
        // Process each group with complete time analysis
        $processed = [];
        foreach ($grouped as $group) {
            // Sort all times chronologically
            array_multisort($group['datetimes'], $group['times']);
            
            $totalEntries = count($group['times']);
            $firstEntry = $group['times'][0];
            $lastEntry = end($group['times']);
            
            // Calculate work hours considering all entries
            $workHours = $this->calculateCompleteWorkHours($group['times']);
            $breakHours = $this->calculateBreakHours($group['times']);
            $netWorkHours = max(0, $workHours - $breakHours);
            
            // Determine entry types (IN/OUT pattern)
            $entryTypes = $this->determineEntryTypes($group['times']);
            
            $processed[] = [
                'department' => $group['department'],
                'name' => $group['name'],
                'employee_no' => $group['employee_no'],
                'original_employee_no' => $group['original_employee_no'],
                'attendance_date' => $group['attendance_date'],
                'first_entry_time' => $firstEntry,
                'last_entry_time' => $lastEntry,
                'total_entries' => $totalEntries,
                'total_work_hours' => $workHours,
                'total_break_hours' => $breakHours,
                'net_work_hours' => $netWorkHours,
                'all_times' => $group['times'],
                'all_datetimes' => $group['datetimes'],
                'entry_types' => $entryTypes,
                'time_entries_json' => json_encode($group['times'])
            ];
        }
        
        echo "<div style='background-color: #d1ecf1; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>Processing Complete:</strong> Processed " . count($processed) . " employee-date combinations with complete time tracking and corrected formatted employee numbers";
        echo "</div>";
        
        return $processed;
    }
    
    /**
     * Calculate complete work hours considering all time entries
     */
    private function calculateCompleteWorkHours($times) {
        if (count($times) < 2) {
            return 0; // Need at least 2 entries to calculate work time
        }
        
        $totalWorkMinutes = 0;
        
        // Process pairs: IN-OUT, IN-OUT, etc.
        for ($i = 0; $i < count($times) - 1; $i += 2) {
            if (isset($times[$i + 1])) {
                $startTime = new DateTime($times[$i]);
                $endTime = new DateTime($times[$i + 1]);
                
                // Handle overnight shifts
                if ($endTime < $startTime) {
                    $endTime->add(new DateInterval('P1D'));
                }
                
                $interval = $startTime->diff($endTime);
                $minutes = ($interval->h * 60) + $interval->i + ($interval->s / 60);
                $totalWorkMinutes += $minutes;
            }
        }
        
        return round($totalWorkMinutes / 60, 2);
    }
    
    /**
     * Calculate break hours between work sessions
     */
    private function calculateBreakHours($times) {
        if (count($times) < 4) {
            return 0; // Need at least 4 entries to have breaks (IN-OUT-IN-OUT)
        }
        
        $totalBreakMinutes = 0;
        
        // Calculate breaks between OUT and next IN
        for ($i = 1; $i < count($times) - 1; $i += 2) {
            if (isset($times[$i + 1])) {
                $breakStart = new DateTime($times[$i]);     // OUT time
                $breakEnd = new DateTime($times[$i + 1]);   // Next IN time
                
                $interval = $breakStart->diff($breakEnd);
                $minutes = ($interval->h * 60) + $interval->i + ($interval->s / 60);
                $totalBreakMinutes += $minutes;
            }
        }
        
        return round($totalBreakMinutes / 60, 2);
    }
    
    /**
     * Determine if each entry is IN or OUT based on sequence
     */
    private function determineEntryTypes($times) {
        $types = [];
        for ($i = 0; $i < count($times); $i++) {
            // Assume alternating pattern: IN, OUT, IN, OUT...
            $types[] = ($i % 2 == 0) ? 'IN' : 'OUT';
        }
        return $types;
    }
    
    /**
     * Save ALL processed data to both summary and detailed tables
     */
    public function saveAllProcessedData($processedData) {
        // Clear existing data to prevent duplicates
        $this->pdo->exec("DELETE FROM processed_attendance");
        $this->pdo->exec("DELETE FROM detailed_time_entries");
        
        // Save to main processed table
        $sql = "INSERT INTO processed_attendance 
                (department, name, employee_no, original_employee_no, attendance_date, first_entry_time, last_entry_time, 
                 total_entries, total_work_hours, total_break_hours, net_work_hours, all_time_entries)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        // Save to detailed entries table
        $detailSql = "INSERT INTO detailed_time_entries 
                      (department, name, employee_no, original_employee_no, attendance_date, entry_time, entry_datetime, entry_sequence, entry_type)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $detailStmt = $this->pdo->prepare($detailSql);
        
        $savedCount = 0;
        $detailCount = 0;
        
        foreach ($processedData as $record) {
            // Save main record
            if ($stmt->execute([
                $record['department'],
                $record['name'],
                $record['employee_no'],
                $record['original_employee_no'],
                $record['attendance_date'],
                $record['first_entry_time'],
                $record['last_entry_time'],
                $record['total_entries'],
                $record['total_work_hours'],
                $record['total_break_hours'],
                $record['net_work_hours'],
                $record['time_entries_json']
            ])) {
                $savedCount++;
            }
            
            // Save all detailed time entries
            for ($i = 0; $i < count($record['all_times']); $i++) {
                if ($detailStmt->execute([
                    $record['department'],
                    $record['name'],
                    $record['employee_no'],
                    $record['original_employee_no'],
                    $record['attendance_date'],
                    $record['all_times'][$i],
                    $record['all_datetimes'][$i],
                    $i + 1,
                    $record['entry_types'][$i]
                ])) {
                    $detailCount++;
                }
            }
        }
        
        return [
            'summary_records' => $savedCount,
            'detailed_entries' => $detailCount
        ];
    }
    
    /**
     * Get complete attendance report with all data
     */
    public function getCompleteAttendanceReport($startDate = null, $endDate = null) {
        $whereClause = "";
        $params = [];
        
        if (!empty($startDate) && !empty($endDate)) {
            $whereClause = "WHERE attendance_date BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        
        $sql = "SELECT * FROM processed_attendance $whereClause ORDER BY attendance_date DESC, employee_no";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get detailed time entries for specific employee/date
     */
    public function getDetailedTimeEntries($employeeNo = null, $date = null) {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($employeeNo)) {
            $whereClause .= " AND (employee_no = ? OR original_employee_no = ?)";
            $params[] = $employeeNo;
            $params[] = $employeeNo;
        }
        
        if (!empty($date)) {
            $whereClause .= " AND attendance_date = ?";
            $params[] = $date;
        }
        
        $sql = "SELECT * FROM detailed_time_entries $whereClause ORDER BY attendance_date, entry_sequence";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Export complete data to CSV with all time entries and corrected formatted employee numbers
     */
    public function exportCompleteDataToCSV($filename, $startDate = null, $endDate = null) {
        $data = $this->getCompleteAttendanceReport($startDate, $endDate);
        $headers = [
            'Department', 'Employee Name', 'Formatted Employee No', 'Original Employee No', 'Date', 
            'First Entry', 'Last Entry', 'Total Entries', 'Total Work Hours',
            'Break Hours', 'Net Work Hours', 'All Time Entries'
        ];
        
        $file = fopen($filename, 'w');
        
        // Write header
        fputcsv($file, $headers);
        
        // Write data
        foreach ($data as $row) {
            $allTimes = json_decode($row['all_time_entries'], true);
            $timeString = is_array($allTimes) ? implode(', ', $allTimes) : $row['all_time_entries'];
            
            fputcsv($file, [
                $row['department'],
                $row['name'],
                $row['employee_no'],
                $row['original_employee_no'],
                $row['attendance_date'],
                $row['first_entry_time'] ?: 'N/A',
                $row['last_entry_time'] ?: 'N/A',
                $row['total_entries'],
                $row['total_work_hours'],
                $row['total_break_hours'],
                $row['net_work_hours'],
                $timeString
            ]);
        }
        
        fclose($file);
        return "Complete data exported to $filename with corrected formatted employee numbers";
    }
}

// Usage Example - Captures ALL Data with Corrected Employee Number Formatting
try {
    // Initialize processor
    $processor = new CompleteAttendanceProcessor();
    
    // Create tables
    echo $processor->createProcessedTable() . "<br><br>";
    
    // Process ALL attendance data with corrected employee number formatting
    echo "<h3>Complete Data Processing - ALL Records Captured with Corrected Employee Number Formatting</h3>";
    $completeProcessed = $processor->processAllAttendanceData();
    
    if (!empty($completeProcessed)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
        echo "<tr style='background-color: #f2f2f2;'>";
        echo "<th>Formatted Employee No</th><th>Original Employee No</th><th>Name</th><th>Department</th><th>Date</th>";
        echo "<th>First Entry</th><th>Last Entry</th><th>Total Entries</th>";
        echo "<th>Work Hours</th><th>Break Hours</th><th>Net Hours</th><th>All Times</th>";
        echo "</tr>";
        
        foreach ($completeProcessed as $record) {
            $allTimesDisplay = implode(', ', array_slice($record['all_times'], 0, 5)); // Show first 5 times
            if (count($record['all_times']) > 5) {
                $allTimesDisplay .= '... +' . (count($record['all_times']) - 5) . ' more';
            }
            
            // Highlight changed employee numbers
            $empNoStyle = $record['employee_no'] !== $record['original_employee_no'] ? 
                'background-color: #fff3cd; font-weight: bold;' : '';
            
            echo "<tr>";
            echo "<td style='$empNoStyle'><strong>{$record['employee_no']}</strong></td>";
            echo "<td>{$record['original_employee_no']}</td>";
            echo "<td>{$record['name']}</td>";
            echo "<td>{$record['department']}</td>";
            echo "<td>{$record['attendance_date']}</td>";
            echo "<td>{$record['first_entry_time']}</td>";
            echo "<td>{$record['last_entry_time']}</td>";
            echo "<td><strong>{$record['total_entries']}</strong></td>";
            echo "<td>{$record['total_work_hours']}</td>";
            echo "<td>{$record['total_break_hours']}</td>";
            echo "<td><strong>{$record['net_work_hours']}</strong></td>";
            echo "<td title='" . implode(', ', $record['all_times']) . "'>{$allTimesDisplay}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // Save all processed data
        $saveResult = $processor->saveAllProcessedData($completeProcessed);
        echo "<div style='background-color: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>Data Saved Successfully:</strong><br>";
        echo "• Summary Records: {$saveResult['summary_records']}<br>";
        echo "• Detailed Time Entries: {$saveResult['detailed_entries']}<br>";
        echo "</div>";
        
        // Export complete data
        echo $processor->exportCompleteDataToCSV('complete_attendance_report_corrected_formatted.csv') . "<br><br>";
        
        // Show sample detailed entries
        if (!empty($completeProcessed)) {
            $sampleEmployee = $completeProcessed[0]['employee_no'];
            $sampleDate = $completeProcessed[0]['attendance_date'];
            $detailedEntries = $processor->getDetailedTimeEntries($sampleEmployee, $sampleDate);
            
            if (!empty($detailedEntries)) {
                echo "<h4>Sample Detailed Time Entries (Employee: $sampleEmployee, Date: $sampleDate)</h4>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr style='background-color: #f2f2f2;'>";
                echo "<th>Sequence</th><th>Entry Time</th><th>Type</th><th>Formatted Emp No</th><th>Original Emp No</th><th>Full DateTime</th>";
                echo "</tr>";
                
                foreach ($detailedEntries as $entry) {
                    $typeColor = $entry['entry_type'] == 'IN' ? 'color: green;' : 'color: red;';
                    $empNoStyle = $entry['employee_no'] !== $entry['original_employee_no'] ? 
                        'background-color: #fff3cd;' : '';
                    
                    echo "<tr>";
                    echo "<td>{$entry['entry_sequence']}</td>";
                    echo "<td>{$entry['entry_time']}</td>";
                    echo "<td style='$typeColor'><strong>{$entry['entry_type']}</strong></td>";
                    echo "<td style='$empNoStyle'><strong>{$entry['employee_no']}</strong></td>";
                    echo "<td>{$entry['original_employee_no']}</td>";
                    echo "<td>{$entry['entry_datetime']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        
    } else {
        echo "<div style='background-color: #fff3cd; padding: 10px; border-radius: 5px;'>";
        echo "No attendance records found in the database.";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}

?>