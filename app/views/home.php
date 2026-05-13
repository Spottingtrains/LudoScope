<?php
// connexion à la base de données
//* déjà dans index.php

// inclusion de l'en-tête
require __DIR__ . '/nav/header.php';
?>

<main>
    <?php var_dump($_SESSION); /* TODO: DEV ONLY - à retirer après les tests */ ?>
    <!-- message de bienvenue après connexion -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <h1>Bienvenue sur Ludothèque</h1>
    <p>Découvrez notre collection de jeux de société, partagez vos expériences et connectez-vous avec d'autres passionnés de jeux. Que vous soyez un joueur occasionnel ou un expert, Ludothèque est l'endroit idéal pour trouver votre prochain jeu préféré.</p>
    <p>Explorez notre catalogue, lisez des critiques, et rejoignez notre communauté pour discuter de vos jeux favoris et découvrir de nouvelles aventures ludiques.</p>
    <h1>Bienvenue sur Ludothèque</h1>
    <p>Découvrez notre collection de jeux de société, partagez vos expériences et connectez-vous avec d'autres passionnés de jeux. Que vous soyez un joueur occasionnel ou un expert, Ludothèque est l'endroit idéal pour trouver votre prochain jeu préféré.</p>
    <p>Explorez notre catalogue, lisez des critiques, et rejoignez notre communauté pour discuter de vos jeux favoris et découvrir de nouvelles aventures ludiques.</p>
    <h1>Bienvenue sur Ludothèque</h1>
    <p>Découvrez notre collection de jeux de société, partagez vos expériences et connectez-vous avec d'autres passionnés de jeux. Que vous soyez un joueur occasionnel ou un expert, Ludothèque est l'endroit idéal pour trouver votre prochain jeu préféré.</p>
    <p>Explorez notre catalogue, lisez des critiques, et rejoignez notre communauté pour discuter de vos jeux favoris et découvrir de nouvelles aventures ludiques.</p>
    <h1>Bienvenue sur Ludothèque</h1>
    <p>Découvrez notre collection de jeux de société, partagez vos expériences et connectez-vous avec d'autres passionnés de jeux. Que vous soyez un joueur occasionnel ou un expert, Ludothèque est l'endroit idéal pour trouver votre prochain jeu préféré.</p>
    <p>Explorez notre catalogue, lisez des critiques, et rejoignez notre communauté pour discuter de vos jeux favoris et découvrir de nouvelles aventures ludiques.</p>
    <h1>Bienvenue sur Ludothèque</h1>
    <p>Découvrez notre collection de jeux de société, partagez vos expériences et connectez-vous avec d'autres passionnés de jeux. Que vous soyez un joueur occasionnel ou un expert, Ludothèque est l'endroit idéal pour trouver votre prochain jeu préféré.</p>
    <p>Explorez notre catalogue, lisez des critiques, et rejoignez notre communauté pour discuter de vos jeux favoris et découvrir de nouvelles aventures ludiques.</p>
    <h1>Bienvenue sur Ludothèque</h1>
    <p>Découvrez notre collection de jeux de société, partagez vos expériences et connectez-vous avec d'autres passionnés de jeux. Que vous soyez un joueur occasionnel ou un expert, Ludothèque est l'endroit idéal pour trouver votre prochain jeu préféré.</p>
    <p>Explorez notre catalogue, lisez des critiques, et rejoignez notre communauté pour discuter de vos jeux favoris et découvrir de nouvelles aventures ludiques.</p>
    <h1>Bienvenue sur Ludothèque</h1>
    <p>Découvrez notre collection de jeux de société, partagez vos expériences et connectez-vous avec d'autres passionnés de jeux. Que vous soyez un joueur occasionnel ou un expert, Ludothèque est l'endroit idéal pour trouver votre prochain jeu préféré.</p>
    <p>Explorez notre catalogue, lisez des critiques, et rejoignez notre communauté pour discuter de vos jeux favoris et découvrir de nouvelles aventures ludiques.</p>
    <h1>Bienvenue sur Ludothèque</h1>
    <p>Découvrez notre collection de jeux de société, partagez vos expériences et connectez-vous avec d'autres passionnés de jeux. Que vous soyez un joueur occasionnel ou un expert, Ludothèque est l'endroit idéal pour trouver votre prochain jeu préféré.</p>
    <p>Explorez notre catalogue, lisez des critiques, et rejoignez notre communauté pour discuter de vos jeux favoris et découvrir de nouvelles aventures ludiques.</p>
    <h1>Bienvenue sur Ludothèque</h1>
    <p>Découvrez notre collection de jeux de société, partagez vos expériences et connectez-vous avec d'autres passionnés de jeux. Que vous soyez un joueur occasionnel ou un expert, Ludothèque est l'endroit idéal pour trouver votre prochain jeu préféré.</p>
    <p>Explorez notre catalogue, lisez des critiques, et rejoignez notre communauté pour discuter de vos jeux favoris et découvrir de nouvelles aventures ludiques.</p>
    <h1>Bienvenue sur Ludothèque</h1>
    <p>Découvrez notre collection de jeux de société, partagez vos expériences et connectez-vous avec d'autres passionnés de jeux. Que vous soyez un joueur occasionnel ou un expert, Ludothèque est l'endroit idéal pour trouver votre prochain jeu préféré.</p>
    <p>Explorez notre catalogue, lisez des critiques, et rejoignez notre communauté pour discuter de vos jeux favoris et découvrir de nouvelles aventures ludiques.</p>
    <h1>Bienvenue sur Ludothèque</h1>
    <p>Découvrez notre collection de jeux de société, partagez vos expériences et connectez-vous avec d'autres passionnés de jeux. Que vous soyez un joueur occasionnel ou un expert, Ludothèque est l'endroit idéal pour trouver votre prochain jeu préféré.</p>
    <p>Explorez notre catalogue, lisez des critiques, et rejoignez notre communauté pour discuter de vos jeux favoris et découvrir de nouvelles aventures ludiques.</p>
    <h1>Bienvenue sur Ludothèque</h1>
    <p>Découvrez notre collection de jeux de société, partagez vos expériences et connectez-vous avec d'autres passionnés de jeux. Que vous soyez un joueur occasionnel ou un expert, Ludothèque est l'endroit idéal pour trouver votre prochain jeu préféré.</p>
    <p>Explorez notre catalogue, lisez des critiques, et rejoignez notre communauté pour discuter de vos jeux favoris et découvrir de nouvelles aventures ludiques.</p>
</main>

<?php
require __DIR__ . '/nav/footer.php';
?>