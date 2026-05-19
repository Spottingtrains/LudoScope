<?php
require __DIR__ . '/nav/header.php';
?>
<main class="container py-4">
    <h1>Demande de suppression</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form id="jeu-delete-form" method="POST" action="index.php?url=jeu/delete&id=<?= (int)$jeu['id_jeu'] ?>" class="row g-3" enctype="multipart/form-data">
        <div class="col-12">
            <label for="titre" class="form-label">Titre du jeu</label>
            <input type="text" id="titre" name="titre" class="form-control" required value="<?= htmlspecialchars($old['titre'] ?? '') ?>">
        </div>
        <div>
            <label for="motif" class="form-label">Motif de la demande</label>
            <textarea id="motif" name="motif" class="form-control" rows="4" required><?= htmlspecialchars($old['motif'] ?? '') ?></textarea>
        </div>
        <div class="col-12">
            <p>Êtes-vous sûr de vouloir supprimer le jeu <strong><?= htmlspecialchars($jeu['titre']) ?></strong> ?</p>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-danger">Supprimer</button>
            <a href="index.php?url=jeu" class="btn btn-secondary">Annuler</a>
        </div>