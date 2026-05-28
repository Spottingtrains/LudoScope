<?php require __DIR__ . '/nav/header.php'; ?>

<main class="large-padding">
    <div class="up-down-padding">
        <h1 class="mb-4">Connexion / Inscription</h1>

        <!-- Message flash (ex. après inscription réussie) -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Onglets de navigation : Connexion / Inscription -->
        <nav class="tab-nav">
            <a class="tab-link <?= ($activeTab ?? 'login') === 'login'  ? 'active' : '' ?>" href="index.php?url=login">Connexion</a>
            <a class="tab-link <?= ($activeTab ?? 'login') === 'signin' ? 'active' : '' ?>" href="index.php?url=login&tab=signin">Inscription</a>
        </nav>

        <?php if (($activeTab ?? 'login') === 'login'): ?>
        <!-- ===== Onglet Connexion ===== -->
        <div id="login-pane">
            <form action="index.php?url=login" method="post" class="row g-3">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="col-12">
                    <label for="login-email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="login-email" name="email" required autofocus>
                </div>
                <div class="col-12">
                    <label for="login-password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="login-password" name="mot_de_passe" required>
                </div>
                <div class="btn-container">
                    <button type="submit" class="btn btn-primary">Se connecter</button>
                </div>
            </form>
            <a class="custom-link centered" href="index.php?url=forgot-password">Mot de passe oublié ?</a>
        </div>
        <!-- ===== Fin onglet Connexion ===== -->

        <?php else: ?>
        <!-- ===== Onglet Inscription ===== -->
        <div id="signin-pane">
            <form action="index.php?url=register" method="post" class="row g-3">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <!-- Identité -->
                <div class="col-md-4">
                    <label for="signin-firstname" class="form-label">Prénom</label>
                    <input type="text" class="form-control" id="signin-firstname" name="firstname" required autofocus>
                </div>
                <div class="col-md-4">
                    <label for="signin-lastname" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="signin-lastname" name="lastname" required>
                </div>
                <div class="col-md-4">
                    <label for="signin-username" class="form-label">Pseudo</label>
                    <input type="text" class="form-control" id="signin-username" name="pseudo" required>
                </div>
                <!-- Email -->
                <div class="col-12">
                    <label for="signin-email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="signin-email" name="email" required aria-describedby="signinEmailHelp">
                    <div id="signinEmailHelp" class="form-text">Format attendu : nom@domaine.tld</div>
                    <div class="invalid-feedback">Le format de l'adresse email n'est pas valide.</div>
                </div>
                <!-- Mot de passe -->
                <div class="col-md-6">
                    <label for="signin-password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="signin-password" name="mot_de_passe" required aria-describedby="signinPasswordHelp">
                    <div id="signinPasswordHelp" class="form-text">Doit contenir au moins 8 caractères, une majuscule et un chiffre</div>
                    <div class="invalid-feedback">Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.</div>
                </div>
                <div class="col-md-6">
                    <label for="signin-password-confirm" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" class="form-control" id="signin-password-confirm" name="mot_de_passe_confirm" required>
                    <div class="invalid-feedback">Les mots de passe ne correspondent pas.</div>
                </div>
                <!-- Question secrète (récupération de compte) -->
                <div class="col-12">
                    <label for="signin-question" class="form-label">Question secrète (en cas de perte du mot de passe)</label>
                    <select class="form-select" id="signin-question" name="question_secrete" required>
                        <option value="" disabled selected>-- Choisissez une question --</option>
                        <option value="Quel est le prénom de votre mère ?">Quel est le prénom de votre mère ?</option>
                        <option value="Quel est le nom de votre premier animal ?">Quel est le nom de votre premier animal ?</option>
                        <option value="Dans quelle ville êtes-vous né(e) ?">Dans quelle ville êtes-vous né(e) ?</option>
                        <option value="Quel est le titre de votre film préféré ?">Quel est le titre de votre film préféré ?</option>
                        <option value="Quel est le prénom de votre meilleur(e) ami(e) d'enfance ?">Quel est le prénom de votre meilleur(e) ami(e) d'enfance ?</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="signin-reponse" class="form-label">Réponse secrète</label>
                    <input type="text" class="form-control" id="signin-reponse" name="reponse_secrete" aria-describedby="signinReponseHelp" required>
                    <div id="signinReponseHelp" class="form-text">Cette réponse vous permettra de réinitialiser votre mot de passe en cas d'oubli. Notez-la bien.</div>
                </div>
                <div class="btn-container">
                    <button type="submit" class="btn btn-primary">Créer mon compte</button>
                </div>
            </form>
        </div>
        <!-- ===== Fin onglet Inscription ===== -->
        <?php endif; ?>

    </div>
</main>

<?php require __DIR__ . '/nav/footer.php'; ?>