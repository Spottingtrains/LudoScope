<?php
require __DIR__ . '/nav/header.php';
?>
<main class="container py-5">
    <h1 class="mb-4">Connexion / Inscription</h1>
    <!-- message de succès d'inscription -->
    <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <!-- onglets de navigation -->
    <ul class="nav nav-tabs" id="authTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= ($activeTab ?? 'login') === 'login' ? 'active' : '' ?>" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-pane" type="button" role="tab" aria-controls="login-pane" aria-selected="true">Connexion</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= ($activeTab ?? 'login') === 'signin' ? 'active' : '' ?>" id="signin-tab" data-bs-toggle="tab" data-bs-target="#signin-pane" type="button" role="tab" aria-controls="signin-pane" aria-selected="false">Inscription</button>
        </li>
    </ul>
    <!-- contenu des onglets -->
    <div class="tab-content border border-top-0 p-4 bg-white" id="authTabsContent">
        <div class="tab-pane fade <?= ($activeTab ?? 'login') === 'login' ? 'show active' : '' ?>" id="login-pane" role="tabpanel" aria-labelledby="login-tab" tabindex="0">
            <form action="index.php?url=login" method="post" class="row g-3">
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="col-12">
                    <label for="login-email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="login-email" name="email" required>
                </div>
                <div class="col-12">
                    <label for="login-password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="login-password" name="mot_de_passe" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-dark">Se connecter</button>
                </div>
            </form>
        </div>
        <div class="tab-pane fade <?= ($activeTab ?? 'login') === 'signin' ? 'show active' : '' ?>" id="signin-pane" role="tabpanel" aria-labelledby="signin-tab" tabindex="0">
            <form action="index.php?url=register" method="post" class="row g-3">
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="col-md-4">
                    <label for="signin-firstname" class="form-label">Prénom</label>
                    <input type="text" class="form-control" id="signin-firstname" name="firstname" required>
                </div>
                <div class="col-md-4">
                    <label for="signin-lastname" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="signin-lastname" name="lastname" required>
                </div>
                <div class="col-md-4">
                    <label for="signin-username" class="form-label">Pseudo</label>
                    <input type="text" class="form-control" id="signin-username" name="pseudo" required>
                </div>
                <div class="col-12">
                    <label for="signin-email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="signin-email" name="email" required>
                    <div class="invalid-feedback">
                    Le format de l'adresse email n'est pas valide.
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="signin-password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="signin-password" name="mot_de_passe" required>
                    <div class="invalid-feedback">
                        Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="signin-password-confirm" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" class="form-control" id="signin-password-confirm" name="mot_de_passe_confirm" required>
                    <div class="invalid-feedback">
                    Les mots de passe ne correspondent pas.
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-dark">Créer mon compte</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
require __DIR__ . '/nav/footer.php';