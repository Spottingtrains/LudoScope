<?php
require __DIR__ . '/nav/header.php';

// Ensure $jeu is defined to avoid undefined variable errors in the view
$jeu = isset($jeu) && is_array($jeu) ? $jeu : [];
$jeu = array_merge([
    'titre' => 'Jeu inconnu',
    'nom_editeur' => null,
    'annee_edition' => null,
    'nb_joueurs_min' => '?',
    'nb_joueurs_max' => '?',
    'duree_partie' => '?',
    'description' => 'Aucune description disponible',
], $jeu);
?>
<main>

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
                <p><strong><?= htmlspecialchars($a['pseudo']) ?></strong> — <?= htmlspecialchars($a['date_avis']) ?></p>
                <p>Note : <?= htmlspecialchars($a['note']) ?>/5</p>
                <p><?= htmlspecialchars($a['commentaire']) ?></p>
            </article>
        <?php endforeach; ?>
    <?php else : ?>
        <p>Aucun avis pour ce jeu.</p>
    <?php endif; ?>

    <a href="index.php">← Retour à la liste</a>

</main>
<?php
require __DIR__ . '/nav/footer.php';