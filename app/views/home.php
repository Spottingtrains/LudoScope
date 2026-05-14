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
    <!-- message de déconnexion -->
    <?php if (isset($_GET['logout'])): ?>
        <div class="alert alert-success">Vous avez été déconnecté.</div>
    <?php endif; ?>
    <!-- Section hero et CTA -->
    <!-- TODO: vérifier les classes Bootstrap et l'intérêt des div -->
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
                        <a href="#catalogue" class="btn btn-primary">
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
            <p>Nombre de jeux : <?php echo $stats['nb_jeux'] ?? 'N/A' ?></p> <!-- Affiche le nombre de jeux ou 'N/A' si la variable n'est pas définie -->
            <p>Nombre d'utilisateurs : <?php echo $stats['nb_utilisateurs'] ?? 'N/A' ?></p> <!-- Affiche le nombre d'utilisateurs ou 'N/A' si la variable n'est pas définie -->
            <p>Nombre d'avis : <?php echo $stats['nb_avis'] ?? 'N/A' ?></p> <!-- Affiche le nombre d'avis ou 'N/A' si la variable n'est pas définie -->
        </div>
    </section>
    <!-- Section derniers jeux ajoutés -->
    <section>
        <div>
            <h2>Derniers jeux ajoutés</h2>
            <?php if (!empty($jeux)): ?>
                <ul>
                    <?php foreach ($jeux as $jeu): ?>
                        <li><?= htmlspecialchars($jeu['titre']) ?> - Ajouté le <?= htmlspecialchars($jeu['date_ajout']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Aucun jeu à afficher.</p>
            <?php endif; ?>
        </div>
    </section>
    <!-- Section catalogue -->
    <!-- TODO: ajouter pagination -->
    <section id="catalogue">
        <div>
            <h2>Découvrez notre catalogue</h2>
            <p>Explorez notre collection et trouvez le jeu parfait pour votre prochaine soirée entre amis ou en famille.</p>
            <!-- Grille de jeux -->
            <div class="custom-grid">
            <?php if (!empty($jeux)): ?>
            <?php foreach ($jeux as $jeu): ?>
            <div class="custom-card">
                <img src="/uploads/<?= htmlspecialchars(!empty($jeu['image']) ? $jeu['image'] : 'default.jpg') ?>" alt="<?= htmlspecialchars($jeu['titre']) ?>">
                <div class="card-body">
                    <div>
                        <h3><?= htmlspecialchars($jeu['titre']) ?></h3>
                        <span><?= $jeu['note_moyenne'] ? $jeu['note_moyenne'] . '/10' : 'Non noté' ?></span>
                    </div>
                    <h4><?= $jeu['annee_edition'] ?? 'N/A' ?> • <?= htmlspecialchars($jeu['nom_editeur'] ?? 'Éditeur inconnu') ?></h4>
                    <p><?= htmlspecialchars($jeu['complexite']) ?> • <?= $jeu['duree_partie'] ?> min</p>
                    <p><?= $jeu['nb_joueurs_min'] ?>–<?= $jeu['nb_joueurs_max'] ?> joueurs • <?= $jeu['age_min'] ?> ans+</p>
                    <div class="btn-container">
                        <a href="index.php?url=jeu&id=<?= $jeu['id_jeu'] ?>" class="btn btn-primary">Lire les avis</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>    
            <?php else: ?>
                <p>Aucun jeu à afficher.</p>
            <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php
require __DIR__ . '/nav/footer.php';
?>