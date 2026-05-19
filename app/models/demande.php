<?php
function createDemande($conn, $data) {
    $stmt = $conn->prepare(
        "INSERT INTO demande (type_demande, message, date_demande, statut, reponse_admin, id_jeu, id_utilisateur)
        VALUES (?, ?, NOW(), 'en_attente', NULL, ?, ?)"
    );

    return $stmt->execute([
        $data['type_demande'],
        $data['message'],
        $data['id_jeu'],
        $data['id_utilisateur'],
    ]);
}

function getDemandesEnAttente($conn) {
    $stmt = $conn->prepare(
        "SELECT d.*, j.titre AS jeu_titre, u.pseudo AS utilisateur_pseudo
        FROM demande d
        LEFT JOIN jeu j ON d.id_jeu = j.id_jeu
        LEFT JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
        WHERE d.statut = 'en_attente'
        ORDER BY d.date_demande DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDemandeById($conn, $id_demande) {
    $stmt = $conn->prepare(
        "SELECT d.*, j.titre AS jeu_titre, j.id_utilisateur AS jeu_owner_id, u.pseudo AS utilisateur_pseudo
        FROM demande d
        LEFT JOIN jeu j ON d.id_jeu = j.id_jeu
        LEFT JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
        WHERE d.id_demande = ?"
    );
    $stmt->execute([(int)$id_demande]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function updateDemandeStatut($conn, $id_demande, $statut, $reponseAdmin = null) {
    $stmt = $conn->prepare("UPDATE demande SET statut = ?, reponse_admin = ? WHERE id_demande = ?");
    return $stmt->execute([$statut, $reponseAdmin, (int)$id_demande]);
}