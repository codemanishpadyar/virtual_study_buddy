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
    <title>Forum - Virtual Study Buddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        .forum-container {
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

        .forum-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .new-topic-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .new-topic-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .search-box {
            flex: 0 0 300px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: #667eea;
            outline: none;
        }

        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .categories {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .category-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            background: white;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .category-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .topics-list {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .topic-item {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .topic-item.pinned {
            background-color: #f8f9ff;
        }

        .topic-item.pinned::before {
            content: "📌";
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 16px;
        }

        .topic-item:last-child {
            border-bottom: none;
        }

        .topic-item:hover {
            background: #f8f9ff;
        }

        .topic-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .topic-title {
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }

        .topic-category {
            font-size: 12px;
            color: #764ba2;
            background: #f0f2ff;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .topic-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 12px;
            color: #666;
        }

        .topic-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .topic-meta i {
            font-size: 14px;
        }

        .like-btn {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .like-btn:hover {
            color: #764ba2;
        }

        .like-btn.liked {
            color: #764ba2;
        }

        .like-btn i {
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .forum-container {
                padding: 20px;
            }

            .forum-actions {
                flex-direction: column;
                gap: 20px;
            }

            .search-box {
                flex: none;
                width: 100%;
            }

            .categories {
                padding-bottom: 5px;
            }

            .topic-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        /* Add new styles for modals and topic view */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .modal-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .topic-view {
            display: none;
        }

        .topic-content {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .topic-author {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: #666;
            font-size: 14px;
        }

        .topic-text {
            color: #333;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .replies-section {
            margin-top: 30px;
        }

        .reply-item {
            background: #f8f9ff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .reply-author {
            font-weight: 500;
            color: #333;
            margin-bottom: 10px;
        }

        .reply-content {
            color: #666;
            line-height: 1.5;
        }

        .back-to-topics {
            margin-bottom: 20px;
            color: #667eea;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .error-message {
            color: #ff6b6b;
            text-align: center;
            padding: 20px;
        }

        .gradient-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .gradient-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .topic-content {
            margin-top: 15px;
            position: relative;
            overflow: hidden;
            max-height: 100px;
            transition: max-height 0.3s ease;
        }

        .topic-content.expanded {
            max-height: none;
        }

        .topic-content-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            background: linear-gradient(transparent, white);
            pointer-events: none;
        }

        .expand-btn {
            display: inline-block;
            margin-top: 10px;
            color: #667eea;
            cursor: pointer;
            font-size: 14px;
        }

        .replies-container {
            margin-top: 20px;
            padding-left: 20px;
            border-left: 2px solid #f0f0f0;
        }

        .reply-item {
            padding: 15px;
            background: #f8f9ff;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .reply-form {
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .reactions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .reaction-btn {
            background: none;
            border: 1px solid #e1e1e1;
            padding: 5px 10px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .reaction-btn:hover {
            background: #f0f2ff;
            border-color: #667eea;
            color: #667eea;
        }

        .reaction-btn.active {
            background: #667eea;
            border-color: #667eea;
            color: white;
        }

        .editor-toolbar {
            display: flex;
            gap: 10px;
            padding: 10px;
            background: #f8f9ff;
            border: 1px solid #e1e1e1;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
        }

        .editor-toolbar button {
            background: none;
            border: 1px solid #e1e1e1;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            color: #666;
            transition: all 0.3s ease;
        }

        .editor-toolbar button:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .editor-toolbar button.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .editor-content {
            border: 1px solid #e1e1e1;
            border-radius: 0 0 8px 8px;
            overflow: hidden;
        }

        .editor-content textarea {
            width: 100%;
            min-height: 200px;
            padding: 15px;
            border: none;
            resize: vertical;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }

        .editor-content textarea:focus {
            outline: none;
        }

        .preview-content {
            padding: 15px;
            border-top: 1px solid #e1e1e1;
            background: #f8f9ff;
            display: none;
        }

        /* Style for formatted content */
        .formatted-content {
            line-height: 1.6;
        }

        .formatted-content h1, 
        .formatted-content h2, 
        .formatted-content h3 {
            margin: 1em 0 0.5em;
        }

        .formatted-content p {
            margin: 0.5em 0;
        }

        .formatted-content ul,
        .formatted-content ol {
            margin: 0.5em 0;
            padding-left: 2em;
        }

        .formatted-content pre {
            background: #f8f9ff;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 0.5em 0;
        }

        .formatted-content blockquote {
            border-left: 4px solid #667eea;
            margin: 0.5em 0;
            padding-left: 1em;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="forum-container">
        <div class="page-title">
            <h1>Study Forum</h1>
            <p>Connect, discuss, and learn together</p>
        </div>

        <div class="forum-actions">
            <button class="new-topic-btn" onclick="showNewTopicModal()">
                <i class="fas fa-plus"></i> New Topic
            </button>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search topics...">
                <i class="fas fa-search"></i>
            </div>
        </div>

        <div class="categories">
            <button class="category-btn active" data-category="All Topics">All Topics</button>
            <button class="category-btn" data-category="Questions">Questions</button>
            <button class="category-btn" data-category="Discussion">Discussion</button>
            <button class="category-btn" data-category="Resources">Resources</button>
            <button class="category-btn" data-category="Study Groups">Study Groups</button>
        </div>

        <div class="topics-list" id="topicsList">
            <!-- Topics will be loaded here dynamically -->
        </div>
    </div>

    <!-- New Topic Modal -->
    <div id="newTopicModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Create New Topic</h2>
            <form id="newTopicForm">
                <div class="form-group">
                    <label for="topicTitle">Title</label>
                    <input type="text" id="topicTitle" required>
                </div>
                <div class="form-group">
                    <label for="topicCategory">Category</label>
                    <select id="topicCategory" required>
                        <option value="Questions">Questions</option>
                        <option value="Discussion">Discussion</option>
                        <option value="Resources">Resources</option>
                        <option value="Study Groups">Study Groups</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="topicContent">Content</label>
                    <div class="editor-toolbar">
                        <button type="button" data-format="bold" title="Bold"><i class="fas fa-bold"></i></button>
                        <button type="button" data-format="italic" title="Italic"><i class="fas fa-italic"></i></button>
                        <button type="button" data-format="list" title="List"><i class="fas fa-list"></i></button>
                        <button type="button" data-format="quote" title="Quote"><i class="fas fa-quote-right"></i></button>
                        <button type="button" data-format="code" title="Code"><i class="fas fa-code"></i></button>
                        <button type="button" id="previewBtn" title="Preview"><i class="fas fa-eye"></i></button>
                    </div>
                    <div class="editor-content">
                        <textarea id="topicContent" required placeholder="Write your content here..."></textarea>
                        <div class="preview-content"></div>
                </div>
                </div>
                <button type="submit" class="submit-btn">Create Topic</button>
            </form>
        </div>
    </div>

    <script>
        // Format text in textarea
        function formatText(command) {
            const textarea = document.getElementById('topicContent');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            let formattedText = '';

            switch(command) {
                case 'bold':
                    formattedText = `**${selectedText}**`;
                    break;
                case 'italic':
                    formattedText = `*${selectedText}*`;
                    break;
                case 'list':
                    formattedText = selectedText.split('\n').map(line => `- ${line}`).join('\n');
                    break;
                case 'quote':
                    formattedText = selectedText.split('\n').map(line => `> ${line}`).join('\n');
                    break;
                case 'code':
                    formattedText = `\`\`\`\n${selectedText}\n\`\`\``;
                    break;
            }

            textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
            updatePreview();
        }

        // Convert markdown-like syntax to HTML
        function formatContent(content) {
            return content
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/^- (.*)$/gm, '<li>$1</li>')
                .replace(/(<li>.*<\/li>\n?)+/g, '<ul>$&</ul>')
                .replace(/^> (.*)$/gm, '<blockquote>$1</blockquote>')
                .replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>')
                .replace(/\n/g, '<br>');
        }

        // Update preview
        function updatePreview() {
            const content = document.getElementById('topicContent').value;
            const preview = document.querySelector('.preview-content');
            preview.innerHTML = formatContent(content);
        }

        // Editor toolbar buttons
        document.querySelectorAll('.editor-toolbar button').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const format = button.dataset.format;
                if (format) {
                    formatText(format);
                } else if (button.id === 'previewBtn') {
                    const preview = document.querySelector('.preview-content');
                    const textarea = document.getElementById('topicContent');
                    if (preview.style.display === 'none') {
                        updatePreview();
                        preview.style.display = 'block';
                        textarea.style.display = 'none';
                        button.classList.add('active');
                    } else {
                        preview.style.display = 'none';
                        textarea.style.display = 'block';
                        button.classList.remove('active');
                    }
                }
            });
        });

        // Load topics function
        function loadTopics(category = 'All Topics', search = '') {
            $.ajax({
                url: 'forum_operations.php',
                type: 'GET',
                data: {
                    action: 'list',
                    category: category,
                    search: search
                },
                success: function(response) {
                    if (response.success) {
                        const topicsList = $('#topicsList');
                        topicsList.empty();
                        
                        response.topics.forEach(topic => {
                            const topicHtml = `
                                <div class="topic-item ${topic.is_pinned ? 'pinned' : ''}" data-id="${topic.id}">
                                    <div class="topic-header">
                                        <h3 class="topic-title">${topic.title}</h3>
                                        <span class="topic-category">${topic.category}</span>
                                    </div>
                                    <div class="topic-content formatted-content">
                                        ${formatContent(topic.content)}
                                        <div class="topic-content-overlay"></div>
                                    </div>
                                    <div class="expand-btn">Read more</div>
                                    <div class="topic-meta">
                                        <span><i class="fas fa-user"></i> ${topic.username}</span>
                                        <span><i class="fas fa-clock"></i> ${new Date(topic.created_at).toLocaleDateString()}</span>
                                        <span><i class="fas fa-comments"></i> ${topic.reply_count} replies</span>
                                        <button class="like-btn ${topic.is_liked ? 'liked' : ''}" onclick="likeTopic(${topic.id})">
                                            <i class="fas fa-heart"></i> ${topic.like_count}
                                        </button>
                                    </div>
                                    <div class="reactions">
                                        <button class="reaction-btn" onclick="react(${topic.id}, 'helpful')">
                                            <i class="fas fa-lightbulb"></i> Helpful
                                        </button>
                                        <button class="reaction-btn" onclick="react(${topic.id}, 'interesting')">
                                            <i class="fas fa-star"></i> Interesting
                                        </button>
                                    </div>
                                    <div class="replies-container" style="display: none;"></div>
                                </div>
                            `;
                            topicsList.append(topicHtml);
                        });
                    }
                }
            });
        }

        // Event handlers
        $(document).ready(function() {
            loadTopics();

            // Category buttons
            $('.category-btn').click(function() {
                $('.category-btn').removeClass('active');
                $(this).addClass('active');
                loadTopics($(this).data('category'), $('#searchInput').val());
            });

            // Search input
            let searchTimeout;
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadTopics($('.category-btn.active').data('category'), $(this).val());
                }, 300);
            });

            // Expand/collapse topic content
            $(document).on('click', '.expand-btn', function() {
                const content = $(this).siblings('.topic-content');
                content.toggleClass('expanded');
                $(this).text(content.hasClass('expanded') ? 'Show less' : 'Read more');
            });

            // Topic click to load replies
            $(document).on('click', '.topic-item', function(e) {
                if (!$(e.target).closest('.like-btn, .reaction-btn, .expand-btn').length) {
                    const topicId = $(this).data('id');
                    const repliesContainer = $(this).find('.replies-container');
                    
                    if (repliesContainer.is(':empty')) {
                        $.ajax({
                            url: 'forum_operations.php',
                            type: 'GET',
                            data: {
                                action: 'get_topic',
                                topic_id: topicId
                            },
                            success: function(response) {
                                if (response.success) {
                                    const replies = response.topic.replies;
                                    let repliesHtml = '';
                                    
                                    replies.forEach(reply => {
                                        repliesHtml += `
                                            <div class="reply-item">
                                                <div class="reply-header">
                                                    <span><i class="fas fa-user"></i> ${reply.username}</span>
                                                    <span><i class="fas fa-clock"></i> ${new Date(reply.created_at).toLocaleDateString()}</span>
                                                </div>
                                                <div class="reply-content">${reply.content}</div>
                    </div>
                                        `;
                                    });
                                    
                                    repliesHtml += `
                                        <div class="reply-form">
                                            <h4>Add Reply</h4>
                                            <textarea class="reply-textarea"></textarea>
                                            <button class="submit-btn" onclick="submitReply(${topicId})">Submit Reply</button>
                    </div>
                `;
                                    
                                    repliesContainer.html(repliesHtml);
                                }
                            }
                        });
                    }
                    
                    repliesContainer.slideToggle();
                }
            });
        });

        // Like topic
        function likeTopic(topicId) {
            $.ajax({
                url: 'forum_operations.php',
                type: 'POST',
                data: {
                    action: 'like',
                    topic_id: topicId
                },
                success: function(response) {
                    if (response.success) {
                        loadTopics($('.category-btn.active').data('category'), $('#searchInput').val());
                    }
                }
            });
        }

        // React to topic
        function react(topicId, reaction) {
            $.ajax({
                url: 'forum_operations.php',
                type: 'POST',
                data: {
                    action: 'react',
                    topic_id: topicId,
                    reaction: reaction
                },
                success: function(response) {
                    if (response.success) {
                        loadTopics($('.category-btn.active').data('category'), $('#searchInput').val());
                    }
                }
            });
        }

        // Submit reply
        function submitReply(topicId) {
            const content = $(`.topic-item[data-id="${topicId}"] .reply-textarea`).val();
            if (content.trim()) {
                $.ajax({
                    url: 'forum_operations.php',
                    type: 'POST',
                    data: {
                        action: 'reply',
                        topic_id: topicId,
                        content: content
                    },
                    success: function(response) {
                        if (response.success) {
                            loadTopics($('.category-btn.active').data('category'), $('#searchInput').val());
                        }
                    }
                });
            }
        }

        // Modal functions
        function showNewTopicModal() {
            $('#newTopicModal').css('display', 'block');
        }

        $('.close').click(function() {
            $('#newTopicModal').css('display', 'none');
        });

        $(window).click(function(e) {
            if (e.target == $('#newTopicModal')[0]) {
                $('#newTopicModal').css('display', 'none');
            }
        });

        $('#newTopicForm').submit(function(e) {
            e.preventDefault();
            const content = $('#topicContent').val();
            
            $.ajax({
                url: 'forum_operations.php',
                type: 'POST',
                data: {
                    action: 'create',
                    title: $('#topicTitle').val(),
                    category: $('#topicCategory').val(),
                    content: content
                },
                success: function(response) {
                    if (response.success) {
                        $('#newTopicModal').css('display', 'none');
                        $('#newTopicForm')[0].reset();
                        loadTopics($('.category-btn.active').data('category'), $('#searchInput').val());
                    }
                }
            });
        });
    </script>
</body>
</html>
