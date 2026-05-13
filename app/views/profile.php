<?php require_once __DIR__ . '/../views/nav/header.php'; ?>

<main>
    <h1>Mon profil</h1>
    <p>Bienvenue <?php echo $_SESSION['pseudo']; ?></p>
</main>

<?php require_once __DIR__ . '/../views/nav/footer.php'; ?>