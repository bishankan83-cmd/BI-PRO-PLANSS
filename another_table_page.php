


<?php
// Database configuration
$host = 'localhost';
$dbname = 'planatir_task_managemen';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

// Create connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// SQL query to update is_hidden to 0 where is_new is 1
$sql = "UPDATE press_selections SET is_new = 1 WHERE is_hidden = '1'";

if ($mysqli->query($sql) === TRUE) {
    echo "Records updated successfully";
} else {
    echo "Error updating records: " . $mysqli->error;
}

// Close connection
$mysqli->close();
?>






<?php
// Database configuration
$host = 'localhost';
$dbname = 'planatir_task_managemen';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

// Create connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// SQL query to update is_hidden to 0 where is_new is 1
$sql = "UPDATE press_selections SET is_hidden = 0 WHERE is_new = '1'";

if ($mysqli->query($sql) === TRUE) {
    echo "Records updated successfully";
} else {
    echo "Error updating records: " . $mysqli->error;
}

// Close connection
$mysqli->close();
?>







<?php
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
$showNonPress = false;
$cavityNamesCache = [];
$availableCavities = [];
$additionalMoldIds = [];

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
    $newPressName = trim($_POST['new_press_name']);

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
    $newPressName = trim($_POST['single_press_name']);

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

// Handle auto-assign press_name for non-press records
if ($_POST && isset($_POST['auto_assign_non_press']) && isset($_POST['new_press_name'])) {
    $newPressName = trim($_POST['new_press_name']);
    
    if (!empty($newPressName)) {
        try {
            $pdo = createPDOConnection($host, $dbname, $username, $password);
            $stmt = $pdo->prepare("
                UPDATE press_selections 
                SET press_name = ? 
                WHERE (press_name IS NULL OR press_name = '') 
                AND is_hidden = 0
            ");
            $stmt->execute([$newPressName]);

            $updatedCount = $stmt->rowCount();
            if ($updatedCount > 0) {
                $successMessage = "Successfully assigned press name to {$updatedCount} non-press record(s)!";
            } else {
                $errorMessage = "No non-press records found to update.";
            }
        } catch (Exception $e) {
            $errorMessage = "Auto-assign press name error: " . $e->getMessage();
        }
    } else {
        $errorMessage = "No press name provided for auto-assignment.";
    }
}

// Handle auto-assign cavity IDs request
if ($_POST && isset($_POST['auto_assign_cavities'])) {
    try {
        $pdo = createPDOConnection($host, $dbname, $username, $password);
        $autoAssignResult = autoAssignCavityIdsByMoldId($pdo, $_POST['press_select']);
        if ($autoAssignResult['success']) {
            $successMessage = $autoAssignResult['message'];
        } else {
            $errorMessage = $autoAssignResult['message'];
        }
    } catch (Exception $e) {
        $errorMessage = "Auto-assign error: " . $e->getMessage();
    }
}

// Handle priority auto-assign cavity IDs request
if ($_POST && isset($_POST['auto_assign_cavities_priority'])) {
    try {
        $pdo = createPDOConnection($host, $dbname, $username, $password);
        $autoAssignResult = autoAssignCavityIdsByMoldIdWithPriority($pdo, $_POST['press_select']);
        if ($autoAssignResult['success']) {
            $successMessage = $autoAssignResult['message'];
        } else {
            $errorMessage = $autoAssignResult['message'];
        }
    } catch (Exception $e) {
        $errorMessage = "Priority auto-assign error: " . $e->getMessage();
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
    ps.start_date = psc.start_date,
    ps.is_completed = psc.is_completed;
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
    $moldIdToInclude = trim($_POST['mold_id_to_include']);
    $selectedIcode = trim($_POST['icode_select']);
    try {
        $pdo = createPDOConnection($host, $dbname, $username, $password);
        $stmt = $pdo->prepare("
            UPDATE press_selections 
            SET is_hidden = 0 
            WHERE icode = ? AND mold_id = ?
        ");
        $stmt->execute([$selectedIcode, $moldIdToInclude]);
        $updatedCount = $stmt->rowCount();
        if ($updatedCount > 0) {
            $successMessage = "Successfully included mold ID {$moldIdToInclude} for ICode {$selectedIcode}!";
        } else {
            $errorMessage = "No records found to include for mold ID {$moldIdToInclude}.";
        }
    } catch (Exception $e) {
        $errorMessage = "Include mold ID error: " . $e->getMessage();
    }
}

// Handle remove pressed records without cavities
if ($_POST && isset($_POST['remove_pressed_no_cavities'])) {
    $selectedPress = isset($_POST['press_select']) ? trim($_POST['press_select']) : '';
    $selectedIcode = isset($_POST['icode_select']) ? trim($_POST['icode_select']) : '';
    $selectedMoldId = isset($_POST['mold_id_select']) ? trim($_POST['mold_id_select']) : '';

    try {
        $pdo = createPDOConnection($host, $dbname, $username, $password);

        // Build the DELETE query with filters
        $query = "
            DELETE FROM press_selections 
            WHERE press_name IS NOT NULL 
            AND press_name != '' 
            AND (cavity_ids IS NULL OR cavity_ids = '')
            AND is_hidden = 0
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

// Handle form submission with filters
if ($_POST && (isset($_POST['press_select']) || isset($_POST['show_non_press']))) {
    $selectedPress = isset($_POST['press_select']) ? trim($_POST['press_select']) : '';
    $selectedIcode = isset($_POST['icode_select']) ? trim($_POST['icode_select']) : '';
    $selectedMoldId = isset($_POST['mold_id_select']) ? trim($_POST['mold_id_select']) : '';
    $showNonPress = isset($_POST['show_non_press']) && $_POST['show_non_press'] == '1';

    try {
        $pdo = createPDOConnection($host, $dbname, $username, $password);
        createCavityIdsColumn($pdo);
        ensureEndDateColumn($pdo);
        ensureStartDateColumn($pdo);
        ensureIsHiddenColumn($pdo);

        // Fetch cavity names for all possible cavity_ids
        $cavityStmt = $pdo->query("SELECT cavity_id, cavity_name FROM cavity");
        while ($row = $cavityStmt->fetch()) {
            $cavityNamesCache[$row['cavity_id']] = $row['cavity_name'];
        }

        // Fetch available cavities with names for the selected press (if any)
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

        // Fetch additional mold IDs for the selected icode that are hidden
        if (!empty($selectedIcode)) {
            $moldStmt = $pdo->prepare("
                SELECT DISTINCT mold_id
                FROM press_selections
                WHERE icode = ? AND is_hidden = 1
                ORDER BY mold_id ASC
            ");
            $moldStmt->execute([$selectedIcode]);
            $additionalMoldIds = $moldStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        // Build dynamic query with filters
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
                p.start_date,
                p.end_date,
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
            WHERE p.is_hidden = 0
        ";
        $params = [];

        if ($showNonPress) {
            $query .= " AND (p.press_name IS NULL OR p.press_name = '')";
        } elseif (!empty($selectedPress)) {
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
                CASE WHEN p.mold_id IS NULL OR p.mold_id = '' THEN 1 ELSE 0 END,
                p.end_date ASC,
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
                htmlspecialchars(($showNonPress ? "Non-Press Records" : $selectedPress) . 
                ($selectedIcode ? ", ICode: $selectedIcode" : "") . 
                ($selectedMoldId ? ", Mold ID: $selectedMoldId" : ""));
        } else {
            if (!$showNonPress && $selectedPress) {
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

            $moldSchedule = [];

            foreach ($searchResult as &$row) {
                $row['press_id'] = $pressInfo['press_id'] ?? null;
                $row['is_available'] = $pressInfo['is_available'] ?? null;
                $row['availability_date'] = $pressInfo['availability_date'] ?? null;
                $row['available_cavity_ids'] = $availableCavityIds;

                $countStmt = $pdo->prepare("
                    SELECT COUNT(DISTINCT mold_id) as planned_mold_id
                    FROM press_selections 
                    WHERE icode = ? 
                    AND (press_name = ? OR press_name REGEXP CONCAT('^', ?, '-[0-9]+$') OR press_name IS NULL OR press_name = '')
                    AND mold_id IS NOT NULL 
                    AND mold_id != ''
                    AND is_hidden = 0
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

                $sumPerMold = calculateSumPerMold($row['tobe_sum'], $row['planned_mold_id']);
                $totalTime = calculateTotalTime($sumPerMold, $row['time_taken']);
                $endDateTime = calculateEndDateTime($totalTime, $row['mold_id'], $moldSchedule);
                if ($endDateTime && $endDateTime['end']) {
                    $estimatedStart = $endDateTime['start']->format('Y-m-d H:i:s');
                    $estimatedEnd = $endDateTime['end']->format('Y-m-d H:i:s');
                    $updateStmt = $pdo->prepare("
                        UPDATE press_selections 
                        SET start_date = ?, end_date = ?
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$estimatedStart, $estimatedEnd, $row['selection_id']]);
                    $row['start_date'] = $estimatedStart;
                    $row['end_date'] = $estimatedEnd;
                    $row['formatted_start_date'] = date('Y-m-d H:i', strtotime($estimatedStart));
                    $row['formatted_end_date'] = date('Y-m-d H:i', strtotime($estimatedEnd));
                } else {
                    $row['formatted_start_date'] = 'Not set';
                    $row['formatted_end_date'] = 'Not set';
                }

                if ($row['start_date'] && $row['start_date'] != '0000-00-00' && $row['start_date'] != '0000-00-00 00:00:00') {
                    $row['formatted_start_date'] = date('Y-m-d H:i', strtotime($row['start_date']));
                } else {
                    $row['formatted_start_date'] = 'Not set';
                }

                if ($row['end_date'] && $row['end_date'] != '0000-00-00' && $row['end_date'] != '0000-00-00 00:00:00') {
                    $row['formatted_end_date'] = date('Y-m-d H:i', strtotime($row['end_date']));
                } else {
                    $row['formatted_end_date'] = 'Not set';
                }
            }
            unset($row);
        }
    } catch (Exception $e) {
        $errorMessage = "Database error: " . $e->getMessage();
        $searchResult = [];
    }
}

// Get all press names, icodes, and mold_ids from the press_selections table
$allPresses = [];
$allIcodes = [];
$allMoldIds = [];
try {
    $pdo = createPDOConnection($host, $dbname, $username, $password);
    $updateError = updatePressNames($pdo);
    if ($updateError) {
        $errorMessage = $updateError;
    }
    createCavityIdsColumn($pdo);
    ensureIsHiddenColumn($pdo);
    
    // Fetch presses
    $stmt = $pdo->query("
        SELECT DISTINCT press_name 
        FROM press_selections 
        WHERE press_name IS NOT NULL 
        AND press_name != ''
        AND is_hidden = 0
        ORDER BY press_name ASC
    ");
    $allPresses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allPresses = array_unique($allPresses);
    sort($allPresses);

    // Fetch icodes
    $stmt = $pdo->query("
        SELECT DISTINCT icode 
        FROM press_selections 
        WHERE icode IS NOT NULL 
        AND icode != ''
        AND is_hidden = 0
        ORDER BY icode ASC
    ");
    $allIcodes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allIcodes = array_unique($allIcodes);
    sort($allIcodes);

    // Fetch mold_ids
    $stmt = $pdo->query("
        SELECT DISTINCT mold_id 
        FROM press_selections 
        WHERE mold_id IS NOT NULL 
        AND mold_id != ''
        AND is_hidden = 0
        ORDER BY mold_id ASC
    ");
    $allMoldIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allMoldIds = array_unique($allMoldIds);
    sort($allMoldIds);
} catch (Exception $e) {
    $errorMessage = "Database connection error: " . $e->getMessage();
}

// Function to ensure start_date column is DATETIME
function ensureStartDateColumn($pdo) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM press_selections LIKE 'start_date'");
        $column = $stmt->fetch();
        if ($column) {
            if (stripos($column['Type'], 'date') !== false && stripos($column['Type'], 'datetime') === false) {
                $pdo->exec("ALTER TABLE press_selections MODIFY COLUMN start_date DATETIME NULL");
            }
        } else {
            $pdo->exec("ALTER TABLE press_selections ADD COLUMN start_date DATETIME NULL");
        }
    } catch (Exception $e) {
        error_log("Error ensuring start_date column: " . $e->getMessage());
    }
}

// Function to ensure end_date column is DATETIME
function ensureEndDateColumn($pdo) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM press_selections LIKE 'end_date'");
        $column = $stmt->fetch();
        if ($column) {
            if (stripos($column['Type'], 'date') !== false && stripos($column['Type'], 'datetime') === false) {
                $pdo->exec("ALTER TABLE press_selections MODIFY COLUMN end_date DATETIME NULL");
            }
        } else {
            $pdo->exec("ALTER TABLE press_selections ADD COLUMN end_date DATETIME NULL");
        }
    } catch (Exception $e) {
        error_log("Error ensuring end_date column: " . $e->getMessage());
    }
}

// Function to ensure is_hidden column exists
function ensureIsHiddenColumn($pdo) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM press_selections LIKE 'is_hidden'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE press_selections ADD COLUMN is_hidden TINYINT(1) DEFAULT 0");
        }
    } catch (Exception $e) {
        error_log("Error ensuring is_hidden column: " . $e->getMessage());
    }
}

// Function to auto-assign cavity IDs based on mold_id only
function autoAssignCavityIdsByMoldId($pdo, $pressName) {
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.id, p.icode, p.mold_id, p.press_name, p.cavity_ids
            FROM press_selections p
            WHERE (p.press_name = ? OR p.press_name REGEXP CONCAT('^', ?, '-[0-9]+$'))
            AND p.mold_id IS NOT NULL
            AND p.mold_id != ''
            AND p.is_hidden = 0
            ORDER BY p.mold_id, p.id
        ");
        $stmt->execute([$pressName, $pressName]);
        $records = $stmt->fetchAll();

        if (empty($records)) {
            return ['success' => false, 'message' => 'No records found for auto-assignment.'];
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
            return ['success' => false, 'message' => 'No available cavity IDs found for this press.'];
        }

        $uniqueMoldIds = array_unique(array_column($records, 'mold_id'));
        sort($uniqueMoldIds);

        $moldToCavityMapping = [];
        $totalCavities = count($availableCavities);
        
        foreach ($uniqueMoldIds as $index => $moldId) {
            $assignedCavityId = $availableCavities[$index % $totalCavities];
            $moldToCavityMapping[$moldId] = $assignedCavityId;
        }

        $assignedCount = 0;
        $skippedCount = 0;
        
        foreach ($records as $record) {
            if (!empty($record['cavity_ids'])) {
                $skippedCount++;
                continue;
            }
            
            $assignedCavityId = $moldToCavityMapping[$record['mold_id']];
            $updateStmt = $pdo->prepare("UPDATE press_selections SET cavity_ids = ? WHERE id = ?");
            $updateStmt->execute([$assignedCavityId, $record['id']]);
            $assignedCount++;
        }

        $totalMoldIds = count($uniqueMoldIds);
        $message = "Auto-assigned cavity IDs to {$assignedCount} records across {$totalMoldIds} unique mold_ids!";
        
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} records already had cavity assignments and were skipped)";
        }
        
        if ($totalMoldIds > $totalCavities) {
            $message .= " Note: {$totalMoldIds} mold_ids were assigned to {$totalCavities} available cavities.";
        }

        return ['success' => true, 'message' => $message];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Auto-assign error: " . $e->getMessage()];
    }
}

// Enhanced version that considers end_date for mold_id priority
function autoAssignCavityIdsByMoldIdWithPriority($pdo, $pressName) {
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.id, p.icode, p.mold_id, p.press_name, p.cavity_ids, p.end_date
            FROM press_selections p
            WHERE (p.press_name = ? OR p.press_name REGEXP CONCAT('^', ?, '-[0-9]+$'))
            AND p.mold_id IS NOT NULL
            AND p.mold_id != ''
            AND p.is_hidden = 0
            ORDER BY p.end_date ASC, p.mold_id, p.id
        ");
        $stmt->execute([$pressName, $pressName]);
        $records = $stmt->fetchAll();

        if (empty($records)) {
            return ['success' => false, 'message' => 'No records found for auto-assignment.'];
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
            return ['success' => false, 'message' => 'No available cavity IDs found for this press.'];
        }

        $moldIdPriority = [];
        foreach ($records as $record) {
            if (!isset($moldIdPriority[$record['mold_id']])) {
                $moldIdPriority[$record['mold_id']] = $record['end_date'];
            } else {
                if ($record['end_date'] && 
                    (!$moldIdPriority[$record['mold_id']] || 
                     $record['end_date'] < $moldIdPriority[$record['mold_id']])) {
                    $moldIdPriority[$record['mold_id']] = $record['end_date'];
                }
            }
        }

        uksort($moldIdPriority, function($a, $b) use ($moldIdPriority) {
            $dateA = $moldIdPriority[$a];
            $dateB = $moldIdPriority[$b];
            
            if ($dateA && $dateB) {
                return strcmp($dateA, $dateB);
            } elseif ($dateA) {
                return -1;
            } elseif ($dateB) {
                return 1;
            }
            return strcmp($a, $b);
        });

        $moldToCavityMapping = [];
        $totalCavities = count($availableCavities);
        $index = 0;
        
        foreach (array_keys($moldIdPriority) as $moldId) {
            $assignedCavityId = $availableCavities[$index % $totalCavities];
            $moldToCavityMapping[$moldId] = $assignedCavityId;
            $index++;
        }

        $assignedCount = 0;
        $skippedCount = 0;
        
        foreach ($records as $record) {
            if (!empty($record['cavity_ids'])) {
                $skippedCount++;
                continue;
            }
            
            $assignedCavityId = $moldToCavityMapping[$record['mold_id']];
            $updateStmt = $pdo->prepare("UPDATE press_selections SET cavity_ids = ? WHERE id = ?");
            $updateStmt->execute([$assignedCavityId, $record['id']]);
            $assignedCount++;
        }

        $totalMoldIds = count($moldIdPriority);
        $message = "Priority-based auto-assigned cavity IDs to {$assignedCount} records across {$totalMoldIds} unique mold_ids!";
        
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} records already had cavity assignments and were skipped)";
        }
        
        if ($totalMoldIds > $totalCavities) {
            $message .= " Note: {$totalMoldIds} mold_ids assigned to {$totalCavities} available cavities with priority.";
        }

        return ['success' => true, 'message' => $message];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Priority auto-assign error: " . $e->getMessage()];
    }
}

// Function to create cavity_ids column if it doesn't exist
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
            AND is_hidden = 0
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
                   htmlspecialchars($displayText) . '</span>';
    }
    $output .= '</div>';
    
    if ($cavityCount && $cavityCount > 1) {
        $output .= '<div style="font-size: 0.8em; color: #666;">Total: ' . $cavityCount . '</div>';
    }
    
    return $output;
}

// Calculation functions
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

function calculateEndDateTime($totalMinutes, $moldId = null, &$moldSchedule = null) {
    if (!is_numeric($totalMinutes) || $totalMinutes == 0) {
        return null;
    }
    
    $defaultStartTime = new DateTime('today 00:00:00');
    
    if ($moldId === null || $moldSchedule === null) {
        $startTime = clone $defaultStartTime;
    } else {
        $startTime = isset($moldSchedule[$moldId]) ? clone $moldSchedule[$moldId] : clone $defaultStartTime;
    }
    
    $endTime = clone $startTime;
    
    $absMinutes = abs($totalMinutes);
    $intervalSpec = 'PT' . intval($absMinutes) . 'M';
    $interval = new DateInterval($intervalSpec);
    
    if ($totalMinutes < 0) {
        $endTime->sub($interval);
    } else {
        $endTime->add($interval);
    }
    
    if ($moldId !== null && $moldSchedule !== null) {
        $moldSchedule[$moldId] = clone $endTime;
    }
    
    return [
        'start' => $startTime,
        'end' => $endTime,
        'total_minutes' => $totalMinutes
    ];
}

function formatDuration($minutes) {
    if (!is_numeric($minutes) || $minutes == 0) return 'N/A';
    
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
    if (!is_numeric($tobeSum) || $tobeSum == 0) return 'N/A';
    return number_format($tobeSum, 0);
}

function formatTimeTaken($timeTaken) {
    if (!is_numeric($timeTaken) || $timeTaken == 0) return 'N/A';
    return $timeTaken . ' min';
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
        .date-cell { white-space: nowrap; }
        .icode-cell, .mold-id-cell { padding: 4px; border-radius: 3px; color: white; }
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

        
    </style>
</head>
<body>
    <div class="container">
        <h1>Press Selection Management</h1>

        <!-- Form for Copy Data and Update from Copy -->
        <div class="filter-group">
            <form method="POST" action="">
                <?php
                // DB connection for copy data
                $conn = new mysqli($host, $username, $password, $dbname);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Handle copy data request
                if (isset($_POST['copy_data'])) {
                    // Step 1: Delete all from target table
                    $deleteSql = "DELETE FROM press_selections_copy";
                    if (!$conn->query($deleteSql)) {
                        die("Error deleting old data: " . $conn->error);
                    }

                    // Step 2: Insert from press_selections to press_selections_copy
                    $insertSql = "INSERT INTO press_selections_copy (
                        id, icode, mold_id, press_name, mold_count, tobe_sum, description, created_at, updated_at, cavity_ids, end_date, start_date, is_completed
                    )
                    SELECT 
                        id, icode, mold_id, press_name, mold_count, tobe_sum, description, created_at, updated_at, cavity_ids, end_date, start_date, is_completed
                    FROM press_selections";

                    if ($conn->query($insertSql)) {
                        // Redirect on success
                        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success");
                        exit();
                    } else {
                        // Redirect on error
                        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&msg=" . urlencode($conn->error));
                        exit();
                    }
                }

                $conn->close();
                ?>
               <?php
if (isset($_POST['go'])) {
    header("Location: copy_com1.php");
    exit();
}
?>

<form method="post">
  <button type="submit" name="go" class="btn btn-primary">Go to Another Page</button>
</form>

            </form>
            <form method="POST" action="">
                <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress) ?>">
                <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode) ?>">
                <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId) ?>">
                <button type="submit" name="update_from_copy" class="btn btn-info">Get Previous Data</button>
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
                            <option value="">Select a Press</option>
                            <?php foreach ($allPresses as $press): ?>
                                <option value="<?= htmlspecialchars($press) ?>" 
                                        <?= ($selectedPress === $press) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($press) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="show_non_press">Show Non-Press Records:</label>
                        <input type="checkbox" name="show_non_press" id="show_non_press" value="1" 
                               <?= $showNonPress ? 'checked' : '' ?> onchange="this.form.submit()">
                    </div>
                    <div>
                        <label for="icode_select">Select ICode:</label>
                        <select name="icode_select" id="icode_select">
                            <option value="">All ICodes</option>
                            <?php foreach ($allIcodes as $icode): ?>
                                <option value="<?= htmlspecialchars($icode) ?>" 
                                        <?= ($selectedIcode === $icode) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($icode) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="mold_id_select">Select Mold ID:</label>
                        <select name="mold_id_select" id="mold_id_select">
                            <option value="">All Mold IDs</option>
                            <?php foreach ($allMoldIds as $moldId): ?>
                                <option value="<?= htmlspecialchars($moldId) ?>" 
                                        <?= ($selectedMoldId === $moldId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($moldId) ?>
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
                <h4>Additional Mold IDs for ICode: <?= htmlspecialchars($selectedIcode) ?></h4>
                <div class="mold-id-list">
                    <?php foreach ($additionalMoldIds as $moldId): ?>
                        <form method="POST" action="" class="inline-form">
                            <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress) ?>">
                            <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode) ?>">
                            <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId) ?>">
                            <input type="hidden" name="mold_id_to_include" value="<?= htmlspecialchars($moldId) ?>">
                            <button type="submit" name="include_mold_id" class="btn btn-success">
                                Include <?= htmlspecialchars($moldId) ?>
                            </button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($searchResult && !empty($searchResult)): ?>
            <h3>Cavity Assignment Tools</h3>
            <div class="bulk-actions">
                <?php if ($selectedPress && !$showNonPress): ?>
                    <form method="POST" action="" class="inline-form">
                        <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress) ?>">
                        <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode) ?>">
                        <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId) ?>">
                        <button type="submit" name="auto_assign_cavities" class="btn btn-warning">
                            Auto-Assign Cavity IDs (by Mold ID)
                        </button>
                    </form>
                    
                    <form method="POST" action="" class="inline-form">
                        <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress) ?>">
                        <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode) ?>">
                        <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId) ?>">
                        <button type="submit" name="auto_assign_cavities_priority" class="btn btn-success">
                            Auto-Assign with Priority (End Date + Mold ID)
                        </button>
                    </form>
                    
                    <form method="POST" action="" class="inline-form">
                        <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress) ?>">
                        <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode) ?>">
                        <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId) ?>">
                        <button type="submit" name="remove_pressed_no_cavities" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to remove records with a press name but no cavity IDs?')">
                            Remove Pressed w/o Cavities
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Summary Information -->
            <div class="summary-box">
                <h3>Press Summary: <?= htmlspecialchars($showNonPress ? 'Non-Press Records' : $selectedPress) ?><?= $selectedIcode ? " | ICode: $selectedIcode" : "" ?><?= $selectedMoldId ? " | Mold ID: $selectedMoldId" : "" ?></h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
                    <div><strong>Total Records:</strong> <?= count($searchResult) ?></div>
                    <div><strong>Unique ICodes:</strong> <?= count(array_unique(array_column($searchResult, 'icode'))) ?></div>
                    <div><strong>Unique Mold IDs:</strong> <?= count(array_unique(array_filter(array_column($searchResult, 'mold_id')))) ?></div>
                    <div><strong>Available Cavities:</strong> <?= htmlspecialchars($searchResult[0]['available_cavity_ids'] ?? 'N/A') ?></div>
                </div>
            </div>

            <!-- Bulk Delete and Bulk Set Form -->
            <form method="POST" action="" id="bulkActionForm">
                <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress) ?>">
                <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode) ?>">
                <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId) ?>">
                <input type="hidden" name="show_non_press" value="<?= $showNonPress ? '1' : '0' ?>">
                
                <div class="bulk-actions">
                    <button type="button" onclick="selectAll()" class="btn btn-secondary">Select All</button>
                    <button type="button" onclick="deselectAll()" class="btn btn-secondary">Deselect All</button>
                    <button type="button" onclick="selectNonPressRecords()" class="btn btn-info">Select Non-Press</button>
                    <button type="submit" name="bulk_delete" class="btn btn-danger" 
                            onclick="return confirm('Are you sure you want to delete selected records?')">
                        Delete Selected
                    </button>
                    <?php if ($showNonPress): ?>
                        <select name="new_press_name" id="new_press_name">
                            <option value="">Select Press Name</option>
                            <?php foreach ($allPresses as $press): ?>
                                <option value="<?= htmlspecialchars($press) ?>">
                                    <?= htmlspecialchars($press) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="assign_press_name" id="assign_press_button" class="btn btn-primary disabled">Assign Press</button>
                        <button type="submit" name="auto_assign_non_press" id="auto_assign_non_press_button" class="btn btn-success">Auto-Assign Non-Press</button>
                    <?php else: ?>
                        <select name="bulk_action" id="bulk_action">
                            <option value="">Select Action</option>
                            <option value="clear">Clear Cavities</option>
                            <option value="set">Set Cavities</option>
                        </select>
                        <select multiple name="bulk_cavity_ids[]" id="bulk_cavity_ids" style="display: none;">
                            <?php foreach ($availableCavities as $cavity): ?>
                                <option value="<?= htmlspecialchars($cavity['cavity_id']) ?>">
                                    <?= htmlspecialchars($cavity['cavity_name'] ?: $cavity['cavity_id']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" id="bulk_submit" class="btn btn-primary disabled">Apply Action</button>
                    <?php endif; ?>
                </div>

                <!-- Results Table -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllCheckbox" onchange="toggleAll(this)"></th>
                                <th>ID</th>
                                <th>ICode</th>
                                <th>Mold ID</th>
                                <th>Mold Count</th>
                                <th>Planned Mold ID</th>
                                <th>To Be Sum</th>
                                <th>Sum/Mold</th>
                                <th>Time Taken</th>
                                <th>Total Time</th>
                                <th>Description</th>
                                <th>Press Name</th>
                                <?php if ($showNonPress): ?>
                                    <th>Assign Press</th>
                                <?php else: ?>
                                    <th>Cavity Names</th>
                                    <th>Cavity Count</th>
                                <?php endif; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $moldSchedule = [];
                            foreach ($searchResult as $row): 
                                $sumPerMold = calculateSumPerMold($row['tobe_sum'], $row['planned_mold_id']);
                                $totalTime = calculateTotalTime($sumPerMold, $row['time_taken']);
                                $endDateTime = calculateEndDateTime($totalTime, $row['mold_id'], $moldSchedule);
                                $currentCavityIds = $row['cavity_ids'] ? array_filter(explode(',', $row['cavity_ids'])) : [];
                            ?>
                                <tr>
                                    <td><input type="checkbox" name="selected_records[]" 
                                               value="<?= htmlspecialchars($row['selection_id']) ?>" 
                                               class="record-checkbox" 
                                               data-press-name="<?= htmlspecialchars($row['press_name'] ?? '') ?>"></td>
                                    <td><?= htmlspecialchars($row['selection_id']) ?></td>
                                    <td>
                                        <div class="icode-cell" 
                                             style="background-color: <?= getIcodeColor($row['icode']) ?>">
                                            <?= htmlspecialchars($row['icode']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mold-id-cell" 
                                             style="background-color: <?= getMoldIdColor($row['mold_id']) ?>">
                                            <?= htmlspecialchars($row['mold_id'] ?? 'N/A') ?>
                                        </div>
                                    </td>
                                    <td class="numeric-cell"><?= htmlspecialchars($row['mold_count'] ?? 'N/A') ?></td>
                                    <td class="numeric-cell"><?= htmlspecialchars($row['planned_mold_id']) ?></td>
                                    <td class="numeric-cell"><?= formatTobeSum($row['tobe_sum']) ?></td>
                                    <td class="numeric-cell"><?= $sumPerMold > 0 ? number_format($sumPerMold, 2) : 'N/A' ?></td>
                                    <td class="numeric-cell"><?= formatTimeTaken($row['time_taken']) ?></td>
                                    <td class="numeric-cell"><?= formatDuration($totalTime) ?></td>
                                    <td><?= htmlspecialchars(substr($row['description'], 0, 50)) ?><?= strlen($row['description']) > 50 ? '...' : '' ?></td>
                                    <td><?= htmlspecialchars($row['press_name'] ?? 'N/A') ?></td>
                                    <?php if ($showNonPress): ?>
                                        <td>
                                            <div class="single-press-form">
                                                <form method="POST" action="" class="inline-form">
                                                    <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress) ?>">
                                                    <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode) ?>">
                                                    <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId) ?>">
                                                    <input type="hidden" name="show_non_press" value="1">
                                                    <input type="hidden" name="selection_id" value="<?= htmlspecialchars($row['selection_id']) ?>">
                                                    <select name="single_press_name">
                                                        <option value="">Select Press</option>
                                                        <?php foreach ($allPresses as $press): ?>
                                                            <option value="<?= htmlspecialchars($press) ?>">
                                                                <?= htmlspecialchars($press) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" name="assign_single_press" class="btn btn-primary">Set</button>
                                                </form>
                                            </div>
                                        </td>
                                    <?php else: ?>
                                        <td><?= displayCavityIds($row['cavity_ids'], $row['cavity_count'], $cavityNamesCache) ?></td>
                                        <td class="numeric-cell"><?= $row['cavity_count'] ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if (!$showNonPress): ?>
                                                <div class="cavity-assignment-form">
                                                    <form method="POST" action="" class="inline-form">
                                                        <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress) ?>">
                                                        <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode) ?>">
                                                        <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId) ?>">
                                                        <input type="hidden" name="selection_id" value="<?= htmlspecialchars($row['selection_id']) ?>">
                                                        <select multiple name="cavity_ids[]">
                                                            <?php foreach ($availableCavities as $cavity): ?>
                                                                <option value="<?= htmlspecialchars($cavity['cavity_id']) ?>" 
                                                                        <?= in_array($cavity['cavity_id'], $currentCavityIds) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($cavity['cavity_name'] ?? $cavity['cavity_id']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" name="assign_cavities" class="btn btn-success">
                                                            Assign
                                                        </button>
                                                    </form>
                                                </div>
                                                
                                                <form method="POST" action="" class="inline-form">
                                                    <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress) ?>">
                                                    <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode) ?>">
                                                    <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId) ?>">
                                                    <input type="hidden" name="selection_id" value="<?= htmlspecialchars($row['selection_id']) ?>">
                                                    <button type="submit" name="clear_cavities" class="btn btn-secondary">
                                                        Clear
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" action="" class="inline-form">
                                                <input type="hidden" name="press_select" value="<?= htmlspecialchars($selectedPress) ?>">
                                                <input type="hidden" name="icode_select" value="<?= htmlspecialchars($selectedIcode) ?>">
                                                <input type="hidden" name="mold_id_select" value="<?= htmlspecialchars($selectedMoldId) ?>">
                                                <input type="hidden" name="show_non_press" value="<?= $showNonPress ? '1' : '0' ?>">
                                                <input type="hidden" name="delete_id" value="<?= htmlspecialchars($row['selection_id']) ?>">
                                                <button type="submit" class="btn btn-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this record?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function selectAll() {
            const checkboxes = document.querySelectorAll('.record-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = true);
            document.getElementById('selectAllCheckbox').checked = true;
            updateBulkSubmitButton();
        }

        function deselectAll() {
            const checkboxes = document.querySelectorAll('.record-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = false);
            document.getElementById('selectAllCheckbox').checked = false;
            updateBulkSubmitButton();
        }

        function selectNonPressRecords() {
            const checkboxes = document.querySelectorAll('.record-checkbox');
            checkboxes.forEach(checkbox => {
                const pressName = checkbox.getAttribute('data-press-name');
                checkbox.checked = !pressName || pressName === 'N/A';
            });
            updateBulkSubmitButton();
            const checkedBoxes = document.querySelectorAll('.record-checkbox:checked');
            const masterCheckbox = document.getElementById('selectAllCheckbox');
            if (checkedBoxes.length === 0) {
                masterCheckbox.indeterminate = false;
                masterCheckbox.checked = false;
            } else if (checkedBoxes.length === checkboxes.length) {
                masterCheckbox.indeterminate = false;
                masterCheckbox.checked = true;
            } else {
                masterCheckbox.indeterminate = true;
            }
        }

        function toggleAll(masterCheckbox) {
            const checkboxes = document.querySelectorAll('.record-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = masterCheckbox.checked);
            updateBulkSubmitButton();
        }

        function updateBulkSubmitButton() {
            const checkboxes = document.querySelectorAll('.record-checkbox:checked');
            const bulkSubmit = document.getElementById('bulk_submit');
            const assignPressButton = document.getElementById('assign_press_button');
            const autoAssignNonPressButton = document.getElementById('auto_assign_non_press_button');
            if (checkboxes.length > 0) {
                if (bulkSubmit) bulkSubmit.classList.remove('disabled');
                if (assignPressButton) assignPressButton.classList.remove('disabled');
                if (autoAssignNonPressButton) autoAssignNonPressButton.classList.remove('disabled');
            } else {
                if (bulkSubmit) bulkSubmit.classList.add('disabled');
                if (assignPressButton) assignPressButton.classList.add('disabled');
                if (autoAssignNonPressButton) autoAssignNonPressButton.classList.add('disabled');
            }
        }

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('record-checkbox')) {
                const checkboxes = document.querySelectorAll('.record-checkbox');
                const checkedBoxes = document.querySelectorAll('.record-checkbox:checked');
                const masterCheckbox = document.getElementById('selectAllCheckbox');
                
                if (checkedBoxes.length === 0) {
                    masterCheckbox.indeterminate = false;
                    masterCheckbox.checked = false;
                } else if (checkedBoxes.length === checkboxes.length) {
                    masterCheckbox.indeterminate = false;
                    masterCheckbox.checked = true;
                } else {
                    masterCheckbox.indeterminate = true;
                }
                updateBulkSubmitButton();
            }

            if (e.target.id === 'bulk_action') {
                const bulkCavityIds = document.getElementById('bulk_cavity_ids');
                if (e.target.value === 'set') {
                    bulkCavityIds.style.display = 'inline-block';
                } else {
                    bulkCavityIds.style.display = 'none';
                }
            }

            if (e.target.id === 'show_non_press') {
                document.getElementById('press_select').disabled = e.target.checked;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            updateBulkSubmitButton();
            const bulkAction = document.getElementById('bulk_action');
            const bulkCavityIds = document.getElementById('bulk_cavity_ids');
            if (bulkAction && bulkAction.value === 'set') {
                bulkCavityIds.style.display = 'inline-block';
            } else if (bulkCavityIds) {
                bulkCavityIds.style.display = 'none';
            }
        });
    </script>
</body>
</html>