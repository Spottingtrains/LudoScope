<?php
// fonction pour vérifier le rôle de l'utilisateur dans les pages qui nécessitent une authentification
function checkRole($roleMin) {
    if (!isset($_SESSION['id_role']) || $_SESSION['id_role'] < $roleMin) {
        header('Location: /index.php?url=home');
        exit();
    }
}