<nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top nav-custom">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><img src="" alt="Logo"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="collapsibleNavbar">
            <ul class="navbar-nav">
                <li class="nav-item"><a href="<?php echo __DIR__ . '/index.php'; ?>" class="nav-link">Rechercher un jeu</a></li>
                <li class="nav-item"><a href="<?php echo __DIR__ . '/add_game.php'; ?>" class="nav-link">Ajouter un jeu</a></li>
                <li class="nav-item"><a href="<?php echo __DIR__ . '/profile.php'; ?>" class="nav-link">Mon profil</a></li>
                <li class="nav-item"><a href="<?php echo __DIR__ . '/signin.php'; ?>" class="nav-link">Inscription</a></li>
                <li class="nav-item"><a href="<?php echo __DIR__ . '/login.php'; ?>" class="nav-link">Connexion</a></li>
                <li class="nav-item"><a href="#" class="nav-link" onclick="logout()">Déconnexion</a></li>
                <li class="nav-item"><a href="<?php echo __DIR__ . '/back-office.php'; ?>" class="nav-link">Back-Office Admin</a></li>
            </ul>
        </div>
    </div>
</nav>