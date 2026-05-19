<?php
require __DIR__ . '/nav/header.php';
$editeurs = $editeurs ?? [];
$categories = $categories ?? [];
?>
<main class="container py-4">
    <h1>Modifier un jeu</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($jeu)): ?>
        <form id="jeu-edit-form" method="POST" action="index.php?url=jeu/edit&id=<?= (int)$jeu['id_jeu'] ?>" class="row g-3" enctype="multipart/form-data">
            <div class="col-12">
                <div class="alert alert-secondary mb-0">
                    Vous modifiez un jeu publié le <?= htmlspecialchars(date('d/m/Y', strtotime($jeu['date_ajout']))) ?>.
                </div>
            </div>

            <div class="col-12">
                <label for="image" class="form-label">Image de couverture</label>
                <?php if (!empty($jeu['image'])): ?>
                    <div class="mb-3">
                        <img src="/uploads/<?= htmlspecialchars($jeu['image']) ?>" alt="<?= htmlspecialchars($jeu['titre']) ?>" class="img-thumbnail" style="max-width: 220px;">
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*" class="form-control">
            </div>

            <div class="col-12">
                <label for="titre" class="form-label">Titre du jeu</label>
                <input type="text" id="titre" name="titre" class="form-control" required value="<?= htmlspecialchars($old['titre'] ?? '') ?>">
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description du jeu</label>
                <textarea id="description" name="description" class="form-control" rows="4" required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            </div>

            <div class="col-md-4">
                <label for="nom_editeur" class="form-label">Éditeur</label>
                <input type="text" id="nom_editeur" name="nom_editeur" class="form-control" list="editeurs-list" autocomplete="off" value="<?= htmlspecialchars($old['nom_editeur'] ?? '') ?>">
                <datalist id="editeurs-list">
                    <?php foreach ($editeurs as $editeur): ?>
                        <option value="<?= htmlspecialchars($editeur['nom_editeur']) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="col-md-4">
                <label for="nom_auteur" class="form-label">Auteur</label>
                <input type="text" id="nom_auteur" name="nom_auteur" class="form-control" value="<?= htmlspecialchars($old['nom_auteur'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label for="nom_illustrateur" class="form-label">Illustrateur</label>
                <input type="text" id="nom_illustrateur" name="nom_illustrateur" class="form-control" value="<?= htmlspecialchars($old['nom_illustrateur'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label for="annee_edition" class="form-label">Année d'édition</label>
                <input type="number" id="annee_edition" name="annee_edition" class="form-control" min="1900" max="<?= date('Y') ?>" value="<?= htmlspecialchars($old['annee_edition'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label for="duree_partie" class="form-label">Durée d'une partie (minutes)</label>
                <input type="number" id="duree_partie" name="duree_partie" class="form-control" min="1" value="<?= htmlspecialchars($old['duree_partie'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label for="age_min" class="form-label">Âge minimum</label>
                <input type="number" id="age_min" name="age_min" class="form-control" min="0" required value="<?= htmlspecialchars($old['age_min'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label for="complexite" class="form-label">Complexité</label>
                <select id="complexite" name="complexite" class="form-select" required>
                    <option value="" disabled <?= empty($old['complexite']) ? 'selected' : '' ?>>-- Choisir --</option>
                    <option value="facile" <?= (isset($old['complexite']) && $old['complexite'] === 'facile') ? 'selected' : '' ?>>Facile</option>
                    <option value="intermediaire" <?= (isset($old['complexite']) && $old['complexite'] === 'intermediaire') ? 'selected' : '' ?>>Intermédiaire</option>
                    <option value="expert" <?= (isset($old['complexite']) && $old['complexite'] === 'expert') ? 'selected' : '' ?>>Expert</option>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Catégorie(s)</label>
                <div class="d-flex flex-wrap gap-3">
                    <?php $oldCats = $old['categories'] ?? []; ?>
                    <?php foreach ($categories as $valeur => $label): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cat_<?= $valeur ?>" name="categories[]" value="<?= $valeur ?>" <?= in_array($valeur, $oldCats, true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="cat_<?= $valeur ?>"><?= $label ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-md-3">
                <label for="nb_joueurs_min" class="form-label">Joueurs min</label>
                <input type="number" id="nb_joueurs_min" name="nb_joueurs_min" class="form-control" min="1" max="20" required value="<?= htmlspecialchars($old['nb_joueurs_min'] ?? 2) ?>">
            </div>
            <div class="col-md-3">
                <label for="nb_joueurs_max" class="form-label">Joueurs max</label>
                <input type="number" id="nb_joueurs_max" name="nb_joueurs_max" class="form-control" min="1" max="20" required value="<?= htmlspecialchars($old['nb_joueurs_max'] ?? 4) ?>">
            </div>

            <div class="col-12">
                <label for="modification_explanation" class="form-label">Expliquer les modifications</label>
                <textarea id="modification_explanation" name="modification_explanation" class="form-control" rows="5" required placeholder="Décrivez ce que vous avez changé et pourquoi."><?= htmlspecialchars($old['modification_explanation'] ?? '') ?></textarea>
            </div>

            <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-dark">Soumettre les modifications</button>
                <a href="index.php?url=jeu&slug=<?= rawurlencode(slugify($jeu['titre'])) ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/nav/footer.php'; ?>