<?php
// connexion à la base de données
//* déjà dans index.php

// inclusion de l'en-tête
require __DIR__ . '/nav/header.php';
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/models/jeu.php';
?>

<main>
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
                            echo '<a href="/jeu/add" class="btn btn-secondary">Contribuer à notre catalogue</a>'; // TODO: ajuster le lien vers la page d'ajout de jeu
                        } else { // Si l'utilisateur n'est pas connecté
                            echo '<a href="/login" class="btn btn-secondary">Rejoindre la communauté</a>'; // TODO: ajuster le lien vers la page de connexion/inscription
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
            <p>Nombre de jeux : <?= htmlspecialchars($stats['nb_jeux'] ?? 'N/A') ?></p> <!-- Affiche le nombre de jeux ou 'N/A' si la variable n'est pas définie -->
            <p>Nombre d'utilisateurs : <?= htmlspecialchars($stats['nb_utilisateurs'] ?? 'N/A') ?></p> <!-- Affiche le nombre d'utilisateurs ou 'N/A' si la variable n'est pas définie -->
            <p>Nombre d'avis : <?= htmlspecialchars($stats['nb_avis'] ?? 'N/A') ?></p> <!-- Affiche le nombre d'avis ou 'N/A' si la variable n'est pas définie -->
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
    <div class="mb-4">
        <div class="input-group">
            <input id="catalog-search" type="search" class="form-control" placeholder="Entrez le nom du jeu, un mot-clé..." aria-label="Recherche jeux">
            <button id="catalog-search-btn" type="button" class="btn btn-primary">Rechercher</button>
        </div>
    </div>
    <div class="mb-4 p-3 border rounded bg-body-tertiary" id="catalog-filters">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="filter-players-min" class="form-label">Joueurs min</label>
                <input type="number" id="filter-players-min" class="form-control" min="1" placeholder="Ex. 2">
            </div>
            <div class="col-12 col-md-3">
                <label for="filter-players-max" class="form-label">Joueurs max</label>
                <input type="number" id="filter-players-max" class="form-control" min="1" placeholder="Ex. 6">
            </div>
            <div class="col-12 col-md-3">
                <label for="filter-duration" class="form-label">Durée</label>
                <select id="filter-duration" class="form-select">
                    <option value="">Toutes</option>
                    <option value="quick">Rapide - moins de 30 min</option>
                    <option value="medium">Moyen - 30 à 60 min</option>
                    <option value="long">Long - plus de 60 min</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="filter-complexity" class="form-label">Complexité</label>
                <select id="filter-complexity" class="form-select">
                    <option value="">Toutes</option>
                    <option value="facile">Facile</option>
                    <option value="intermediaire">Intermédiaire</option>
                    <option value="expert">Expert</option>
                </select>
            </div>
            <div class="col-12">
                <span class="form-label d-block mb-2">Catégories</span>
                <div class="d-flex flex-wrap gap-3">
                    <?php foreach ($categories ?? [] as $categorie): ?>
                        <?php $categorieId = 'cat-filter-' . slugify($categorie); ?>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input filter-category" type="checkbox" id="<?= htmlspecialchars($categorieId) ?>" value="<?= htmlspecialchars($categorie) ?>">
                            <label class="form-check-label" for="<?= htmlspecialchars($categorieId) ?>"><?= htmlspecialchars($categorie) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                <button id="apply-filters-btn" type="button" class="btn btn-primary">Appliquer les filtres</button>
                <button id="reset-filters-btn" type="button" class="btn btn-outline-secondary">Réinitialiser les filtres</button>
            </div>
        </div>
    </div>
    <section id="catalogue">
        <div id="catalogue-list" class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (!empty($jeux)): ?>
            <?php foreach ($jeux as $jeu): ?>
                <div class="col">
                    <a href="index.php?url=jeu&slug=<?= rawurlencode(slugify($jeu['titre'])) ?>">
                        <div class="custom-card"
                            data-titre="<?= htmlspecialchars(mb_strtolower($jeu['titre'] ?? '')) ?>"
                            data-description="<?= htmlspecialchars(mb_strtolower($jeu['description'] ?? '')) ?>"
                            data-players-min="<?= htmlspecialchars((string)($jeu['nb_joueurs_min'] ?? '')) ?>"
                            data-players-max="<?= htmlspecialchars((string)($jeu['nb_joueurs_max'] ?? '')) ?>"
                            data-duration="<?= htmlspecialchars((string)($jeu['duree_partie'] ?? '')) ?>"
                            data-complexity="<?= htmlspecialchars(mb_strtolower($jeu['complexite'] ?? '')) ?>"
                            data-categories="<?= htmlspecialchars(mb_strtolower($jeu['categories'] ?? '')) ?>">
                            <img src="/uploads/<?= htmlspecialchars(!empty($jeu['image']) ? $jeu['image'] : 'default.jpg') ?>" alt="<?= htmlspecialchars($jeu['titre']) ?>">
                            <div class="card-body">
                                <div>
                                    <h3><?= htmlspecialchars($jeu['titre']) ?></h3>
                                    <span><?= htmlspecialchars($jeu['note_moyenne'] ? $jeu['note_moyenne'] . '/10' : 'Non noté') ?></span>
                                </div>
                                <p><?= htmlspecialchars($jeu['complexite']) ?> • <?= $jeu['duree_partie'] ?> min</p>
                                <p><?= htmlspecialchars($jeu['nb_joueurs_min']) ?>–<?= htmlspecialchars($jeu['nb_joueurs_max']) ?> joueurs<?php if (!empty($jeu['age_min'])): ?> • <?= htmlspecialchars($jeu['age_min']) ?> ans+<?php endif; ?></p>
                                <div class="btn-container">
                                    <a href="index.php?url=jeu&slug=<?= rawurlencode(slugify($jeu['titre'])) ?>" class="btn btn-primary">Lire les avis</a>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun jeu à afficher.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
require __DIR__ . '/nav/footer.php';
?>