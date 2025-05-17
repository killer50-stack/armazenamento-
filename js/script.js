document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const fileInput = document.getElementById('file-input');
    const uploadArea = document.getElementById('upload-area');
    const fileGrid = document.getElementById('file-grid');
    const progressOverlay = document.getElementById('progress-overlay');
    const progressBarFill = document.getElementById('progress-bar-fill');
    const progressText = document.getElementById('progress-text');
    const alertError = document.getElementById('alert-error');
    const alertSuccess = document.getElementById('alert-success');
    const storageBarFill = document.getElementById('storage-bar-fill');
    const storageUsed = document.getElementById('storage-used');
    const storageTotal = document.getElementById('storage-total');
    const storagePercentage = document.getElementById('storage-percentage');
    const viewerContainer = document.getElementById('viewer-container');
    const viewerContent = document.getElementById('viewer-content');
    const viewerClose = document.getElementById('viewer-close');

    // Event listeners
    uploadArea.addEventListener('click', () => fileInput.click());
    
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        if (e.dataTransfer.files.length > 0) {
            handleFileUpload(e.dataTransfer.files[0]);
        }
    });
    
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            handleFileUpload(fileInput.files[0]);
        }
    });
    
    if (viewerClose) {
        viewerClose.addEventListener('click', closeViewer);
    }
    
    // Initial load
    loadFiles();

    // Functions
        function loadFiles() {        fetch('php/list_files.php')            .then(response => {                if (!response.ok) {                    throw new Error(`Erro HTTP: ${response.status}`);                }                return response.json();            })            .then(data => {                if (data.success) {                    updateStorageInfo(data.storage);                    renderFiles(data.files);                } else {                    showAlert('error', data.message || 'Erro ao carregar arquivos.');                }            })            .catch(error => {                console.error('Erro ao carregar arquivos:', error);                showAlert('error', 'Falha ao se comunicar com o servidor. Verifique se o Apache está rodando.');            });
    }

    function handleFileUpload(file) {
        // Verificar tamanho máximo (29 GB)
        const maxSize = 29 * 1024 * 1024 * 1024; // 29 GB em bytes
        if (file.size > maxSize) {
            showAlert('error', 'O arquivo excede o limite de 29 GB.');
            return;
        }
        
        const formData = new FormData();
        formData.append('file', file);
        
        showProgress(0, `Preparando para enviar ${file.name}`);
        
        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentage = Math.round((e.loaded / e.total) * 100);
                showProgress(percentage, `Enviando arquivo... ${percentage}%`);
            }
        });
        
        xhr.addEventListener('load', () => {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    hideProgress();
                    showAlert('success', 'Arquivo enviado com sucesso!');
                    loadFiles(); // Recarregar a lista de arquivos
                } else {
                    hideProgress();
                    showAlert('error', response.message || 'Erro ao enviar arquivo.');
                }
            } catch (e) {
                hideProgress();
                showAlert('error', 'Erro inesperado ao processar a resposta do servidor.');
            }
        });
        
        xhr.addEventListener('error', () => {
            hideProgress();
            showAlert('error', 'Erro de conexão ao enviar o arquivo.');
        });
        
        xhr.addEventListener('abort', () => {
            hideProgress();
            showAlert('error', 'Upload cancelado.');
        });
        
        xhr.open('POST', 'php/upload.php', true);
        xhr.send(formData);
    }

    function renderFiles(files) {
        fileGrid.innerHTML = '';
        
        if (files.length === 0) {
            fileGrid.innerHTML = `
                <div class="empty-state">
                    <div class="upload-icon">📁</div>
                    <p>Nenhum arquivo encontrado. Faça upload do seu primeiro arquivo!</p>
                </div>
            `;
            return;
        }
        
        files.forEach(file => {
            let previewClass = '';
            let previewContent = '';
            
            // Determinar o tipo de preview
            if (file.viewType === 'image') {
                previewClass = 'image-preview';
                previewContent = `<img src="php/view_file.php?id=${file.id}" alt="${file.name}">`;
            } else if (file.viewType === 'video') {
                previewClass = 'video-preview';
                previewContent = '';
            } else if (file.viewType === 'pdf') {
                previewClass = 'pdf-preview';
                previewContent = '';
            } else {
                previewClass = 'generic-preview';
                previewContent = `<div class="file-icon">${getFileIcon(file.type)}</div>`;
            }
            
            const fileCard = document.createElement('div');
            fileCard.className = 'file-card';
            fileCard.innerHTML = `
                <div class="file-preview ${previewClass}" data-id="${file.id}" data-type="${file.viewType}">
                    ${previewContent}
                </div>
                <div class="file-info">
                    <div class="file-name" title="${file.name}">${file.name}</div>
                    <div class="file-meta">
                        <span>${file.size}</span>
                        <span>${file.date}</span>
                    </div>
                    <div class="file-actions">
                        <button class="btn view-btn" data-id="${file.id}" data-type="${file.viewType}">Visualizar</button>
                        <button class="btn btn-danger delete-btn" data-id="${file.id}">Excluir</button>
                    </div>
                </div>
            `;
            
            // Adicionar evento para visualização
            const viewBtn = fileCard.querySelector('.view-btn');
            const previewArea = fileCard.querySelector('.file-preview');
            
            viewBtn.addEventListener('click', () => viewFile(file));
            previewArea.addEventListener('click', () => viewFile(file));
            
            // Adicionar evento para exclusão
            const deleteBtn = fileCard.querySelector('.delete-btn');
            deleteBtn.addEventListener('click', () => deleteFile(file.id));
            
            fileGrid.appendChild(fileCard);
        });
    }

    function viewFile(file) {
        const id = file.id;
        const type = file.viewType;
        
        if (type === 'image') {
            openViewer(`<img src="php/view_file.php?id=${id}" alt="${file.name}">`);
        } else if (type === 'video') {
            openViewer(`<video controls autoplay><source src="php/view_file.php?id=${id}" type="video/${file.type}"></video>`);
        } else if (type === 'pdf') {
            openViewer(`<iframe src="php/view_file.php?id=${id}"></iframe>`);
        } else {
            // Para outros tipos, oferecer download
            window.location.href = `php/view_file.php?id=${id}&mode=download`;
        }
    }

    function openViewer(content) {
        viewerContent.innerHTML = content;
        viewerContainer.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeViewer() {
        viewerContainer.style.display = 'none';
        viewerContent.innerHTML = '';
        document.body.style.overflow = '';
    }

    function deleteFile(id) {
        if (confirm('Tem certeza que deseja excluir este arquivo?')) {
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('php/delete_file.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Arquivo excluído com sucesso!');
                    loadFiles(); // Recarregar a lista de arquivos
                } else {
                    showAlert('error', data.message || 'Erro ao excluir o arquivo.');
                }
            })
            .catch(error => {
                console.error('Erro ao excluir arquivo:', error);
                showAlert('error', 'Falha ao se comunicar com o servidor.');
            });
        }
    }

    function updateStorageInfo(storage) {
        const percentageValue = storage.percentage;
        storageBarFill.style.width = `${percentageValue}%`;
        storageUsed.textContent = `${storage.used} GB`;
        storageTotal.textContent = `${storage.max} GB`;
        storagePercentage.textContent = `${percentageValue}%`;
    }

    function showProgress(percentage, message) {
        progressBarFill.style.width = `${percentage}%`;
        progressText.textContent = message;
        progressOverlay.style.display = 'flex';
    }

    function hideProgress() {
        progressOverlay.style.display = 'none';
    }

    function showAlert(type, message) {
        const alert = type === 'error' ? alertError : alertSuccess;
        alert.textContent = message;
        alert.style.display = 'block';
        
        setTimeout(() => {
            alert.style.display = 'none';
        }, 5000);
    }

    function getFileIcon(fileType) {
        const icons = {
            'pdf': '📄',
            'doc': '📝',
            'docx': '📝',
            'xls': '📊',
            'xlsx': '📊',
            'ppt': '📋',
            'pptx': '📋',
            'txt': '📝',
            'zip': '🗂️',
            'rar': '🗂️',
            'mp3': '🎵',
            'wav': '🎵',
            'mp4': '🎬',
            'mov': '🎬',
            'avi': '🎬',
            'jpg': '🖼️',
            'jpeg': '🖼️',
            'png': '🖼️',
            'gif': '🖼️',
            'svg': '🖼️'
        };
        
        return icons[fileType] || '📁';
    }
}); 