<?php
require __DIR__ . '/../views/nav/header.php';
$activeTab = $activeTab ?? ($_GET['tab'] ?? 'informations');
?>

<main class="small-padding">
    <div class="up-down-padding">

        <!-- ===== En-tête du profil + onglets de navigation ===== -->
        <section id="profile">
            <div class="text-center">
                <h1>Mon profil</h1>
                <p>Bienvenue <strong><?= htmlspecialchars($_SESSION['pseudo'] ?? '') ?></strong> !</p>
                <p>Vous n'êtes pas <?= htmlspecialchars($_SESSION['pseudo'] ?? '') ?> ? <a class="custom-link" href="index.php?url=logout">Me déconnecter</a></p>
            </div>
            <nav class="tab-nav">
                <?php $cur = $_GET['tab'] ?? 'informations'; ?>
                <a class="tab-link <?= $cur === 'informations' ? 'active' : '' ?>" href="index.php?url=profile&tab=informations">Modifier votre profil</a>
                <a class="tab-link <?= $cur === 'favoris'      ? 'active' : '' ?>" href="index.php?url=profile&tab=favoris">Vos favoris</a>
                <a class="tab-link <?= $cur === 'mes-jeux'     ? 'active' : '' ?>" href="index.php?url=profile&tab=mes-jeux">Liste des jeux ajoutés</a>
                <a class="tab-link <?= $cur === 'mes-avis'     ? 'active' : '' ?>" href="index.php?url=profile&tab=mes-avis">Liste des avis ajoutés</a>
            </nav>
        </section>

        <!-- Messages flash -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>


        <?php if ($activeTab === 'informations'): ?>
        <!-- ===== Onglet : informations personnelles ===== -->
        <section id="informations">
            <h2>Mes informations</h2>

            <!-- Formulaire de mise à jour du profil -->
            <form id="profile-form" action="" method="post" enctype="multipart/form-data" class="row g-3">

                <!-- Zone d'erreur client-side (alimentée par profileHandler en JS) -->
                <div>
                    <div id="client-error" class="alert alert-danger d-none" role="alert" style="display:none;"></div>
                </div>

                <!-- Photo de profil -->
                <div class="centered">
                    <img src="<?= htmlspecialchars(BASE_URL . ltrim($user['photo_profil'] ?? '/uploads/default-profile.webp', '/')) ?>" alt="image de profil" class="img-thumbnail mb-3">
                </div>
                <div class="col-12 mb-3">
                    <label for="image_profil" class="form-label">Modifier l'image de profil :</label>
                    <input type="file" id="image_profil" name="image_profil" accept="image/*" class="form-control">
                </div>

                <!-- Identité -->
                <div class="col-4">
                    <label for="prenom" class="form-label">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>">
                </div>
                <div class="col-4">
                    <label for="nom" class="form-label">Nom :</label>
                    <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom'] ?? '') ?>">
                </div>
                <div class="col-4">
                    <label for="pseudo" class="form-label">Pseudo :</label>
                    <input type="text" id="pseudo" name="pseudo" class="form-control" value="<?= htmlspecialchars($user['pseudo'] ?? '') ?>">
                </div>

                <!-- Email -->
                <div class="col-12">
                    <label for="email" class="form-label">Email :</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" aria-describedby="emailHelp">
                    <div id="emailHelp" class="form-text">Format attendu : nom@domaine.tld</div>
                </div>

                <!-- Mot de passe (laisser vide pour ne pas changer) -->
                <div class="col-6">
                    <label for="new_password" class="form-label">Nouveau mot de passe :</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Laissez vide pour ne pas changer" aria-describedby="passwordHelp">
                    <div id="passwordHelp" class="form-text">Doit contenir au moins 8 caractères, une majuscule et un chiffre</div>
                </div>
                <div class="col-6">
                    <label for="confirm_password" class="form-label">Confirmer le mot de passe :</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Laissez vide pour ne pas changer">
                </div>

                <!-- Question et réponse secrètes -->
                <div class="col-12">
                    <label for="question_secrete" class="form-label">Question secrète :</label>
                    <select class="form-select" id="question_secrete" name="question_secrete">
                        <option value="" disabled <?= empty($user['question_secrete']) ? 'selected' : '' ?>>-- Choisissez une question --</option>
                        <option value="Quel est le prénom de votre mère ?"                          <?= ($user['question_secrete'] ?? '') === "Quel est le prénom de votre mère ?"                          ? 'selected' : '' ?>>Quel est le prénom de votre mère ?</option>
                        <option value="Quel est le nom de votre premier animal ?"                   <?= ($user['question_secrete'] ?? '') === "Quel est le nom de votre premier animal ?"                   ? 'selected' : '' ?>>Quel est le nom de votre premier animal ?</option>
                        <option value="Dans quelle ville êtes-vous né(e) ?"                         <?= ($user['question_secrete'] ?? '') === "Dans quelle ville êtes-vous né(e) ?"                         ? 'selected' : '' ?>>Dans quelle ville êtes-vous né(e) ?</option>
                        <option value="Quel est le titre de votre film préféré ?"                   <?= ($user['question_secrete'] ?? '') === "Quel est le titre de votre film préféré ?"                   ? 'selected' : '' ?>>Quel est le titre de votre film préféré ?</option>
                        <option value="Quel est le prénom de votre meilleur(e) ami(e) d'enfance ?"  <?= ($user['question_secrete'] ?? '') === "Quel est le prénom de votre meilleur(e) ami(e) d'enfance ?"  ? 'selected' : '' ?>>Quel est le prénom de votre meilleur(e) ami(e) d'enfance ?</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="reponse_secrete" class="form-label">Réponse secrète :</label>
                    <div class="input-group">
                        <input type="password" id="reponse_secrete" name="reponse_secrete" class="form-control" value="<?= htmlspecialchars($user['reponse_secrete'] ?? '') ?>" aria-describedby="reponseSecreteHelp">
                        <button type="button" class="btn btn-outline-primary" id="toggle-reponse">Afficher</button>
                    </div>
                    <div id="reponseSecreteHelp" class="form-text">Laissez vide pour conserver la réponse actuelle.</div>
                </div>

                <!-- Boutons (désactivés tant qu'aucune modification n'est détectée) -->
                <div class="btn-container">
                    <button type="submit" id="submitBtn" class="btn btn-primary" disabled>Modifier mes informations</button>
                    <button type="button" id="cancelBtn" class="btn btn-secondary" onclick="window.location.href='index.php?url=profile'" disabled>Annuler les modifications</button>
                </div>
            </form>

            <!-- Suppression du compte -->
            <div class="btn-container">
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                    Supprimer mon compte définitivement
                </button>
            </div>

            <!-- Modale de confirmation de suppression du compte -->
            <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteAccountModalLabel">Confirmer la suppression du compte</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body">
                            Cette action supprimera définitivement votre compte. Vos jeux seront réattribués à un administrateur et vos avis seront anonymisés, mais toutes vos autres données seront perdues. Êtes-vous sûr de vouloir continuer ?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                            <form action="index.php?url=profile" method="post" class="m-0">
                                <input type="hidden" name="action" value="delete_account">
                                <input type="hidden" name="confirm_delete" value="1">
                                <button type="submit" class="btn btn-danger btn-sm">Confirmer la suppression</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ===== Fin onglet informations ===== -->


        <?php elseif ($activeTab === 'favoris'): ?>
        <!-- ===== Onglet : favoris (à implémenter) ===== -->
        <section id="favoris">
            <h2>Mes jeux favoris</h2>
            <!-- TODO: Implémenter la section des jeux favoris -->
            <p>Liste de vos jeux favoris - en construction</p>
        </section>


        <?php elseif ($activeTab === 'mes-jeux'): ?>
        <!-- ===== Onglet : jeux ajoutés ===== -->
        <section id="historique">
            <h2>Mon historique de jeux ajoutés</h2>
            <p>Retrouvez ici les jeux que vous avez ajoutés dans le catalogue.</p>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Date d'ajout</th>
                            <th>Nombre de commentaires</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($addedGames)): ?>
                            <?php foreach ($addedGames as $game): ?>
                                <tr>
                                    <td><?= htmlspecialchars($game['titre']) ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($game['date_ajout']))) ?></td>
                                    <td><?= (int)$game['nb_commentaires'] ?></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="index.php?url=jeu&slug=<?= rawurlencode(slugify($game['titre'])) ?>" class="btn btn-sm btn-primary">Voir</a>
                                            <a href="index.php?url=jeu/edit&id=<?= (int)$game['id_jeu'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
                                            <a href="index.php?url=jeu/delete&id=<?= (int)$game['id_jeu'] ?>" class="btn btn-sm btn-danger">Supprimer</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">Vous n'avez ajouté aucun jeu.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <!-- ===== Fin onglet jeux ajoutés ===== -->


        <?php elseif ($activeTab === 'mes-avis'): ?>
        <!-- ===== Onglet : avis laissés ===== -->
        <section id="avis">
            <h2>Mes avis laissés</h2>
            <p>Retrouvez ici vos derniers avis publiés sur les jeux du catalogue.</p>
            <?php if (!empty($addedReviews)): ?>
                <div class="row row-cols-1 row-cols-lg-2 g-4">
                    <?php foreach ($addedReviews as $review): ?>
                        <div class="col">
                            <article class="card h-100 shadow-sm review-card" data-review-id="<?= (int)$review['id_avis'] ?>">
                                <div class="card-body d-flex flex-column gap-3">

                                    <!-- En-tête : titre du jeu, date, note -->
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <h3 class="h5 mb-1"><?= htmlspecialchars($review['titre']) ?></h3>
                                            <small class="text-muted">Publié le <?= htmlspecialchars(date('d/m/Y', strtotime($review['date_avis']))) ?></small>
                                        </div>
                                        <span class="badge bg-primary fs-6 review-note"><?= htmlspecialchars($review['note']) ?>/10</span>
                                    </div>

                                    <!-- Formulaire d'édition inline (géré par profileReviewsHandler) -->
                                    <form class="review-edit-form w-100" action="index.php?url=profile" method="post">
                                        <input type="hidden" name="action" value="edit_review">
                                        <input type="hidden" name="id" value="<?= (int)$review['id_avis'] ?>">
                                        <textarea name="commentaire" class="form-control review-textarea mb-2" rows="4" disabled><?= htmlspecialchars($review['commentaire']) ?></textarea>
                                        <div class="d-flex align-items-center gap-2">
                                            <select name="note" class="form-select form-select-sm review-note-select w-auto" disabled>
                                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                                    <option value="<?= $i ?>" <?= (int)$review['note'] === $i ? 'selected' : '' ?>><?= $i ?>/10</option>
                                                <?php endfor; ?>
                                            </select>
                                            <div class="ms-auto d-flex gap-2">
                                                <a href="index.php?url=jeu&slug=<?= rawurlencode(slugify($review['titre'])) ?>" class="btn btn-sm btn-primary">Voir le jeu</a>
                                                <button type="button" class="btn btn-sm btn-secondary btn-edit-review">Modifier</button>
                                                <button type="submit" class="btn btn-sm btn-outline-primary btn-save-review d-none">Enregistrer</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary btn-cancel-edit d-none">Annuler</button>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteReviewModal<?= (int)$review['id_avis'] ?>">Supprimer</button>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Modale de confirmation de suppression de l'avis -->
                                    <div class="modal fade" id="deleteReviewModal<?= (int)$review['id_avis'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirmer la suppression</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Voulez-vous vraiment supprimer cet avis publié le <?= htmlspecialchars(date('d/m/Y', strtotime($review['date_avis']))) ?> ? Cette action est définitive.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                                                    <form action="index.php?url=profile" method="post" class="m-0">
                                                        <input type="hidden" name="action" value="delete_review">
                                                        <input type="hidden" name="id" value="<?= (int)$review['id_avis'] ?>">
                                                        <input type="hidden" name="confirm_delete" value="1">
                                                        <button type="submit" class="btn btn-danger btn-sm">Confirmer</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-secondary mb-0">Vous n'avez ajouté aucun avis.</div>
            <?php endif; ?>
        </section>
        <!-- ===== Fin onglet avis ===== -->
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../views/nav/footer.php'; ?>