<?php require __DIR__ . '/nav/header.php'; ?>

<main class="container py-5">
    <h1 class="mb-4">Réinitialiser mon mot de passe</h1>

    <!-- Message d'erreur (token invalide ou mots de passe non conformes) -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- ===== Formulaire de réinitialisation (token transmis en paramètre GET) ===== -->
    <form action="index.php?url=reset-password&token=<?= htmlspecialchars($token) ?>" method="post" class="row g-3">
        <div class="col-md-6">
            <label for="mot_de_passe" class="form-label">Nouveau mot de passe</label>
            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
        </div>
        <div class="col-md-6">
            <label for="mot_de_passe_confirm" class="form-label">Confirmer le mot de passe</label>
            <input type="password" class="form-control" id="mot_de_passe_confirm" name="mot_de_passe_confirm" required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-dark">Réinitialiser</button>
        </div>
    </form>
    <!-- ===== Fin formulaire de réinitialisation ===== -->
</main>

<?php require __DIR__ . '/nav/footer.php'; ?>