<?php 
require __DIR__ . '/../views/nav/header.php';
require_once __DIR__ . '/../../app/middleware/auth.php';
?>

<main>
    <h1>Mon profil</h1>
    <p>Bienvenue <strong><?php echo $_SESSION['pseudo']; ?></strong> !</p>
    <p>Vous n'êtes pas <?php echo $_SESSION['pseudo']; ?> ? <a href="" onclick="logout()">Me déconnecter</a></p>
    <!-- Section pour modifier les informations du profil -->
    <section>
        <h2>Mes informations</h2>
        <form action="" class="custom-form">
            <div class="form-group">
                <label for="firstname">Prénom :</label>
                <input type="text" id="firstname" name="firstname" value="<?php echo $user['nom'] ?? ''; ?>" disabled>
            </div>
            <div class="form-group">
                <label for="lastname">Nom :</label>
                <input type="text" id="lastname" name="lastname" value="<?php echo $user['prenom'] ?? ''; ?>" disabled>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo $user['email'] ?? ''; ?>" disabled>
            </div>
            <div class="form-group">
                <label for="pseudo">Pseudo :</label>
                <input type="text" id="pseudo" name="pseudo" value="<?php echo $user['pseudo'] ?? ''; ?>" disabled>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" value="<?php echo $user['mot_de_passe'] ?? ''; ?>" disabled>
            </div>
            <div class="form-group">
                <label for="new_password">Nouveau mot de passe :</label>
                <input type="password" id="new_password" name="new_password" placeholder="Laissez vide pour ne pas changer">
            </div>
            <button type="submit" class="btn btn-primary">Modifier mes informations</button>
        </form>
    </section>
    <!-- Section jeux favoris -->
    <section>
        <h2>Mes jeux favoris</h2>
        <p>Liste de vos jeux favoris - en construction</p>
    </section>
    <!-- Section historique de jeux ajoutés -->
    <section>
        <h2>Mon historique de jeux ajoutés</h2>
        <p>Historique des jeux que vous avez ajoutés - en construction</p>
    </section>
    <!-- Section avis laissés -->
    <section>
        <h2>Mes avis laissés</h2>
        <p>Liste de vos avis laissés - en construction</p>
    </section>
</main>

<?php require_once __DIR__ . '/../views/nav/footer.php'; ?>