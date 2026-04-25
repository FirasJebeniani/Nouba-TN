<?php
// ============================================================
// NoubaTN — delete_evenement.php
// Suppression d'un Évènement par son ID
// Utilise exec() pour DELETE simple
// ============================================================
require_once 'connexion.php';

$id      = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$erreur  = '';
$succes  = false;
$evt     = null;

if ($id <= 0) {
    header('Location: search_evenements.php');
    exit;
}

$pdo = getConnexion();

$stmt = $pdo->prepare('SELECT * FROM evenements WHERE id = :id');
$stmt->execute([':id' => $id]);
$evt  = $stmt->fetch();

if (!$evt) {
    $erreur = "Évènement introuvable (ID=$id).";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evt) {
    try {
        // exec() — DELETE direct
        $pdo->exec('DELETE FROM evenements WHERE id = ' . $id);
        $succes = true;
    } catch (PDOException $e) {
        $erreur = 'Erreur BD : ' . htmlspecialchars($e->getMessage());
    }
}

$dateAff = '';
if ($evt) {
    $dt = DateTime::createFromFormat('Y-m-d', $evt['date_evt']);
    $dateAff = $dt ? $dt->format('d/m/Y') : $evt['date_evt'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NoubaTN — Supprimer un Évènement</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body       { font-family: sans-serif; padding: 2rem; background: #f5f5f5; }
        .container { max-width: 600px; margin: auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .warning   { background: #fef9e7; border-left: 4px solid #f39c12; padding: 1.2rem; border-radius: 4px; margin-bottom: 1.5rem; }
        .success   { background: #eafaf1; border-left: 4px solid #2ecc71; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .error-box { background: #fdf2f2; border-left: 4px solid #e74c3c; padding: 1rem; border-radius: 4px; }
        table      { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; }
        th, td     { border: 1px solid #ddd; padding: 9px 12px; text-align: left; font-size: 0.9rem; }
        th         { background: #9b59b6; color: #fff; width: 35%; }
        .btn       { padding: .7rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; text-decoration: none; display: inline-block; }
        .btn-red   { background: #e74c3c; color: #fff; }
        .btn-gray  { background: #95a5a6; color: #fff; }
        .nav-links a { color: #3498db; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav-links" style="margin-bottom:1rem;">
        <a href="search_evenements.php">← Retour à la liste</a>
    </div>

    <h1>🗑 Supprimer un Évènement</h1>

    <?php if ($erreur): ?>
        <div class="error-box">❌ <?= htmlspecialchars($erreur) ?></div>

    <?php elseif ($succes): ?>
        <div class="success">
            ✅ L'évènement <strong><?= htmlspecialchars($evt['titre']) ?></strong> a été supprimé.
        </div>
        <a href="search_evenements.php" class="btn btn-gray">← Retour à la liste</a>

    <?php else: ?>
        <div class="warning">⚠️ <strong>Attention !</strong> Cette action est irréversible.</div>
        <p>Vous êtes sur le point de supprimer l'évènement suivant :</p>

        <table>
            <tr><th>ID</th>          <td><?= $evt['id'] ?></td></tr>
            <tr><th>Titre</th>       <td><strong><?= htmlspecialchars($evt['titre']) ?></strong></td></tr>
            <tr><th>Date</th>        <td><?= $dateAff ?></td></tr>
            <tr><th>Lieu</th>        <td><?= htmlspecialchars($evt['lieu']) ?></td></tr>
            <tr><th>Ville</th>       <td><?= htmlspecialchars($evt['ville']) ?></td></tr>
            <tr><th>Type</th>        <td><?= htmlspecialchars($evt['type']) ?></td></tr>
        </table>

        <form method="POST" action="delete_evenement.php?id=<?= $id ?>">
            <button type="submit" class="btn btn-red">🗑 Confirmer la suppression</button>
            <a href="search_evenements.php" class="btn btn-gray" style="margin-left:1rem;">Annuler</a>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
