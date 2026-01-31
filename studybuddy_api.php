<?php
/**
 * StudyBuddy AI API — uses OpenAI GPT to answer user questions and summarize notes.
 * Set STUDYBUDDY_OPENAI_API_KEY in studybuddy_config.php to enable GPT responses.
 */
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['reply' => 'Please log in to use StudyBuddy AI.', 'using_demo' => false]);
    exit;
}

$message = trim($_POST['message'] ?? '');
$level = in_array($_POST['level'] ?? '', ['beginner', 'intermediate', 'advanced']) ? $_POST['level'] : 'beginner';
$notesContext = trim($_POST['notes_context'] ?? '');

if ($message === '') {
    echo json_encode(['reply' => 'Type a question or topic and I\'ll explain it at your level.', 'using_demo' => true]);
    exit;
}

$isSummarize = $notesContext !== '';
if ($isSummarize) {
    // Style derived from level: beginner → beginner, intermediate → exam-ready, advanced → clean
    $styleMap = ['beginner' => 'beginner', 'intermediate' => 'exam-ready', 'advanced' => 'clean'];
    $outputStyle = $styleMap[$level];
} else {
    $outputStyle = 'animated';
}

// Load API key from config (same folder as this file)
$apiKey = null;
$configPath = __DIR__ . DIRECTORY_SEPARATOR . 'studybuddy_config.php';
if (file_exists($configPath)) {
    include_once $configPath;
    if (defined('STUDYBUDDY_OPENAI_API_KEY')) {
        $apiKey = trim((string) STUDYBUDDY_OPENAI_API_KEY);
    }
}
if (!$apiKey || $apiKey === '') {
    $envKey = getenv('OPENAI_API_KEY');
    $apiKey = $envKey !== false ? trim((string) $envKey) : null;
}
$placeholderKeys = ['your-openai-api-key-here', 'your-key', 'sk-your-key', 'sk-placeholder', ''];
if ($apiKey !== null && ($apiKey === '' || in_array(strtolower($apiKey), array_map('strtolower', $placeholderKeys), true))) {
    $apiKey = null;
}

if ($apiKey) {
    $reply = callOpenAI($apiKey, $message, $level, $notesContext, $isSummarize, $outputStyle);
    if ($reply !== null && $reply !== '') {
        echo json_encode(['reply' => $reply, 'using_demo' => false]);
        exit;
    }
}

echo json_encode([
    'reply' => getDemoReply($message, $level, $notesContext, $isSummarize),
    'using_demo' => true
]);
exit;

function callOpenAI($apiKey, $message, $level, $notesContext, $isSummarize = false, $outputStyle = 'animated') {
    $apiKey = trim((string) $apiKey);
    $model = (defined('STUDYBUDDY_OPENAI_MODEL') && STUDYBUDDY_OPENAI_MODEL !== '') ? STUDYBUDDY_OPENAI_MODEL : 'gpt-4o-mini';

    $systemPrompt = getSystemPrompt($level, $notesContext, $isSummarize, $outputStyle);
    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $message]
        ],
        'max_tokens' => 2048,
        'temperature' => 0.7
    ];

    $sslVerify = !defined('STUDYBUDDY_OPENAI_SSL_VERIFY') || STUDYBUDDY_OPENAI_SSL_VERIFY;
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 90,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => $sslVerify,
        CURLOPT_SSL_VERIFYHOST => $sslVerify ? 2 : 0
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($response === false || $response === '') {
        $err = $curlErr ?: 'No response from server';
        return "**Connection error**\n\nCould not reach the AI service. Please check your internet connection and try again.\n\nDetails: " . $err;
    }

    if ($httpCode !== 200) {
        $data = json_decode($response, true);
        $msg = is_array($data) && isset($data['error']['message']) ? $data['error']['message'] : substr($response, 0, 200);
        if ($httpCode === 401) {
            return "**API key error**\n\nYour OpenAI API key may be invalid or expired. Check:\n• File is named exactly `studybuddy_config.php` (same folder as this site)\n• You have `define('STUDYBUDDY_OPENAI_API_KEY', 'sk-...');` with a valid key\n• Get a key at https://platform.openai.com/api-keys\n\nServer message: " . $msg;
        }
        if ($httpCode === 429) {
            return "**Rate limit**\n\nToo many requests. Please wait a moment and try again.\n\n" . $msg;
        }
        return "**AI service error (HTTP " . $httpCode . ")**\n\n" . $msg;
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        return "**Invalid response**\n\nThe AI service returned an unexpected response. Please try again.";
    }
    $content = isset($data['choices'][0]['message']['content']) ? $data['choices'][0]['message']['content'] : null;
    if ($content === null || $content === '') {
        return "**Empty response**\n\nThe AI service did not return any text. Please try again.";
    }
    return trim((string) $content);
}

function getSystemPrompt($level, $notesContext, $isSummarize = false, $outputStyle = 'animated') {
    $levelNote = [
        'beginner' => 'Use simple language, analogies, and examples. Define terms. Avoid jargon.',
        'intermediate' => 'Assume basic familiarity. Use correct terminology, key details, and examples.',
        'advanced' => 'Be precise and concise. Use technical language; include nuances and connections to deeper theory.'
    ];

    $styleRules = [
        'clean' => 'No emojis, minimal formatting. Headings and bullet points only. Calm, professional tone.',
        'animated' => 'Use light study-themed emojis where helpful: 📘 ✨ 🧠 🔍 ✔️. Sparing and purposeful.',
        'exam-ready' => 'Bullet points, key facts, formulas. Clear and scannable for revision.',
        'beginner' => 'Simple language, analogies, examples. Short paragraphs, friendly tone.'
    ];
    $styleRule = $styleRules[$outputStyle] ?? $styleRules['animated'];

    $base = "You are StudyBuddy AI — a friendly, accurate, and highly reliable AI tutor built to help students learn anything. Your job is to always respond to the user's questions clearly, correctly, and in an easy-to-understand way.

Core abilities:
1. Answer any academic question with simple, clear explanations.
2. Summarize user-provided text or extracted content from files (PDF, DOCX, XML, TXT, PPT, etc.).
3. Provide definitions, examples, formulas, analogies, and step-by-step breakdowns when helpful.
4. Adapt explanations to the user's chosen difficulty level. Current level: " . $level . ". " . $levelNote[$level] . "
5. Provide study-friendly outputs: headings, bullet points, short paragraphs, clear step-by-step sequences.

Style for this request: " . $outputStyle . ". " . $styleRule . "

Rules:
- ALWAYS answer the user's question. NEVER stay silent.
- NEVER say \"cannot answer\" unless the request is unsafe or harmful.
- NEVER hallucinate facts or invent missing information.
- If the user provides text or file content (extracted text), summarize or explain based ONLY on that text. Do not add information that is not in the provided content.
- If the provided text is unclear or incomplete, ask the user for a better file or more information — but still give a helpful response based on what you have.

Output requirements:
- Start with a short overview.
- Follow with a structured explanation (headings, bullets, short paragraphs as appropriate).
- Be supportive, helpful, and student-friendly.
- Output plain text with markdown-style headers and bullets (no HTML).

Your mission: Help the user learn faster, understand deeply, and feel supported while studying.";

    if ($isSummarize && $notesContext !== '') {
        $base .= "\n\nExtracted text from the user's file or pasted content to summarize or explain:\n\n---\n" . $notesContext . "\n---";
        return $base;
    }

    if ($notesContext !== '') {
        $base .= "\n\nThe user has provided the following notes or text. When relevant, summarize or explain this content as part of your response. Base your answer only on this content plus general knowledge as appropriate for the question:\n\n---\n" . $notesContext . "\n---";
    }

    return $base;
}

function getDemoReply($message, $level, $notesContext, $isSummarize = false) {
    $lower = strtolower($message);

    if ($isSummarize && $notesContext !== '') {
        return "📘 **Summarizer (demo mode)**\n\nI'm in **demo mode**, so I can't generate a full AI summary of your content yet.\n\n" .
            "✔️ **What you can do:**\n" .
            "• Add your OpenAI API key in `studybuddy_config.php` (see studybuddy_config.sample.php)\n" .
            "• Then I'll turn your notes into a structured summary: Overview, Key Points, Definitions, Examples, and optional next steps (flashcards, quiz, deeper explanation).\n\n" .
            "✨ Until then, you can ask me to **explain** a concept (e.g. \"Explain photosynthesis\") and I'll give a sample explanation.";
    }

    if ($notesContext !== '') {
        return "📘 **Notes context received**\n\nI'm in **demo mode** right now, so I can't fully analyze your notes yet.\n\n" .
            "✔️ **What you can do:**\n" .
            "• Add your OpenAI API key in `studybuddy_config.php` (see instructions in that file)\n" .
            "• Then I'll be able to summarize and explain your pasted notes at your chosen level (" . $level . ")\n\n" .
            "✨ Until then, ask me any general concept (e.g. \"Explain photosynthesis\" or \"What is Newton's first law?\") and I'll give you a sample explanation.";
    }

    // Sample demo replies for common topics
    if (strpos($lower, 'photosynthesis') !== false) {
        return "📘 **Photosynthesis** (at " . $level . " level)\n\n" .
            "Photosynthesis is how plants make their own food using light.\n\n" .
            "✔️ **Simple steps:**\n" .
            "• Plants take in **carbon dioxide** (from air) and **water** (from soil)\n" .
            "• **Chlorophyll** in leaves captures **sunlight**\n" .
            "• They produce **glucose** (sugar) and release **oxygen**\n\n" .
            "✨ **Memory tip:** \"Light + CO₂ + Water → Sugar + Oxygen\"\n\n" .
            "➤ For full, personalized explanations, add your OpenAI API key in config.";
    }

    if (strpos($lower, 'newton') !== false || strpos($lower, 'law of motion') !== false) {
        return "📘 **Newton's Laws of Motion**\n\n" .
            "**1st law (Inertia):** An object stays at rest or in constant motion unless a force acts on it.\n" .
            "**2nd law:** Force = mass × acceleration (F = ma).\n" .
            "**3rd law:** For every action there is an equal and opposite reaction.\n\n" .
            "✔️ **Example:** When you push a wall, the wall pushes you back — that's the 3rd law.\n\n" .
            "➤ Add your API key for explanations tailored to your level.";
    }

    if (strpos($lower, 'hello') !== false || strpos($lower, 'hi ') === 0) {
        return "✨ Hi! I'm StudyBuddy AI. Ask me to explain any topic — e.g. \"Explain mitosis\" or \"What is PHP?\" — and I'll break it down at your level.\n\n" .
            "📘 In **demo mode** I can only give sample answers. Add your OpenAI API key in `studybuddy_config.php` for full AI-powered tutoring.";
    }

    if (strpos($lower, 'help') !== false || strpos($lower, 'what can you') !== false) {
        return "✔️ **What I can do:**\n" .
            "• Explain concepts (math, science, programming, etc.) at Beginner / Intermediate / Advanced\n" .
            "• Use headers, bullet points, and examples\n" .
            "• (With API key) Summarize or explain pasted notes and uploaded files\n\n" .
            "➤ **Try:** \"Explain [topic]\" or paste notes and ask \"Summarize this.\"\n\n" .
            "📘 Right now I'm in demo mode. Add your OpenAI API key for full AI responses.";
    }

    // More sample answers so the chatbot "answers" in demo mode
    if (strpos($lower, 'php') !== false) {
        return "📘 **PHP** (at " . $level . " level)\n\n" .
            "PHP is a scripting language used mainly for web development. It runs on the server and can generate HTML, handle forms, and talk to databases.\n\n" .
            "✔️ **In short:** PHP code is executed on the server; the browser receives the result (usually HTML).\n\n" .
            "➤ Add your OpenAI API key for a full explanation at your level.";
    }
    if (strpos($lower, 'recursion') !== false || strpos($lower, 'recursive') !== false) {
        return "📘 **Recursion**\n\n" .
            "Recursion is when a function calls itself to solve a smaller part of the problem until it hits a base case (a condition that stops the calls).\n\n" .
            "✔️ **Example:** Factorial: n! = n × (n−1)! and 0! = 1.\n\n" .
            "➤ Add your API key for more examples and level-specific explanation.";
    }
    if (strpos($lower, 'mitosis') !== false) {
        return "📘 **Mitosis**\n\n" .
            "Mitosis is cell division that produces two identical daughter cells, each with the same number of chromosomes as the parent. It’s used for growth and repair.\n\n" .
            "✔️ **Stages (simplified):** Prophase → Metaphase → Anaphase → Telophase → Cytokinesis.\n\n" .
            "➤ Add your API key for a full, level-appropriate explanation.";
    }
    if (strpos($lower, 'gravity') !== false) {
        return "📘 **Gravity**\n\n" .
            "Gravity is the force that pulls objects with mass toward each other. On Earth, it gives things weight and keeps them on the ground.\n\n" .
            "✔️ **Newton:** F = G × (m₁ × m₂) / r².\n\n" .
            "➤ Add your API key for more detail at your level.";
    }
    if (strpos($lower, 'explain') !== false || strpos($lower, 'what is') !== false || strpos($lower, 'define') !== false || strpos($lower, 'how does') !== false) {
        $topic = trim(preg_replace('/^(explain|what is|define|how does)\s+/i', '', $message));
        if (strlen($topic) > 60) {
            $topic = substr($topic, 0, 57) . '...';
        }
        $topicDisplay = $topic !== '' ? " **" . $topic . "**" : " that";
        return "📘 **Got it**\n\n" .
            "You asked about" . $topicDisplay . ". In **demo mode** I can only give sample answers for a few topics (e.g. photosynthesis, Newton's laws, PHP, recursion).\n\n" .
            "✔️ **To get a full answer for any question:**\n" .
            "• Add your OpenAI API key in `studybuddy_config.php` (copy from studybuddy_config.sample.php)\n" .
            "• Then I'll explain any topic at your chosen level with examples and structure.\n\n" .
            "✨ Try asking: \"Explain photosynthesis\" or \"What is Newton's first law?\" for a sample reply.";
    }

    // Default: acknowledge the question and give a short tip
    return "📘 **I read your question**\n\n" .
        "In **demo mode** I can only give sample answers for a few topics (e.g. photosynthesis, Newton's laws, PHP, recursion, mitosis, gravity). For anything else, I need an OpenAI API key to answer fully.\n\n" .
        "✔️ **To get answers for any question:**\n" .
        "• Create `studybuddy_config.php` from studybuddy_config.sample.php\n" .
        "• Add your OpenAI API key (from https://platform.openai.com/api-keys)\n" .
        "• Then ask me anything and I'll answer at your level.\n\n" .
        "✨ You can also paste or upload notes (PDF, DOCX, TXT) and I'll summarize them once the API key is set.";
}
