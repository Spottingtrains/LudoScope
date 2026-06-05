-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 28 mai 2026 à 13:09
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ludoscope`
--
CREATE DATABASE IF NOT EXISTS `ludoscope` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ludoscope`;

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

DROP TABLE IF EXISTS `avis`;
CREATE TABLE IF NOT EXISTS `avis` (
  `id_avis` int NOT NULL AUTO_INCREMENT,
  `commentaire` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` tinyint NOT NULL,
  `date_avis` datetime NOT NULL,
  `date_modification` datetime DEFAULT NULL,
  `id_utilisateur` int DEFAULT NULL,
  `id_jeu` int NOT NULL,
  PRIMARY KEY (`id_avis`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_jeu` (`id_jeu`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `id_categorie` int NOT NULL AUTO_INCREMENT,
  `libelle_categorie` enum('plateau','ambiance','cartes','coopératif','rôle','dés') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_categorie`),
  UNIQUE KEY `libelle_categorie` (`libelle_categorie`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demande`
--

DROP TABLE IF EXISTS `demande`;
CREATE TABLE IF NOT EXISTS `demande` (
  `id_demande` int NOT NULL AUTO_INCREMENT,
  `type_demande` enum('modification','suppression') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_demande` datetime NOT NULL,
  `statut` enum('en_attente','traite','refuse') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `reponse_admin` text COLLATE utf8mb4_unicode_ci,
  `id_jeu` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  PRIMARY KEY (`id_demande`),
  KEY `id_jeu` (`id_jeu`),
  KEY `id_utilisateur` (`id_utilisateur`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `editeur`
--

DROP TABLE IF EXISTS `editeur`;
CREATE TABLE IF NOT EXISTS `editeur` (
  `id_editeur` int NOT NULL AUTO_INCREMENT,
  `nom_editeur` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_editeur`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `favori`
--

DROP TABLE IF EXISTS `favori`;
CREATE TABLE IF NOT EXISTS `favori` (
  `id_utilisateur` int NOT NULL,
  `id_jeu` int NOT NULL,
  `date_ajout` datetime NOT NULL,
  PRIMARY KEY (`id_utilisateur`,`id_jeu`),
  KEY `id_jeu` (`id_jeu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jeu`
--

DROP TABLE IF EXISTS `jeu`;
CREATE TABLE IF NOT EXISTS `jeu` (
  `id_jeu` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `nb_joueurs_min` int NOT NULL,
  `nb_joueurs_max` int NOT NULL,
  `age_min` int NOT NULL,
  `duree_partie` int NOT NULL,
  `complexite` enum('facile','intermédiaire','expert') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_ajout` datetime NOT NULL,
  `auteur` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `illustrateur` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `annee_edition` year DEFAULT NULL,
  `id_utilisateur` int NOT NULL,
  `id_editeur` int DEFAULT NULL,
  PRIMARY KEY (`id_jeu`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_editeur` (`id_editeur`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jeu_categorie`
--

DROP TABLE IF EXISTS `jeu_categorie`;
CREATE TABLE IF NOT EXISTS `jeu_categorie` (
  `id_jeu` int NOT NULL,
  `id_categorie` int NOT NULL,
  PRIMARY KEY (`id_jeu`,`id_categorie`),
  KEY `id_categorie` (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
  `id_role` int NOT NULL AUTO_INCREMENT,
  `libelle_role` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_role`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `token_reset`
--

DROP TABLE IF EXISTS `token_reset`;
CREATE TABLE IF NOT EXISTS `token_reset` (
  `id_token` int NOT NULL AUTO_INCREMENT,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_expiration` datetime NOT NULL,
  `id_utilisateur` int NOT NULL,
  PRIMARY KEY (`id_token`),
  UNIQUE KEY `token` (`token`),
  KEY `id_utilisateur` (`id_utilisateur`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pseudo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo_profil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_inscription` datetime NOT NULL,
  `derniere_connexion` datetime DEFAULT NULL,
  `id_role` int NOT NULL DEFAULT '2',
  `question_secrete` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `reponse_secrete` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `pseudo` (`pseudo`),
  UNIQUE KEY `email` (`email`),
  KEY `id_role` (`id_role`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- DONNÉES DE DÉMONSTRATION
-- ========================================

-- Rôles
INSERT INTO `role` (`id_role`, `libelle_role`) VALUES
(1, 'visiteur'),
(2, 'compte'),
(3, 'admin');

-- Catégories
INSERT INTO `categorie` (`id_categorie`, `libelle_categorie`) VALUES
(1, 'plateau'),
(2, 'ambiance'),
(3, 'cartes'),
(4, 'coopératif'),
(5, 'rôle'),
(6, 'dés');

-- Éditeurs
INSERT INTO `editeur` (`id_editeur`, `nom_editeur`) VALUES
(1, 'Repos Production'),
(2, 'Space Cowboys'),
(3, 'Iello'),
(4, 'Asmodee'),
(5, 'Hasbro');

-- Utilisateurs (mot de passe : Azerty123)
INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `pseudo`, `email`, `mot_de_passe`, `photo_profil`, `date_inscription`, `derniere_connexion`, `id_role`, `question_secrete`, `reponse_secrete`) VALUES
(1, 'Admin', 'Site', 'admin', 'admin@ludoscope.com', '$2y$10$Pw0QHfKYyuJN8dsAp.OhJ./V4zS50N4vNjawWYGBFYZM9yKj0scv.', NULL, '2026-05-11 18:22:17', NULL, 3, 'Quel est le prénom de votre mère ?', 'Marie'),
(2, 'Dupont', 'Jean', 'jeanjeu', 'jean@example.com', '$2y$10$Pw0QHfKYyuJN8dsAp.OhJ./V4zS50N4vNjawWYGBFYZM9yKj0scv.', NULL, '2026-05-11 18:22:17', NULL, 2, 'Quel est le prénom de votre mère ?', 'Marie'),
(3, 'Martin', 'Sophie', 'sophiegames', 'sophie@example.com', '$2y$10$Pw0QHfKYyuJN8dsAp.OhJ./V4zS50N4vNjawWYGBFYZM9yKj0scv.', NULL, '2026-05-11 18:22:17', NULL, 2, 'Quel est le prénom de votre mère ?', 'Marie');

-- Jeux
INSERT INTO `jeu` (`id_jeu`, `titre`, `description`, `nb_joueurs_min`, `nb_joueurs_max`, `age_min`, `duree_partie`, `complexite`, `image`, `date_ajout`, `auteur`, `illustrateur`, `annee_edition`, `id_utilisateur`, `id_editeur`) VALUES
(1, 'Catan', 'Jeu de stratégie et de commerce sur une île aux ressources limitées. Collectez des matériaux, construisez des routes et des villes, et échangez avec vos adversaires pour dominer l\'île.', 3, 4, 10, 90, 'intermédiaire', 'jeu_6a18951c55ea51.72808643.jpg', '2026-05-11 18:22:17', 'Klaus Teuber', NULL, '1995', 1, NULL),
(2, 'Splendor', 'Jeu de collection de gemmes et de cartes. Incarnez un marchand de la Renaissance et développez votre empire en achetant des mines, des moyens de transport et des artisans.', 2, 4, 10, 30, 'facile', 'jeu_6a189604b2d5e1.52391061.webp', '2026-05-11 18:22:17', 'Marc André', NULL, '2014', 1, 2),
(3, '7 Wonders', 'Jeu de draft de cartes et de construction de civilisation. Guidez une cité antique à travers trois âges en développant son commerce, son armée et ses merveilles architecturales.', 2, 7, 10, 30, 'intermédiaire', 'jeu_6a1896311381f9.11830374.jpg', '2026-05-11 18:22:17', 'Antoine Bauza', NULL, '2010', 1, 4),
(4, 'Monopoly', 'Jeu de gestion immobilière où les joueurs achètent, vendent et échangent des propriétés pour ruiner leurs adversaires. Un classique familial aux parties parfois interminables.', 2, 6, 8, 120, 'facile', 'jeu_6a189659eac077.93795657.webp', '2026-05-11 18:22:17', 'Charles Darrow', NULL, '1935', 1, 5),
(5, 'Pandemic', 'Jeu coopératif où les joueurs collaborent pour enrayer quatre épidémies mondiales. Chaque joueur dispose d\'un rôle unique et doit coordonner ses actions avec l\'équipe pour sauver l\'humanité.', 2, 4, 8, 45, 'intermédiaire', 'jeu_6a1896833abe02.81106022.jpg', '2026-05-11 18:22:17', 'Matt Leacock', NULL, '2008', 1, NULL),
(6, 'Ticket to Ride', 'Jeu de pose de wagons sur un réseau ferroviaire européen. Reliez les villes indiquées sur vos cartes destination avant vos adversaires, tout en les empêchant de prendre vos routes.', 2, 5, 8, 60, 'facile', 'jeu_6a1896d6d3e4e5.92097308.webp', '2026-05-11 18:22:17', 'Alan R. Moon', NULL, '2004', 2, NULL),
(7, 'Dixit', 'Jeu d\'ambiance où les joueurs devinent des cartes illustrées à partir d\'indices poétiques. Le conteur doit être suffisamment vague pour ne pas être deviné par tout le monde.', 3, 6, 8, 30, 'facile', 'jeu_6a189711a053e5.28410758.webp', '2026-05-11 18:22:17', 'Jean-Louis Roubira', NULL, '2008', 2, 4),
(8, 'Codenames', 'Jeu d\'association de mots en équipe. Les chefs d\'équipe donnent des indices en un seul mot pour faire deviner plusieurs cartes à leurs coéquipiers, sans toucher les cartes adverses.', 4, 8, 10, 20, 'facile', 'jeu_6a1897a225b7a7.11206393.jpg', '2026-05-11 18:22:17', 'Vlaada Chvátil', NULL, '2015', 3, NULL),
(9, 'King of Tokyo', 'Jeu de dés où des monstres géants s\'affrontent pour dominer Tokyo. Lancez les dés, accumulez de l\'énergie, infligez des dégâts et survivez pour être le dernier monstre debout.', 2, 6, 8, 30, 'facile', 'jeu_6a189801372890.70049659.jpg', '2026-05-11 18:22:17', 'Richard Garfield', NULL, '2011', 3, 3);

-- Catégories des jeux
INSERT INTO `jeu_categorie` (`id_jeu`, `id_categorie`) VALUES
(1, 1),
(2, 3),
(3, 3),
(4, 1),
(4, 6),
(5, 4),
(6, 1),
(7, 2),
(8, 3),
(9, 6);

-- Avis
INSERT INTO `avis` (`id_avis`, `commentaire`, `note`, `date_avis`, `date_modification`, `id_utilisateur`, `id_jeu`) VALUES
(1, 'Un classique incontournable ! Les échanges entre joueurs rendent chaque partie unique.', 10, '2026-05-11 18:22:17', NULL, 2, 1),
(2, 'Facile à apprendre, difficile à maîtriser. Parfait pour débuter dans les jeux modernes.', 8, '2026-05-11 18:22:17', NULL, 3, 2),
(3, 'Le meilleur jeu de draft du marché. Fonctionne aussi bien à 2 qu\'à 7 joueurs.', 10, '2026-05-11 18:22:17', NULL, 2, 3),
(4, 'Coopératif et stressant à souhait. On se croirait vraiment en train de sauver le monde.', 9, '2026-05-11 18:22:17', NULL, 3, 5),
(5, 'Idéal pour les soirées en famille. Simple mais jamais ennuyeux.', 8, '2026-05-11 18:22:17', NULL, 2, 7);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `demande`
--
ALTER TABLE `demande`
  ADD CONSTRAINT `demande_ibfk_1` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `demande_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `favori`
--
ALTER TABLE `favori`
  ADD CONSTRAINT `favori_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `favori_ibfk_2` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `jeu`
--
ALTER TABLE `jeu`
  ADD CONSTRAINT `jeu_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `jeu_ibfk_2` FOREIGN KEY (`id_editeur`) REFERENCES `editeur` (`id_editeur`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `jeu_categorie`
--
ALTER TABLE `jeu_categorie`
  ADD CONSTRAINT `jeu_categorie_ibfk_1` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jeu_categorie_ibfk_2` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `token_reset`
--
ALTER TABLE `token_reset`
  ADD CONSTRAINT `token_reset_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `utilisateur_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;