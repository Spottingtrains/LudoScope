# Ludothèque

Application web de gestion collaborative de jeux de société.

## Architecture MVC

Ce projet suit le pattern MVC (Modèle - Vue - Contrôleur).

### Routeur (`index.php`)
Point d'entrée unique de l'application. Lit le paramètre `?url=` dans l'URL et appelle la fonction du contrôleur correspondante.

Exemple : `index.php?url=login` → appelle `login()` dans `authController.php`

### Contrôleurs (`app/controllers/`)
Chaque contrôleur gère un domaine fonctionnel :
- `authController.php` : connexion, déconnexion, inscription
- `userController.php` : profil utilisateur
- `jeuController.php` : catalogue et ajout de jeux
- `adminController.php` : tableau de bord et gestion des utilisateurs

Chaque fonction de contrôleur suit le même schéma :
1. Vérification des droits (`checkRole()`)
2. Connexion BDD (`connect()`)
3. Appel du modèle
4. Inclusion de la vue

Les formulaires sont détectés via `$_SERVER['REQUEST_METHOD'] === 'POST']` plutôt que par les noms de boutons (`$_POST['bValider']`), ce qui rend le code indépendant du HTML.

### Modèles (`app/models/`)
Contiennent uniquement les requêtes SQL :
- `database.php` : connexion à la base de données
- `user.php` : opérations sur les utilisateurs
- `jeu.php` : opérations sur les jeux
- `avis.php` : opérations sur les avis

### Vues (`app/views/`)
Contiennent uniquement le HTML. Elles reçoivent les données du contrôleur sous forme de variables PHP.

### Middleware (`app/middleware/`)
- `auth.php` : vérification des droits d'accès via `checkRole($roleMin)`

## Rôles utilisateurs
| id_role | Libellé | Accès |
|---------|---------|-------|
| 1 | Visiteur | Pages publiques uniquement |
| 2 | Compte | Pages utilisateur + publiques |
| 3 | Admin | Toutes les pages |

## Stack technique
- PHP (MVC)
- MySQL
- Bootstrap 5
- JavaScript

## Sécurité
- Mots de passe hashés avec `password_hash()` (bcrypt)
- Requêtes SQL préparées (`prepare()` + `bind_param()`) contre les injections SQL
- Vérification des droits via session à chaque page protégée
- Variables d'environnement dans `.env` (non versionné)