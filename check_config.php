<?php
// Verificar versão do PHP
$phpVersion = phpversion();
$phpVersionCheck = version_compare($phpVersion, '7.4.0', '>=');

// Verificar extensões necessárias
$pdo = extension_loaded('pdo_sqlite');
$fileinfo = extension_loaded('fileinfo');

// Verificar permissões de diretórios
$uploadsDir = __DIR__ . '/uploads';
$dbDir = __DIR__ . '/db';

if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

$uploadsWritable = is_writable($uploadsDir);
$dbWritable = is_writable($dbDir);

// Limite de upload do PHP
$uploadMaxFilesize = ini_get('upload_max_filesize');
$postMaxSize = ini_get('post_max_size');

// Verificar conexão com o banco de dados
$dbConnection = false;
if ($pdo) {
    try {
        $db = new PDO('sqlite:' . $dbDir . '/storage.db');
        $dbConnection = true;
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}

// Saída em HTML para fácil visualização
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Configuração - ArquivoFácil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #5D4037;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .item h3 {
            margin-bottom: 5px;
        }
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .success {
            background-color: #E8F5E9;
            color: #388E3C;
        }
        .warning {
            background-color: #FFF3E0;
            color: #F57C00;
        }
        .error {
            background-color: #FFEBEE;
            color: #D32F2F;
        }
        .actions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #795548;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verificação de Configuração - ArquivoFácil</h1>
        
        <div class="item">
            <h3>Versão do PHP</h3>
            <p>Versão atual: <?php echo $phpVersion; ?></p>
            <p>Status: 
                <span class="status <?php echo $phpVersionCheck ? 'success' : 'error'; ?>">
                    <?php echo $phpVersionCheck ? 'OK' : 'Problema - Necessário PHP 7.4 ou superior'; ?>
                </span>
            </p>
        </div>
        
        <div class="item">
            <h3>Extensões necessárias</h3>
            <p>PDO SQLite: 
                <span class="status <?php echo $pdo ? 'success' : 'error'; ?>">
                    <?php echo $pdo ? 'Instalada' : 'Não instalada'; ?>
                </span>
            </p>
            <p>Fileinfo: 
                <span class="status <?php echo $fileinfo ? 'success' : 'error'; ?>">
                    <?php echo $fileinfo ? 'Instalada' : 'Não instalada'; ?>
                </span>
            </p>
        </div>
        
        <div class="item">
            <h3>Permissão de diretórios</h3>
            <p>Uploads (<?php echo $uploadsDir; ?>): 
                <span class="status <?php echo $uploadsWritable ? 'success' : 'error'; ?>">
                    <?php echo $uploadsWritable ? 'Gravável' : 'Sem permissão de escrita'; ?>
                </span>
            </p>
            <p>Banco de dados (<?php echo $dbDir; ?>): 
                <span class="status <?php echo $dbWritable ? 'success' : 'error'; ?>">
                    <?php echo $dbWritable ? 'Gravável' : 'Sem permissão de escrita'; ?>
                </span>
            </p>
        </div>
        
        <div class="item">
            <h3>Limites de upload do PHP</h3>
            <p>upload_max_filesize: <?php echo $uploadMaxFilesize; ?></p>
            <p>post_max_size: <?php echo $postMaxSize; ?></p>
            <p>Status: 
                <span class="status <?php echo (intval($uploadMaxFilesize) >= 100) ? 'success' : 'warning'; ?>">
                    <?php echo (intval($uploadMaxFilesize) >= 100) ? 'OK' : 'Pode ser insuficiente para arquivos grandes'; ?>
                </span>
            </p>
        </div>
        
        <div class="item">
            <h3>Conexão com o banco de dados</h3>
            <p>Status: 
                <span class="status <?php echo $dbConnection ? 'success' : 'error'; ?>">
                    <?php echo $dbConnection ? 'Conectado com sucesso' : 'Falha na conexão: ' . (isset($dbError) ? $dbError : 'Erro desconhecido'); ?>
                </span>
            </p>
        </div>
        
        <div class="actions">
            <a href="index.html" class="btn">Voltar ao site</a>
            <a href="check_config.php" class="btn">Verificar novamente</a>
        </div>
    </div>
</body>
</html> 