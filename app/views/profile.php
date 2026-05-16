<?php 
require __DIR__ . '/../views/nav/header.php';
require_once __DIR__ . '/../../app/middleware/auth.php';
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
        <form action="index.php?url=login" method="post" class="row g-3">
            <div class="col-4">
                <label for="firstname" class="form-label">Prénom :</label>
                <input type="text" id="firstname" name="firstname" class="form-control" value="<?php echo $user['nom'] ?? ''; ?>" disabled>
            </div>
            <div class="col-4">
                <label for="lastname" class="form-label">Nom :</label>
                <input type="text" id="lastname" name="lastname" class="form-control" value="<?php echo $user['prenom'] ?? ''; ?>" disabled>
            </div>
            <div class="col-4">
                <label for="pseudo" class="form-label">Pseudo :</label>
                <input type="text" id="pseudo" name="pseudo" class="form-control" value="<?php echo $user['pseudo'] ?? ''; ?>" disabled>
            </div>
            <div class="col-12">
                <label for="email" class="form-label">Email :</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email'] ?? ''; ?>" disabled>
            </div>
            <div class="col-6">
                <label for="password" class="form-label">Mot de passe :</label>
                <input type="password" id="password" name="password" class="form-control" value="<?php echo $user['mot_de_passe'] ?? ''; ?>" disabled>
            </div>
            <div class="col-6">
                <label for="new_password" class="form-label">Nouveau mot de passe :</label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Laissez vide pour ne pas changer">
            </div>
            <button type="submit" class="btn btn-primary">Modifier mes informations</button>
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
    </section>
    <!-- Section avis laissés -->
    <section id="avis">
        <h2>Mes avis laissés</h2>
        <p>Liste de vos avis laissés - en construction</p>
    </section>
</main>

<?php require_once __DIR__ . '/../views/nav/footer.php'; ?>