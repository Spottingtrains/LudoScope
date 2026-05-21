<?php require __DIR__ . '/nav/header.php'; ?>
<main class="container my-5">
    <nav class="nav nav-pills mb-4">
        <?php $cur = $_GET['url'] ?? ''; ?>
        <a class="nav-link <?= $cur === 'back-office' ? 'active' : '' ?>" href="index.php?url=back-office">Dashboard</a>
        <a class="nav-link <?= $cur === 'admin_users' ? 'active' : '' ?>" href="index.php?url=admin_users">Gestion utilisateurs</a>
        <a class="nav-link <?= $cur === 'admin_content' ? 'active' : '' ?>" href="index.php?url=admin_content">Gestion contenu</a>
    </nav>
    <h1>Gestion du contenu</h1>

        <!-- Modal de confirmation admin -->
        <div class="modal fade" id="adminConfirmModal" tabindex="-1" aria-labelledby="adminConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminConfirmModalLabel">Confirmer la suppression</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="adminConfirmModalBody">Voulez-vous vraiment supprimer cet élément ? Cette action est définitive.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" id="adminConfirmModalConfirm" class="btn btn-danger">Supprimer</button>
                    </div>
                </div>
            </div>
        </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <section class="mb-5">
        <h2>Tous les jeux</h2>
        <?php if (!empty($jeux)): ?>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jeux as $jeu): ?>
                    <tr>
                        <td><?= (int)$jeu['id_jeu'] ?></td>
                        <td>
                            <form method="post" action="index.php?url=admin_content_update_game" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="id_jeu" value="<?= (int)$jeu['id_jeu'] ?>">
                                <input type="text" name="titre" class="form-control form-control-sm" value="<?= htmlspecialchars($jeu['titre']) ?>">
                        </td>
                        <td><input type="text" name="auteur" class="form-control form-control-sm" value="<?= htmlspecialchars($jeu['auteur'] ?? '') ?>"></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($jeu['date_ajout'] ?? $jeu['date'] ?? 'now'))) ?></td>
                        <td>
                                <button type="submit" class="btn btn-sm btn-success">Enregistrer</button>
                            </form>
                            <form method="post" action="index.php?url=admin_content_delete_game" class="d-inline ms-2 admin-delete-form" data-item-type="jeu">
                                <input type="hidden" name="id" value="<?= (int)$jeu['id_jeu'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger ms-2">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-secondary">Aucun jeu trouvé.</div>
        <?php endif; ?>
    </section>

    <section>
        <h2>Tous les avis</h2>
        <?php if (!empty($avis)): ?>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Jeu</th>
                        <th>Auteur</th>
                        <th>Commentaire</th>
                        <th>Note</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($avis as $a): ?>
                    <tr>
                        <td><?= (int)$a['id_avis'] ?></td>
                        <td><?= htmlspecialchars($a['titre'] ?? '') ?></td>
                        <td><?= htmlspecialchars($a['pseudo'] ?? 'Anonyme') ?></td>
                        <td style="width:40%">
                            <form method="post" action="index.php?url=admin_content_update_avis">
                                <input type="hidden" name="id_avis" value="<?= (int)$a['id_avis'] ?>">
                                <textarea name="commentaire" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($a['commentaire']) ?></textarea>
                        </td>
                        <td>
                            <?= htmlspecialchars((string)(int)($a['note'] ?? 0)) ?>
                        </td>
                        <td>
                                <button type="submit" class="btn btn-sm btn-success">Enregistrer</button>
                            </form>
                            <form method="post" action="index.php?url=admin_content_delete_avis" class="d-inline ms-2 admin-delete-form" data-item-type="avis">
                                <input type="hidden" name="id" value="<?= (int)$a['id_avis'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger ms-2">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-secondary">Aucun avis trouvé.</div>
        <?php endif; ?>
    </section>

    
</main>
<?php require __DIR__ . '/nav/footer.php'; ?>
