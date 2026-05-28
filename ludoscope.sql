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
) ;

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

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id_categorie`, `libelle_categorie`) VALUES
(1, 'plateau'),
(2, 'ambiance'),
(3, 'cartes'),
(4, 'coopératif'),
(5, 'rôle'),
(6, 'dés');

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `editeur`
--

DROP TABLE IF EXISTS `editeur`;
CREATE TABLE IF NOT EXISTS `editeur` (
  `id_editeur` int NOT NULL AUTO_INCREMENT,
  `nom_editeur` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_editeur`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `editeur`
--

INSERT INTO `editeur` (`id_editeur`, `nom_editeur`) VALUES
(1, 'Repos Production'),
(2, 'Space Cowboys'),
(3, 'Iello'),
(4, 'Asmodee'),
(5, 'Hasbro');

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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`id_role`, `libelle_role`) VALUES
(1, 'visiteur'),
(2, 'compte'),
(3, 'admin');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
