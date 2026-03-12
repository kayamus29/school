<?php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

$internalPattern = '/^\/(login|register|logout|home|portal|api|school|attendances|classes|sections|teachers|students|marks|results|exams|promotions|accounting|ajax|staff|audit|academics|settings|calendar-|routine|syllabus|notice|courses|password|storage)/i';

if (preg_match($internalPattern, $uri)) {
    require_once __DIR__ . '/public/index.php';
} else {
    if (file_exists(__DIR__ . '/public/index.html')) {
        readfile(__DIR__ . '/public/index.html');
    } else {
        require_once __DIR__ . '/public/index.php';
    }
}
