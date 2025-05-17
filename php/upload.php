<?php
require_once 'database.php';

header('Content-Type: application/json');

$db = new Database();
$userId = $db->getDefaultUserId();

// Verificar se o arquivo foi enviado
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado ou erro no upload']);
    exit;
}

$file = $_FILES['file'];
$fileSize = $file['size'];
$fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$originalFilename = $file['name'];

// Verificar tamanho máximo do arquivo (29 GB)
$maxFileSize = 29 * 1024 * 1024 * 1024; // 29 GB em bytes
if ($fileSize > $maxFileSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'O arquivo excede o limite de 29 GB']);
    exit;
}

// Verificar espaço de armazenamento disponível
$storageInfo = $db->getUserStorage($userId);
$totalUsed = $storageInfo['total_storage_used'];
$maxStorage = $storageInfo['max_storage'];

if ($totalUsed + $fileSize > $maxStorage) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Limite de armazenamento excedido']);
    exit;
}

// Verificar e criar diretório de uploads se não existir
$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Gerar nome de arquivo único baseado no timestamp e um valor aleatório
$uniqueFilename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileType;
$filePath = $uploadDir . $uniqueFilename;

// Mover o arquivo para o diretório de uploads
if (move_uploaded_file($file['tmp_name'], $filePath)) {
    try {
        // Obter o tipo MIME do arquivo
        $fileMimeType = mime_content_type($filePath);
        
        // Salvar informações no banco de dados
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO files (user_id, filename, original_filename, filetype, filesize, filepath) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $uniqueFilename, $originalFilename, $fileMimeType, $fileSize, $filePath]);
        
        // Atualizar o espaço utilizado pelo usuário
        $db->updateUserStorage($userId, $fileSize, 'add');
        
        // Resposta de sucesso
        echo json_encode([
            'success' => true, 
            'message' => 'Arquivo enviado com sucesso',
            'file' => [
                'id' => $conn->lastInsertId(),
                'name' => $originalFilename,
                'size' => $fileSize,
                'type' => $fileMimeType
            ]
        ]);
    } catch (PDOException $e) {
        // Remover o arquivo em caso de erro
        unlink($filePath);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar informações no banco de dados']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao mover o arquivo enviado']);
}
?> 