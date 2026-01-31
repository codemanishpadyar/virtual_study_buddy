<?php
session_start();
header('Content-Type: application/json');

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

// Load API key from config if present
$apiKey = null;
if (file_exists(__DIR__ . '/studybuddy_config.php')) {
    include __DIR__ . '/studybuddy_config.php';
    $apiKey = defined('STUDYBUDDY_OPENAI_API_KEY') ? STUDYBUDDY_OPENAI_API_KEY : (getenv('OPENAI_API_KEY') ?: null);
}
if (!$apiKey) {
    $apiKey = getenv('OPENAI_API_KEY') ?: null;
}

if ($apiKey) {
    $reply = callOpenAI($apiKey, $message, $level, $notesContext);
    if ($reply !== null) {
        echo json_encode(['reply' => $reply, 'using_demo' => false]);
        exit;
    }
}

// Demo / fallback responses
echo json_encode([
    'reply' => getDemoReply($message, $level, $notesContext),
    'using_demo' => true
]);
exit;

function callOpenAI($apiKey, $message, $level, $notesContext) {
    $systemPrompt = getSystemPrompt($level, $notesContext);
    $payload = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $message]
        ],
        'max_tokens' => 1024,
        'temperature' => 0.7
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 60
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return null;
    }

    $data = json_decode($response, true);
    $content = $data['choices'][0]['message']['content'] ?? null;
    return $content ? trim($content) : null;
}

function getSystemPrompt($level, $notesContext) {
    $levelNote = [
        'beginner' => 'Explain in very simple terms, with everyday analogies. Avoid jargon; define any terms you use.',
        'intermediate' => 'Assume basic familiarity. Use correct terminology and structure; include key details and examples.',
        'advanced' => 'Be precise and concise. Use technical language; focus on nuances, edge cases, and connections to deeper theory.'
    ];

    $base = "You are StudyBuddy AI — an animated, friendly, and highly knowledgeable AI tutor.

Your goals:
1. Explain academic concepts clearly and simply.
2. Adjust complexity based on the learner's level. Current level: " . $level . ". " . $levelNote[$level] . "
3. Provide structured, visually appealing answers with:
   - headers (## or ###)
   - bullet points
   - short paragraphs
   - examples
   - analogies when helpful
4. Use light, study-friendly formatting:
   - ✨ for key ideas
   - 📘 for sections
   - ✔️ for steps/checklists
   - ➤ for flows

Keep explanations concise but precise. Tone: supportive, motivating, encouraging. Never fabricate facts. Output plain text with markdown-style headers and bullets (no HTML).";

    if ($notesContext !== '') {
        $base .= "\n\nThe user has provided the following notes or text. When relevant, summarize or explain this content as part of your response:\n\n---\n" . $notesContext . "\n---";
    }

    return $base;
}

function getDemoReply($message, $level, $notesContext) {
    $lower = strtolower($message);

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
            "• (With API key) Summarize or explain pasted notes\n\n" .
            "➤ **Try:** \"Explain [topic]\" or paste notes and ask \"Summarize this.\"\n\n" .
            "📘 Right now I'm in demo mode. Add your OpenAI API key for full AI responses.";
    }

    // Default demo reply
    return "📘 **Got your question**\n\n" .
        "I'm currently in **demo mode**, so I'm giving a short sample reply.\n\n" .
        "✔️ **To get full AI explanations:**\n" .
        "• Create a file `studybuddy_config.php` in this project folder\n" .
        "• Add: define('STUDYBUDDY_OPENAI_API_KEY', 'your-key'); in that file\n" .
        "• Replace with your real OpenAI API key\n\n" .
        "✨ Then ask me anything (e.g. \"Explain photosynthesis\" or \"What is recursion?\") and I'll respond at your chosen level with full, structured answers.";
}
