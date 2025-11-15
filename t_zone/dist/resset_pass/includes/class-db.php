<?php
require_once 'config.php';

class DB {
    private $conn;
    
    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }
    
    /**
     * Verifica si la tabla de tokens está vacía
     */
    public function is_table_empty() {
        $stmt = $this->conn->prepare("SELECT id FROM oauth_tokens WHERE provider = ?");
        $provider = 'google';
        $stmt->bind_param("s", $provider);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows === 0;
    }
    
    /**
     * Obtiene el refresh token (alias de get_oauth_token para compatibilidad)
     */
    public function get_refresh_token() {
        return $this->get_oauth_token();
    }
    
    /**
     * Obtiene el token OAuth
     */
    public function get_oauth_token($provider = 'google') {
        $stmt = $this->conn->prepare("SELECT provider_value FROM oauth_tokens WHERE provider = ?");
        $stmt->bind_param("s", $provider);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows ? $result->fetch_assoc()['provider_value'] : null;
    }
    
    /**
     * Actualiza el refresh token (alias de update_oauth_token para compatibilidad)
     */
    public function update_refresh_token($token) {
        return $this->update_oauth_token($token);
    }
    
    /**
     * Actualiza el token OAuth
     */
    public function update_oauth_token($token, $provider = 'google') {
        if($this->is_table_empty()) {
            $stmt = $this->conn->prepare("INSERT INTO oauth_tokens(provider, provider_value) VALUES(?, ?)");
            $stmt->bind_param("ss", $provider, $token);
        } else {
            $stmt = $this->conn->prepare("UPDATE oauth_tokens SET provider_value = ? WHERE provider = ?");
            $stmt->bind_param("ss", $token, $provider);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Cierra la conexión a la base de datos
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}