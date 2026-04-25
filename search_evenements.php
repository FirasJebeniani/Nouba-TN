<?php
// ============================================================
// NoubaTN — search_evenements.php
// Recherche dans la table evenements
// Utilise : query(), prepare()+execute(), fetchAll(), fetch()
// ============================================================
require_once 'connexion.php';

$pdo       = getConnexion();
$resultats = [];
$erreur    = '';

$motCle  = trim($_GET['q']      ?? '');
$critere = trim($_GET['critere'] ?? 'titre');
$type    = trim($_GET['type']    ?? 'tous');

// QUERY() — Statistiques globales
$stats = $pdo->query('SELECT COUNT(*) AS total FROM evenements')->fetch();

// PREPARE() + EXECUTE() + FETCHALL() — Recherche filtrée
try {
    $colonnesValides = ['titre', 'ville', 'lieu'];
    if (!in_array($critere, $colonnesValides)) $critere = 'titre';

    $sql    = 'SELECT * FROM evenements WHERE 1=1';
    $params = [];

    if (!empty($motCle)) {
        $sql    .= ' AND ' . $critere . ' LIKE ?';
        $params[] = '%' . $motCle . '%';
    }
    if ($type !== 'tous' && !empty($type)) {
        $sql    .= ' AND type = ?';
        $params[] = $type;
    }
    $sql .= ' ORDER BY date_evt ASC';

    $stmt      = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultats = $stmt->fetchAll();

} catch (PDOException $e) {
    $erreur = htmlspecialchars($e->getMessage());
}

// FETCH() — Détail d'un évènement par ID
$detail = null;
if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
    $s = $pdo->prepare('SELECT * FROM evenements WHERE id = :id');
    $s->execute([':id' => (int)$_GET['id']]);
    $detail = $s->fetch();
}

$typesEvenements = ['Concert','Festival','Conférence','Atelier','Exposition','Gala'];
$badgeColors = [
    'Concert'     => '#3498db',
    'Festival'    => '#9b59b6',
    'Conférence'  => '#e67e22',
    'Atelier'     => '#1abc9c',
    'Exposition'  => '#e74c3c',
    'Gala'        => '#f39c12',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NoubaTN — Recherche Évènements</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body        { font-family: sans-serif; padding: 2rem; background: #f5f5f5; }
        .container  { max-width: 960px; margin: auto; }
        .card       { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); margin-bottom: 2rem; }
        .stats      { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
        .stat-box   { flex: 1; background: #9b59b6; color: #fff; padding: 1rem; border-radius: 8px; text-align: center; }
        .stat-box strong { display: block; font-size: 2rem; }
        form        { display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end; margin-bottom: 1.5rem; }
        form label  { display: block; font-size: 0.85rem; color: #555; margin-bottom: 4px; }
        form input, form select { padding: .6rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem; }
        .btn        { padding: .65rem 1.4rem; background: #3498db; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 0.9rem; }
        .btn-red    { background: #e74c3c; }
        .btn-green  { background: #27ae60; }
        table       { width: 100%; border-collapse: collapse; }
        th, td      { border: 1px solid #ddd; padding: 10px 12px; text-align: left; font-size: 0.88rem; }
        th          { background: #2c3e50; color: #fff; }
        tr:hover td { background: #f0f4f8; }
        .badge      { padding: 2px 10px; border-radius: 12px; font-size: 0.8rem; color: #fff; }
        .nav-links a { margin-right: 1rem; color: #3498db; text-decoration: none; }
        .detail-box { background: #eaf4fb; border-left: 4px solid #3498db; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav-links" style="margin-bottom:1.5rem;">
        <a href="index.html">🏠 Accueil</a>
        <a href="search_cheikhs.php">🎵 Cheikhs</a>
        <a href="insert_evenement.php">➕ Ajouter un Évènement</a>
    </div>

    <h1>🔍 Recherche — Évènements du Malouf</h1>

    <div class="stats">
        <div class="stat-box">
            <strong><?= $stats['total'] ?></strong>
            Évènements au total
        </div>
        <div class="stat-box" style="background:#27ae60;">
            <strong><?= count($resultats) ?></strong>
            Résultat(s) affiché(s)
        </div>
    </div>

    <?php if ($detail): ?>
    <div class="detail-box">
        <strong>📋 Détail de l'évènement #<?= $detail['id'] ?> :</strong>
        <?= htmlspecialchars($detail['titre']) ?> —
        <?= htmlspecialchars($detail['lieu']) ?>, <?= htmlspecialchars($detail['ville']) ?> —
        <?= htmlspecialchars($detail['date_evt']) ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <form method="GET" action="search_evenements.php">
            <div>
                <label>Mot-clé</label>
                <input type="text" name="q" value="<?= htmlspecialchars($motCle) ?>" placeholder="Ex: Tunis, Concert...">
            </div>
            <div>
                <label>Critère</label>
                <select name="critere">
                    <option value="titre" <?= $critere==='titre' ? 'selected':'' ?>>Titre</option>
                    <option value="ville" <?= $critere==='ville' ? 'selected':'' ?>>Ville</option>
                    <option value="lieu"  <?= $critere==='lieu'  ? 'selected':'' ?>>Lieu</option>
                </select>
            </div>
            <div>
                <label>Type</label>
                <select name="type">
                    <option value="tous">Tous les types</option>
                    <?php foreach ($typesEvenements as $t): ?>
                        <option value="<?= $t ?>" <?= $type===$t ? 'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn">🔍 Rechercher</button>
            <a href="search_evenements.php" class="btn btn-red">✕ Réinitialiser</a>
        </form>

        <?php if ($erreur): ?>
            <p style="color:#e74c3c;"><?= $erreur ?></p>
        <?php elseif (empty($resultats)): ?>
            <p style="color:#e74c3c;">Aucun évènement trouvé.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Titre</th><th>Date</th><th>Lieu</th>
                        <th>Ville</th><th>Type</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resultats as $e):
                    $bg = $badgeColors[$e['type']] ?? '#95a5a6';
                    $dateObj = DateTime::createFromFormat('Y-m-d', $e['date_evt']);
                    $dateAff = $dateObj ? $dateObj->format('d/m/Y') : $e['date_evt'];
                ?>
                    <tr>
                        <td><?= $e['id'] ?></td>
                        <td><strong><?= htmlspecialchars($e['titre']) ?></strong></td>
                        <td><?= $dateAff ?></td>
                        <td><?= htmlspecialchars($e['lieu']) ?></td>
                        <td><?= htmlspecialchars($e['ville']) ?></td>
                        <td><span class="badge" style="background:<?= $bg ?>;"><?= htmlspecialchars($e['type']) ?></span></td>
                        <td>
                            <a href="search_evenements.php?id=<?= $e['id'] ?>" class="btn" style="padding:3px 8px;font-size:0.78rem;">👁</a>
                            <a href="update_evenement.php?id=<?= $e['id'] ?>" class="btn btn-green" style="padding:3px 8px;font-size:0.78rem;">✏</a>
                            <a href="delete_evenement.php?id=<?= $e['id'] ?>"
                               class="btn btn-red" style="padding:3px 8px;font-size:0.78rem;"
                               onclick="return confirm('Supprimer cet évènement ?')">🗑</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
