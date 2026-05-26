<?php
require __DIR__ . '/nav/header.php';
?>
<main class="small-padding">
    <div class="up-down-padding">
        <h1 class="centered">Gestion des utilisateurs</h1>
        <nav class="tab-nav">
            <?php $cur = $_GET['url'] ?? ''; ?>
            <a class="tab-link <?= $cur === 'back-office' ? 'active' : '' ?>" href="index.php?url=back-office">Dashboard</a>
            <a class="tab-link <?= $cur === 'admin_users' ? 'active' : '' ?>" href="index.php?url=admin_users">Gestion utilisateurs</a>
            <a class="tab-link <?= $cur === 'admin_content' ? 'active' : '' ?>" href="index.php?url=admin_content">Gestion contenu</a>
        </nav>
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php $currentId = $currentId ?? null; ?>
        <!-- Barre de recherche, filtres et tri (côté client) -->
        <section class="mt-4">
            <h2>Liste des utilisateurs</h2>

            <form id="user-controls" class="row g-2 mb-3" onsubmit="return false;">
                <div class="col-md-6">
                    <input id="user-search" type="search" class="form-control" placeholder="Rechercher (ID, pseudo, email)">
                </div>
                <div class="col-md-2">
                    <select id="filter-role" class="form-select">
                        <option value="">Tous les rôles</option>
                        <option value="3">Administrateur</option>
                        <option value="2">Compte</option>
                        <option value="1">Visiteur</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input id="filter-lastlogin" type="date" class="form-control" title="Afficher si dernière connexion >= cette date">
                </div>
                <div class="col-md-2">
                    <input id="filter-registered" type="date" class="form-control" title="Afficher si inscription >= cette date">
                </div>
            </form>

            <div class="table-wrapper">
                <?php if (!empty($users)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th data-sort="id">ID</th>
                                <th data-sort="role">Rôle</th>
                                <th data-sort="pseudo">Pseudo</th>
                                <th data-sort="email">Email</th>
                                <th data-sort="registered">Date d'inscription</th>
                                <th data-sort="lastlogin">Dernière connexion</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr
                                    data-id="<?= (int)$user['id_utilisateur'] ?>"
                                    data-pseudo="<?= htmlspecialchars($user['pseudo']) ?>"
                                    data-email="<?= htmlspecialchars($user['email']) ?>"
                                    data-role="<?= htmlspecialchars($user['id_role'] ?? $user['role'] ?? '') ?>"
                                    data-lastlogin="<?= htmlspecialchars($user['derniere_connexion'] ?? '') ?>"
                                    data-registered="<?= htmlspecialchars($user['date_inscription'] ?? '') ?>"
                                    data-search="<?= htmlspecialchars(trim(($user['id_utilisateur'] ?? '') . ' ' . ($user['pseudo'] ?? '') . ' ' . ($user['email'] ?? ''))) ?>">
                                    <td class="cell-id"><?= htmlspecialchars($user['id_utilisateur']) ?></td>
                                    <td class="cell-role">
                                        <?= htmlspecialchars(($user['id_role'] ?? $user['role']) == 3 ? 'Administrateur' : (($user['id_role'] ?? $user['role']) == 2 ? 'Compte' : 'Visiteur')) ?>
                                    </td>
                                    <td class="cell-pseudo">
                                        <form method="post" action="index.php?url=admin/users/edit&id=<?= (int)$user['id_utilisateur'] ?>" class="m-0">
                                            <input type="text" name="pseudo" class="form-control form-control-sm" value="<?= htmlspecialchars($user['pseudo']) ?>">
                                    </td>
                                    <td class="cell-email">
                                            <input type="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($user['email']) ?>">
                                    </td>
                                    <td class="cell-registered">
                                        <?= htmlspecialchars($user['date_inscription_display'] ?? ($user['date_inscription'] ?? '')) ?>
                                    </td>
                                    <td class="cell-lastlogin">
                                        <?= htmlspecialchars($user['derniere_connexion_display'] ?? ($user['derniere_connexion'] ?? '')) ?>
                                    </td>
                                    <td>
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        </form>
                                        <?php if ($currentId !== null && $currentId === (int)$user['id_utilisateur']): ?>
                                            <button class="btn btn-danger" disabled title="Vous ne pouvez pas supprimer votre propre compte">Supprimer</button>
                                        <?php else: ?>
                                            <form method="post" action="index.php?url=admin/users/delete" class="d-inline ms-2 admin-delete-form" data-item-type="utilisateur" onsubmit="return false;">
                                                <input type="hidden" name="id" value="<?= (int)$user['id_utilisateur'] ?>">
                                                <button type="button" class="btn btn-danger admin-delete-btn">Supprimer</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
            </div>
            <?php else: ?>
                <div class="alert alert-secondary mb-0">Aucun utilisateur à afficher.</div>
            <?php endif; ?>
        </section>
        <section>
            <form method="post" action="index.php?url=admin/users/create" class="mt-4">
                <h3>Ajouter un nouvel utilisateur</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <select name="role" id="role" class="form-select" required>
                            <option value="" disabled selected>-- Rôle --</option>
                            <option value="2">Compte</option>
                            <option value="3">Administrateur</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="pseudo" class="form-control" placeholder="Pseudo" required>
                    </div>
                    <div class="col-md-6">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="col-md-6">
                        <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Créer l'utilisateur</button>
            </form>
        </section>
    </div>
</main>

<?php require __DIR__ . '/nav/footer.php'; ?>   