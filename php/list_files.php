<?php
require_once 'database.php';

header('Content-Type: application/json');

$db = new Database();
$userId = $db->getDefaultUserId();

try {
    // Obter informações de armazenamento do usuário
    $storageInfo = $db->getUserStorage($userId);
    $totalUsed = $storageInfo['total_storage_used'];
    $maxStorage = $storageInfo['max_storage'];
    
    // Formatar os valores de armazenamento para exibição
    $usedGB = round($totalUsed / (1024 * 1024 * 1024), 2);
    $maxGB = round($maxStorage / (1024 * 1024 * 1024), 2);
    
    // Obter todos os arquivos do usuário
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT id, original_filename, filetype, filesize, filepath, upload_date FROM files WHERE user_id = ? ORDER BY upload_date DESC");
    $stmt->execute([$userId]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Processar os dados dos arquivos para o formato adequado para exibição
    $processedFiles = [];
    foreach ($files as $file) {
        // Determinar o tipo de visualização baseado no tipo de arquivo
        $fileType = strtolower(pathinfo($file['original_filename'], PATHINFO_EXTENSION));
        $viewType = 'download'; // Padrão
        
        if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
            $viewType = 'image';
        } elseif (in_array($fileType, ['mp4', 'webm', 'ogg'])) {
            $viewType = 'video';
        } elseif ($fileType === 'pdf') {
            $viewType = 'pdf';
        }
        
        // Formatar o tamanho do arquivo
        $size = $file['filesize'];
        $sizeStr = '';
        
        if ($size < 1024) {
            $sizeStr = $size . ' B';
        } elseif ($size < 1024 * 1024) {
            $sizeStr = round($size / 1024, 2) . ' KB';
        } elseif ($size < 1024 * 1024 * 1024) {
            $sizeStr = round($size / (1024 * 1024), 2) . ' MB';
        } else {
            $sizeStr = round($size / (1024 * 1024 * 1024), 2) . ' GB';
        }
        
        // Formatar a data
        $date = new DateTime($file['upload_date']);
        $formattedDate = $date->format('d/m/Y H:i:s');
        
        // Adicionar ao array de arquivos processados
        $processedFiles[] = [
            'id' => $file['id'],
            'name' => $file['original_filename'],
            'type' => $fileType,
            'viewType' => $viewType,
            'size' => $sizeStr,
            'rawSize' => $file['filesize'],
            'path' => str_replace('../', '', $file['filepath']), // Remover ../ para URLs relativas
            'date' => $formattedDate
        ];
    }
    
    // Retornar resultado
    echo json_encode([
        'success' => true,
        'storage' => [
            'used' => $usedGB,
            'max' => $maxGB,
            'percentage' => ($maxStorage > 0) ? round(($totalUsed / $maxStorage) * 100, 2) : 0
        ],
        'files' => $processedFiles
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar arquivos: ' . $e->getMessage()]);
}
?> 