document.addEventListener('DOMContentLoaded', () => {
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('file-upload');
    const uploadBtn = document.getElementById('upload-btn');
    const compressBtn = document.getElementById('compress-btn');
    
    const originalPreview = document.getElementById('original-preview');
    const originalPreviewPlaceholder = document.getElementById('original-placeholder');

    const compressPreview = document.getElementById('compress-preview');
    const compressPreviewPlaceholder = document.getElementById('compress-placeholder');
    const resultArea = document.getElementById('result-area');
    const downloadLink = document.getElementById('download-link');

    let uploadedFile = null;
    let serverFilename = null; // The path returned by the server, e.g., "uploads/image.jpg"
    let isUploading = false;
    let isCompressing = false;

    // --- Initialize UI ---
    compressBtn.disabled = true;

    // --- Event Listeners ---

    // Open file dialog when the upload area is clicked
    uploadArea.addEventListener('click', () => fileInput.click());

    // Handle file selection
    fileInput.addEventListener('change', handleFileSelect);

    // Handle drag and drop
    setupDragAndDrop();

    // Handle upload button click
    uploadBtn.addEventListener('click', handleUpload);

    // Handle compress button click
    compressBtn.addEventListener('click', handleCompress);

    // --- Functions ---

    function handleFileSelect() {
        if (fileInput.files && fileInput.files[0]) {
            const file = fileInput.files[0];
            
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Tipe file tidak valid. Silakan pilih gambar JPG, PNG, atau GIF.');
                return;
            }

            uploadedFile = file;
            displayPreview(file);
            compressBtn.disabled = true; // Disable compress until uploaded
            uploadBtn.disabled = false;
            if(downloadLink) downloadLink.classList.add('hidden');
        }
    }

    function displayPreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            originalPreview.src = e.target.result;
            originalPreview.classList.remove('hidden');
            if (originalPreviewPlaceholder) originalPreviewPlaceholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    function setupDragAndDrop() {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => uploadArea.style.borderColor = '#2980b9');
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => uploadArea.style.borderColor = '#ccc');
        });

        uploadArea.addEventListener('drop', (e) => {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect(); // Process the dropped file
        });
    }

    function handleUpload() {
        if (!uploadedFile) {
            alert('Silakan pilih file terlebih dahulu.');
            fileInput.click();
            return;
        }
        if (isUploading) return;

        isUploading = true;
        uploadBtn.textContent = 'MENGUNGGAH...';
        uploadBtn.disabled = true;

        const formData = new FormData();
        formData.append('image', uploadedFile);

        fetch('php/upload.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    serverFilename = data.filename;
                    alert('Unggah berhasil! Sekarang klik "KOMPRES".');
                    compressBtn.disabled = false;
                    uploadBtn.textContent = 'BERHASIL DIUNGGAH';
                } else {
                    alert('Error: ' + data.msg);
                    uploadBtn.textContent = 'UPLOAD';
                    uploadBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                alert('Terjadi kesalahan saat mengunggah. Periksa konsol browser (F12) untuk detail.');
                uploadBtn.textContent = 'UPLOAD';
                uploadBtn.disabled = false;
            })
            .finally(() => {
                isUploading = false;
            });
    }

    function handleCompress() {
        if (!serverFilename) {
            alert('Silakan unggah file terlebih dahulu.');
            return;
        }
        if (isCompressing) return;

        isCompressing = true;
        compressBtn.textContent = 'MENGOMPRES...';
        compressBtn.disabled = true;
        uploadBtn.disabled = true;

        fetch('php/compress.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'filename=' + encodeURIComponent(serverFilename)
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Add a timestamp to bust cache
                    compressPreview.src = data.compressed + '?t=' + new Date().getTime();
                    compressPreview.classList.remove('hidden');
                    if(compressPreviewPlaceholder) compressPreviewPlaceholder.classList.add('hidden');
                    
                    if(downloadLink) {
                        downloadLink.href = data.compressed;
                        downloadLink.download = 'compressed_' + serverFilename.split('/').pop();
                        downloadLink.classList.remove('hidden');
                    }
                    
                    alert('Gambar berhasil dikompres!');
                    compressBtn.textContent = 'SELESAI';
                } else {
                    alert('Error: ' + data.msg);
                    compressBtn.textContent = 'COMPRESS';
                    compressBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Compress error:', error);
                alert('Terjadi kesalahan saat mengompres. Periksa konsol browser (F12) untuk detail.');
                compressBtn.textContent = 'COMPRESS';
                compressBtn.disabled = false;
            })
            .finally(() => {
                isCompressing = false;
            });
    }
});
