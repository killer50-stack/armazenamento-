<?php
require_once 'database.php';

header('Content-Type: application/json');

// Verificar se o método é POST e se o ID do arquivo foi fornecido
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do arquivo não fornecido']);
    exit;
}

$fileId = $_POST['id'];
$db = new Database();
$userId = $db->getDefaultUserId();

try {
    $conn = $db->getConnection();
    
    // Obter informações do arquivo antes de excluí-lo
    $stmt = $conn->prepare("SELECT filepath, filesize FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$fileId, $userId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$file) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Arquivo não encontrado ou não pertence ao usuário']);
        exit;
    }
    
    // Excluir o arquivo físico do sistema de arquivos
    if (file_exists($file['filepath']) && is_file($file['filepath'])) {
        if (!unlink($file['filepath'])) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir o arquivo físico']);
            exit;
        }
    }
    
    // Excluir o registro do banco de dados
    $stmt = $conn->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$fileId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        // Atualizar o espaço utilizado pelo usuário
        $db->updateUserStorage($userId, $file['filesize'], 'subtract');
        
        echo json_encode(['success' => true, 'message' => 'Arquivo excluído com sucesso']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Arquivo não encontrado ou não pertence ao usuário']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir o arquivo: ' . $e->getMessage()]);
}
?> 