<?php
require __DIR__ . '/nav/header.php';
?>
<main class="container my-5">
    <nav class="nav nav-pills mb-4">
        <?php $cur = $_GET['url'] ?? ''; ?>
        <a class="nav-link <?= $cur === 'back-office' ? 'active' : '' ?>" href="index.php?url=back-office">Dashboard</a>
        <a class="nav-link <?= $cur === 'admin_users' ? 'active' : '' ?>" href="index.php?url=admin_users">Gestion utilisateurs</a>
        <a class="nav-link <?= $cur === 'admin_content' ? 'active' : '' ?>" href="index.php?url=admin_content">Gestion contenu</a>
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
        <h1>Gestion des utilisateurs</h1>
    </section>
    <section class="mt-4">
        <h2>Liste des utilisateurs</h2>
        <?php if (!empty($users)): ?>
            <table class="table table-striped">
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
                                    <button type="submit" class="btn btn-sm btn-success">Enregistrer</button>
                                </form>
                                <?php if ($currentId !== null && $currentId === (int)$user['id_utilisateur']): ?>
                                    <button class="btn btn-sm btn-danger" disabled title="Vous ne pouvez pas supprimer votre propre compte">Supprimer</button>
                                <?php else: ?>
                                    <form method="post" action="index.php?url=admin/users/delete" class="d-inline ms-2" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                        <input type="hidden" name="id" value="<?= (int)$user['id_utilisateur'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-secondary mb-0">Aucun utilisateur à afficher.</div>
        <?php endif; ?>
        <form method="post" action="index.php?url=admin/users/create" class="mt-4">
            <h3>Ajouter un nouvel utilisateur</h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <select name="role" id="role" class="form-select" required>
                        <option value="" disabled selected>-- Rôle --</option>
                        <option value="2">Compte</option>
                        <option value="3">Administrateur</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="pseudo" class="form-control" placeholder="Pseudo" required>
                </div>
                <div class="col-md-4">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="col-md-4">
                    <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Créer l'utilisateur</button>
        </form>
    </section>
</main>

<?php require __DIR__ . '/nav/footer.php'; ?>   