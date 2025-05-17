<?php
require_once 'database.php';

// Verificar se o ID do arquivo foi fornecido
if (!isset($_GET['id'])) {
    http_response_code(400);
    die('ID do arquivo não fornecido');
}

$fileId = $_GET['id'];
$db = new Database();
$userId = $db->getDefaultUserId();

try {
    $conn = $db->getConnection();
    
    // Obter informações do arquivo
    $stmt = $conn->prepare("SELECT filepath, filetype, original_filename FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$fileId, $userId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$file) {
        http_response_code(404);
        die('Arquivo não encontrado ou não pertence ao usuário');
    }
    
    // Verificar se o arquivo existe fisicamente
    if (!file_exists($file['filepath']) || !is_file($file['filepath'])) {
        http_response_code(404);
        die('Arquivo físico não encontrado');
    }
    
    // Configurar cabeçalhos para download/visualização
    $mimeType = $file['filetype'];
    $filename = $file['original_filename'];
    
    // Definir o tipo de visualização com base no parâmetro "mode"
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'inline';
    
    header('Content-Type: ' . $mimeType);
    
    if ($mode === 'download') {
        // Configurar para download
        header('Content-Disposition: attachment; filename="' . $filename . '"');
    } else {
        // Configurar para visualização inline (no navegador)
        header('Content-Disposition: inline; filename="' . $filename . '"');
    }
    
    header('Content-Length: ' . filesize($file['filepath']));
    header('Cache-Control: public, max-age=86400');
    
    // Enviar o arquivo
    readfile($file['filepath']);
    
} catch (PDOException $e) {
    http_response_code(500);
    die('Erro ao acessar o arquivo: ' . $e->getMessage());
}
?> 