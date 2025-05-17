<?php
// Configuração do banco de dados SQLite
class Database {
    private $db;
    
    public function __construct() {
        try {
            $this->db = new PDO('sqlite:' . __DIR__ . '/../db/storage.db');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->initializeDatabase();
        } catch (PDOException $e) {
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }
    
    private function initializeDatabase() {
        // Criar tabela de usuários
        $this->db->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            total_storage_used INTEGER DEFAULT 0,
            max_storage INTEGER DEFAULT 1072618086400
        )');
        
        // Criar tabela de arquivos
        $this->db->exec('CREATE TABLE IF NOT EXISTS files (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            filename TEXT NOT NULL,
            original_filename TEXT NOT NULL,
            filetype TEXT NOT NULL,
            filesize INTEGER NOT NULL,
            filepath TEXT NOT NULL,
            upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )');
        
        // Inserir usuário padrão se não existir
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = 'default_user'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $stmt = $this->db->prepare("INSERT INTO users (username, max_storage) VALUES ('default_user', 1072618086400)");
            $stmt->execute();
        }
    }
    
    public function getConnection() {
        return $this->db;
    }
    
    public function getUserStorage($userId) {
        $stmt = $this->db->prepare("SELECT total_storage_used, max_storage FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateUserStorage($userId, $filesize, $operation = 'add') {
        $stmt = $this->db->prepare("SELECT total_storage_used FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $currentUsage = $stmt->fetchColumn();
        
        $newUsage = ($operation === 'add') ? $currentUsage + $filesize : $currentUsage - $filesize;
        $newUsage = max(0, $newUsage); // Não permitir valores negativos
        
        $stmt = $this->db->prepare("UPDATE users SET total_storage_used = ? WHERE id = ?");
        $stmt->execute([$newUsage, $userId]);
        
        return $newUsage;
    }
    
    public function getDefaultUserId() {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = 'default_user'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
?> 