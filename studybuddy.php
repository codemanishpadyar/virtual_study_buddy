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
    <title>StudyBuddy AI - Virtual Study Buddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8ecf7 50%, #f5f7ff 100%);
        }

        .studybuddy-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 80px);
        }

        .page-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-size: 28px;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .page-header h1 .icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .page-header p {
            color: #666;
            font-size: 14px;
            margin-top: 8px;
        }

        .level-selector {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .level-btn {
            padding: 8px 18px;
            border: 2px solid #e0e4f0;
            border-radius: 24px;
            background: white;
            color: #666;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .level-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .level-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            color: white;
        }

        .chat-panel {
            flex: 1;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .chat-message {
            max-width: 85%;
            padding: 16px 20px;
            border-radius: 18px;
            line-height: 1.6;
            font-size: 15px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .chat-message.user {
            align-self: flex-end;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .chat-message.buddy {
            align-self: flex-start;
            background: #f4f6ff;
            border: 1px solid #e8ecff;
            color: #333;
        }

        .chat-message.buddy .msg-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            font-weight: 600;
            color: #764ba2;
        }

        .chat-message .msg-content h3, .chat-message .msg-content h4 {
            margin: 12px 0 6px;
            font-size: 1em;
        }

        .chat-message .msg-content ul, .chat-message .msg-content ol {
            margin: 8px 0 8px 20px;
        }

        .chat-message .msg-content p { margin: 6px 0; }

        .welcome-msg {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .welcome-msg .welcome-icon {
            font-size: 56px;
            margin-bottom: 16px;
            opacity: 0.8;
        }

        .welcome-msg h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 10px;
        }

        .input-area {
            padding: 20px;
            border-top: 1px solid #eee;
            background: #fafbff;
        }

        .notes-context {
            margin-bottom: 12px;
        }

        .notes-context label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 6px;
        }

        .notes-context textarea {
            width: 100%;
            min-height: 60px;
            padding: 12px;
            border: 1px solid #e0e4f0;
            border-radius: 12px;
            font-size: 14px;
            resize: vertical;
        }

        .notes-context textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .input-row {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .input-row textarea {
            flex: 1;
            min-height: 52px;
            max-height: 120px;
            padding: 14px 18px;
            border: 2px solid #e0e4f0;
            border-radius: 14px;
            font-size: 15px;
            resize: none;
            transition: border-color 0.3s;
        }

        .input-row textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .send-btn {
            width: 52px;
            height: 52px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .send-btn:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .send-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .typing-indicator {
            align-self: flex-start;
            padding: 14px 20px;
            background: #f4f6ff;
            border-radius: 18px;
            color: #666;
            font-size: 14px;
        }

        .typing-indicator span {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #764ba2;
            border-radius: 50%;
            margin: 0 2px;
            animation: bounce 0.6s ease infinite;
        }

        .typing-indicator span:nth-child(2) { animation-delay: 0.1s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.2s; }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }

        .api-notice {
            text-align: center;
            font-size: 12px;
            color: #888;
            margin-top: 8px;
        }

        .api-notice a { color: #667eea; }

        @media (max-width: 768px) {
            .studybuddy-container { padding: 12px; }
            .chat-message { max-width: 95%; }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="studybuddy-container">
        <div class="page-header">
            <h1><span class="icon">🧠</span> StudyBuddy AI</h1>
            <p>Ask anything — get clear, structured explanations at your level</p>
        </div>

        <div class="level-selector">
            <button type="button" class="level-btn active" data-level="beginner">Beginner</button>
            <button type="button" class="level-btn" data-level="intermediate">Intermediate</button>
            <button type="button" class="level-btn" data-level="advanced">Advanced</button>
        </div>

        <div class="chat-panel">
            <div class="chat-messages" id="chatMessages">
                <div class="welcome-msg" id="welcomeMsg">
                    <div class="welcome-icon">✨</div>
                    <h2>Hi, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
                    <p>I'm your StudyBuddy AI. Ask me to explain a concept, summarize notes, or walk through a topic step by step.</p>
                    <p style="margin-top:12px;font-size:13px;">Choose your level above. Paste or upload notes to summarize, or ask a question below.</p>
                </div>
            </div>

            <div class="input-area">
                <div class="notes-context">
                    <label for="notesContext">📘 Paste notes or upload a file (PDF, DOCX, PPTX, TXT)</label>
                    <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px;flex-wrap:wrap;">
                        <input type="file" id="fileUpload" accept=".txt,.pdf,.doc,.docx,.ppt,.pptx,.xml" style="font-size:12px;max-width:220px;" title="Upload file to extract text">
                        <span style="font-size:11px;color:#888;" id="fileStatus"></span>
                    </div>
                    <textarea id="notesContext" placeholder="Paste notes or study material here to get a structured summary, or leave empty to chat."></textarea>
                </div>
                <div class="input-row">
                    <textarea id="userInput" placeholder="Ask a question or type a message..." rows="1"></textarea>
                    <button type="button" class="send-btn" id="sendBtn" title="Send">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <p class="api-notice" id="apiNotice">Using demo mode. Add your OpenAI API key in config for full AI responses.</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        (function() {
            const chatMessages = document.getElementById('chatMessages');
            const welcomeMsg = document.getElementById('welcomeMsg');
            const userInput = document.getElementById('userInput');
            const sendBtn = document.getElementById('sendBtn');
            const notesContext = document.getElementById('notesContext');
            const apiNotice = document.getElementById('apiNotice');
            const levelBtns = document.querySelectorAll('.level-btn');
            const fileUpload = document.getElementById('fileUpload');
            const fileStatus = document.getElementById('fileStatus');

            let currentLevel = 'beginner';

            levelBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    levelBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentLevel = this.getAttribute('data-level');
                });
            });

            function setFileStatus(msg, isError) {
                if (fileStatus) {
                    fileStatus.textContent = msg || '';
                    fileStatus.style.color = isError ? '#c00' : '#666';
                }
            }

            function extractTextFromPdf(file) {
                return new Promise(function(resolve, reject) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const pdfjsLib = window['pdfjsLib'] || window['pdfjs-dist/build/pdf'];
                        if (!pdfjsLib || !pdfjsLib.getDocument) {
                            reject(new Error('PDF.js not loaded'));
                            return;
                        }
                        if (pdfjsLib.GlobalWorkerOptions && !pdfjsLib.GlobalWorkerOptions.workerSrc) {
                            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
                        }
                        pdfjsLib.getDocument({ data: e.target.result }).promise
                            .then(function(pdf) {
                                const numPages = pdf.numPages;
                                const parts = [];
                                function next(pageNum) {
                                    if (pageNum > numPages) {
                                        resolve(parts.join('\n'));
                                        return;
                                    }
                                    pdf.getPage(pageNum).then(function(page) {
                                        return page.getTextContent();
                                    }).then(function(content) {
                                        const line = content.items.map(function(item) { return item.str; }).join(' ');
                                        parts.push(line);
                                        next(pageNum + 1);
                                    }).catch(reject);
                                }
                                next(1);
                            })
                            .catch(reject);
                    };
                    reader.onerror = function() { reject(new Error('Could not read file')); };
                    reader.readAsArrayBuffer(file);
                });
            }

            function uploadAndExtract(file) {
                return new Promise(function(resolve, reject) {
                    const fd = new FormData();
                    fd.append('file', file);
                    fetch('studybuddy_extract.php', { method: 'POST', body: fd })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.success) resolve(data.text || '');
                            else reject(new Error(data.error || 'Extraction failed'));
                        })
                        .catch(reject);
                });
            }

            if (fileUpload) {
                fileUpload.addEventListener('change', function() {
                    const f = this.files && this.files[0];
                    if (!f) return;
                    const name = (f.name || '').toLowerCase();
                    setFileStatus('Extracting text...', false);

                    function done(text, err) {
                        if (err) {
                            setFileStatus(err.message || 'Failed', true);
                            return;
                        }
                        notesContext.value = text || '';
                        setFileStatus('Ready: ' + f.name, false);
                        this.value = '';
                    }

                    const self = this;
                    if (name.endsWith('.txt') || name.endsWith('.xml')) {
                        const r = new FileReader();
                        r.onload = function() {
                            notesContext.value = r.result || '';
                            setFileStatus('Ready: ' + f.name, false);
                            self.value = '';
                        };
                        r.onerror = function() { setFileStatus('Could not read file', true); self.value = ''; };
                        r.readAsText(f);
                        return;
                    }

                    if (name.endsWith('.pdf')) {
                        extractTextFromPdf(f).then(function(text) {
                            notesContext.value = text || '';
                            setFileStatus('Ready: ' + f.name, false);
                            self.value = '';
                        }).catch(function(e) {
                            setFileStatus(e.message || 'PDF extraction failed', true);
                            self.value = '';
                        });
                        return;
                    }

                    if (name.endsWith('.doc') || name.endsWith('.docx') || name.endsWith('.ppt') || name.endsWith('.pptx')) {
                        uploadAndExtract(f).then(function(text) {
                            notesContext.value = text || '';
                            setFileStatus('Ready: ' + f.name, false);
                            self.value = '';
                        }).catch(function(e) {
                            setFileStatus(e.message || 'Extraction failed', true);
                            self.value = '';
                        });
                        return;
                    }

                    setFileStatus('Unsupported format', true);
                    self.value = '';
                });
            }

            function scrollToBottom() {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            function addMessage(content, isUser) {
                if (welcomeMsg) welcomeMsg.style.display = 'none';
                const div = document.createElement('div');
                div.className = 'chat-message ' + (isUser ? 'user' : 'buddy');
                if (isUser) {
                    div.textContent = content;
                } else {
                    div.innerHTML = '<div class="msg-header">🧠 StudyBuddy AI</div><div class="msg-content">' + formatResponse(content) + '</div>';
                }
                chatMessages.appendChild(div);
                scrollToBottom();
            }

            function formatResponse(text) {
                return text
                    .replace(/\n/g, '<br>')
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/^### (.*)$/gm, '<h4>$1</h4>')
                    .replace(/^## (.*)$/gm, '<h3>$1</h3>')
                    .replace(/^• (.*)$/gm, '<li>$1</li>')
                    .replace(/(<li>.*?<\/li>)/gs, '<ul>$1</ul>');
            }

            function showTyping() {
                const div = document.createElement('div');
                div.className = 'typing-indicator';
                div.id = 'typingIndicator';
                div.innerHTML = 'Thinking <span></span><span></span><span></span>';
                chatMessages.appendChild(div);
                scrollToBottom();
            }

            function hideTyping() {
                const el = document.getElementById('typingIndicator');
                if (el) el.remove();
            }

            async function sendMessage() {
                let text = (userInput.value || '').trim();
                const notes = (notesContext.value || '').trim();

                if (notes && !text) {
                    text = 'Summarize the following content.';
                }
                if (!text) return;

                userInput.value = '';
                addMessage(text, true);

                sendBtn.disabled = true;
                showTyping();

                try {
                    const formData = new FormData();
                    formData.append('message', text);
                    formData.append('level', currentLevel);
                    if (notes) formData.append('notes_context', notes);

                    const res = await fetch('studybuddy_api.php', {
                        method: 'POST',
                        body: formData
                    });

                    let data;
                    const contentType = res.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        data = await res.json();
                    } else {
                        const raw = await res.text();
                        hideTyping();
                        addMessage('The server did not return a valid response. If you see PHP errors above, check the server logs. Otherwise try logging in again or add your OpenAI API key in studybuddy_config.php.', false);
                        if (raw && raw.length < 500) {
                            console.error('StudyBuddy API response:', raw);
                        }
                        sendBtn.disabled = false;
                        return;
                    }

                    hideTyping();
                    const reply = data.reply || 'Sorry, I couldn\'t generate a response. Please try again.';
                    addMessage(reply, false);

                    if (data.using_demo !== undefined && !data.using_demo) {
                        apiNotice.textContent = 'Powered by AI.';
                    }
                } catch (e) {
                    hideTyping();
                    addMessage('Something went wrong. Please check your connection, that you are logged in, and try again. Error: ' + (e.message || 'Unknown'), false);
                }

                sendBtn.disabled = false;
            }

            sendBtn.addEventListener('click', sendMessage);
            userInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
        })();
    </script>
</body>
</html>
