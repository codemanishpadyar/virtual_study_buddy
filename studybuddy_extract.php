<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in.', 'text' => '']);
    exit;
}

$allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'xml'];
$file = $_FILES['file'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload failed.', 'text' => '']);
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed, true)) {
    echo json_encode(['success' => false, 'error' => 'File type not supported. Use PDF, DOC, DOCX, PPT, PPTX, TXT, or XML.', 'text' => '']);
    exit;
}

$tmpPath = $file['tmp_name'];

// TXT: read directly
if ($ext === 'txt' || $ext === 'xml') {
    $text = @file_get_contents($tmpPath);
    $text = $text === false ? '' : trim($text);
    if (function_exists('mb_convert_encoding')) {
        $enc = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($enc && $enc !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $enc);
        }
    }
    echo json_encode(['success' => true, 'text' => $text, 'error' => '']);
    exit;
}

// DOCX: ZIP with word/document.xml (legacy .doc is binary and not supported)
if ($ext === 'docx' || $ext === 'doc') {
    if ($ext === 'doc') {
        echo json_encode(['success' => false, 'error' => 'Legacy .doc format is not supported. Please save the file as .docx or paste the text.', 'text' => '']);
        exit;
    }
    if (!class_exists('ZipArchive')) {
        echo json_encode(['success' => false, 'error' => 'Server does not support DOCX extraction (ZipArchive missing).', 'text' => '']);
        exit;
    }
    $zip = new ZipArchive();
    if ($zip->open($tmpPath, ZipArchive::RDONLY) !== true) {
        echo json_encode(['success' => false, 'error' => 'Could not open DOCX file.', 'text' => '']);
        exit;
    }
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    if ($xml === false) {
        echo json_encode(['success' => false, 'error' => 'No document content in file.', 'text' => '']);
        exit;
    }
    $text = strip_tags($xml);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', trim($text));
    echo json_encode(['success' => true, 'text' => $text, 'error' => '']);
    exit;
}

// PPTX: ZIP with ppt/slides/slideN.xml (legacy .ppt is binary and not supported)
if ($ext === 'pptx' || $ext === 'ppt') {
    if ($ext === 'ppt') {
        echo json_encode(['success' => false, 'error' => 'Legacy .ppt format is not supported. Please save the file as .pptx or paste the text.', 'text' => '']);
        exit;
    }
    if (!class_exists('ZipArchive')) {
        echo json_encode(['success' => false, 'error' => 'Server does not support PPTX extraction (ZipArchive missing).', 'text' => '']);
        exit;
    }
    $zip = new ZipArchive();
    if ($zip->open($tmpPath, ZipArchive::RDONLY) !== true) {
        echo json_encode(['success' => false, 'error' => 'Could not open PPTX file.', 'text' => '']);
        exit;
    }
    $allText = [];
    for ($i = 1; $i <= 500; $i++) {
        $name = "ppt/slides/slide{$i}.xml";
        if ($zip->locateName($name) === false) {
            break;
        }
        $xml = $zip->getFromName($name);
        if ($xml !== false) {
            $allText[] = strip_tags($xml);
        }
    }
    $zip->close();
    $text = html_entity_decode(implode("\n", $allText), ENT_QUOTES | ENT_XML1, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', trim($text));
    echo json_encode(['success' => true, 'text' => $text, 'error' => '']);
    exit;
}

// PDF: require external tool or return message for client-side
if ($ext === 'pdf') {
    // Try pdftotext if available (common on Linux; on Windows often not)
    $pdftotext = 'pdftotext';
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $pdftotext = 'pdftotext.exe';
    }
    $outFile = $tmpPath . '.txt';
    @exec(escapeshellcmd($pdftotext) . ' ' . escapeshellarg($tmpPath) . ' ' . escapeshellarg($outFile), $_, $ret);
    if ($ret === 0 && is_file($outFile)) {
        $text = trim(@file_get_contents($outFile));
        @unlink($outFile);
        echo json_encode(['success' => true, 'text' => $text, 'error' => '']);
        exit;
    }
    echo json_encode([
        'success' => false,
        'error' => 'PDF extraction is not available on this server. Please use the "Upload file" button and choose your PDF — it will be extracted in your browser.',
        'text' => ''
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unsupported file type.', 'text' => '']);
