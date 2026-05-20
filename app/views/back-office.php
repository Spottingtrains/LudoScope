<?php
require __DIR__ . '/nav/header.php';
require_once __DIR__ . '/../../app/middleware/auth.php';
?>

<main class="container">
    <h1>Back-Office</h1>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <section class="mb-5">
        <h2>Demandes en attente</h2>
        <?php if (!empty($demandes)): ?>
            <div class="d-grid gap-3">
                <?php foreach ($demandes as $demande): ?>
                    <?php $payload = json_decode($demande['message'], true); ?>
                    <?php $isSuppression = ($demande['type_demande'] ?? '') === 'suppression'; ?>
                    <article class="card shadow-sm request-card">
                        <div class="request-card-bar <?= $isSuppression ? 'request-card-bar--suppression' : 'request-card-bar--modification' ?>"></div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                <div>
                                    <h3 class="h5 mb-1"><?= htmlspecialchars($demande['jeu_titre'] ?? 'Jeu inconnu') ?></h3>
                                    <div class="text-muted small">
                                        Par <?= htmlspecialchars($demande['utilisateur_pseudo'] ?? 'Utilisateur supprimé') ?> le <?= htmlspecialchars(date('d/m/Y H:i', strtotime($demande['date_demande']))) ?>
                                    </div>
                                </div>
                                <span class="badge bg-warning text-dark"><?= $isSuppression ? 'Suppression en attente' : 'Modification en attente' ?></span>
                            </div>

                            <p class="mb-2"><strong>Motif :</strong> <?= htmlspecialchars($payload['motif'] ?? $demande['message']) ?></p>

                            <?php if (!$isSuppression && !empty($payload['proposed_changes']) && is_array($payload['proposed_changes'])): ?>
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm align-middle mb-0">
                                        <tbody>
                                            <?php foreach ($payload['proposed_changes'] as $key => $value): ?>
                                                <?php if ($key === 'categories' && is_array($value)): ?>
                                                    <tr>
                                                        <th scope="row">catégories</th>
                                                        <td><?= htmlspecialchars(implode(', ', $value)) ?></td>
                                                    </tr>
                                                <?php elseif ($key === 'image'): ?>
                                                    <tr>
                                                        <th scope="row">image</th>
                                                        <td><?= !empty($value) ? htmlspecialchars($value) : 'Aucune nouvelle image' ?></td>
                                                    </tr>
                                                <?php else: ?>
                                                    <tr>
                                                        <th scope="row"><?= htmlspecialchars($key) ?></th>
                                                        <td><?= htmlspecialchars((string)$value) ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>

                            <form method="post" class="row g-2">
                                <input type="hidden" name="id_demande" value="<?= (int)$demande['id_demande'] ?>">
                                <div class="col-12">
                                    <label class="form-label" for="reponse_admin_<?= (int)$demande['id_demande'] ?>">Réponse admin</label>
                                    <textarea class="form-control" id="reponse_admin_<?= (int)$demande['id_demande'] ?>" name="reponse_admin" rows="2" placeholder="Optionnel"></textarea>
                                </div>
                                <?php if ($isSuppression): ?>
                                    <div class="col-12 form-check">
                                        <input class="form-check-input" type="checkbox" id="confirm_admin_delete_<?= (int)$demande['id_demande'] ?>" name="confirm_admin_delete" value="1">
                                        <label class="form-check-label" for="confirm_admin_delete_<?= (int)$demande['id_demande'] ?>">Je confirme la suppression définitive de ce jeu.</label>
                                    </div>
                                <?php endif; ?>
                                <div class="col-12 d-flex flex-wrap gap-2">
                                    <button type="submit" name="decision" value="accepter" class="btn btn-success"><?= $isSuppression ? 'Accepter et supprimer' : 'Accepter et appliquer' ?></button>
                                    <button type="submit" name="decision" value="refuser" class="btn btn-outline-danger">Refuser</button>
                                </div>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-secondary mb-0">Aucune demande en attente.</div>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/nav/footer.php'; ?>