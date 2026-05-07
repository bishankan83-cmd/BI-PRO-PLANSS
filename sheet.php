<?php
require_once 'vendor/autoload.php';

class SheetsDatabaseSync {
    private $spreadsheetId = '10U9woW397aTSkejFSFvbwdned4amr03oC2UkpQczvOg';
    private $client;
    private $service;
    private $dbConnection;
    private $range;

    public function __construct($credentials) {
        $this->range = 'Sheet1!A:AA';  // Adjust if your sheet name is different
        
        // Initialize Google Sheets API client
        $this->client = new Google_Client();
        $this->client->setAuthConfig($credentials);
        $this->client->setScopes(['https://www.googleapis.com/auth/spreadsheets.readonly']);
        $this->service = new Google_Service_Sheets($this->client);
        
        // Initialize database connection
        $this->dbConnection = new mysqli(
            "localhost",
            "planatir_task_managemen",
            "Bishan@1919",
            "planatir_task_managemen"
        );
        
        if ($this->dbConnection->connect_error) {
            throw new Exception("Connection failed: " . $this->dbConnection->connect_error);
        }
        
        $this->dbConnection->set_charset("utf8mb4");
    }
    
    public function syncData() {
        try {
            // Get data from Google Sheets
            $response = $this->service->spreadsheets_values->get(
                $this->spreadsheetId,
                $this->range
            );
            $sheetData = $response->getValues();
            
            if (empty($sheetData)) {
                throw new Exception("No data found in spreadsheet.");
            }
            
            // Start transaction
            $this->dbConnection->begin_transaction();
            
            // Get existing records
            $existingRecords = [];
            $result = $this->dbConnection->query("SELECT Item, icode FROM bom_new");
            if ($result === false) {
                throw new Exception("Error fetching existing records: " . $this->dbConnection->error);
            }
            while ($row = $result->fetch_assoc()) {
                $existingRecords[$row['Item'] . '_' . $row['icode']] = true;
            }
            
            // Skip header row
            array_shift($sheetData);
            
            // Prepare error log
            $errorLog = [];
            $successCount = 0;
            
            foreach ($sheetData as $rowIndex => $row) {
                if (empty($row[0]) || empty($row[1])) {
                    continue; // Skip rows without Item or icode
                }
                
                // Pad row with NULL values if necessary
                $row = array_pad($row, 27, null);
                
                $key = $row[0] . '_' . $row[1];  // Item_icode
                
                try {
                    // Check if record exists
                    $stmt = $this->dbConnection->prepare(
                        "SELECT COUNT(*) FROM bom_new WHERE Item = ? AND icode = ?"
                    );
                    $stmt->bind_param("ss", $row[0], $row[1]);
                    $stmt->execute();
                    $stmt->bind_result($count);
                    $stmt->fetch();
                    $stmt->close();
                    
                    if ($count > 0) {
                        // Update existing record
                        $updateStmt = $this->dbConnection->prepare("
                            UPDATE bom_new SET 
                                t_size = ?, `Item Description` = ?,
                                a = ?, b = ?, c = ?, d = ?, e = ?,
                                f = ?, g = ?, h = ?, i = ?, j = ?,
                                k = ?, l = ?, m = ?, n = ?, o = ?,
                                p = ?, q = ?, r = ?,
                                `Grand Totalcompound weight` = ?,
                                Color = ?, Brand = ?,
                                `Green Tire weight` = ?,
                                PBweight = ?
                            WHERE Item = ? AND icode = ?
                        ");
                        
                        $updateStmt->bind_param(
                            "sssssssssssssssssssssssssss",
                            $row[2], $row[3], $row[4], $row[5], $row[6],
                            $row[7], $row[8], $row[9], $row[10], $row[11],
                            $row[12], $row[13], $row[14], $row[15], $row[16],
                            $row[17], $row[18], $row[19], $row[20], $row[21],
                            $row[22], $row[23], $row[24], $row[25], $row[26],
                            $row[0], $row[1]
                        );
                        
                        $updateStmt->execute();
                        $updateStmt->close();
                    } else {
                        // Insert new record
                        $insertStmt = $this->dbConnection->prepare("
                            INSERT INTO bom_new VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                                                      ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                                                      ?, ?, ?, ?, ?, ?, ?)
                        ");
                        
                        $insertStmt->bind_param(
                            "sssssssssssssssssssssssssss",
                            $row[0], $row[1], $row[2], $row[3], $row[4],
                            $row[5], $row[6], $row[7], $row[8], $row[9],
                            $row[10], $row[11], $row[12], $row[13], $row[14],
                            $row[15], $row[16], $row[17], $row[18], $row[19],
                            $row[20], $row[21], $row[22], $row[23], $row[24],
                            $row[25], $row[26]
                        );
                        
                        $insertStmt->execute();
                        $insertStmt->close();
                    }
                    
                    $successCount++;
                    unset($existingRecords[$key]);
                    
                } catch (Exception $e) {
                    $errorLog[] = [
                        'row' => $rowIndex + 2,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            // Commit transaction
            $this->dbConnection->commit();
            
            return [
                "success" => true,
                "message" => "Sync completed. Processed $successCount records successfully.",
                "errors" => $errorLog
            ];
            
        } catch (Exception $e) {
            $this->dbConnection->rollback();
            return [
                "success" => false,
                "message" => $e->getMessage(),
                "errors" => $errorLog ?? []
            ];
        }
    }
    
    public function __destruct() {
        $this->dbConnection->close();
    }
}

// Usage
try {
    $sync = new SheetsDatabaseSync('credentials.json');
    $result = $sync->syncData();
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to initialize sync: " . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>