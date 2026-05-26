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
        <section>
        </section>
        <!-- TODO: ajouter une recherche -->
        <section class="mt-4">
            <h2>Liste des utilisateurs</h2>
            <div class="table-wrapper">
                <?php if (!empty($users)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pseudo</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['id_utilisateur']) ?></td>
                                    <td>
                                        <form method="post" action="index.php?url=admin/users/edit&id=<?= (int)$user['id_utilisateur'] ?>" class="m-0">
                                            <input type="text" name="pseudo" class="form-control form-control-sm" value="<?= htmlspecialchars($user['pseudo']) ?>">
                                    </td>
                                    <td>
                                            <input type="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($user['email']) ?>">
                                    </td>
                                        <td><?= htmlspecialchars(($user['id_role'] ?? $user['role']) == 3 ? 'Administrateur' : (($user['id_role'] ?? $user['role']) == 2 ? 'Compte' : 'Visiteur')) ?></td>
                                    <td>
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        </form>
                                        <?php if ($currentId !== null && $currentId === (int)$user['id_utilisateur']): ?>
                                            <button class="btn btn-danger" disabled title="Vous ne pouvez pas supprimer votre propre compte">Supprimer</button>
                                        <?php else: ?>
                                            <form method="post" action="index.php?url=admin/users/delete" class="d-inline ms-2" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                                <input type="hidden" name="id" value="<?= (int)$user['id_utilisateur'] ?>">
                                                <button type="submit" class="btn btn-danger">Supprimer</button>
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