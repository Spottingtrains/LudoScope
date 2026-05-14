<?php
// connexion à la base de données
//* déjà dans index.php

// inclusion de l'en-tête
require __DIR__ . '/nav/header.php';
require_once __DIR__ . '/../../app/middleware/auth.php';
?>

<main>
    <?php var_dump($_SESSION); /* TODO: DEV ONLY - à retirer après les tests */ ?>
    <!-- message de bienvenue après connexion -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <!-- Section hero et CTA -->
    <!-- TODO: vérifier les classes Bootstrap -->
    <section class="py-24 ">
        <div class="container mw-screen-xl">
            <div class="bg-body rounded shadow-soft-3 border p-5">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-10 col-lg-8 text-center">
                        <h1 class="ls-tight display-6 ">
                            Votre prochaine partie commence ici !
                        </h1>
                        <h2>
                            Découvrez, évaluez et partagez vos jeux de société préférés
                        </h2>
                        <div class="hstack gap-3 justify-content-center">
                        <a href="#" class="btn btn-primary">
                            Explorer les jeux de notre catalogue
                        </a>
                        <?php
                        if (check_role() >= 2) { // Si l'utilisateur est connecté ou admin
                            echo '<a href="/jeu/add" class="btn btn-secondary">Contribuer à notre catalogue</a>';
                        } else { // Si l'utilisateur n'est pas connecté
                            echo '<a href="/login" class="btn btn-secondary">Rejoindre la communauté</a>';
                        } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Section statistiques du sites -->
    <section>
        <div>
            <h2>Statistiques du site</h2>
            <p>Nombre de jeux : <?= $stats['nb_jeux'] ?></p>
            <p>Nombre d'utilisateurs : <?= $stats['nb_utilisateurs'] ?></p>
            <p>Nombre d'avis : <?= $stats['nb_avis'] ?></p>
        </div>
    </section>
</main>

<?php
require __DIR__ . '/nav/footer.php';
?>