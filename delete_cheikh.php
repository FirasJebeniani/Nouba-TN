<?php
// ============================================================
// NoubaTN — delete_cheikh.php
// Suppression d'un Cheikh par son ID
// Utilise exec() pour DELETE simple
// ============================================================
require_once 'connexion.php';

$id      = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$erreur  = '';
$succes  = false;
$cheikh  = null;

if ($id <= 0) {
    header('Location: search_cheikhs.php');
    exit;
}

$pdo = getConnexion();

// Récupérer le cheikh avant suppression (pour afficher son nom)
$stmt   = $pdo->prepare('SELECT * FROM cheikhs WHERE id = :id');
$stmt->execute([':id' => $id]);
$cheikh = $stmt->fetch();

if (!$cheikh) {
    $erreur = "Cheikh introuvable (ID=$id).";
}

// Suppression confirmée via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cheikh) {
    try {
        // exec() — DELETE direct (méthode requise par le sujet)
        $pdo->exec('DELETE FROM cheikhs WHERE id = ' . $id);
        $succes = true;
    } catch (PDOException $e) {
        $erreur = 'Erreur BD : ' . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NoubaTN — Supprimer un Cheikh</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body       { font-family: sans-serif; padding: 2rem; background: #f5f5f5; }
        .container { max-width: 600px; margin: auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .warning   { background: #fef9e7; border-left: 4px solid #f39c12; padding: 1.2rem; border-radius: 4px; margin-bottom: 1.5rem; }
        .success   { background: #eafaf1; border-left: 4px solid #2ecc71; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .error-box { background: #fdf2f2; border-left: 4px solid #e74c3c; padding: 1rem; border-radius: 4px; }
        table      { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; }
        th, td     { border: 1px solid #ddd; padding: 9px 12px; text-align: left; font-size: 0.9rem; }
        th         { background: #2c3e50; color: #fff; width: 35%; }
        .btn       { padding: .7rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; text-decoration: none; display: inline-block; }
        .btn-red   { background: #e74c3c; color: #fff; }
        .btn-gray  { background: #95a5a6; color: #fff; }
        .nav-links a { color: #3498db; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav-links" style="margin-bottom:1rem;">
        <a href="search_cheikhs.php">← Retour à la liste</a>
    </div>

    <h1>🗑 Supprimer un Cheikh</h1>

    <?php if ($erreur): ?>
        <div class="error-box">❌ <?= htmlspecialchars($erreur) ?></div>

    <?php elseif ($succes): ?>
        <div class="success">
            ✅ Le cheikh <strong><?= htmlspecialchars($cheikh['nom']) ?></strong> a été supprimé avec succès.
        </div>
        <a href="search_cheikhs.php" class="btn btn-gray">← Retour à la liste</a>

    <?php else: ?>
        <div class="warning">
            ⚠️ <strong>Attention !</strong> Cette action est irréversible.
        </div>

        <p>Vous êtes sur le point de supprimer le cheikh suivant :</p>

        <table>
            <tr><th>ID</th>         <td><?= $cheikh['id'] ?></td></tr>
            <tr><th>Nom</th>        <td><strong><?= htmlspecialchars($cheikh['nom']) ?></strong></td></tr>
            <tr><th>Rôle</th>       <td><?= htmlspecialchars($cheikh['role']) ?></td></tr>
            <tr><th>Ville</th>      <td><?= htmlspecialchars($cheikh['ville']) ?></td></tr>
            <tr><th>Naissance</th>  <td><?= $cheikh['naissance'] ?></td></tr>
        </table>

        <form method="POST" action="delete_cheikh.php?id=<?= $id ?>">
            <button type="submit" class="btn btn-red">🗑 Confirmer la suppression</button>
            <a href="search_cheikhs.php" class="btn btn-gray" style="margin-left:1rem;">Annuler</a>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
