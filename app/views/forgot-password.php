<?php require __DIR__ . '/nav/header.php'; ?>

<main class="large-padding">
    <div class="up-down-padding">
        <h1 class="centered">Mot de passe oublié</h1>

        <!-- Messages flash -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php $step = $step ?? 1; ?>
        <?php if ($step === 1): ?>
        <!-- ===== Étape 1 : saisie de l'email ===== -->
        <form action="index.php?url=forgot-password" method="post" class="row g-3">
            <input type="hidden" name="step" value="1">
            <div class="col-12">
                <label for="email" class="form-label">Votre adresse email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Continuer</button>
            </div>
        </form>

        <?php elseif ($step === 2): ?>
        <!-- ===== Étape 2 : vérification de la question secrète ===== -->
        <form action="index.php?url=forgot-password" method="post" class="row g-3">
            <input type="hidden" name="step" value="2">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
            <div class="col-12">
                <label for="question" class="form-label">Question secrète</label>
                <select class="form-select" id="question" name="question" required>
                    <option value="" disabled selected>-- Choisissez une question --</option>
                    <option value="Quel est le prénom de votre mère ?">Quel est le prénom de votre mère ?</option>
                    <option value="Quel est le nom de votre premier animal ?">Quel est le nom de votre premier animal ?</option>
                    <option value="Dans quelle ville êtes-vous né(e) ?">Dans quelle ville êtes-vous né(e) ?</option>
                    <option value="Quel est le titre de votre film préféré ?">Quel est le titre de votre film préféré ?</option>
                    <option value="Quel est le prénom de votre meilleur(e) ami(e) d'enfance ?">Quel est le prénom de votre meilleur(e) ami(e) d'enfance ?</option>
                </select>
            </div>
            <div class="col-12">
                <label for="reponse" class="form-label">Votre réponse</label>
                <input type="text" class="form-control" id="reponse" name="reponse" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Vérifier</button>
            </div>
        </form>

        <?php elseif ($step === 3): ?>
        <!-- ===== Étape 3 : saisie du nouveau mot de passe ===== -->
        <form action="index.php?url=forgot-password" method="post" class="row g-3">
            <input type="hidden" name="step" value="3">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
            <div class="col-md-6">
                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required aria-describedby="passwordHelp">
                <div id="passwordHelp" class="form-text">Doit contenir au moins 8 caractères, une majuscule et un chiffre</div>
            </div>
            <div class="col-md-6">
                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Réinitialiser mon mot de passe</button>
            </div>
        </form>
        <?php endif; ?>

        <a href="index.php?url=login" class="custom-link">← Retour à la connexion</a>
    </div>
</main>

<?php require __DIR__ . '/nav/footer.php'; ?>