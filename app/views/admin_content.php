<?php require __DIR__ . '/nav/header.php'; ?>
<main class="small-padding">
    <div class="up-down-padding">
        <h1 class="centered">Gestion du contenu</h1>
        <nav class="tab-nav">
            <?php $cur = $_GET['url'] ?? ''; ?>
            <a class="tab-link <?= $cur === 'back-office' ? 'active' : '' ?>" href="index.php?url=back-office">Dashboard</a>
            <a class="tab-link <?= $cur === 'admin_users' ? 'active' : '' ?>" href="index.php?url=admin_users">Gestion utilisateurs</a>
            <a class="tab-link <?= $cur === 'admin_content' ? 'active' : '' ?>" href="index.php?url=admin_content">Gestion contenu</a>
        </nav>

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
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Ajouté par</th>
                            <th>Date ajout</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jeux as $jeu): ?>
                        <tr>
                            <td><?= (int)$jeu['id_jeu'] ?></td>
                            <td><?= htmlspecialchars($jeu['titre']) ?></td>
                            <td><?= htmlspecialchars($jeu['pseudo_createur'] ?? 'Anonyme') ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($jeu['date_ajout'] ?? $jeu['date'] ?? 'now'))) ?></td>
                            <td>
                                <a href="index.php?url=admin_content/edit_game&id=<?= (int)$jeu['id_jeu'] ?>" class="btn btn-sm btn-primary">Modifier</a>
                                <form method="post" action="index.php?url=admin_content_delete_game" class="d-inline ms-2 admin-delete-form" data-item-type="jeu">
                                    <input type="hidden" name="id" value="<?= (int)$jeu['id_jeu'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
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
            <div class="table-wrapper">
                <table class="table">
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
                                    <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                                </form>
                                <form method="post" action="index.php?url=admin_content_delete_avis" class="d-inline ms-2 admin-delete-form" data-item-type="avis">
                                    <input type="hidden" name="id" value="<?= (int)$a['id_avis'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
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
    </div>
</main>
<?php require __DIR__ . '/nav/footer.php'; ?>
