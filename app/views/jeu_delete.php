<?php
require __DIR__ . '/nav/header.php';
?>
<main class="large-padding">
    <section id="jeuEdit" class="up-down-padding">
        <h1>Demande de suppression</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form id="jeu-delete-form" method="POST" action="index.php?url=jeu/delete&id=<?= (int)$jeu['id_jeu'] ?>" class="row g-3">
            <div class="col-12">
                <label for="titre" class="form-label">Titre du jeu</label>
                <input type="text" id="titre" class="form-control" disabled value="<?= htmlspecialchars($jeu['titre'] ?? '') ?>">
            </div>
            <div class="col-12">
                <label for="motif" class="form-label">Motif de la demande</label>
                <textarea id="motif" name="motif" class="form-control" rows="4" required><?= htmlspecialchars($old['motif'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Soumettre la demande</button>
                <a href="index.php?url=profile" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </section>
</main>