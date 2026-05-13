<?php
function check_role() {
    return isset($_SESSION['id_role']) ? $_SESSION['id_role'] : null;
}

function checkRole($roleMin) {
    if (!isset($_SESSION['id_role']) || $_SESSION['id_role'] < $roleMin) {
        http_response_code(403);
        include __DIR__ . '/../views/404.php';
        exit();
    }
}