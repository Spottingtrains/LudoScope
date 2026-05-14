<nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top nav-custom">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php?url=home"><img src="" alt="Logo"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="collapsibleNavbar">
            <ul class="navbar-nav">
                <li class="nav-item"><a href="index.php?url=home#catalogue" class="nav-link">Rechercher un jeu</a></li>

                <?php if (!isset($_SESSION['id_utilisateur'])): ?>
                <li class="nav-item"><a href="index.php?url=login" class="nav-link">Connexion / Inscription</a></li>

                <?php else: ?>
                <li class="nav-item"><a href="index.php?url=jeu/add" class="nav-link">Ajouter un jeu</a></li>
                <li class="nav-item"><a href="index.php?url=profile" class="nav-link">Mon profil</a></li>
                <li class="nav-item"><a href="index.php?url=logout" class="nav-link">Déconnexion</a></li>

                <?php if ($_SESSION['id_role'] >= 3): ?>
                <li class="nav-item"><a href="index.php?url=back-office" class="nav-link">Back-Office Admin</a></li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>