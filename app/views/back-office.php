<?php
require __DIR__ . '/nav/header.php';
require_once __DIR__ . '/../controllers/authController.php';

// TODO: DEV ONLY - à supprimer avant livraison - donne le role admin pour tester l'accès au back-office 
checkRole(3);
?>

<main class="container">
    <h1>Back-Office</h1>
    <p>Page de back-office - en construction</p>
</main>

<?php
require __DIR__ . '/nav/footer.php';