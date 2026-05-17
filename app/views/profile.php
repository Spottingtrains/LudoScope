<?php 
require __DIR__ . '/../views/nav/header.php';
?>

<main class="container py-5">
    <h1>Mon profil</h1>
    <p>Bienvenue <strong><?php echo $_SESSION['pseudo']; ?></strong> !</p>
    <p>Vous n'êtes pas <?php echo $_SESSION['pseudo']; ?> ? <a href="" onclick="logout()">Me déconnecter</a></p>
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
        <form action="" method="post" enctype="multipart/form-data" class="row g-3">
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
                <img src="<?php echo $user['image_profil'] ?? '/../uploads/default-profile.webp'; ?>" alt="image de profil" class="img-thumbnail mb-3" style="width: 150px; height: 150px; border-radius: 50%;"> <!-- TODO: retirer les styles inline et les mettre dans le CSS -->
            </div>
            <div class="col-12 mb-3">
                <label for="image_profil" class="form-label">Modifier l'image de profil :</label>
                <input type="file" id="image_profil" name="image_profil" accept="image/*" class="form-control">
            </div>
            <div class="col-4">
                <label for="prenom" class="form-label">Prénom :</label>
                <input type="text" id="prenom" name="prenom" class="form-control" value="<?php echo $user['prenom'] ?? ''; ?>">
            </div>
            <div class="col-4">
                <label for="nom" class="form-label">Nom :</label>
                <input type="text" id="nom" name="nom" class="form-control" value="<?php echo $user['nom'] ?? ''; ?>">
            </div>
            <div class="col-4">
                <label for="pseudo" class="form-label">Pseudo :</label>
                <input type="text" id="pseudo" name="pseudo" class="form-control" value="<?php echo $user['pseudo'] ?? ''; ?>">
            </div>
            <div class="col-12">
                <label for="email" class="form-label">Email :</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email'] ?? ''; ?>" aria-describedby="emailHelp">
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
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($addedGames)): ?>
                    <?php foreach ($addedGames as $game): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($game['titre']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($game['date_ajout']))); ?></td>
                            <td><?php echo (int)$game['nb_commentaires']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Vous n'avez ajouté aucun jeu.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
    <!-- Section avis laissés -->
    <section id="avis">
        <h2>Mes avis laissés</h2>
        <p>Liste de vos avis laissés - en construction</p>
        <table class="table">
            <thead>
                <tr>
                    <th>Titre du jeu</th>
                    <th>Note</th>
                    <th>Date de l'avis</th>
                    <th>Commentaire</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($addedReviews)): ?>
                    <?php foreach ($addedReviews as $review): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($review['titre']); ?></td>
                            <td><?php echo htmlspecialchars($review['note']); ?> / 10</td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($review['date_avis']))); ?></td>
                            <td><?php echo htmlspecialchars($review['commentaire']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Vous n'avez ajouté aucun avis.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php require_once __DIR__ . '/../views/nav/footer.php'; ?>