<?php
require __DIR__ . '/nav/header.php';
?>
<main>
    <section id="jeuDetail" class="small-padding orange-bg">
        <?php if (isset($jeu)) : ?>
            <div class="jeu-header">
                <div class="jeu-title">
                    <h1><?= htmlspecialchars($jeu['titre']) ?></h1>
                    <small><?= htmlspecialchars($jeu['date_ajout'] ?? 'N/A') ?></small>
                </div>
                <div class="note-moyenne">
                    <span><?= htmlspecialchars($jeu['note_moyenne'] ?? 'N/A') ?>/10</span>
                </div>
            </div>
            <img src="/uploads/<?= htmlspecialchars(!empty($jeu['image']) ? $jeu['image'] : 'default.jpg') ?>" alt="<?= htmlspecialchars($jeu['titre']) ?>">
            <div class="large-padding">
                <h2>Description</h2>
                <p><?= htmlspecialchars($jeu['description']) ?></p>
            </div>
            <div class="large-padding">
                <h2>Catégories</h2>
                <?php if (!empty($categories)) : ?>
                    <div class="tags">
                        <?php foreach ($categories as $cat) : ?>
                            <span class="tag"><?= htmlspecialchars($cat['nom_categorie']) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p>Aucune catégorie associée.</p>
                <?php endif; ?>
                <table class="jeu-details-table">
                    <tr>
                        <th>Éditeur</th>
                        <td><?= htmlspecialchars($jeu['nom_editeur'] ?? 'Non renseigné') ?></td>
                    </tr>
                    <tr>
                        <th>Auteur</th>
                        <td><?= htmlspecialchars($jeu['auteur'] ?? 'Non renseigné') ?></td>
                    </tr>
                    <tr>
                        <th>Illustrateur</th>
                        <td><?= htmlspecialchars($jeu['illustrateur'] ?? 'Non renseigné') ?></td>
                    </tr>
                    <tr>
                        <th>Année d'édition</th>
                        <td><?= htmlspecialchars($jeu['annee_edition'] ?? 'Non renseignée') ?></td>
                    </tr>
                    <tr>
                        <th>Nombre de joueurs</th>
                        <td><?= htmlspecialchars($jeu['nb_joueurs_min']) ?> - <?= htmlspecialchars($jeu['nb_joueurs_max']) ?></td>
                    </tr>
                    <tr>
                        <th>À partir de</th>
                        <td><?= htmlspecialchars($jeu['age_min'] ?? 'Non renseigné') ?> ans</td>
                    </tr>
                    <tr>
                        <th>Durée</th>
                        <td><?= htmlspecialchars($jeu['duree_partie']) ?> min</td>
                    </tr>
                    <tr>
                        <th>Complexité</th>
                        <td><?= htmlspecialchars($jeu['complexite']) ?></td>
                    </tr>
                </table>
            </div>
        <?php else : ?>
            <p>Jeu introuvable.</p>
        <?php endif; ?>
    </section>

    <?php if (isset($jeu)) : ?>
    <section id="avisSection" class="small-padding">
        <h2>Avis</h2>

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

        <?php if (!empty($avis)) : ?>
            <div class="avis-list">
                <?php foreach ($avis as $a) : ?>
                    <div class="avis-card">
                        <div class="card-body">
                            <div class="avis-header">
                                <img
                                    class="avis-avatar"
                                    src="<?= htmlspecialchars(!empty($a['photo_profil']) ? $a['photo_profil'] : '/uploads/default-profile.webp') ?>"
                                    alt="Photo de profil de <?= htmlspecialchars($a['pseudo'] ?? 'Utilisateur supprimé') ?>"
                                >
                                <div>
                                    <p><strong><?= htmlspecialchars($a['pseudo'] ?? 'Utilisateur supprimé') ?></strong> — <?= htmlspecialchars($a['date_avis']) ?></p>
                                    <p>Note : <?= htmlspecialchars($a['note']) ?>/10</p>
                                </div>
                            </div>
                            <p><?= htmlspecialchars($a['commentaire']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p>Aucun avis pour ce jeu.</p>
        <?php endif; ?>

        <a href="index.php">← Retour à la liste</a>

        <!-- TODO: Implémenter la section "ces jeux pourraient vous plaire" -->
        <h2>Ces jeux pourraient vous plaire</h2>
    </section>
    <?php endif; ?>

</main>
<?php
require __DIR__ . '/nav/footer.php';