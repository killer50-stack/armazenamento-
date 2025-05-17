# ArquivoFácil - Sistema de Armazenamento de Arquivos

Um sistema web simples e intuitivo para armazenar e gerenciar arquivos (vídeos, imagens e PDFs).

## Características

- Upload de arquivos (vídeos, imagens e PDFs)
- Visualização de arquivos diretamente no navegador
- Limite de 999 GB de armazenamento total por usuário
- Limite de 29 GB por arquivo enviado
- Listagem e organização dos arquivos enviados
- Opção para excluir arquivos
- Interface de usuário simples e intuitiva com tema marrom

## Requisitos do Sistema

- Servidor web com suporte a PHP (recomendado: XAMPP)
- PHP 7.4 ou superior
- Extensão PDO SQLite habilitada em PHP
- Navegador web moderno (Chrome, Firefox, Edge)

## Instalação

1. Baixe ou clone este repositório
2. Coloque os arquivos na pasta htdocs do XAMPP (ou no diretório do seu servidor web)
3. Certifique-se de que o servidor web tem permissão de escrita nas pastas `uploads` e `db`
4. Inicie o servidor Apache no XAMPP
5. Acesse o site através do navegador: `http://localhost/ArquivoFacil` (ou conforme a sua configuração)

## Estrutura do Projeto

```
ArquivoFacil/
├── css/
│   └── style.css              # Estilo CSS com tema marrom
├── js/
│   └── script.js              # JavaScript para funcionalidades do site
├── php/
│   ├── database.php           # Classe de conexão com o banco de dados
│   ├── delete_file.php        # Script para excluir arquivos
│   ├── list_files.php         # Script para listar arquivos
│   ├── upload.php             # Script para upload de arquivos
│   └── view_file.php          # Script para visualizar arquivos
├── db/
│   └── storage.db             # Banco de dados SQLite (criado automaticamente)
├── uploads/                   # Diretório onde os arquivos são salvos
├── index.html                 # Página principal
└── README.md                  # Documentação
```

## Uso

1. Abra o site em seu navegador
2. Utilize a área de upload para enviar arquivos (arrastar e soltar ou clicar para selecionar)
3. Veja a lista de arquivos na seção "Meus Arquivos"
4. Clique em "Visualizar" para ver o arquivo ou em "Excluir" para removê-lo
5. Acompanhe o uso de armazenamento na barra de progresso no topo da página

## Limitações

- O tamanho máximo de upload pode ser limitado pela configuração do PHP (php.ini)
- Para permitir upload de arquivos grandes, ajuste as seguintes configurações no php.ini:
  - `upload_max_filesize`
  - `post_max_size`
  - `max_execution_time`
  - `memory_limit`

## Solução de Problemas

### Permissões de Diretório
Se ocorrerem erros de upload, certifique-se que as permissões dos diretórios `uploads` e `db` estão corretas:

```bash
# No Linux/Mac
chmod -R 755 uploads
chmod -R 755 db
```

No Windows usando XAMPP, configure as permissões de escrita para os diretórios através do Explorador de Arquivos.

### Extensões PHP
Certifique-se que as extensões PHP necessárias estão habilitadas no php.ini:

```
extension=pdo_sqlite
extension=fileinfo
```

## Licença

Este projeto é de uso livre e gratuito.

---

Desenvolvido para fins educacionais e práticos. 