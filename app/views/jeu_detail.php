<?php
require __DIR__ . '/nav/header.php';
?>
<main>
<?php if (isset($jeu)) : ?>
    <h1><?= htmlspecialchars($jeu['titre']) ?></h1>

    <p>Éditeur : <?= htmlspecialchars($jeu['nom_editeur'] ?? 'Non renseigné') ?></p>
    <p>Année : <?= htmlspecialchars($jeu['annee_edition'] ?? 'Non renseignée') ?></p>
    <p>Joueurs : <?= htmlspecialchars($jeu['nb_joueurs_min']) ?> - <?= htmlspecialchars($jeu['nb_joueurs_max']) ?></p>
    <p>Durée : <?= htmlspecialchars($jeu['duree_partie']) ?> min</p>
    <p>Description : <?= htmlspecialchars($jeu['description']) ?></p>

    <h2>Catégories</h2>
    <?php if (!empty($categories)) : ?>
        <ul>
            <?php foreach ($categories as $cat) : ?>
                <li><?= htmlspecialchars($cat['nom_categorie']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p>Aucune catégorie associée.</p>
    <?php endif; ?>

    <h2>Avis</h2>

<?php if (!empty($avis)) : ?>
    <?php foreach ($avis as $a) : ?>
        <article>
            <p><strong><?= htmlspecialchars($a['pseudo'] ?? 'Utilisateur supprimé') ?></strong> — <?= htmlspecialchars($a['date_avis']) ?></p>
            <p>Note : <?= htmlspecialchars($a['note']) ?>/5</p>
            <p><?= htmlspecialchars($a['commentaire']) ?></p>
        </article>
    <?php endforeach; ?>
<?php else : ?>
    <p>Aucun avis pour ce jeu.</p>
<?php endif; ?>

    <h2>Laisser un avis</h2>

    <?php if (isset($_SESSION['id_utilisateur'])) : ?>
        <?php if (isset($avisError)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($avisError) ?></div>
        <?php endif; ?>
        <form method="post">
            <div>
                <label for="note">Note (1 à 10)</label>
                <select name="note" id="note" required>
                    <option value="">-- Choisir --</option>
                    <?php for ($i = 1; $i <= 10; $i++) : ?>
                        <option value="<?= $i ?>" <?= (isset($_POST['note']) && (int)$_POST['note'] === $i) ? 'selected' : '' ?>>
                            <?= $i ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label for="commentaire">Commentaire</label>
                <textarea name="commentaire" id="commentaire" rows="4" required><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Publier l'avis</button>
        </form>
    <?php else : ?>
        <p><a href="index.php?url=login">Connectez-vous</a> pour laisser un avis.</p>
    <?php endif; ?>

    <a href="index.php">← Retour à la liste</a>

<?php else : ?>
    <p>Jeu introuvable.</p>
<?php endif; ?>

</main>
<?php
require __DIR__ . '/nav/footer.php';