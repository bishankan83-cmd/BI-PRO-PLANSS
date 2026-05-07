

class Database {
    private $conn;
    
    public function __construct() {
        $this->conn = new mysqli(
            Config::DB_HOST, 
            Config::DB_USER, 
            Config::DB_PASS, 
            Config::DB_NAME
        );
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        
        $this->setupErrorReporting();
    }
    
    private function setupErrorReporting() {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function closeConnection() {
        $this->conn->close();
    }
    
    public function escapeString($string) {
        return $this->conn->real_escape_string($string);
    }
}