# Ludothèque

Application web collaborative de gestion de jeux de société.  
Les utilisateurs peuvent consulter le catalogue, ajouter des jeux et laisser des avis.  
Les administrateurs gèrent le contenu et valident les demandes de modification/suppression des jeux.

---

## Liens

- [Maquettes Figma](#)
- [Projet Trello](#)
- Base de données : fichier `ludoscope.sql` à la racine du dépôt

---

## Schémas et analyse

- Impact mapping : Ludoscope-Impact Mapping.pdf
- Arborescence : Ludoscope-Arborescence.pdf
- Zoning : LudoScope-Zoning.pdf
- Wireframe : Ludoscope-Wireframe.pdf
- Analyse business : LudoScope-Analyse business.pdf

---

## Installation

### Prérequis

- PHP 8+
- MySQL 8
- [WampServer](https://www.wampserver.com/)
- L'extension PHP `pdo_mysql` activée dans `php.ini`

### Étapes

1. **Télécharger le projet**  
   Téléchargez le dépôt depuis GitHub (**Code → Download ZIP**), extrayez le dossier et placez-le dans `C:/wamp64/www/`.

2. **Créer la base de données**  
   Ouvrez phpMyAdmin (`http://localhost/phpmyadmin`) et importez le fichier `ludoscope.sql` via l'onglet **Importer**. La base de données est créée automatiquement.

3. **Configurer l'environnement**  
   Renommez le fichier `.env.example` en `.env`, puis renseignez vos identifiants :
```dotenv
   DB_HOST=localhost
   DB_USER=root
   DB_PASSWORD=
   DB_NAME=ludotheque
```

4. **Lancer le projet**  
   Accédez à :
```
   http://localhost/LudoScope/LudoScope-main/
```

---

## Comptes de test

| Rôle        | Email             | Mot de passe |
|-------------|-------------------|--------------|
| Admin       | admin@example.com | `Azerty123`  |
| Utilisateur | user@example.com  | `Azerty123`  |

---

## Architecture MVC

Ce projet suit le pattern MVC (Modèle - Vue - Contrôleur).

### Routeur (`index.php`)

Point d'entrée unique de l'application.  
Lit le paramètre `?url=` dans l'URL et appelle la fonction du contrôleur correspondante.

Exemple : `index.php?url=login` → `login()` dans `authController.php`

### Contrôleurs (`app/controllers/`)

Chaque contrôleur gère un domaine fonctionnel :

| Fichier                  | Responsabilité                                                              |
|--------------------------|-----------------------------------------------------------------------------|
| `authController.php`     | Connexion, déconnexion, inscription, réinitialisation du mot de passe (question secrète) |
| `userController.php`     | Profil : consultation, modification, gestion des avis, suppression de compte |
| `jeuController.php`      | Catalogue, détail, recherche AJAX, ajout, demandes de modification/suppression |
| `adminController.php`    | Tableau de bord, gestion des utilisateurs, gestion du contenu, traitement des demandes |
| `homeController.php`     | Page d'accueil (statistiques, meilleures notes, catégories) |
| `passwordController.php` | Réinitialisation du mot de passe par email (token, PHPMailer) — non actif par défaut |

Chaque fonction de contrôleur suit le même schéma :

1. Vérification des droits (`checkRole()`)
2. Connexion BDD (`connect()`)
3. Appel du modèle
4. Inclusion de la vue

Les formulaires sont détectés via `$_SERVER['REQUEST_METHOD'] === 'POST'` plutôt que par les noms de boutons (`$_POST['bValider']`), ce qui rend le code indépendant du HTML.

### Modèles (`app/models/`)

Contiennent uniquement les requêtes SQL (aucune logique métier) :

| Fichier        | Responsabilité                                       |
|----------------|------------------------------------------------------|
| `database.php` | Connexion PDO à la base de données                   |
| `user.php`     | CRUD utilisateurs                                    |
| `jeu.php`      | CRUD jeux, catégories, éditeurs                      |
| `avis.php`     | CRUD avis                                            |
| `demande.php`  | Demandes de modification/suppression de jeux         |
| `stats.php`    | Statistiques globales (nb jeux, utilisateurs, avis)  |
| `token.php`    | Tokens de réinitialisation de mot de passe par email |

### Vues (`app/views/`)

Contiennent uniquement le HTML.  
Elles reçoivent les données du contrôleur sous forme de variables PHP.

### Middleware (`app/middleware/`)

- `auth.php` : contrôle d'accès via `checkRole(int $roleMin)`.  
  Retourne une page 404 (erreur 403 dans la console) si le rôle de l'utilisateur est insuffisant.

---

## Rôles utilisateurs

| id_role | Libellé  | Accès                                       |
|---------|----------|---------------------------------------------|
| 1       | Visiteur | Pages publiques uniquement                  |
| 2       | Compte   | Pages publiques + espace utilisateur        |
| 3       | Admin    | Toutes les pages (y compris le back-office) |

---

## Routes disponibles

| URL                    | Contrôleur           | Accès         |
|------------------------|----------------------|---------------|
| `?url=home`            | `home()`             | Tous          |
| `?url=jeu&slug=…`      | `jeu()`              | Tous          |
| `?url=jeu/search&q=…`  | `jeuSearch()`        | Tous (JSON)   |
| `?url=jeu/add`         | `jeuAdd()`           | Rôle ≥ 2      |
| `?url=jeu/edit&id=…`   | `jeuEditRequest()`   | Auteur du jeu |
| `?url=jeu/delete&id=…` | `jeuDeleteRequest()` | Auteur du jeu |
| `?url=login`           | `login()`            | Tous          |
| `?url=register`        | `register()`         | Tous          |
| `?url=logout`          | `logout()`           | Connectés     |
| `?url=forgot-password` | `forgotPassword()`   | Tous          |
| `?url=profile`         | `profile()`          | Rôle ≥ 2      |
| `?url=back-office`     | `dashboard()`        | Rôle 3        |
| `?url=admin_users`     | `adminUsers()`       | Rôle 3        |
| `?url=admin_content`   | `adminContent()`     | Rôle 3        |

---

## Stack technique

- PHP 8+ (architecture MVC personnalisée, sans framework)
- MySQL 8
- PDO avec requêtes préparées
- Bootstrap 5
- JavaScript (vanilla)

---

## Sécurité

- Mots de passe hachés avec `password_hash()` (bcrypt)
- Requêtes SQL préparées via PDO (`prepare()` + `execute()`) — protection contre les injections SQL
- Émulation des requêtes préparées désactivée (`ATTR_EMULATE_PREPARES = false`)
- Vérification du rôle via session (`checkRole()`) à chaque page protégée
- Validation du type MIME réel des images uploadées (`finfo`)
- Variables d'environnement dans `.env` (non versionné — voir `.env.example`)
- Nécessite l'extension PHP `pdo_mysql` (à activer dans `php.ini` si besoin)