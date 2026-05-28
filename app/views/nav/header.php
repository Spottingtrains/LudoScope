<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LudoScope</title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>uploads/ludoscope_favicon.svg">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts : Fredoka (titres H1) + Poppins (corps) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- Feuille de style personnalisée -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <script>const BASE_URL = "<?= BASE_URL ?>";</script>
</head>
<body>
    <header>
        <?php require __DIR__ . '/nav.php'; ?>
    </header>