<?php
class DB {
    private $dbHost     = "190.8.176.241";
    private $dbUsername = "saludmen_sanamente";
    private $dbPassword = "Sanamente2020.*+";
    private $dbName     = "saludmen_sanamente";
   
    public function __construct(){
        if(!isset($this->db)){
            // Connect to the database
            $conn = new mysqli($this->dbHost, $this->dbUsername, $this->dbPassword, $this->dbName);
            if($conn->connect_error){
                die("Failed to connect with MySQL: " . $conn->connect_error);
            }else{
                $this->db = $conn;
            }
        }
    }
   
    public function is_table_empty() {
        $result = $this->db->query("SELECT id FROM oauth_tokens WHERE provider = 'google'");
        if($result->num_rows) {
            return false;
        }
   
        return true;
    }
   
    public function get_refresh_token() {
        $sql = $this->db->query("SELECT provider_value FROM oauth_tokens WHERE provider = 'google'");
        $result = $sql->fetch_assoc();
        return $result['provider_value'];
    }
   
    public function update_refresh_token($token) {
        if($this->is_table_empty()) {
            $sql = sprintf("INSERT INTO oauth_tokens(provider, provider_value) VALUES('%s', '%s')", 'google', $this->db->real_escape_string($token));
            $this->db->query($sql);
        } else {
            $sql = sprintf("UPDATE oauth_tokens SET provider_value = '%s' WHERE provider = '%s'", $this->db->real_escape_string($token), 'google');
            $this->db->query($sql);
        }
    }
}