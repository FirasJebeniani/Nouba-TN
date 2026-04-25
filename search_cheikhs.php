<?php
// ============================================================
// NoubaTN — search_cheikhs.php
// Recherche dans la table cheikhs avec critères
// Utilise : query(), prepare()+execute(), fetch(), fetchAll(), fetchObject()
// ============================================================
require_once 'connexion.php';

$pdo       = getConnexion();
$resultats = [];
$total     = 0;
$erreur    = '';

// ---- Paramètres de recherche ----
$motCle  = trim($_GET['q']        ?? '');
$critere = trim($_GET['critere']  ?? 'nom');
$statut  = trim($_GET['statut']   ?? 'tous'); // tous | vivant | decede

// ============================================================
// QUERY() — Statistiques globales (SELECT sans paramètre)
// ============================================================
$stats = $pdo->query('SELECT COUNT(*) AS total, AVG(noubas) AS moy_noubas FROM cheikhs')->fetch();

// ============================================================
// FETCHOBJECT() — Cheikh en vedette (le plus récent ajouté)
// ============================================================
$vedette = $pdo->query('SELECT * FROM cheikhs ORDER BY id DESC LIMIT 1')->fetchObject();

// ============================================================
// PREPARE() + EXECUTE() + FETCHALL() — Recherche filtrée
// ============================================================
try {
    // Construction de la requête selon le critère
    $colonnesValides = ['nom', 'ville', 'role'];
    if (!in_array($critere, $colonnesValides)) $critere = 'nom';

    $sql    = 'SELECT * FROM cheikhs WHERE 1=1';
    $params = [];

    if (!empty($motCle)) {
        $sql    .= ' AND ' . $critere . ' LIKE ?';
        $params[] = '%' . $motCle . '%';
    }

    if ($statut === 'vivant')  { $sql .= ' AND deces IS NULL';     }
    if ($statut === 'decede')  { $sql .= ' AND deces IS NOT NULL'; }

    $sql .= ' ORDER BY naissance ASC';

    $stmt      = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultats = $stmt->fetchAll();           // fetchAll() → tous les résultats
    $total     = count($resultats);

} catch (PDOException $e) {
    $erreur = 'Erreur de recherche : ' . htmlspecialchars($e->getMessage());
}

// ============================================================
// FETCH() — Récupérer un seul cheikh par ID (si demandé)
// ============================================================
$detailCheikh = null;
if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
    $stmtDetail = $pdo->prepare('SELECT * FROM cheikhs WHERE id = :id');
    $stmtDetail->execute([':id' => (int)$_GET['id']]);
    $detailCheikh = $stmtDetail->fetch();     // fetch() → un seul enregistrement
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NoubaTN — Recherche Cheikhs</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body        { font-family: sans-serif; padding: 2rem; background: #f5f5f5; }
        .container  { max-width: 960px; margin: auto; }
        .card       { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); margin-bottom: 2rem; }
        .stats      { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
        .stat-box   { flex: 1; background: #2c3e50; color: #fff; padding: 1rem; border-radius: 8px; text-align: center; }
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
        .badge-vivant  { background: #2ecc71; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 0.78rem; }
        .badge-decede  { background: #95a5a6; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 0.78rem; }
        .vedette    { background: #fef9e7; border-left: 4px solid #f39c12; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .detail-box { background: #eaf4fb; border-left: 4px solid #3498db; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .error-box  { background: #fdf2f2; border-left: 4px solid #e74c3c; padding: 1rem; border-radius: 4px; }
        .nav-links  { margin-bottom: 1.5rem; }
        .nav-links a { margin-right: 1rem; color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">

    <!-- Navigation -->
    <div class="nav-links">
        <a href="index.html">🏠 Accueil</a>
        <a href="insert_cheikh.php">➕ Ajouter un Cheikh</a>
        <a href="search_evenements.php">📅 Évènements</a>
    </div>

    <h1>🔍 Recherche — Les Cheikhs du Malouf</h1>

    <!-- Statistiques globales (via query()) -->
    <div class="stats">
        <div class="stat-box">
            <strong><?= $stats['total'] ?></strong>
            Cheikhs au total
        </div>
        <div class="stat-box" style="background:#27ae60;">
            <strong><?= number_format($stats['moy_noubas'], 1) ?></strong>
            Noubas en moyenne
        </div>
        <div class="stat-box" style="background:#e67e22;">
            <strong><?= $total ?></strong>
            Résultat(s) affiché(s)
        </div>
    </div>

    <!-- Cheikh en vedette (via fetchObject()) -->
    <?php if ($vedette): ?>
    <div class="vedette">
        ⭐ <strong>Dernier ajout :</strong>
        <?= htmlspecialchars($vedette->nom) ?> — <?= htmlspecialchars($vedette->role) ?>
        (<?= htmlspecialchars($vedette->ville) ?>)
    </div>
    <?php endif; ?>

    <!-- Détail d'un cheikh (via fetch()) -->
    <?php if ($detailCheikh): ?>
    <div class="detail-box">
        <strong>📋 Détail du cheikh #<?= $detailCheikh['id'] ?> :</strong>
        <?= htmlspecialchars($detailCheikh['nom']) ?> —
        <?= htmlspecialchars($detailCheikh['role']) ?> —
        <?= htmlspecialchars($detailCheikh['ville']) ?> —
        <?= $detailCheikh['naissance'] ?> / <?= $detailCheikh['deces'] ?? 'Présent' ?>
    </div>
    <?php endif; ?>

    <!-- Formulaire de recherche -->
    <div class="card">
        <form method="GET" action="search_cheikhs.php">
            <div>
                <label for="q">Mot-clé</label>
                <input type="text" id="q" name="q"
                       value="<?= htmlspecialchars($motCle) ?>"
                       placeholder="Ex: Tunis, Oudiste...">
            </div>
            <div>
                <label for="critere">Critère</label>
                <select id="critere" name="critere">
                    <option value="nom"   <?= $critere==='nom'   ? 'selected':'' ?>>Nom</option>
                    <option value="ville" <?= $critere==='ville' ? 'selected':'' ?>>Ville</option>
                    <option value="role"  <?= $critere==='role'  ? 'selected':'' ?>>Rôle</option>
                </select>
            </div>
            <div>
                <label for="statut">Statut</label>
                <select id="statut" name="statut">
                    <option value="tous"   <?= $statut==='tous'   ? 'selected':'' ?>>Tous</option>
                    <option value="vivant" <?= $statut==='vivant' ? 'selected':'' ?>>Vivants</option>
                    <option value="decede" <?= $statut==='decede' ? 'selected':'' ?>>Décédés</option>
                </select>
            </div>
            <button type="submit" class="btn">🔍 Rechercher</button>
            <a href="search_cheikhs.php" class="btn btn-red">✕ Réinitialiser</a>
        </form>

        <?php if ($erreur): ?>
            <div class="error-box"><?= $erreur ?></div>
        <?php elseif (empty($resultats)): ?>
            <p style="color:#e74c3c;">Aucun cheikh trouvé pour ces critères.</p>
        <?php else: ?>
            <!-- Tableau des résultats (via fetchAll()) -->
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Nom</th>
                        <th>Rôle</th>
                        <th>Ville</th>
                        <th>Période</th>
                        <th>Noubas</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resultats as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($c['photo'] ?? 'default_cheikh.jpg') ?>"
                                 alt="<?= htmlspecialchars($c['nom']) ?>"
                                 style="width:45px;height:45px;object-fit:cover;border-radius:50%;">
                        </td>
                        <td><strong><?= htmlspecialchars($c['nom']) ?></strong></td>
                        <td><?= htmlspecialchars($c['role']) ?></td>
                        <td><?= htmlspecialchars($c['ville']) ?></td>
                        <td><?= $c['naissance'] ?> – <?= $c['deces'] ?? 'Présent' ?></td>
                        <td style="text-align:center;"><?= $c['noubas'] ?></td>
                        <td>
                            <?php if (is_null($c['deces'])): ?>
                                <span class="badge-vivant">Vivant</span>
                            <?php else: ?>
                                <span class="badge-decede">Décédé</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="search_cheikhs.php?id=<?= $c['id'] ?>" class="btn" style="padding:3px 8px;font-size:0.78rem;">👁 Détail</a>
                            <a href="update_cheikh.php?id=<?= $c['id'] ?>" class="btn btn-green" style="padding:3px 8px;font-size:0.78rem;">✏ Modifier</a>
                            <a href="delete_cheikh.php?id=<?= $c['id'] ?>"
                               class="btn btn-red" style="padding:3px 8px;font-size:0.78rem;"
                               onclick="return confirm('Supprimer <?= htmlspecialchars(addslashes($c['nom'])) ?> ?')">
                               🗑 Supprimer
                            </a>
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
