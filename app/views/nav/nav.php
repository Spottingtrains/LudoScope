<nav class="navbar navbar-expand-sm fixed-top navbar-dark nav-custom">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php?url=home"><img src="uploads/ludoscope_logo.svg" alt="LudoScope"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="collapsibleNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a href="index.php?url=home#catalogue" class="nav-link">Rechercher un jeu</a></li>

                <?php if (!isset($_SESSION['id_utilisateur'])): ?>
                <li class="nav-item"><a href="index.php?url=login" class="nav-link">Connexion / Inscription</a></li>

                <?php else: ?>
                <li class="nav-item"><a href="index.php?url=jeu/add" class="nav-link">Ajouter un jeu</a></li>
                <li class="nav-item"><a href="index.php?url=profile" class="nav-link">Mon profil</a></li>
                <li class="nav-item"><a href="index.php?url=logout" class="nav-link">Déconnexion</a></li>

                <?php if ($_SESSION['id_role'] >= 3): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Back-Office</a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="adminDropdown">
                        <li><a class="dropdown-item" href="index.php?url=back-office">Dashboard</a></li>
                        <li><a class="dropdown-item" href="index.php?url=admin_users">Gestion utilisateurs</a></li>
                        <li><a class="dropdown-item" href="index.php?url=admin_content">Gestion contenu</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>