<?php 
require __DIR__ . '/../views/nav/header.php';
?>

<main class="container py-5">
    <h1>Mon profil</h1>
    <p>Bienvenue <strong><?php echo htmlspecialchars($_SESSION['pseudo'] ?? ''); ?></strong> !</p>
    <p>Vous n'êtes pas <?php echo htmlspecialchars($_SESSION['pseudo'] ?? ''); ?> ? <a href="index.php?url=logout">Me déconnecter</a></p>
    <ul class="nav nav-tabs" id="authTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="#informations">Mes informations</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="#favoris">Mes jeux favoris</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="#historique">Mon historique</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="#avis">Mes avis</a>
        </li>
    </ul>
    <!-- Section pour modifier les informations du profil -->
    <section id="informations">
        <h2>Mes informations</h2>
        <form id="profile-form" action="" method="post" enctype="multipart/form-data" class="row g-3">
            <div>
                <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']);
                endif;
                if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']);
                endif; ?>
                <!-- Conteneur pour erreurs côté client (JS) -->
                <div id="client-error" class="alert alert-danger d-none" role="alert" style="display:none;"></div>
            </div>
            <div>
                <img src="<?php echo htmlspecialchars($user['photo_profil'] ?? '/uploads/default-profile.webp'); ?>" alt="image de profil" class="img-thumbnail mb-3" style="width: 150px; height: 150px; border-radius: 50%;"> <!-- TODO: retirer les styles inline et les mettre dans le CSS -->
            </div>
            <div class="col-12 mb-3">
                <label for="image_profil" class="form-label">Modifier l'image de profil :</label>
                <input type="file" id="image_profil" name="image_profil" accept="image/*" class="form-control">
            </div>
            <div class="col-4">
                <label for="prenom" class="form-label">Prénom :</label>
                <input type="text" id="prenom" name="prenom" class="form-control" value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>">
            </div>
            <div class="col-4">
                <label for="nom" class="form-label">Nom :</label>
                <input type="text" id="nom" name="nom" class="form-control" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>">
            </div>
            <div class="col-4">
                <label for="pseudo" class="form-label">Pseudo :</label>
                <input type="text" id="pseudo" name="pseudo" class="form-control" value="<?php echo htmlspecialchars($user['pseudo'] ?? ''); ?>">
            </div>
            <div class="col-12">
                <label for="email" class="form-label">Email :</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" aria-describedby="emailHelp">
                <div id="emailHelp" class="form-text">Format attendu : nom@domaine.tld</div>
            </div>
            <div class="col-6">
                <label for="new_password" class="form-label">Nouveau mot de passe :</label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Laissez vide pour ne pas changer" aria-describedby="passwordHelp">
                <div id="passwordHelp" class="form-text">Doit contenir au moins 8 caractères, une majuscule et un chiffre</div>
            </div>
            <div class="col-6">
                <label for="confirm_password" class="form-label">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Laissez vide pour ne pas changer" aria-describedby="passwordHelp">
            </div>
            <button type="submit" id="submitBtn" class="btn btn-primary" disabled>Modifier mes informations</button>
            <button type="button" id="cancelBtn" class="btn btn-secondary" onclick="window.location.href='index.php?url=profile'" disabled>Annuler les modifications</button>
        </form>
    </section>

    <!-- Section jeux favoris -->
    <section id="favoris">
        <h2>Mes jeux favoris</h2>
        <p>Liste de vos jeux favoris - en construction</p>
    </section>
    <!-- Section historique de jeux ajoutés -->
    <section id="historique">
        <h2>Mon historique de jeux ajoutés</h2>
        <p>Historique des jeux que vous avez ajoutés - en construction</p>
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
                            <td><?php echo htmlspecialchars($game['titre']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($game['date_ajout']))); ?></td>
                            <td><?php echo (int)$game['nb_commentaires']; ?></td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="index.php?url=jeu&slug=<?php echo rawurlencode(slugify($game['titre'])); ?>" class="btn btn-sm btn-outline-primary">Voir</a>
                                    <a href="index.php?url=jeu/edit&id=<?php echo (int)$game['id_jeu']; ?>" class="btn btn-sm btn-outline-secondary">Modifier</a>
                                    <a href="index.php?url=jeu/delete&id=<?php echo (int)$game['id_jeu']; ?>" class="btn btn-sm btn-outline-danger">Supprimer</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Vous n'avez ajouté aucun jeu.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
    <!-- Section avis laissés -->
    <section id="avis">
        <h2>Mes avis laissés</h2>
        <p>Retrouvez ici vos derniers avis publiés sur les jeux du catalogue.</p>
                <?php if (!empty($addedReviews)): ?>
            <div class="row row-cols-1 row-cols-lg-2 g-4">
                <?php foreach ($addedReviews as $review): ?>
                    <div class="col">
                        <article class="card h-100 shadow-sm review-card" data-review-id="<?php echo (int)$review['id_avis']; ?>">
                            <div class="card-body d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <h3 class="h5 mb-1"><?php echo htmlspecialchars($review['titre']); ?></h3>
                                        <small class="text-muted">
                                            Publié le <?php echo htmlspecialchars(date('d/m/Y', strtotime($review['date_avis']))); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-primary fs-6 review-note">
                                        <?php echo htmlspecialchars($review['note']); ?>/10
                                    </span>
                                </div>
                                <form class="review-edit-form w-100" action="index.php?url=profile" method="post">
                                    <input type="hidden" name="action" value="edit_review">
                                    <input type="hidden" name="id" value="<?php echo (int)$review['id_avis']; ?>">
                                    <textarea name="commentaire" class="form-control review-textarea mb-2" rows="4" disabled><?php echo htmlspecialchars($review['commentaire']); ?></textarea>
                                    <div class="d-flex align-items-center gap-2">
                                        <select name="note" class="form-select form-select-sm review-note-select w-auto" disabled>
                                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php if ((int)$review['note'] === $i) echo 'selected'; ?>><?php echo $i; ?>/10</option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="ms-auto d-flex gap-2">
                                            <a href="index.php?url=jeu&slug=<?php echo rawurlencode(slugify($review['titre'])); ?>" class="btn btn-sm btn-outline-primary">Voir le jeu</a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-review">Modifier</button>
                                            <button type="submit" class="btn btn-sm btn-primary btn-save-review d-none">Enregistrer</button>
                                            <button type="button" class="btn btn-sm btn-secondary btn-cancel-edit d-none">Annuler</button>
                                            <!-- Bouton ouverture modal suppression -->
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteReviewModal<?php echo (int)$review['id_avis']; ?>">Supprimer</button>
                                        </div>
                                    </div>
                                </form>

                                <!-- Modal de confirmation (Bootstrap) -->
                                <div class="modal fade" id="deleteReviewModal<?php echo (int)$review['id_avis']; ?>" tabindex="-1" aria-labelledby="deleteReviewModalLabel<?php echo (int)$review['id_avis']; ?>" aria-hidden="true">
                                  <div class="modal-dialog">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h5 class="modal-title" id="deleteReviewModalLabel<?php echo (int)$review['id_avis']; ?>">Confirmer la suppression</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                      </div>
                                      <div class="modal-body">
                                        Voulez-vous vraiment supprimer cet avis publié le <?php echo htmlspecialchars(date('d/m/Y', strtotime($review['date_avis']))); ?> ? Cette action est définitive.
                                      </div>
                                      <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                                        <form action="index.php?url=profile" method="post" class="m-0">
                                            <input type="hidden" name="action" value="delete_review">
                                            <input type="hidden" name="id" value="<?php echo (int)$review['id_avis']; ?>">
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
            <div class="alert alert-secondary mb-0">
                Vous n'avez ajouté aucun avis.
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../views/nav/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.review-card').forEach(card => {
        const editBtn = card.querySelector('.btn-edit-review');
        const saveBtn = card.querySelector('.btn-save-review');
        const cancelBtn = card.querySelector('.btn-cancel-edit');
        const textarea = card.querySelector('.review-textarea');
        const noteSelect = card.querySelector('.review-note-select');

        editBtn.addEventListener('click', function () {
            textarea.disabled = false;
            noteSelect.disabled = false;
            editBtn.classList.add('d-none');
            saveBtn.classList.remove('d-none');
            cancelBtn.classList.remove('d-none');
            textarea.focus();
        });

        cancelBtn.addEventListener('click', function () {
            // revert values to original by reloading the page (simpler) or disable inputs
            textarea.disabled = true;
            noteSelect.disabled = true;
            editBtn.classList.remove('d-none');
            saveBtn.classList.add('d-none');
            cancelBtn.classList.add('d-none');
            // restore original values from dataset if available
            // (we keep it simple and don't change textarea value here)
        });
        // saveBtn is a normal submit button inside the form; no JS required to submit
    });
});
</script>