<?php
require_once 'config/Database.php';

class AuthController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function login($username, $password) {
        try {
            $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    return [
                        'success' => true,
                        'user_id' => $user['id'],
                        'username' => $user['username'],
                        'role' => $user['role']
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Username atau password salah'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Logout berhasil'
        ];
    }
}
?>
