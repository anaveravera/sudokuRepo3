<?php

declare(strict_types=1);

header('Content-Type: application/json');
http_response_code(200);

echo json_encode([
    'status' => 'ok',
    'service' => 'sudoku-repo3',
    'timestamp' => gmdate('c'),
], JSON_UNESCAPED_SLASHES);
