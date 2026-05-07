<?php
// Start session to persist filter state
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone to Asia/Colombo
date_default_timezone_set('Asia/Colombo');

// Database configuration
$host = 'localhost';
$dbname = 'planatir_task_managemen';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

// Initialize variables
$searchResult = [];
$errorMessage = '';
$successMessage = '';
$selectedPress = '';
$selectedIcode = '';
$selectedMoldId = '';
$cavityNamesCache = [];
$availableCavities = [];
$additionalMoldIds = [];
$erpHeaders = [];
$nonMatchingRecords = [];

// Handle session for filters to persist across refreshes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['press_select'])) {
    $_SESSION['press_select'] = trim($_POST['press_select'] ?? '');
    $_SESSION['icode_select'] = trim($_POST['icode_select'] ?? '');
    $_SESSION['mold_id_select'] = trim($_POST['mold_id_select'] ?? '');
}
$selectedPress = $_SESSION['press_select'] ?? '';
$selectedIcode = $_SESSION['icode_select'] ?? '';
$selectedMoldId = $_SESSION['mold_id_select'] ?? '';

// Function to create PDO connection with error handling
function createPDOConnection($host, $dbname, $username, $password) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

// Ensure process table schema has required columns
function ensureProcessTableSchema($pdo) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM process LIKE 'tobe'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE process ADD COLUMN tobe DECIMAL(10,2) DEFAULT 0");
        }
        $stmt = $pdo->query("SHOW COLUMNS FROM process LIKE 'press_name'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE process ADD COLUMN press_name VARCHAR(255)");
        }
        $stmt = $pdo->query("SHOW COLUMNS FROM process LIKE 'start_date'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE process ADD COLUMN start_date DATETIME DEFAULT NULL");
        }
        $stmt = $pdo->query("SHOW COLUMNS FROM process LIKE 'end_date'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE process ADD COLUMN end_date DATETIME DEFAULT NULL");
        }
    } catch (Exception $e) {
        error_log("Error ensuring process table schema: " . $e->getMessage());
    }
}

// Handle save start/end dates
if ($_POST && isset($_POST['save_start_dates'])) {
    try {
        $pdo = createPDOConnection($host, $dbname, $username, $password);
        foreach ($_POST['start_date'] as $selectionId => $erps) {
            $icode = $_POST['icode'][$selectionId] ?? '';
            $moldId = $_POST['mold_id'][$selectionId] ?? '';
            if (empty($icode) || empty($moldId)) continue;
            foreach ($erps as $erp => $newStartDate) {
                if (!empty($newStartDate)) {
                    $newStartDate = str_replace('T', ' ', $newStartDate) . ':00';
                    // Update plannew1
                    $stmt = $pdo->prepare("UPDATE plannew1 SET start_date = ? WHERE icode = ? AND erp = ?");
                    $stmt->execute([$newStartDate, $icode, $erp]);
                    // Update process
                    $stmt = $pdo->prepare("UPDATE process SET start_date = ? WHERE icode = ? AND erp = ? AND mold_id = ?");
                    $stmt->execute([$newStartDate, $icode, $erp, $moldId]);
                }
            }
        }
        foreach ($_POST['end_date'] as $selectionId => $erps) {
            $icode = $_POST['icode'][$selectionId] ?? '';
            $moldId = $_POST['mold_id'][$selectionId] ?? '';
            if (empty($icode) || empty($moldId)) continue;
            foreach ($erps as $erp => $newEndDate) {
                if (!empty($newEndDate)) {
                    $newEndDate = str_replace('T', ' ', $newEndDate) . ':00';
                    // Update plannew1
                    $stmt = $pdo->prepare("UPDATE plannew1 SET end_date = ? WHERE icode = ? AND erp = ?");
                    $stmt->execute([$newEndDate, $icode, $erp]);
                    // Update process
                    $stmt = $pdo->prepare("UPDATE process SET end_date = ? WHERE icode = ? AND erp = ? AND mold_id = ?");
                    $stmt->execute([$newEndDate, $icode, $erp, $moldId]);
                }
            }
        }
        $successMessage = "Start and end dates updated successfully!";
    } catch (Exception $e) {
        $errorMessage = "Error updating dates: " . $e->getMessage();
    }
}

// Handle copy data request with redirect
if ($_POST && isset($_POST['copy_data'])) {
    try {
        $pdo = createPDOConnection($host, $dbname, $username, $password);
        $pdo->exec("DELETE FROM press_selections_copy");
        $pdo->exec("
            INSERT INTO press_selections_copy (
                id, icode, mold_id, press_name, mold_count, tobe_sum, description, created_at, updated_at, cavity_ids, is_completed
            )
            SELECT 
                id, icode, mold_id, press_name, mold_count, tobe_sum, description, created_at, updated_at, cavity_ids, is_completed
            FROM press_selections
        ");
        $successMessage = "Data copied successfully!";
        header("Location: copy_com1.php");
        exit();
    } catch (Exception $e) {
        $errorMessage = "Error copying data: " . $e->getMessage();
    }
}

// Handle bulk delete request
if ($_POST && isset($_POST['bulk_delete']) && isset($_POST['selected_records'])) {
    $selectedIds = $_POST['selected_records'];
    if (!empty($selectedIds) && is_array($selectedIds)) {
        try {
            $pdo = createPDOConnection($host, $dbname, $username, $password);
            $selectedIds = array_map('intval', $selectedIds);
            $selectedIds = array_filter($selectedIds, function($id) { return $id > 0; });
            if (empty($selectedIds)) {
                $errorMessage = "No valid records selected for deletion.";
            } else {
                $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
                $stmt = $pdo->prepare("DELETE FROM press_selections WHERE id IN ($placeholders)");
                $stmt->execute($selectedIds);
                $deletedCount = $stmt->rowCount();
                if ($deletedCount > 0) {
                    $successMessage = "Successfully deleted {$deletedCount} record(s)!";
                } else {
                    $errorMessage = "No records were deleted.";
                }
            }
        } catch (Exception $e) {
            $errorMessage = "Bulk delete error: " . $e->getMessage();
        }
    } else {
        $errorMessage = "No records selected for deletion.";
    }
}

// Handle single delete request
if ($_POST && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    if ($deleteId > 0) {
        try {
            $pdo = createPDOConnection($host, $dbname, $username, $password);
            $stmt = $pdo->prepare("DELETE FROM press_selections WHERE id = ?");
            $stmt->execute([$deleteId]);
            if ($stmt->rowCount() > 0) {
                $successMessage = "Press record deleted successfully!";
            } else {
                $errorMessage = "No record found to delete.";
            }
        } catch (Exception $e) {
            $errorMessage = "Delete error: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Invalid record ID for deletion.";
    }
}

// Handle cavity ID assignment (single record)
if ($_POST && isset($_POST['assign_cavities'])) {
    $selectionId = intval($_POST['selection_id']);
    $cavityIds = isset($_POST['cavity_ids']) && is_array($_POST['cavity_ids']) ? $_POST['cavity_ids'] : [];
    if ($selectionId > 0) {
        try {
            $pdo = createPDOConnection($host, $dbname, $username, $password);
            $validCavityIds = array_filter($cavityIds, function($id) {
                return is_numeric($id) && intval($id) > 0;
            });
            $cavityIdsString = !empty($validCavityIds) ? implode(',', $validCavityIds) : '';
            $stmt = $pdo->prepare("UPDATE press_selections SET cavity_ids = ? WHERE id = ?");
            $stmt->execute([$cavityIdsString, $selectionId]);
            if ($stmt->rowCount() > 0) {
                $successMessage = "Cavity IDs assigned successfully!";
            } else {
                $errorMessage = "No record found to update or no changes made.";
            }
        } catch (Exception $e) {
            $errorMessage = "Update error: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Invalid selection ID.";
    }
}

// Handle clear cavity IDs request for a single record
if ($_POST && isset($_POST['clear_cavities'])) {
    $selectionId = intval($_POST['selection_id']);
    if ($selectionId > 0) {
        try {
            $pdo = createPDOConnection($host, $dbname, $username, $password);
            $stmt = $pdo->prepare("UPDATE press_selections SET cavity_ids = '' WHERE id = ?");
            $stmt->execute([$selectionId]);
            if ($stmt->rowCount() > 0) {
                $successMessage = "Cavity IDs cleared successfully!";
            } else {
                $errorMessage = "No record found to clear or no changes made.";
            }
        } catch (Exception $e) {
            $errorMessage = "Clear cavities error: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Invalid selection ID.";
    }
}

// Handle bulk action request (clear or set cavities)
if ($_POST && isset($_POST['bulk_action']) && isset($_POST['selected_records'])) {
    $selectedIds = $_POST['selected_records'];
    $bulkAction = $_POST['bulk_action'];
    $cavityIds = isset($_POST['bulk_cavity_ids']) && is_array($_POST['bulk_cavity_ids']) ? $_POST['bulk_cavity_ids'] : [];
    if (!empty($selectedIds) && is_array($selectedIds)) {
        try {
            $pdo = createPDOConnection($host, $dbname, $username, $password);
            $selectedIds = array_map('intval', $selectedIds);
            $selectedIds = array_filter($selectedIds, function($id) { return $id > 0; });
            if (empty($selectedIds)) {
                $errorMessage = "No valid records selected.";
            } else {
                $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
                if ($bulkAction === 'clear') {
                    $stmt = $pdo->prepare("UPDATE press_selections SET cavity_ids = '' WHERE id IN ($placeholders)");
                    $stmt->execute($selectedIds);
                    $updatedCount = $stmt->rowCount();
                    if ($updatedCount > 0) {
                        $successMessage = "Cavity IDs cleared for {$updatedCount} record(s)!";
                    } else {
                        $errorMessage = "No records updated.";
                    }
                } elseif ($bulkAction === 'set') {
                    $validCavityIds = array_filter($cavityIds, function($id) {
                        return is_numeric($id) && intval($id) > 0;
                    });
                    $cavityIdsString = !empty($validCavityIds) ? implode(',', $validCavityIds) : '';
                    $stmt = $pdo->prepare("UPDATE press_selections SET cavity_ids = ? WHERE id IN ($placeholders)");
                    $params = array_merge([$cavityIdsString], $selectedIds);
                    $stmt->execute($params);
                    $updatedCount = $stmt->rowCount();
                    if ($updatedCount > 0) {
                        $successMessage = "Cavity IDs set for {$updatedCount} record(s)!";
                    } else {
                        $errorMessage = "No records updated.";
                    }
                } else {
                    $errorMessage = "Invalid bulk action selected.";
                }
            }
        } catch (Exception $e) {
            $errorMessage = "Bulk action error: " . $e->getMessage();
        }
    } else {
        $errorMessage = "No records selected for bulk action.";
    }
}

// Handle assign press_name request (bulk)
if ($_POST && isset($_POST['assign_press_name']) && isset($_POST['selected_records']) && isset($_POST['new_press_name'])) {
    $selectedIds = $_POST['selected_records'];
    $newPressName = trim($_POST['new_press_name'] ?? '');
    if (!empty($selectedIds) && is_array($selectedIds) && !empty($newPressName)) {
        try {
            $pdo = createPDOConnection($host, $dbname, $username, $password);
            $selectedIds = array_map('intval', $selectedIds);
            $selectedIds = array_filter($selectedIds, function($id) { return $id > 0; });
            if (empty($selectedIds)) {
                $errorMessage = "No valid records selected for press assignment.";
            } else {
                $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
                $stmt = $pdo->prepare("UPDATE press_selections SET press_name = ? WHERE id IN ($placeholders)");
                $params = array_merge([$newPressName], $selectedIds);
                $stmt->execute($params);
                $updatedCount = $stmt->rowCount();
                if ($updatedCount > 0) {
                    $successMessage = "Successfully assigned press name to {$updatedCount} record(s)!";
                } else {
                    $errorMessage = "No records updated.";
                }
            }
        } catch (Exception $e) {
            $errorMessage = "Press name assignment error: " . $e->getMessage();
        }
    } else {
        $errorMessage = "No records selected or no press name provided.";
    }
}

// Handle single press_name assignment for a record
if ($_POST && isset($_POST['assign_single_press']) && isset($_POST['selection_id']) && isset($_POST['single_press_name'])) {
    $selectionId = intval($_POST['selection_id']);
    $newPressName = trim($_POST['single_press_name'] ?? '');
    if ($selectionId > 0 && !empty($newPressName)) {
        try {
            $pdo = createPDOConnection($host, $dbname, $username, $password);
            $stmt = $pdo->prepare("UPDATE press_selections SET press_name = ? WHERE id = ?");
            $stmt->execute([$newPressName, $selectionId]);
            $updatedCount = $stmt->rowCount();
            if ($updatedCount > 0) {
                $successMessage = "Successfully assigned press name to record ID {$selectionId}!";
            } else {
                $errorMessage = "No record updated for ID {$selectionId}.";
            }
        } catch (Exception $e) {
            $errorMessage = "Single press name assignment error: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Invalid selection ID or no press name provided.";
    }
}

// Handle auto-assign press_name
if ($_POST && isset($_POST['auto_assign_press']) && isset($_POST['new_press_name'])) {
    $newPressName = trim($_POST['new_press_name'] ?? '');
    if (!empty($newPressName)) {
        try {
            $pdo = createPDOConnection($host, $dbname, $username, $password);
            $stmt = $pdo->prepare("
                UPDATE press_selections 
                SET press_name = ? 
                WHERE (press_name IS NULL OR press_name = '')
            ");
            $stmt->execute([$newPressName]);
            $updatedCount = $stmt->rowCount();
            if ($updatedCount > 0) {
                $successMessage = "Successfully assigned press name to {$updatedCount} record(s)!";
            } else {
                $errorMessage = "No records found to update.";
            }
        } catch (Exception $e) {
            $errorMessage = "Auto-assign press name error: " . $e->getMessage();
        }
    } else {
        $errorMessage = "No press name provided for auto-assignment.";
    }
}

// Handle priority auto-assign cavity IDs request and update process table
if ($_POST && isset($_POST['auto_assign_cavities_priority'])) {
    try {
        $pdo = createPDOConnection($host, $dbname, $username, $password);
        $autoAssignResult = autoAssignCavityIdsByMoldIdWithPriority($pdo, $_POST['press_select'] ?? '');
        if ($autoAssignResult['success']) {
            $successMessage = $autoAssignResult['message'];
            $sql1 = "
                UPDATE process p 
                JOIN press_selections ps 
                ON p.icode = ps.icode AND p.mold_id = ps.mold_id 
                SET p.cavity_id = CAST(SUBSTRING_INDEX(ps.cavity_ids, ',', 1) AS UNSIGNED)
            ";
            $sql2 = "
                UPDATE process p 
                JOIN cavity c ON p.cavity_id = c.id 
                SET p.cavity_name = c.cavity_name
            ";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute();
            $updatedCount1 = $stmt1->rowCount();
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute();
            $updatedCount2 = $stmt2->rowCount();
            $successMessage .= " Updated {$updatedCount1} cavity IDs and {$updatedCount2} cavity names in process table!";
            header("Location: planewd2N.php");
            exit();
        } else {
            $errorMessage = $autoAssignResult['message'];
        }
    } catch (Exception $e) {
        $errorMessage = "Priority auto-assign error: " . $e->getMessage();
    }
}

// Handle auto-assign cavity IDs for all press names
if ($_POST && isset($_POST['auto_assign_cavities_all_presses'])) {
    try {
        $pdo = createPDOConnection($host, $dbname, $username, $password);
        $autoAssignResult = autoAssignCavityIdsForAllPresses($pdo);
        if ($autoAssignResult['success']) {
            $successMessage = $autoAssignResult['message'];
            $sql1 = "
                UPDATE process p 
                JOIN press_selections ps 
                ON p.icode = ps.icode AND p.mold_id = ps.mold_id 
                SET p.cavity_id = CAST(SUBSTRING_INDEX(ps.cavity_ids, ',', 1) AS UNSIGNED)
            ";
            $sql2 = "
                UPDATE process p 
                JOIN cavity c ON p.cavity_id = c.id 
                SET p.cavity_name = c.cavity_name
            ";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute();
            $updatedCount1 = $stmt1->rowCount();
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute();
            $updatedCount2 = $stmt2->rowCount();
            $successMessage .= " Updated {$updatedCount1} cavity IDs and {$updatedCount2} cavity names in process table!";
            header("Location: planewd2N.php");
            exit();

        } else {
            $errorMessage = $autoAssignResult['message'];
        }
    } catch (Exception $e) {
        $errorMessage = "Auto-assign all presses error: " . $e->getMessage();
    }
}


// Handle update from press_selections_copy request
if ($_POST && isset($_POST['update_from_copy'])) {
    try {
        $pdo = createPDOConnection($host, $dbname, $username, $password);
        $stmt = $pdo->prepare("
            UPDATE press_selections ps
            INNER JOIN press_selections_copy psc 
            ON ps.icode = psc.icode AND ps.mold_id = psc.mold_id
            SET ps.cavity_ids = psc.cavity_ids,
                ps.press_name = psc.press_name,
                ps.is_completed = psc.is_completed
        ");
        $stmt->execute();
        $updatedCount = $stmt->rowCount();
        if ($updatedCount > 0) {
            $successMessage = "Successfully updated {$updatedCount} record(s) from press_selections_copy!";
        } else {
            $errorMessage = "No matching records found to update.";
        }
    } catch (Exception $e) {
        $errorMessage = "Update from copy error: " . $e->getMessage();
    }
}

// Handle include additional mold IDs request
if ($_POST && isset($_POST['include_mold_id']) && isset($_POST['mold_id_to_include'])) {
    $moldIdToInclude = trim($_POST['mold_id_to_include'] ?? '');
    $selectedIcode = trim($_POST['icode_select'] ?? '');
    try {
        $pdo = createPDOConnection($host, $dbname, $username, $password);
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM press_selections 
            WHERE icode = ? AND mold_id = ?
        ");
        $stmt->execute([$selectedIcode, $moldIdToInclude]);
        $exists = $stmt->fetchColumn();
        if ($exists == 0) {
            // Determine press_name to insert
            $pressNameToInsert = '';
            if (!empty($selectedPress)) {
                // Use the selected press if available
                $pressStmt = $pdo->prepare("
                    SELECT press_name FROM press 
                    WHERE press_name = ? AND is_available = 1
                    LIMIT 1
                ");
                $pressStmt->execute([$selectedPress]);
                $pressNameToInsert = $pressStmt->fetchColumn() ?: '';
            }
            if (empty($pressNameToInsert)) {
                // Fallback to an available press if no selected press or selected press is unavailable
                $pressStmt = $pdo->prepare("
                    SELECT press_name FROM press 
                    WHERE is_available = 1
                    ORDER BY press_name ASC
                    LIMIT 1
                ");
                $pressStmt->execute();
                $pressNameToInsert = $pressStmt->fetchColumn() ?: '';
            }
            // Insert the record with the determined press_name
            $insertStmt = $pdo->prepare("
                INSERT INTO press_selections (icode, mold_id, press_name) 
                VALUES (?, ?, ?)
            ");
            $insertStmt->execute([$selectedIcode, $moldIdToInclude, $pressNameToInsert]);
            $successMessage = "Successfully included mold ID {$moldIdToInclude} for ICode {$selectedIcode} with press {$pressNameToInsert}!";
        } else {
            $errorMessage = "Mold ID {$moldIdToInclude} already exists for ICode {$selectedIcode}.";
        }
    } catch (Exception $e) {
        $errorMessage = "Include mold ID error: " . $e->getMessage();
    }
}

// Handle remove pressed records without cavities
if ($_POST && isset($_POST['remove_pressed_no_cavities'])) {
    $selectedPress = trim($_POST['press_select'] ?? '');
    $selectedIcode = trim($_POST['icode_select'] ?? '');
    $selectedMoldId = trim($_POST['mold_id_select'] ?? '');
    try {
        $pdo = createPDOConnection($host, $dbname, $username, $password);
        $query = "
            DELETE FROM press_selections 
            WHERE press_name IS NOT NULL 
            AND press_name != '' 
            AND (cavity_ids IS NULL OR cavity_ids = '')
        ";
        $params = [];
        if (!empty($selectedPress)) {
            $query .= " AND (press_name = ? OR press_name REGEXP CONCAT('^', ?, '-[0-9]+$'))";
            $params[] = $selectedPress;
            $params[] = $selectedPress;
        }
        if (!empty($selectedIcode)) {
            $query .= " AND icode = ?";
            $params[] = $selectedIcode;
        }
        if (!empty($selectedMoldId)) {
            $query .= " AND mold_id = ?";
            $params[] = $selectedMoldId;
        }
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $deletedCount = $stmt->rowCount();
        if ($deletedCount > 0) {
            $successMessage = "Successfully removed {$deletedCount} record(s) with press name but no cavity IDs!";
        } else {
            $errorMessage = "No records found with a press name but no cavity IDs.";
        }
    } catch (Exception $e) {
        $errorMessage = "Error removing records: " . $e->getMessage();
    }
}

if ($_POST && isset($_POST['redirect_single_non_matching']) && isset($_POST['selection_id'])) {
    $selectionId = intval($_POST['selection_id']);
    if ($selectionId > 0) {
        try {
            $pdo = createPDOConnection($host, $dbname, $username, $password);
            $stmt = $pdo->prepare("
                SELECT p.id, p.icode, p.mold_id, p.cavity_ids, p.press_name
                FROM press_selections p
                LEFT JOIN process pr ON p.icode = pr.icode AND p.mold_id = pr.mold_id
                WHERE p.id = ? AND pr.icode IS NULL AND pr.mold_id IS NULL
            ");
            $stmt->execute([$selectionId]);
            $record = $stmt->fetch();

            if (!$record) {
                $errorMessage = "Record ID {$selectionId} is not non-matching or does not exist.";
            } else {
                $icode = $record['icode'];
                $moldId = $record['mold_id'];
                $cavityIds = $record['cavity_ids'];
                $pressName = $record['press_name'];
                $cavityId = !empty($cavityIds) ? explode(',', $cavityIds)[0] : null;

                // Fetch total_tobe and erp from tobeplan
                $tobeStmt = $pdo->prepare("
                    SELECT SUM(tobe) as total_tobe, erp
                    FROM tobeplan
                    WHERE icode = ? AND tobe > 0
                    GROUP BY erp
                    LIMIT 1
                ");
                $tobeStmt->execute([$icode]);
                $tobeResult = $tobeStmt->fetch();

                $totalTobe = $tobeResult['total_tobe'] ?: 0;
                $erp = $tobeResult['erp'] ?: ''; // Use empty string as default if no ERP found

                // Count records in press_selections for the given icode
                $countStmt = $pdo->prepare("
                    SELECT COUNT(*) as record_count
                    FROM press_selections
                    WHERE icode = ?
                ");
                $countStmt->execute([$icode]);
                $recordCount = $countStmt->fetchColumn() ?: 1;

                // Calculate tobe per record
                $tobePerRecord = $totalTobe > 0 ? round($totalTobe / $recordCount, 2) : 0;

                // Insert into process table with erp
                $insertStmt = $pdo->prepare("
                    INSERT INTO process (icode, mold_id, cavity_id, tobe, press_name, erp)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insertStmt->execute([$icode, $moldId, $cavityId, $tobePerRecord, $pressName, $erp]);

                // Update tires_per_mold in process table
                if ($totalTobe > 0 && $erp !== '') {
                    // Count records in process table for the given icode and erp
                    $processCountStmt = $pdo->prepare("
                        SELECT COUNT(*) as process_count
                        FROM process
                        WHERE icode = ? AND erp = ?
                    ");
                    $processCountStmt->execute([$icode, $erp]);
                    $processRecordCount = $processCountStmt->fetchColumn() ?: 1;

                    // Calculate tires_per_mold
                    $tiresPerMold = round($totalTobe / $processRecordCount, 2);

                    // Update tires_per_mold in process table for matching icode and erp
                    $updateStmt = $pdo->prepare("
                        UPDATE process
                        SET tires_per_mold = ?
                        WHERE icode = ? AND erp = ?
                    ");
                    $updateStmt->execute([$tiresPerMold, $icode, $erp]);
                }
            }
        } catch (Exception $e) {
            $errorMessage = "Error redirecting record ID {$selectionId}: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Invalid record ID for redirection.";
    }
}

// Fetch data based on current filters
try {
    $pdo = createPDOConnection($host, $dbname, $username, $password);
    ensureProcessTableSchema($pdo);
    createCavityIdsColumn($pdo);
    // Fetch cavity names
    $cavityStmt = $pdo->query("SELECT cavity_id, cavity_name FROM cavity");
    while ($row = $cavityStmt->fetch()) {
        $cavityNamesCache[$row['cavity_id']] = $row['cavity_name'];
    }
    // Fetch available cavities for selected press
    if ($selectedPress) {
        $cavityStmt = $pdo->prepare("
            SELECT DISTINCT pc.cavity_id, c.cavity_name
            FROM press_cavity pc 
            INNER JOIN press ps ON pc.press_id = ps.press_id
            INNER JOIN cavity c ON pc.cavity_id = c.cavity_id
            WHERE ps.press_name = ?
            ORDER BY pc.cavity_id ASC
        ");
        $cavityStmt->execute([$selectedPress]);
        $availableCavities = $cavityStmt->fetchAll();
    }
    // Fetch additional mold IDs from tire_mold
    if ($selectedIcode) {
        $moldStmt = $pdo->prepare("
            SELECT DISTINCT mold_id
            FROM tire_mold
            WHERE icode = ?
            ORDER BY mold_id ASC
        ");
        $moldStmt->execute([$selectedIcode]);
        $allMoldIdsForIcode = $moldStmt->fetchAll(PDO::FETCH_COLUMN);
        $currentMoldStmt = $pdo->prepare("
            SELECT DISTINCT mold_id
            FROM press_selections
            WHERE icode = ?
        ");
        $currentMoldStmt->execute([$selectedIcode]);
        $currentMoldIds = $currentMoldStmt->fetchAll(PDO::FETCH_COLUMN);
        $additionalMoldIds = array_diff($allMoldIdsForIcode, $currentMoldIds);
    }
    // Fetch ERP numbers and refs
    $erpStmt = $pdo->query("
        SELECT DISTINCT t.erp, w.ref
        FROM tobeplan1 t
        INNER JOIN worder w ON t.erp = w.erp
        WHERE t.tobe > 0
        ORDER BY w.date ASC
    ");
    $erpHeaders = $erpStmt->fetchAll(PDO::FETCH_ASSOC);
    $erpRefMapping = array_column($erpHeaders, 'ref', 'erp');
    $erpHeaders = array_column($erpHeaders, 'ref');
    // Fetch ERP numbers with dates
    $erpNumbersByIcode = [];
    $erpStmt = $pdo->prepare("
        SELECT t.icode, t.erp, t.tobe,
               p.start_date, p.end_date
        FROM tobeplan1 t
        INNER JOIN worder w ON t.erp = w.erp
        LEFT JOIN plannew1 p ON t.icode = p.icode AND t.erp = p.erp
        WHERE t.tobe > 0
        ORDER BY w.date ASC, t.icode, t.erp
    ");
    $erpStmt->execute();
    while ($row = $erpStmt->fetch()) {
        $erpNumbersByIcode[$row['icode']][$row['erp']] = [
            'tobe' => $row['tobe'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date']
        ];
    }
    // Compute date ranges
    $dateRangesByIcodeMold = [];
    foreach ($erpNumbersByIcode as $icode => $erpData) {
        $stmt = $pdo->prepare("
            SELECT DISTINCT mold_id
            FROM press_selections
            WHERE icode = ?
        ");
        $stmt->execute([$icode]);
        $moldIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($moldIds as $moldId) {
            $startDates = [];
            $endDates = [];
            foreach ($erpData as $erp => $data) {
                if ($data['start_date']) {
                    $startDates[] = strtotime($data['start_date']);
                }
                if ($data['end_date']) {
                    $endDates[] = strtotime($data['end_date']);
                }
            }
            $earliestStartDate = !empty($startDates) ? date('Y-m-d H:i', min($startDates)) : '';
            $latestEndDate = !empty($endDates) ? date('Y-m-d H:i', max($endDates)) : '';
            $dateRangesByIcodeMold[$icode][$moldId] = [
                'earliest_start_date' => $earliestStartDate,
                'latest_end_date' => $latestEndDate
            ];
        }
    }
    // Fetch non-matching records
    $nonMatchingStmt = $pdo->prepare("
        SELECT p.id
        FROM press_selections p
        LEFT JOIN process pr ON p.icode = pr.icode AND p.mold_id = pr.mold_id
        WHERE pr.icode IS NULL AND pr.mold_id IS NULL
    ");
    $nonMatchingStmt->execute();
    $nonMatchingRecords = array_column($nonMatchingStmt->fetchAll(), 'id');
    // Fetch data with filters
    $query = "
        SELECT 
            p.id AS selection_id,
            p.icode,
            p.mold_id,
            p.mold_count,
            COALESCE(
                (SELECT SUM(t.tobe)
                 FROM tobeplan1 t
                 WHERE t.icode = p.icode AND t.tobe > 0),
                0
            ) as tobe_sum,
            p.description,
            p.cavity_ids,
            p.press_name,
            COALESCE(
                (SELECT t.time_taken 
                 FROM tire t 
                 WHERE t.icode = p.icode 
                 AND t.time_taken IS NOT NULL 
                 AND t.time_taken > 0 
                 ORDER BY t.id ASC 
                 LIMIT 1), 
                0
            ) as time_taken,
            COALESCE(
                (SELECT SUM(t.tobe)
                 FROM tobeplan1 t
                 WHERE t.icode = p.icode AND t.tobe > 0),
                0
            ) as original_tobe_sum
        FROM press_selections p
        WHERE 1=1
    ";
    $params = [];
    if (!empty($selectedPress)) {
        $query .= " AND (p.press_name = ? OR p.press_name REGEXP CONCAT('^', ?, '-[0-9]+$'))";
        $params[] = $selectedPress;
        $params[] = $selectedPress;
    }
    if (!empty($selectedIcode)) {
        $query .= " AND p.icode = ?";
        $params[] = $selectedIcode;
    }
    if (!empty($selectedMoldId)) {
        $query .= " AND p.mold_id = ?";
        $params[] = $selectedMoldId;
    }
    $query .= "
        ORDER BY 
            p.mold_id ASC,
            p.icode ASC,
            p.press_name ASC,
            p.id ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $searchResult = $stmt->fetchAll();
    if (empty($searchResult)) {
        $errorMessage = "No data found for the selected filters: " . 
            htmlspecialchars(($selectedPress ? "Press: $selectedPress" : "None") . 
            ($selectedIcode ? ", ICode: $selectedIcode" : "") . 
            ($selectedMoldId ? ", Mold ID: $selectedMoldId" : ""));
    } else {
        if ($selectedPress) {
            $pressInfoStmt = $pdo->prepare("
                SELECT press_id, is_available, availability_date, press_name
                FROM press 
                WHERE press_name = ?
                LIMIT 1
            ");
            $pressInfoStmt->execute([$selectedPress]);
            $pressInfo = $pressInfoStmt->fetch();
            $rowCavityIds = array_column($availableCavities, 'cavity_id');
            $availableCavityIds = implode(',', $rowCavityIds);
        } else {
            $pressInfo = [];
            $availableCavityIds = '';
        }
        foreach ($searchResult as &$row) {
            $row['press_id'] = $pressInfo['press_id'] ?? null;
            $row['is_available'] = $pressInfo['is_available'] ?? null;
            $row['availability_date'] = $pressInfo['availability_date'] ?? null;
            $row['available_cavity_ids'] = $availableCavityIds;
            $row['erp_numbers'] = $erpNumbersByIcode[$row['icode']] ?? [];
            $row['earliest_start_date'] = $dateRangesByIcodeMold[$row['icode']][$row['mold_id']]['earliest_start_date'] ?? '';
            $row['latest_end_date'] = $dateRangesByIcodeMold[$row['icode']][$row['mold_id']]['latest_end_date'] ?? '';
            $countStmt = $pdo->prepare("
                SELECT COUNT(DISTINCT mold_id) as planned_mold_id
                FROM press_selections 
                WHERE icode = ? 
                AND (press_name = ? OR press_name REGEXP CONCAT('^', ?, '-[0-9]+$') OR press_name IS NULL OR press_name = '')
                AND mold_id IS NOT NULL 
                AND mold_id != ''
            ");
            $countStmt->execute([$row['icode'], $selectedPress, $selectedPress]);
            $countResult = $countStmt->fetch();
            $row['planned_mold_id'] = $countResult['planned_mold_id'] ?? 0;
            if ($row['cavity_ids']) {
                $cavityArray = array_filter(explode(',', $row['cavity_ids']));
                $row['cavity_count'] = count($cavityArray);
            } else {
                $row['cavity_count'] = 0;
            }
            $row['tobe_sum'] = $row['tobe_sum'] ?? 0;
            $row['time_taken'] = $row['time_taken'] ?? 0;
            if ($row['time_taken'] == 0 && $row['icode']) {
                $timeStmt = $pdo->prepare("
                    SELECT time_taken 
                    FROM tire 
                    WHERE icode = ? 
                    AND time_taken IS NOT NULL 
                    AND time_taken > 0 
                    ORDER BY id DESC 
                    LIMIT 1
                ");
                $timeStmt->execute([$row['icode']]);
                $timeResult = $timeStmt->fetch();
                if ($timeResult) {
                    $row['time_taken'] = $timeResult['time_taken'];
                }
            }
        }
        unset($row);
    }
} catch (Exception $e) {
    $errorMessage = "Database error: " . $e->getMessage();
    $searchResult = [];
}
// Get all press names, icodes, and mold_ids
$allPresses = [];
$allIcodes = [];
$allMoldIds = [];
try {
    $pdo = createPDOConnection($host, $dbname, $username, $password);
    ensureProcessTableSchema($pdo);
    $updateError = updatePressNames($pdo);
    if ($updateError) {
        $errorMessage = $updateError;
    }
    createCavityIdsColumn($pdo);
    $stmt = $pdo->query("
        SELECT DISTINCT press_name 
        FROM press_selections 
        WHERE press_name IS NOT NULL 
        AND press_name != ''
        ORDER BY press_name ASC
    ");
    $allPresses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allPresses = array_unique($allPresses);
    sort($allPresses);
    $stmt = $pdo->query("
        SELECT DISTINCT icode 
        FROM press_selections 
        WHERE icode IS NOT NULL 
        AND icode != ''
        ORDER BY icode ASC
    ");
    $allIcodes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allIcodes = array_unique($allIcodes);
    $stmt = $pdo->query("
        SELECT DISTINCT mold_id 
        FROM press_selections 
        WHERE mold_id IS NOT NULL 
        AND mold_id != ''
        ORDER BY mold_id DESC
    ");
    $allMoldIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allMoldIds = array_unique($allMoldIds);
    sort($allMoldIds);
} catch (Exception $e) {
    $errorMessage = "Database connection error: " . $e->getMessage();
}


// Function to auto-assign cavity IDs for all press names
function autoAssignCavityIdsForAllPresses($pdo) {
    try {
        $logMessages = [];
        $stmt = $pdo->query("
            SELECT DISTINCT press_name
            FROM press_selections
            WHERE press_name IS NOT NULL AND press_name != ''
        ");
        $pressNames = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($pressNames)) {
            $logMessages[] = "No press names found.";
            return ['success' => false, 'message' => 'No press names found for auto-assignment.', 'logs' => $logMessages];
        }
        $logMessages[] = "Found " . count($pressNames) . " press names: " . implode(', ', $pressNames);

        $assignedCount = 0;
        $skippedCount = 0;
        foreach ($pressNames as $pressName) {
            $stmt = $pdo->prepare("
                SELECT DISTINCT p.id, p.icode, p.mold_id, p.press_name, p.cavity_ids,
                    COALESCE(
                        (SELECT SUM(t.tobe)
                         FROM tobeplan1 t
                         WHERE t.icode = p.icode AND t.tobe > 0),
                        0
                    ) as tobe_sum,
                    COALESCE(
                        (SELECT t.time_taken 
                         FROM tire t 
                         WHERE t.icode = p.icode 
                         AND t.time_taken IS NOT NULL 
                         AND t.time_taken > 0 
                         ORDER BY t.id ASC LIMIT 1), 
                        0
                    ) as time_taken
                FROM press_selections p
                LEFT JOIN process pr ON p.icode = pr.icode AND p.mold_id = pr.mold_id
                WHERE (p.press_name = ? OR p.press_name REGEXP CONCAT('^', ?, '-[0-9]+$'))
                AND p.mold_id IS NOT NULL
                AND p.mold_id != ''
                AND pr.icode IS NOT NULL
                AND pr.mold_id IS NOT NULL
                ORDER BY p.mold_id ASC, p.icode ASC, p.id ASC
            ");
            $stmt->execute([$pressName, $pressName]);
            $records = $stmt->fetchAll();
            if (empty($records)) {
                $logMessages[] = "No valid records found for press: $pressName";
                continue;
            }
            $logMessages[] = "Fetched " . count($records) . " records for press: $pressName";

            $erpStmt = $pdo->query("
                SELECT DISTINCT t.erp, w.date
                FROM tobeplan1 t
                LEFT JOIN worder w ON t.erp = w.erp
                WHERE t.tobe > 0
                ORDER BY COALESCE(w.date, '9999-12-31 23:59:59') ASC
            ");
            $erpOrder = [];
            $index = 0;
            while ($row = $erpStmt->fetch()) {
                $erpOrder[$row['erp']] = $index++;
            }

            $erpNumbersByIcode = [];
            $erpStmt = $pdo->prepare("
                SELECT t.icode, t.erp, t.tobe
                FROM tobeplan1 t
                LEFT JOIN worder w ON t.erp = w.erp
                WHERE t.tobe > 0
                ORDER BY COALESCE(w.date, '9999-12-31 23:59:59') ASC, t.icode, t.erp
            ");
            $erpStmt->execute();
            while ($row = $erpStmt->fetch()) {
                $erpNumbersByIcode[$row['icode']][$row['erp']] = $row['tobe'];
            }

            $cavityStmt = $pdo->prepare("
                SELECT DISTINCT pc.cavity_id
                FROM press_cavity pc 
                INNER JOIN press ps ON pc.press_id = ps.press_id
                WHERE ps.press_name = ?
                ORDER BY pc.cavity_id ASC
            ");
            $cavityStmt->execute([$pressName]);
            $availableCavities = $cavityStmt->fetchAll(PDO::FETCH_COLUMN);
            if (empty($availableCavities)) {
                $logMessages[] = "No cavities found for press: $pressName";
                continue;
            }
            $logMessages[] = "Available cavities for press $pressName: " . implode(', ', $availableCavities);

            $moldGroups = [];
            foreach ($records as $record) {
                $key = $record['mold_id'];
                if (!isset($moldGroups[$key])) {
                    $moldGroups[$key] = [
                        'mold_id' => $record['mold_id'],
                        'records' => []
                    ];
                }
                $moldGroups[$key]['records'][] = $record;
            }
            $logMessages[] = "Grouped into " . count($moldGroups) . " mold_id groups for press: $pressName";

            $groupPriorities = [];
            foreach ($moldGroups as $key => $group) {
                $earliestErpOrder = PHP_INT_MAX;
                foreach ($group['records'] as $record) {
                    $erpA = $erpNumbersByIcode[$record['icode']] ?? [];
                    foreach ($erpA as $erp => $tobe) {
                        if ($tobe > 0 && isset($erpOrder[$erp]) && $erpOrder[$erp] < $earliestErpOrder) {
                            $earliestErpOrder = $erpOrder[$erp];
                        }
                    }
                }
                $groupPriorities[$key] = $earliestErpOrder === PHP_INT_MAX ? 999999 : $earliestErpOrder;
            }
            uasort($moldGroups, function($a, $b) use ($groupPriorities) {
                $keyA = $a['mold_id'];
                $keyB = $b['mold_id'];
                return $groupPriorities[$keyA] <=> $groupPriorities[$keyB];
            });

            $moldToCavityMapping = [];
            $totalCavities = count($availableCavities);
            $index = 0;
            foreach (array_keys($moldGroups) as $key) {
                $moldToCavityMapping[$key] = $availableCavities[$index % $totalCavities];
                $index++;
            }
            $logMessages[] = "Assigned cavities to mold_id groups for press $pressName: " . json_encode($moldToCavityMapping);

            foreach ($moldGroups as $key => $group) {
                $cavityId = $moldToCavityMapping[$key];
                $recordsForGroup = $group['records'];
                foreach ($recordsForGroup as $record) {
                    if (empty($record['cavity_ids'])) {
                        $updateStmt = $pdo->prepare("UPDATE press_selections SET cavity_ids = ? WHERE id = ?");
                        $updateStmt->execute([$cavityId, $record['id']]);
                        $assignedCount++;
                        $logMessages[] = "Assigned cavity ID $cavityId to record ID {$record['id']} (icode: {$record['icode']}, mold_id: {$record['mold_id']}, press: $pressName)";
                    } else {
                        $skippedCount++;
                        $logMessages[] = "Skipped record ID {$record['id']} (already has cavity: {$record['cavity_ids']}, press: $pressName)";
                    }
                }
            }
        }

        $totalPresses = count($pressNames);
        $message = "Auto-assigned cavity IDs to {$assignedCount} records across {$totalPresses} press names!";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} records already had cavity assignments and were skipped)";
        }
        return ['success' => true, 'message' => $message, 'logs' => $logMessages];
    } catch (Exception $e) {
        $logMessages[] = "Exception: " . $e->getMessage();
        return ['success' => false, 'message' => "Auto-assign all presses error: " . $e->getMessage(), 'logs' => $logMessages];
    }
}

// Function to auto-assign cavity IDs with priority
function autoAssignCavityIdsByMoldIdWithPriority($pdo, $pressName) {
    try {
        $today = new DateTime('today 07:00:00', new DateTimeZone('Asia/Colombo'));
        $logMessages = [];
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.id, p.icode, p.mold_id, p.press_name, p.cavity_ids,
                COALESCE(
                    (SELECT SUM(t.tobe)
                     FROM tobeplan1 t
                     WHERE t.icode = p.icode AND t.tobe > 0),
                    0
                ) as tobe_sum,
                COALESCE(
                    (SELECT t.time_taken 
                     FROM tire t 
                     WHERE t.icode = p.icode 
                     AND t.time_taken IS NOT NULL 
                     AND t.time_taken > 0 
                     ORDER BY t.id ASC LIMIT 1), 
                    0
                ) as time_taken
            FROM press_selections p
            LEFT JOIN process pr ON p.icode = pr.icode AND p.mold_id = pr.mold_id
            WHERE (p.press_name = ? OR p.press_name REGEXP CONCAT('^', ?, '-[0-9]+$'))
            AND p.mold_id IS NOT NULL
            AND p.mold_id != ''
            AND pr.icode IS NOT NULL
            AND pr.mold_id IS NOT NULL
            ORDER BY p.mold_id ASC, p.icode ASC, p.id ASC
        ");
        $stmt->execute([$pressName, $pressName]);
        $records = $stmt->fetchAll();
        if (empty($records)) {
            $logMessages[] = "No valid records found for press: $pressName";
            return ['success' => false, 'message' => 'No valid records found for auto-assignment.', 'logs' => $logMessages];
        }
        $logMessages[] = "Fetched " . count($records) . " records";
        $erpStmt = $pdo->query("
            SELECT DISTINCT t.erp, w.date
            FROM tobeplan1 t
            LEFT JOIN worder w ON t.erp = w.erp
            WHERE t.tobe > 0
            ORDER BY COALESCE(w.date, '9999-12-31 23:59:59') ASC
        ");
        $erpOrder = [];
        $erpHeaders = [];
        $index = 0;
        while ($row = $erpStmt->fetch()) {
            $erpOrder[$row['erp']] = $index++;
            $erpHeaders[] = $row['erp'];
        }
        $logMessages[] = "ERP headers: " . implode(', ', $erpHeaders);
        $erpNumbersByIcode = [];
        $erpStmt = $pdo->prepare("
            SELECT t.icode, t.erp, t.tobe
            FROM tobeplan1 t
            LEFT JOIN worder w ON t.erp = w.erp
            WHERE t.tobe > 0
            ORDER BY COALESCE(w.date, '9999-12-31 23:59:59') ASC, t.icode, t.erp
        ");
        $erpStmt->execute();
        while ($row = $erpStmt->fetch()) {
            $erpNumbersByIcode[$row['icode']][$row['erp']] = $row['tobe'];
        }
        $logMessages[] = "Fetched ERP numbers for " . count($erpNumbersByIcode) . " icodes";
        $cavityStmt = $pdo->prepare("
            SELECT DISTINCT pc.cavity_id
            FROM press_cavity pc 
            INNER JOIN press ps ON pc.press_id = ps.press_id
            WHERE ps.press_name = ?
            ORDER BY pc.cavity_id ASC
        ");
        $cavityStmt->execute([$pressName]);
        $availableCavities = $cavityStmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($availableCavities)) {
            $logMessages[] = "No cavities found for press: $pressName";
            return ['success' => false, 'message' => 'No available cavity IDs found for this press.', 'logs' => $logMessages];
        }
        $logMessages[] = "Available cavities: " . implode(', ', $availableCavities);
        $moldGroups = [];
        foreach ($records as $record) {
            $key = $record['mold_id'];
            if (!isset($moldGroups[$key])) {
                $moldGroups[$key] = [
                    'mold_id' => $record['mold_id'],
                    'records' => []
                ];
            }
            $moldGroups[$key]['records'][] = $record;
        }
        $logMessages[] = "Grouped into " . count($moldGroups) . " mold_id groups";
        $groupPriorities = [];
        foreach ($moldGroups as $key => $group) {
            $earliestErpOrder = PHP_INT_MAX;
            foreach ($group['records'] as $record) {
                $erpA = $erpNumbersByIcode[$record['icode']] ?? [];
                foreach ($erpA as $erp => $tobe) {
                    if ($tobe > 0 && isset($erpOrder[$erp]) && $erpOrder[$erp] < $earliestErpOrder) {
                        $earliestErpOrder = $erpOrder[$erp];
                    }
                }
            }
            $groupPriorities[$key] = $earliestErpOrder === PHP_INT_MAX ? 999999 : $earliestErpOrder;
        }
        uasort($moldGroups, function($a, $b) use ($groupPriorities) {
            $keyA = $a['mold_id'];
            $keyB = $b['mold_id'];
            return $groupPriorities[$keyA] <=> $groupPriorities[$keyB];
        });
        $moldToCavityMapping = [];
        $totalCavities = count($availableCavities);
        $index = 0;
        foreach (array_keys($moldGroups) as $key) {
            $moldToCavityMapping[$key] = $availableCavities[$index % $totalCavities];
            $index++;
        }
        $logMessages[] = "Assigned cavities to mold_id groups: " . json_encode($moldToCavityMapping);
        $assignedCount = 0;
        $skippedCount = 0;
        foreach ($moldGroups as $key => $group) {
            $cavityId = $moldToCavityMapping[$key];
            $recordsForGroup = $group['records'];
            foreach ($recordsForGroup as $record) {
                if (empty($record['cavity_ids'])) {
                    $updateStmt = $pdo->prepare("UPDATE press_selections SET cavity_ids = ? WHERE id = ?");
                    $updateStmt->execute([$cavityId, $record['id']]);
                    $assignedCount++;
                    $logMessages[] = "Assigned cavity ID $cavityId to record ID {$record['id']} (icode: {$record['icode']}, mold_id: {$record['mold_id']})";
                } else {
                    $skippedCount++;
                    $logMessages[] = "Skipped record ID {$record['id']} (already has cavity: {$record['cavity_ids']})";
                }
            }
        }
        $totalGroups = count($moldGroups);
        $message = "Priority-based auto-assigned cavity IDs to {$assignedCount} records across {$totalGroups} unique mold_id groups, sorted by ERP order!";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} records already had cavity assignments and were skipped)";
        }
        if ($totalGroups > $totalCavities) {
            $message .= " Note: {$totalGroups} mold_id groups were assigned to {$totalCavities} available cavities.";
        }
        return ['success' => true, 'message' => $message, 'logs' => $logMessages];
    } catch (Exception $e) {
        $logMessages[] = "Exception: " . $e->getMessage();
        return ['success' => false, 'message' => "Priority auto-assign error: " . $e->getMessage(), 'logs' => $logMessages];
    }
}





// Function to create cavity_ids column
function createCavityIdsColumn($pdo) {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM press_selections LIKE 'cavity_ids'");
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE press_selections ADD COLUMN cavity_ids TEXT");
        }
    } catch (Exception $e) {
        error_log("Error creating cavity_ids column: " . $e->getMessage());
    }
}
// Function to update press names with sequential suffixes
function updatePressNames($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT icode, mold_id, press_name, id
            FROM press_selections
            WHERE icode IS NOT NULL AND mold_id IS NOT NULL
            ORDER BY icode, mold_id, id
        ");
        $records = $stmt->fetchAll();
        $groups = [];
        foreach ($records as $record) {
            $key = $record['icode'] . '|' . $record['mold_id'];
            $groups[$key][] = $record;
        }
        foreach ($groups as $group) {
            if (count($group) > 1) {
                $basePressName = $group[0]['press_name'];
                if (preg_match('/^(.*)-\d+$/', $basePressName, $matches)) {
                    $basePressName = $matches[1];
                }
                foreach ($group as $index => $record) {
                    $newPressName = $basePressName . '-' . ($index + 1);
                    $updateStmt = $pdo->prepare("UPDATE press_selections SET press_name = ? WHERE id = ?");
                    $updateStmt->execute([$newPressName, $record['id']]);
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error updating press names: " . $e->getMessage());
        return "Error updating press names: " . $e->getMessage();
    }
    return null;
}
// Utility functions for display
function getIcodeColor($icode) {
    if (!$icode) return '#666666';
    $hash = md5($icode);
    $colors = [
        '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
        '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9',
        '#F8C471', '#82E0AA', '#F1948A', '#85929E', '#D2B4DE'
    ];
    $index = hexdec(substr($hash, 0, 2)) % count($colors);
    return $colors[$index];
}
function getMoldIdColor($moldId) {
    if (!$moldId) return '#f0f0f0';
    $hash = md5($moldId);
    $colors = [
        '#F28018', '#FF6F61', '#6A5ACD', '#40E0D0', '#FFD700',
        '#00BFFF', '#FF69B4', '#90EE90', '#FFA07A', '#20B2AA',
        '#FF8C00', '#7CFC00', '#BA55D3', '#DC143C', '#00CED1',
        '#008B8B', '#FF1493', '#1E90FF'
    ];
    $index = hexdec(substr($hash, 0, 2)) % count($colors);
    return $colors[$index];
}
function getCavityIdColor($cavityId) {
    if (!$cavityId) return '#6f42c1';
    $hash = md5($cavityId);
    $colors = [
        '#dc3545', '#28a745', '#007bff', '#6f42c1', '#fd7e14',
        '#20c997', '#6610f2', '#e83e8c', '#17a2b8', '#ffc107',
        '#343a40', '#6c757d', '#495057', '#212529', '#e9ecef'
    ];
    $index = hexdec(substr($hash, 0, 2)) % count($colors);
    return $colors[$index];
}
function displayCavityIds($cavityIds, $cavityCount = null, $cavityNamesCache = []) {
    if (!$cavityIds) {
        return '<span style="color: #999;">No cavities assigned</span>';
    }
    $cavityArray = array_filter(explode(',', $cavityIds));
    $output = '<div class="cavity-id-multiple">';
    foreach ($cavityArray as $cavityId) {
        $cavityId = trim($cavityId);
        $displayText = isset($cavityNamesCache[$cavityId]) ? $cavityNamesCache[$cavityId] : $cavityId;
        $output .= '<span class="cavity-id-badge" style="background-color: ' . 
                   getCavityIdColor($cavityId) . '; color: white; padding: 2px 6px; margin: 2px; border-radius: 3px; display: inline-block;">' . 
                   htmlspecialchars($displayText ?? '') . '</span>';
    }
    $output .= '</div>';
    if ($cavityCount && $cavityCount > 1) {
        $output .= '<div style="font-size: 0.8em; color: #666;">Total: ' . $cavityCount . '</div>';
    }
    return $output;
}
function calculateSumPerMold($tobeSum, $plannedMoldId) {
    if (!is_numeric($tobeSum) || !is_numeric($plannedMoldId) || $tobeSum <= 0 || $plannedMoldId <= 0) {
        return 0;
    }
    return round($tobeSum / $plannedMoldId, 2);
}
function calculateTotalTime($sumPerMold, $timeTaken) {
    if (!is_numeric($sumPerMold) || !is_numeric($timeTaken) || $sumPerMold <= 0 || $timeTaken <= 0) {
        return 0;
    }
    return round($sumPerMold * $timeTaken, 0);
}
function formatDuration($minutes) {
    if (!is_numeric($minutes) || $minutes == 0) return '';
    $absMinutes = abs($minutes);
    $hours = floor($absMinutes / 60);
    $mins = $absMinutes % 60;
    $sign = $minutes < 0 ? '-' : '';
    if ($hours > 0) {
        return $sign . $hours . 'h ' . $mins . 'm';
    } else {
        return $sign . $mins . 'm';
    }
}
function formatTobeSum($tobeSum) {
    if (!is_numeric($tobeSum) || $tobeSum == 0) return '';
    return number_format($tobeSum, 0);
}
function formatTimeTaken($timeTaken) {
    if (!is_numeric($timeTaken) || $timeTaken == 0) return '';
    return $timeTaken . ' min';
}
function formatDate($date) {
    if (!$date || $date === '') return '';
    return date('Y-m-d H:i', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Press Selection Management</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .btn { padding: 5px 10px; margin: 2px; cursor: pointer; border: none; border-radius: 3px; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-warning { background-color: #ffc107; color: black; }
        .btn-secondary { background-color: #6c757d; color: white; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-info { background-color: #17a2b8; color: white; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .numeric-cell { text-align: right; }
        .icode-cell, .mold-id-cell { padding: 4px; border-radius: 3px; color: white; }
        .non-matching-row { background-color: #b4f7e6ff !important; }
        .non-matching-row .icode-cell, .non-matching-row .mold-id-cell { color: black !important; }
        .inline-form { display: inline; }
        .cavity-assignment-form select { width: 150px; max-height: 100px; }
        .bulk-actions select { width: 150px; max-height: 100px; margin-right: 5px; }
        .bulk-actions select#bulk_action { width: 120px; max-height: unset; }
        .bulk-actions select#new_press_name { width: 150px; }
        .bulk-actions { display: flex; align-items: center; gap: 5px; margin-bottom: 10px; }
        .action-buttons { display: flex; gap: 5px; }
        .disabled { opacity: 0.5; pointer-events: none; }
        .filter-group { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
        .additional-molds { margin-top: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .additional-molds h4 { margin-top: 0; }
        .mold-id-list { display: flex; flex-wrap: wrap; gap: 5px; }
        .single-press-form select { width: 150px; }
        .erp-numbers { margin-top: 5px; }
        .erp-sub-column { font-size: 0.8em; }
        .erp-sub-column input { width: 100%; }
        .loading { display: none; text-align: center; margin: 10px 0; }
        .spinner { 
            border: 4px solid #f3f3f3; 
            border-top: 4px solid #3498db; 
            border-radius: 50%; 
            width: 30px; 
            height: 30px; 
            animation: spin 1s linear infinite; 
            margin: 0 auto; 
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        function toggleAll(checkbox) {
            const checkboxes = document.querySelectorAll('.record-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
            updateButtonStates();
        }
        function selectAll() {
            document.querySelectorAll('.record-checkbox').forEach(cb => cb.checked = true);
            updateButtonStates();
        }
        function deselectAll() {
            document.querySelectorAll('.record-checkbox').forEach(cb => cb.checked = false);
            updateButtonStates();
        }
        function updateButtonStates() {
            const checkboxes = document.querySelectorAll('.record-checkbox');
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            const assignPressButton = document.getElementById('assign_press_button');
            const bulkSubmitButton = document.getElementById('bulk_submit');
            assignPressButton.classList.toggle('disabled', !anyChecked);
            bulkSubmitButton.classList.toggle('disabled', !anyChecked);
            const bulkAction = document.getElementById('bulk_action');
            const bulkCavityIds = document.getElementById('bulk_cavity_ids');
            if (bulkAction.value === 'set') {
                bulkCavityIds.style.display = 'block';
            } else {
                bulkCavityIds.style.display = 'none';
            }
        }
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }
        function enableEditDates(btn) {
            let row = btn.closest('tr');
            row.querySelectorAll('.date-display').forEach(el => {
                el.style.display = 'none';
            });
            row.querySelectorAll('input[type="datetime-local"]').forEach(el => {
                el.style.display = 'block';
            });
            btn.style.display = 'none';
        }
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.record-checkbox');
            checkboxes.forEach(cb => cb.addEventListener('change', updateButtonStates));
            document.getElementById('bulk_action').addEventListener('change', updateButtonStates);
            updateButtonStates();
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Press Selection Management</h1>
        <!-- Form for Update from Copy and Copy Data -->
        <div class="filter-group">
            <form method="POST" action="" class="inline-form">
                <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress ?? '') ?>">
                <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode ?? '') ?>">
                <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId ?? '') ?>">
                <button type="submit" name="update_from_copy" class="btn btn-info">Get Previous Data</button>
            </form>
            <form method="POST" action="" class="inline-form">
                <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress ?? '') ?>">
                <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode ?? '') ?>">
                <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId ?? '') ?>">
                <button type="submit" name="copy_data" class="btn btn-info">Generate Plan</button>
            </form>
        </div>
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>
        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>
        <!-- Press Selection Form with Filters -->
        <div class="form-group">
            <form method="POST" action="">
                <div class="filter-group">
                    <div>
                        <label for="press_select">Select Press:</label>
                        <select name="press_select" id="press_select">
                            <option value="">All Presses</option>
                            <?php foreach ($allPresses as $press): ?>
                                <option value="<?= htmlspecialchars($press ?? '') ?>" 
                                        <?= ($selectedPress === $press) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($press ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="icode_select">Select ICode:</label>
                        <select name="icode_select" id="icode_select">
                            <option value="">All ICodes</option>
                            <?php foreach ($allIcodes as $icode): ?>
                                <option value="<?= htmlspecialchars($icode ?? '') ?>" 
                                        <?= ($selectedIcode === $icode) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($icode ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="mold_id_select">Select Mold ID:</label>
                        <select name="mold_id_select" id="mold_id_select">
                            <option value="">All Mold IDs</option>
                            <?php foreach ($allMoldIds as $moldId): ?>
                                <option value="<?= htmlspecialchars($moldId ?? '') ?>" 
                                        <?= ($selectedMoldId === $moldId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($moldId ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>
        <!-- Additional Mold IDs Section -->
        <?php if (!empty($additionalMoldIds) && !empty($selectedIcode)): ?>
            <div class="additional-molds">
                <h4>Additional Mold IDs for ICode: <?= htmlspecialchars($selectedIcode ?? '') ?></h4>
                <div class="mold-id-list">
                    <?php foreach ($additionalMoldIds as $moldId): ?>
                        <form method="POST" action="" class="inline-form">
                            <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress ?? '') ?>">
                            <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode ?? '') ?>">
                            <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId ?? '') ?>">
                            <input type="hidden" name="mold_id_to_include" value="<?= htmlspecialchars($moldId ?? '') ?>">
                            <button type="submit" name="include_mold_id" class="btn btn-success">
                                Include <?= htmlspecialchars($moldId ?? '') ?>
                            </button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($searchResult && !empty($searchResult)): ?>
            <h3>Cavity Assignment Tools</h3>
            <div class="bulk-actions">
                  <form method="POST" action="" class="inline-form" id="autoAssignAllForm">
                    <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress ?? '') ?>">
                    <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode ?? '') ?>">
                    <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId ?? '') ?>">
                    <button type="submit" name="auto_assign_cavities_all_presses" class="btn btn-success" onclick="showLoading()">
                        Auto-Assign All Presses
                    </button>
                </form>
                <?php if ($selectedPress): ?>
                    <form method="POST" action="" class="inline-form" id="autoAssignForm">
                        <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress ?? '') ?>">
                        <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode ?? '') ?>">
                        <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId ?? '') ?>">
                        <button type="submit" name="auto_assign_cavities_priority" class="btn btn-success" onclick="showLoading()">
                            Auto-Assign with Priority (Mold ID)
                        </button>


                        
                    </form>
                    <form method="POST" action="" class="inline-form">
                        <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress ?? '') ?>">
                        <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode ?? '') ?>">
                        <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId ?? '') ?>">
                        <button type="submit" name="remove_pressed_no_cavities" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to remove records with a press name but no cavity IDs?')">
                            Remove Pressed w/o Cavities
                        </button>
                    </form>
                    <div class="loading" id="loading">
                        <div class="spinner"></div>
                        <p>Processing update...</p>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Summary Information -->
            <div class="summary-box">
                <h3>Current Filters</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
                    <div><strong>Press Name:</strong> <?= htmlspecialchars($selectedPress ?: 'None') ?></div>
                    <div><strong>ICode:</strong> <?= htmlspecialchars($selectedIcode ?: 'None') ?></div>
                    <div><strong>Mold ID:</strong> <?= htmlspecialchars($selectedMoldId ?: 'None') ?></div>
                    <div><strong>Total Records:</strong> <?= count($searchResult) ?></div>
                    <div><strong>Unique ICodes:</strong> <?= count(array_unique(array_column($searchResult, 'icode'))) ?></div>
                    <div><strong>Unique Mold IDs:</strong> <?= count(array_unique(array_filter(array_column($searchResult, 'mold_id')))) ?></div>
                    <div><strong>Available Cavities:</strong> <?= htmlspecialchars($searchResult[0]['available_cavity_ids'] ?? '') ?></div>
                </div>
            </div>
            <!-- Bulk Delete and Bulk Set Form -->
            <form method="POST" id="bulkActionForm">
                <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress ?? '') ?>">
                <input type="hidden" name="icode_select" id="icode_selection" value="<?= htmlspecialchars($selectedIcode ?? '') ?>">
                <input type="hidden" name="mold_id_select" id="mold_id_selection" value="<?= htmlspecialchars($selectedMoldId ?? '') ?>">
                <div class="bulk-actions">
                    <button type="button" class="btn btn-secondary" onclick="selectAll()">Select All</button>
                    <button type="button" class="btn btn-secondary" onclick="deselectAll()">Deselect All</button>
                    <button type="submit" name="bulk_delete" id="bulk_delete_selected" class="btn btn-danger" 
                            onclick="return confirm('Are you sure you want to delete selected records?')">
                        Delete Selected
                    </button>
                    <select name="new_press_name" id="new_press_name">
                        <option value="">Select Press Name</option>
                        <?php foreach ($allPresses as $press): ?>
                            <option value="<?= htmlspecialchars($press ?? '') ?>">
                                <?= htmlspecialchars($press ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="assign_press_name" id="assign_press_button" class="btn btn-primary disabled">Assign Press</button>
                    <button type="submit" name="auto_assign_press" id="auto_assign_press_button" class="btn btn-success">Auto-Assign Press</button>
                    <select name="bulk_action" id="bulk_action">
                        <option value="">Select Action</option>
                        <option value="clear">Clear Cavities</option>
                        <option value="set">Set Cavities</option>
                    </select>
                    <select multiple name="bulk_cavity_ids[]" id="bulk_cavity_ids" style="display: none;">
                        <?php foreach ($availableCavities as $cavity): ?>
                            <option value="<?= htmlspecialchars($cavity['cavity_id'] ?? '') ?>">
                                <?= htmlspecialchars($cavity['cavity_name'] ?: $cavity['cavity_id'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" id="bulk_submit" class="btn btn-primary disabled">Apply Action</button>
                </div>
                <!-- Results Table -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllCheckbox" onchange="toggleAll(this)"></th>
                                <th>ICode</th>
                                <?php foreach ($erpHeaders as $ref): ?>
                                    <th>
                                        <?= htmlspecialchars($ref ?? '') ?>
                                        <div class="erp-sub-column">To Be</div>
                                        <div class="erp-sub-column">Start Date</div>
                                        <div class="erp-sub-column">End Date</div>
                                    </th>
                                <?php endforeach; ?>
                                <th>Mold ID</th>
                                <th>Mold Info</th>
                                <th>To Be Sum</th>
                           
                                <th>Time Taken</th>
                               
                                <th>Description</th>
                                <th>Press Name</th>
                                <th>Cavity IDs</th>
                                <th>Earliest Start Date</th>
                                <th>Latest End Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searchResult as $row): ?>
                                <?php $isNonMatching = in_array($row['selection_id'], $nonMatchingRecords); ?>
                                <tr class="<?= $isNonMatching ? 'non-matching-row' : '' ?>" 
                                    <?= $isNonMatching ? 'title="This record does not exist in the process table and can be redirected."' : '' ?>>
                                    <td><input type="checkbox" name="selected_records[]" value="<?= htmlspecialchars($row['selection_id'] ?? '') ?>" class="record-checkbox"></td>
                                    <td class="icode-cell" style="background-color: <?= getIcodeColor($row['icode'] ?? '') ?>;">
                                        <?= htmlspecialchars($row['icode'] ?? '') ?>
                                        <input type="hidden" name="icode[<?= htmlspecialchars($row['selection_id'] ?? '') ?>]" value="<?= htmlspecialchars($row['icode'] ?? '') ?>">
                                        <input type="hidden" name="mold_id[<?= htmlspecialchars($row['selection_id'] ?? '') ?>]" value="<?= htmlspecialchars($row['mold_id'] ?? '') ?>">
                                    </td>
                                    <?php foreach ($erpHeaders as $ref): ?>
                                        <td class="numeric-cell">
                                            <div class="erp-numbers">
                                                <div>
                                                    <?php
                                                    $erpForRef = array_search($ref, $erpRefMapping);
                                                    echo isset($row['erp_numbers'][$erpForRef]) ? formatTobeSum($row['erp_numbers'][$erpForRef]['tobe'] ?? '') : '';
                                                    ?>
                                                </div>
                                                <div class="erp-sub-column">
                                                    <span class="date-display"><?= isset($row['erp_numbers'][$erpForRef]) ? formatDate($row['erp_numbers'][$erpForRef]['start_date'] ?? '') : '' ?></span>
                                                    <input type="datetime-local" name="start_date[<?= htmlspecialchars($row['selection_id'] ?? '') ?>][<?= htmlspecialchars($erpForRef ?? '') ?>]" 
                                                           value="<?= isset($row['erp_numbers'][$erpForRef]['start_date']) && $row['erp_numbers'][$erpForRef]['start_date'] ? date('Y-m-d\TH:i', strtotime($row['erp_numbers'][$erpForRef]['start_date'])) : '' ?>" style="display:none;">
                                                </div>
                                                <div class="erp-sub-column">
                                                    <span class="date-display"><?= isset($row['erp_numbers'][$erpForRef]) ? formatDate($row['erp_numbers'][$erpForRef]['end_date'] ?? '') : '' ?></span>
                                                    <input type="datetime-local" name="end_date[<?= htmlspecialchars($row['selection_id'] ?? '') ?>][<?= htmlspecialchars($erpForRef ?? '') ?>]" 
                                                           value="<?= isset($row['erp_numbers'][$erpForRef]['end_date']) && $row['erp_numbers'][$erpForRef]['end_date'] ? date('Y-m-d\TH:i', strtotime($row['erp_numbers'][$erpForRef]['end_date'])) : '' ?>" style="display:none;">
                                                </div>
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="mold-id-cell" style="background-color: <?= getMoldIdColor($row['mold_id'] ?? '') ?>;">
                                        <?= htmlspecialchars($row['mold_id'] ?? '') ?>
                                    </td>
                                    <td class="numeric-cell"><?= htmlspecialchars($row['mold_count'] ?? '') . ' / ' . htmlspecialchars($row['planned_mold_id'] ?? '') ?></td>
                                    <td class="numeric-cell"><?= formatTobeSum($row['tobe_sum'] ?? '') ?></td>
                                    
                                    <td class="numeric-cell"><?= formatTimeTaken($row['time_taken'] ?? '') ?></td>
                                    
                                    <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
                                    <td>
                                        <form method="POST" action="" class="inline-form single-press-form">
                                            <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress ?? '') ?>">
                                            <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode ?? '') ?>">
                                            <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId ?? '') ?>">
                                            <input type="hidden" name="selection_id" value="<?= htmlspecialchars($row['selection_id'] ?? '') ?>">
                                            <select name="single_press_name">
                                                <option value=""><?= htmlspecialchars($row['press_name'] ?? 'Select Press') ?></option>
                                                <?php foreach ($allPresses as $press): ?>
                                                    <option value="<?= htmlspecialchars($press ?? '') ?>" 
                                                            <?= ($row['press_name'] === $press) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($press ?? '') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="assign_single_press" class="btn btn-primary">Set</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" action="" class="inline-form cavity-assignment-form">
                                            <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress ?? '') ?>">
                                            <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode ?? '') ?>">
                                            <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId ?? '') ?>">
                                            <input type="hidden" name="selection_id" value="<?= htmlspecialchars($row['selection_id'] ?? '') ?>">
                                            <select multiple name="cavity_ids[]">
                                                <option value="">Select Cavities</option>
                                                <?php foreach ($availableCavities as $cavity): ?>
                                                    <option value="<?= htmlspecialchars($cavity['cavity_id'] ?? '') ?>" 
                                                            <?= in_array($cavity['cavity_id'], explode(',', $row['cavity_ids'] ?? '')) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($cavity['cavity_name'] ?: $cavity['cavity_id'] ?? '') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="action-buttons">
                                                <button type="submit" name="assign_cavities" class="btn btn-primary">Assign</button>
                                                <button type="submit" name="clear_cavities" class="btn btn-warning">Clear</button>
                                            </div>
                                        </form>
                                        <div>
                                            <?= displayCavityIds($row['cavity_ids'] ?? '', $row['cavity_count'] ?? null,                                            $cavityNamesCache) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars(formatDate($row['earliest_start_date'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars(formatDate($row['latest_end_date'] ?? '')) ?></td>
                                    <td class="action-buttons">
                                        <form method="POST" action="" class="inline-form">
                                            <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress ?? '') ?>">
                                            <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode ?? '') ?>">
                                            <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId ?? '') ?>">
                                            <input type="hidden" name="delete_id" value="<?= htmlspecialchars($row['selection_id'] ?? '') ?>">
                                            <button type="submit" class="btn btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this record?')">
                                                Delete
                                            </button>

                                        </form>
                                        <?php if ($isNonMatching): ?>
                                            <form method="POST" action="" class="inline-form">
                                                <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress ?? '') ?>">
                                                <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode ?? '') ?>">
                                                <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId ?? '') ?>">
                                                <input type="hidden" name="selection_id" value="<?= htmlspecialchars($row['selection_id'] ?? '') ?>">
                                                <button type="submit" name="redirect_single_non_matching" class="btn btn-warning">
                                                    Add Mold
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-info edit-dates-btn" onclick="enableEditDates(this)">Edit Dates</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="save_start_dates" class="btn btn-success">Save All Start/End Dates</button>
            </form>
        <?php else: ?>
            <p>No records found for the selected filters.</p>
        <?php endif; ?>
        <!-- Press Information -->
        <?php if ($pressInfo && !empty($pressInfo)): ?>
            <div class="summary-box" style="margin-top: 20px;">
                <h3>Press Information</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
                    <div><strong>Press Name:</strong> <?= htmlspecialchars($pressInfo['press_name'] ?? '') ?></div>
                    <div><strong>Press ID:</strong> <?= htmlspecialchars($pressInfo['press_id'] ?? '') ?></div>
                    <div><strong>Is Available:</strong> <?= $pressInfo['is_available'] ? 'Yes' : 'No' ?></div>
                    <div><strong>Availability Date:</strong> <?= htmlspecialchars($pressInfo['availability_date'] ?? '') ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>