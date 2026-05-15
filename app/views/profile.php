<?php 
require __DIR__ . '/../views/nav/header.php';
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../../app/models/user.php';
checkRole(2); // Seuls les utilisateurs avec un rôle >= 2 peuvent accéder au profil
?>

<main>
    <h1>Mon profil</h1>
    <p>Bienvenue <strong><?php echo $_SESSION['pseudo']; ?></strong> !</p>
    <p>Vous n'êtes pas <?php echo $_SESSION['pseudo']; ?> ? <a href="" onclick="logout()">Me déconnecter</a></p>
    <section>
        <h2>Mes informations</h2>
        <form action="">
            <label for="firstname">Prénom :</label>
            <input type="text" id="firstname" name="firstname" value="<?php echo $_SESSION['firstname']; ?>" disabled>
            <br>
            <label for="lastname">Nom :</label>
            <input type="text" id="lastname" name="lastname" value="<?php echo $_SESSION['lastname']; ?>" disabled>
            <br>
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" value="<?php echo $_SESSION['email']; ?>" disabled>
            <br>
            <label for="pseudo">Pseudo :</label>
            <input type="text" id="pseudo" name="pseudo" value="<?php echo $_SESSION['pseudo']; ?>" disabled>
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" value="<?php echo $_SESSION['mot_de_passe']; ?>" disabled>
            <br>
            <label for="pseudo">Pseudo :</label>
            <input type="text" id="pseudo" name="pseudo" value="<?php echo $_SESSION['pseudo']; ?>" disabled>
        </form>
    </section>
</main>

<?php require_once __DIR__ . '/../views/nav/footer.php'; ?>