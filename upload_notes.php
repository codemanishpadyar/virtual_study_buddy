<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Notes - Virtual Study Buddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7ff 0%, #f0f0f0 100%);
        }

        .notes-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }

        .page-title p {
            color: #666;
            font-size: 16px;
        }

        .upload-section {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            margin-bottom: 40px;
        }

        .upload-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
        }

        .upload-area {
            border: 2px dashed #e1e1e1;
            border-radius: 12px;
            padding: 40px;
            margin: 20px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .upload-text {
            color: #666;
            margin: 10px 0;
        }

        .upload-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 52px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 2px solid #5a67d8;
            border-radius: 12px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.25);
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
            border-color: #764ba2;
        }

        .upload-btn:active {
            transform: scale(0.97) translateY(0);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
        }

        .upload-btn:disabled {
            opacity: 0.8;
            cursor: not-allowed;
            transform: none;
        }

        .notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .note-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .note-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .note-preview {
            height: 150px;
            background: #f8f9ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #764ba2;
        }

        .note-info {
            padding: 20px;
        }

        .note-title {
            font-size: 16px;
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
        }

        .note-meta {
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
        }

        .note-actions {
            display: flex;
            gap: 10px;
        }

        .note-btn {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .download-btn {
            background: #f0f2ff;
            color: #764ba2;
        }

        .download-btn:hover {
            background: #e0e4ff;
        }

        .delete-btn {
            background: #fff0f0;
            color: #ff6b6b;
        }

        .delete-btn:hover {
            background: #ffe0e0;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .filter-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            background: white;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        @media (max-width: 768px) {
            .notes-container {
                padding: 20px;
            }

            .upload-section {
                padding: 20px;
            }

            .upload-area {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="notes-container">
        <div class="page-title">
            <h1>Upload Notes</h1>
            <p>Share your study materials with the community</p>
        </div>

        <div class="upload-section">
            <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <h2>Upload Your Notes</h2>
            <p class="upload-text">Drag and drop your files here or click to browse</p>
            
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="upload-area" id="dropZone" onclick="document.getElementById('file-input').click()">
                    <i class="fas fa-file-alt" style="font-size: 48px; color: #764ba2;"></i>
                    <p>Supported formats: PDF, DOC, DOCX, PPT, PPTX</p>
                    <input type="file" id="file-input" name="files[]" style="display: none;" multiple accept=".pdf,.doc,.docx,.ppt,.pptx">
                </div>
                
                <div id="fileList" class="file-list"></div>
                
                <button type="submit" class="upload-btn">
                    <i class="fas fa-upload"></i> Upload Files
                </button>
            </form>
        </div>

        <div class="notes-section">
            <h2>Your Uploaded Notes</h2>
            <div class="filters">
                <button class="filter-btn active" data-type="all">All Files</button>
                <button class="filter-btn" data-type="pdf">PDF</button>
                <button class="filter-btn" data-type="doc">Documents</button>
                <button class="filter-btn" data-type="ppt">Presentations</button>
            </div>
            <div class="notes-grid" id="notesGrid">
                <!-- Notes will be loaded here -->
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Handle drag and drop
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('file-input');
        const fileList = document.getElementById('fileList');

        // Make drop zone click to browse files
        dropZone.addEventListener('click', () => {
            fileInput.click();
        });

        // Handle drag events
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#667eea';
            dropZone.style.background = '#f8f9ff';
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#e1e1e1';
            dropZone.style.background = 'white';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#e1e1e1';
            dropZone.style.background = 'white';
            
            const files = e.dataTransfer.files;
            fileInput.files = files;
            updateFileList();
        });

        // Update file list when files are selected
        fileInput.addEventListener('change', updateFileList);

        function updateFileList() {
            fileList.innerHTML = '';
            const files = fileInput.files;
            
            if (files.length === 0) return;
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <i class="fas ${getFileIcon(file.name)}"></i>
                    <span>${file.name}</span>
                `;
                fileList.appendChild(fileItem);
            }
        }

        // Handle form submission
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const files = fileInput.files;
            if (files.length === 0) {
                alert('Please select at least one file to upload');
                return;
            }
            
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            
            // Show loading indicator
            const uploadBtn = document.querySelector('.upload-btn');
            const originalBtnText = uploadBtn.innerHTML;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            uploadBtn.disabled = true;
            
            // Send the files to the server
            fetch('handle_upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Upload response:', data);
                
                // Reset button
                uploadBtn.innerHTML = originalBtnText;
                uploadBtn.disabled = false;
                
                if (data.success) {
                    // Clear file input and list
                    fileInput.value = '';
                    fileList.innerHTML = '';
                    
                    // Show success message
                    alert('Files uploaded successfully!');
                    
                    // Reload the notes display
                    loadNotes();
                } else {
                    // Show error message
                    const errorMsg = data.error || 'Error uploading files';
                    alert(errorMsg);
                }
            })
            .catch(error => {
                console.error('Error during upload:', error);
                
                // Reset button
                uploadBtn.innerHTML = originalBtnText;
                uploadBtn.disabled = false;
                
                alert('Error uploading files. Please try again.');
            });
        });

        // Get file icon based on extension
        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            switch(ext) {
                case 'pdf':
                    return 'fa-file-pdf';
                case 'doc':
                case 'docx':
                    return 'fa-file-word';
                case 'ppt':
                case 'pptx':
                    return 'fa-file-powerpoint';
                default:
                    return 'fa-file';
            }
        }

        // Load notes function
        function loadNotes(filter = 'all') {
            const notesGrid = document.getElementById('notesGrid');
            notesGrid.innerHTML = '<div class="loading-notes"><i class="fas fa-spinner fa-spin"></i> Loading notes...</div>';
            
            fetch('get_notes.php?filter=' + filter)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Notes data:', data);
                    notesGrid.innerHTML = '';
                    
                    if (!data.success) {
                        notesGrid.innerHTML = `<p class="error-message">${data.error || 'Error loading notes'}</p>`;
                        return;
                    }
                    
                    if (data.notes.length === 0) {
                        notesGrid.innerHTML = '<p class="no-notes">No notes found</p>';
                        return;
                    }
                    
                    // Display the notes
                    data.notes.forEach(note => {
                        const noteCard = document.createElement('div');
                        noteCard.className = 'note-card';
                        
                        // Format the date
                        const uploadDate = new Date(note.uploaded_at);
                        const formattedDate = uploadDate.toLocaleDateString();
                        
                        noteCard.innerHTML = `
                            <div class="note-preview">
                                <i class="fas ${getFileIcon(note.filename)}"></i>
                            </div>
                            <div class="note-info">
                                <h3 class="note-title">${note.title}</h3>
                                <p class="note-meta">
                                    <span><i class="fas fa-calendar"></i> ${formattedDate}</span>
                                    <span><i class="fas fa-file"></i> ${note.file_size}</span>
                                </p>
                                <div class="note-actions">
                                    <button class="note-btn download-btn" onclick="downloadNote(${note.id})">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                    <button class="note-btn delete-btn" onclick="deleteNote(${note.id})">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        `;
                        
                        notesGrid.appendChild(noteCard);
                    });
                })
                .catch(error => {
                    console.error('Error loading notes:', error);
                    notesGrid.innerHTML = '<p class="error-message">Error loading notes. Please try again later.</p>';
                });
        }

        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                loadNotes(btn.dataset.type);
            });
        });

        // Download note
        function downloadNote(noteId) {
            window.location.href = 'download_note.php?id=' + noteId;
        }

        // Delete note
        function deleteNote(noteId) {
            if (confirm('Are you sure you want to delete this note?')) {
                fetch('delete_note.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + noteId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload notes after successful deletion
                        loadNotes(document.querySelector('.filter-btn.active').dataset.type);
                    } else {
                        alert('Error deleting note: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting note. Please try again.');
                });
            }
        }

        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            .file-list {
                margin: 20px 0;
            }
            .file-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px;
                background: #f8f9ff;
                border-radius: 8px;
                margin-bottom: 10px;
            }
            .file-item i {
                color: #764ba2;
                font-size: 24px;
            }
            .loading-notes {
                text-align: center;
                padding: 20px;
                color: #666;
            }
            .loading-notes i {
                margin-right: 10px;
                color: #764ba2;
            }
            .no-notes {
                text-align: center;
                color: #666;
                padding: 20px;
                background: white;
                border-radius: 8px;
            }
            .error-message {
                color: #ff6b6b;
                text-align: center;
                padding: 20px;
                background: #fff0f0;
                border-radius: 8px;
            }
        `;
        document.head.appendChild(style);

        // Load notes when page loads
        loadNotes();
    </script>
</body>
</html>
