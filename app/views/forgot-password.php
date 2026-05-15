<?php 
require __DIR__ . '/nav/header.php'; 
?>

<main class="container py-5">
    <h1 class="mb-4">Mot de passe oublié</h1>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="index.php?url=forgot-password" method="post" class="row g-3">
        <div class="col-12">
            <label for="email" class="form-label">Votre adresse email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-dark">Envoyer le lien</button>
            <a href="index.php?url=login" class="btn btn-link">Retour à la connexion</a>
        </div>
    </form>
</main>

<?php require __DIR__ . '/nav/footer.php'; ?>