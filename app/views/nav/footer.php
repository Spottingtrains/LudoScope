<!-- ===== Pied de page ===== -->
<footer class="text-center text-lg-start text-white bg-dark nav-custom">
    <div class="container">
        <section class="footer-content">

            <!-- Colonne : description du site -->
            <div class="col-md-4 col-lg-4 col-xl-4">
                <a class="footer-brand" href="index.php?url=home">
                    <img src="uploads/ludoscope_logo.svg" alt="Logo du site LudoScope">
                </a>
                <p>Ludothèque est une plateforme dédiée aux passionnés de jeux de société, offrant une collection exhaustive de jeux, des informations détaillées et un espace de discussion pour les joueurs.</p>
            </div>

            <!-- Colonne : navigation principale -->
            <div>
                <h4>Navigation principale</h4>
                <nav>
                    <ul class="navbar-nav navbar-dark">
                        <li class="nav-item"><a href="index.php?url=home#catalogue" class="nav-link">Rechercher un jeu</a></li>
                        <?php if (!isset($_SESSION['id_utilisateur'])): ?>
                        <li class="nav-item"><a href="index.php?url=login" class="nav-link">Connexion / Inscription</a></li>
                        <?php else: ?>
                        <li class="nav-item"><a href="index.php?url=jeu/add" class="nav-link">Ajouter un jeu</a></li>
                        <li class="nav-item"><a href="index.php?url=profile" class="nav-link">Mon profil</a></li>
                        <li class="nav-item"><a href="index.php?url=logout" class="nav-link">Déconnexion</a></li>
                        <?php if (isset($_SESSION['id_role']) && $_SESSION['id_role'] >= 3): ?>
                        <li class="nav-item"><a href="index.php?url=back-office" class="nav-link">Back-Office</a></li>
                        <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>

            <!-- Colonne : navigation secondaire (pages légales) -->
            <div>
                <h4>Navigation secondaire</h4>
                <nav>
                    <ul class="navbar-nav navbar-dark">
                        <li class="nav-item"><a href="index.php?url=legal" class="nav-link">Mentions légales</a></li>
                        <li class="nav-item"><a href="index.php?url=privacy" class="nav-link">Politique de confidentialité</a></li>
                        <li class="nav-item"><a href="index.php?url=terms" class="nav-link">CGU</a></li>
                    </ul>
                </nav>
            </div>

        </section>
    </div>
</footer>
</body>

<!-- Bootstrap JS + script principal -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="assets/js/script.js"></script>