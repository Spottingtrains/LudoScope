<?php
// connexion à la base de données
//* déjà dans index.php

// inclusion de l'en-tête
require __DIR__ . '/nav/header.php';
require_once __DIR__ . '/../../app/middleware/auth.php';
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
    <section id="heroSection">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">

            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3"></button>
            </div>

            <div class="carousel-inner">

                <div class="carousel-item active hero-slide" style="background-image: url('/uploads/hero-1.jpg');">
                    <div class="hero-overlay"></div>
                    <div class="hero-content text-center text-white">
                        <h1 class="display-4 fw-bold">Votre prochaine partie commence ici !</h1>
                        <p class="fs-4 mt-3">Découvrez, évaluez et partagez vos jeux de société préférés</p>
                        <div class="hstack gap-3 justify-content-center flex-wrap mt-4">
                            <a href="#catalogue" class="btn btn-primary btn-lg">Explorer les jeux</a>
                            <?php if (check_role() >= 2): ?>
                                <a href="/jeu/add" class="btn btn-secondary btn-lg">Contribuer au catalogue</a>
                            <?php else: ?>
                                <a href="/login" class="btn btn-secondary btn-lg">Rejoindre la communauté</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="carousel-item hero-slide" style="background-image: url('/uploads/hero-2.jpg');">
                    <div class="hero-overlay"></div>
                    <div class="hero-content text-center text-white">
                        <p class="hero-stat"><?= htmlspecialchars($stats['nb_jeux'] ?? 'N/A') ?></p>
                        <h2 class="fw-bold">jeux dans notre catalogue</h2>
                        <div class="hstack gap-3 justify-content-center flex-wrap mt-4">
                            <a href="#catalogue" class="btn btn-primary btn-lg">Explorer les jeux</a>
                            <?php if (check_role() >= 2): ?>
                                <a href="/jeu/add" class="btn btn-secondary btn-lg">Contribuer au catalogue</a>
                            <?php else: ?>
                                <a href="/login" class="btn btn-secondary btn-lg">Rejoindre la communauté</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="carousel-item hero-slide" style="background-image: url('/uploads/hero-3.jpg');">
                    <div class="hero-overlay"></div>
                    <div class="hero-content text-center text-white">
                        <p class="hero-stat"><?= htmlspecialchars($stats['nb_utilisateurs'] ?? 'N/A') ?></p>
                        <h2 class="fw-bold">joueurs nous ont déjà rejoints</h2>
                        <div class="hstack gap-3 justify-content-center flex-wrap mt-4">
                            <a href="#catalogue" class="btn btn-primary btn-lg">Explorer les jeux</a>
                            <?php if (check_role() >= 2): ?>
                                <a href="/jeu/add" class="btn btn-secondary btn-lg">Contribuer au catalogue</a>
                            <?php else: ?>
                                <a href="/login" class="btn btn-secondary btn-lg">Rejoindre la communauté</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="carousel-item hero-slide" style="background-image: url('/uploads/hero-4.jpg');">
                    <div class="hero-overlay"></div>
                    <div class="hero-content text-center text-white">
                        <p class="hero-stat"><?= htmlspecialchars($stats['nb_avis'] ?? 'N/A') ?></p>
                        <h2 class="fw-bold">avis partagés par la communauté</h2>
                        <div class="hstack gap-3 justify-content-center flex-wrap mt-4">
                            <a href="#catalogue" class="btn btn-primary btn-lg">Explorer les jeux</a>
                            <?php if (check_role() >= 2): ?>
                                <a href="/jeu/add" class="btn btn-secondary btn-lg">Contribuer au catalogue</a>
                            <?php else: ?>
                                <a href="/login" class="btn btn-secondary btn-lg">Rejoindre la communauté</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
                <span class="visually-hidden">Précédent</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
                <span class="visually-hidden">Suivant</span>
            </button>

        </div>
    </section>
    <!-- Section jeux les mieux notés -->
    <section id="bestNote" class="small-padding">
        <div>
            <h2>Les mieux notés</h2>
            <?php if (!empty($bestJeux)): ?>
                <div class="row g-4">
                    <?php foreach ($bestJeux as $jeu): ?>
                        <div class="col-12 col-md-6 col-lg-4 best-card">
                            <div class="img-wrapper">
                                <img src="/uploads/<?= htmlspecialchars(!empty($jeu['image']) ? $jeu['image'] : 'default.jpg') ?>" alt="<?= htmlspecialchars($jeu['titre']) ?>">
                                <span class="card-note"><?= htmlspecialchars((int)($jeu['note_moyenne'] ?? '0') . ' / 10') ?></span>
                            </div>
                            <h3><?= htmlspecialchars($jeu['titre']) ?></h3>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Aucun jeu à afficher.</p>
            <?php endif; ?>
        </div>
    </section>
    <!-- Section catalogue -->
    <!-- TODO: ajouter pagination -->
    <section id="catalogue" class="orange-bg small-padding">
        <h2>Notre catalogue</h2>
        <!-- Barre de recherche -->
        <div class="mb-4">
            <div class="input-group">
                <input id="catalog-search" type="search" class="form-control" placeholder="Entrez le nom du jeu, un mot-clé..." aria-label="Recherche jeux">
                <button id="catalog-search-btn" type="button" class="btn btn-primary">Rechercher</button>
            </div>
        </div>
        <!-- Filtres -->
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
        <!-- Grille des jeux -->
        <div id="catalogue-list" class="custom-grid">
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