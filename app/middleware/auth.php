<?php
function check_role() {
    return isset($_SESSION['id_role']) ? $_SESSION['id_role'] : null;
}

function checkRole($roleMin) {
    if (!isset($_SESSION['id_role']) || $_SESSION['id_role'] < $roleMin) {
        header('Location: index.php?url=login');
        exit();
    }
}