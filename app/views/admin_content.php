<?php require __DIR__ . '/nav/header.php'; ?>
<main class="container my-5">
    <nav class="nav nav-pills mb-4">
        <?php $cur = $_GET['url'] ?? ''; ?>
        <a class="nav-link <?= $cur === 'back-office' ? 'active' : '' ?>" href="index.php?url=back-office">Dashboard</a>
        <a class="nav-link <?= $cur === 'admin_users' ? 'active' : '' ?>" href="index.php?url=admin_users">Gestion utilisateurs</a>
        <a class="nav-link <?= $cur === 'admin_content' ? 'active' : '' ?>" href="index.php?url=admin_content">Gestion contenu</a>
    </nav>
    <h1>Gestion du contenu</h1>
    <p>Page en cours de création.</p>
</main>
<?php require __DIR__ . '/nav/footer.php'; ?>
