<footer class="text-center text-lg-start text-white bg-dark navbar-dark">
	<div class="container p-4 pb-0">
		<section class="row">
			<div class="col-md-6 col-lg-6 col-xl-6 mx-auto mt-3">
				<h4>Ludothèque</h4>
				<p>Ludothèque est une plateforme dédiée aux passionnés de jeux de société, offrant une collection exhaustive de jeux, des informations détaillées et un espace de discussion pour les joueurs.</p>
			</div>

			<div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
				<h4>Navigation principale</h4>
				<!-- TODO: fix la navigation pour n'afficher que les éléments appropriés pour l'utilisateur/admin connecté ou non -->
				<nav>
                    <ul class="navbar-nav">
                        <li class="nav-item"><a href="/index.php" class="nav-link">Rechercher un jeu</a></li>
                        <li class="nav-item"><a href="/add_game.php" class="nav-link">Ajouter un jeu</a></li>
                        <li class="nav-item"><a href="/profile.php" class="nav-link">Mon profil</a></li>
                        <li class="nav-item"><a href="/signin.php" class="nav-link">Inscription</a></li>
                        <li class="nav-item"><a href="/login.php" class="nav-link">Connexion</a></li>
                        <li class="nav-item"><a href="#" class="nav-link" onclick="logout()">Déconnexion</a></li>
                        <li class="nav-item"><a href="/back-office.php" class="nav-link">Back-Office Admin</a></li>
                    </ul>
				</nav>
			</div>
			<div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
				<h4>Navigation secondaire</h4>
				<!-- TODO: créer les pages de navigation secondaire et vérifier les liens -->
				<nav>
					<ul class="navbar-nav">
						<li class="nav-item"><a href="/legal.php" class="nav-link">Mentions légales</a></li>
						<li class="nav-item"><a href="/privacy.php" class="nav-link">Politique de confifentialité</a></li>
						<li class="nav-item"><a href="/terms.php" class="nav-link">CGU</a></li>
					</ul>
				</nav>
			</div>
		</section>
	</div>
</footer>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="assets/js/script.js"></script>
