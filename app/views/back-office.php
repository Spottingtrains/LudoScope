<?php
require __DIR__ . '/nav/header.php';
require_once __DIR__ . '/../../app/middleware/auth.php';

// Seule les admins (id_role >= 3) peuvent accéder au back-office
checkRole(3);
?>

<main class="container">
    <h1>Back-Office</h1>
    <p>Page de back-office - en construction</p>
</main>

<?php require __DIR__ . '/nav/footer.php'; ?>