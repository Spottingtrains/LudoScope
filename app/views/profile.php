<?php require_once __DIR__ . '/../views/nav/header.php'; ?>

<main>
    <h1>Mon profil</h1>
    <p>Bienvenue <strong><?php echo $_SESSION['pseudo']; ?></strong> !</p>
</main>

<?php require_once __DIR__ . '/../views/nav/footer.php'; ?>