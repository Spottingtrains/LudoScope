<?php
function getAllJeux($conn) {
    $stmt = $conn->prepare("
        SELECT jeu.*, 
            editeur.nom_editeur,
            ROUND(AVG(avis.note), 1) AS note_moyenne,
            COUNT(DISTINCT avis.id_avis) AS nb_avis,
            GROUP_CONCAT(DISTINCT categorie.libelle_categorie SEPARATOR ', ') AS categories
        FROM jeu
        LEFT JOIN editeur ON jeu.id_editeur = editeur.id_editeur
        LEFT JOIN avis ON jeu.id_jeu = avis.id_jeu
        LEFT JOIN jeu_categorie ON jeu.id_jeu = jeu_categorie.id_jeu
        LEFT JOIN categorie ON jeu_categorie.id_categorie = categorie.id_categorie
        GROUP BY jeu.id_jeu
        ORDER BY jeu.date_ajout DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getJeuById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM jeu WHERE id_jeu = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function createJeu($conn, $id_utilisateur, $data) {
    $stmt = $conn->prepare("INSERT INTO jeu (titre, description, nb_joueurs_min, nb_joueurs_max, age_min, age_max, duree_partie, complexite, image, date_ajout, id_utilisateur) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("ssiiiiiisi", $data['titre'], $data['description'], $data['nb_joueurs_min'], $data['nb_joueurs_max'], $data['age_min'], $data['age_max'], $data['duree_partie'], $data['complexite'], $data['image'], $id_utilisateur);
    return $stmt->execute();
}

function getDerniersJeux($conn) {
    $stmt = $conn->prepare("SELECT * FROM jeu ORDER BY date_ajout DESC LIMIT 5");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getDemandesEnAttente($conn) {
    $stmt = $conn->prepare("SELECT * FROM demande WHERE statut = 'en_attente' ORDER BY date_demande DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}