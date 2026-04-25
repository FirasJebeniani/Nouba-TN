<?php
// ============================================================
// NoubaTN — update_cheikh.php
// Modification d'un Cheikh existant
// Utilise prepare() + execute() NOMMÉ pour UPDATE
// ============================================================
require_once 'connexion.php';

$id      = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$erreurs = [];
$succes  = false;
$cheikh  = null;

if ($id <= 0) {
    header('Location: search_cheikhs.php');
    exit;
}

$pdo = getConnexion();

// Charger le cheikh à modifier
$stmt   = $pdo->prepare('SELECT * FROM cheikhs WHERE id = :id');
$stmt->execute([':id' => $id]);
$cheikh = $stmt->fetch();

if (!$cheikh) {
    die('<p>Cheikh introuvable.</p><a href="search_cheikhs.php">← Retour</a>');
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom       = trim($_POST['nom']       ?? '');
    $nom_arabe = trim($_POST['nom_arabe'] ?? '');
    $role      = trim($_POST['role']      ?? '');
    $naissance = (int)($_POST['naissance'] ?? 0);
    $deces     = trim($_POST['deces']     ?? '');
    $ville     = trim($_POST['ville']     ?? '');
    $noubas    = (int)($_POST['noubas']   ?? 0);
    $bio       = trim($_POST['bio']       ?? '');
    $decesVal  = ($deces === '' || $deces === '0') ? null : (int)$deces;

    // Validation PHP
    if (empty($nom))                                  $erreurs[] = 'Le nom est obligatoire.';
    if (empty($role))                                 $erreurs[] = 'Le rôle est obligatoire.';
    if (empty($ville))                                $erreurs[] = 'La ville est obligatoire.';
    if ($naissance < 1800 || $naissance > 2025)       $erreurs[] = 'Année de naissance invalide.';
    if ($decesVal !== null && $decesVal <= $naissance) $erreurs[] = 'L\'année de décès doit être postérieure à la naissance.';
    if ($noubas < 0 || $noubas > 13)                 $erreurs[] = 'Nombre de noubas invalide (0–13).';

    // UPDATE avec prepare() + execute() NOMMÉ (:param)
    if (empty($erreurs)) {
        try {
            $stmt = $pdo->prepare(
                'UPDATE cheikhs
                 SET nom=:nom, nom_arabe=:nom_arabe, role=:role,
                     naissance=:naissance, deces=:deces,
                     ville=:ville, noubas=:noubas, bio=:bio
                 WHERE id=:id'
            );
            $stmt->execute([
                ':nom'       => $nom,
                ':nom_arabe' => $nom_arabe ?: null,
                ':role'      => $role,
                ':naissance' => $naissance,
                ':deces'     => $decesVal,
                ':ville'     => $ville,
                ':noubas'    => $noubas,
                ':bio'       => $bio ?: null,
                ':id'        => $id,
            ]);
            $succes = true;
            // Recharger les données mises à jour
            $stmt2  = $pdo->prepare('SELECT * FROM cheikhs WHERE id = :id');
            $stmt2->execute([':id' => $id]);
            $cheikh = $stmt2->fetch();
        } catch (PDOException $e) {
            $erreurs[] = 'Erreur BD : ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NoubaTN — Modifier un Cheikh</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body       { font-family: sans-serif; padding: 2rem; background: #f5f5f5; }
        .container { max-width: 650px; margin: auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        h1         { color: #2c3e50; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1.2rem; }
        label      { display: block; font-size: 0.85rem; font-weight: bold; color: #555; margin-bottom: 4px; }
        input, select, textarea { width: 100%; padding: .65rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem; box-sizing: border-box; }
        textarea   { resize: vertical; height: 90px; }
        .row       { display: flex; gap: 1rem; }
        .row .form-group { flex: 1; }
        .btn       { width: 100%; padding: .85rem; background: #27ae60; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; margin-top: .5rem; }
        .btn:hover { background: #219a52; }
        .success   { background: #eafaf1; border-left: 4px solid #2ecc71; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .error-box { background: #fdf2f2; border-left: 4px solid #e74c3c; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .nav-links a { color: #3498db; text-decoration: none; margin-right: 1rem; font-size: 0.9rem; }
        span.req   { color: #e74c3c; }
        span.err   { color: #e74c3c; font-size: 0.8rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav-links" style="margin-bottom:1rem;">
        <a href="search_cheikhs.php">← Retour à la liste</a>
        <a href="delete_cheikh.php?id=<?= $id ?>" style="color:#e74c3c;">🗑 Supprimer ce cheikh</a>
    </div>

    <h1>✏ Modifier le Cheikh #<?= $id ?></h1>

    <?php if ($succes): ?>
        <div class="success">✅ Les modifications ont été enregistrées avec succès !</div>
    <?php endif; ?>

    <?php if (!empty($erreurs)): ?>
        <div class="error-box">
            <strong>❌ Erreurs :</strong>
            <ul><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="update_cheikh.php?id=<?= $id ?>" onsubmit="return validerUpdate()">

        <div class="row">
            <div class="form-group">
                <label>Nom <span class="req">*</span></label>
                <input type="text" name="nom" id="nom"
                       value="<?= htmlspecialchars($cheikh['nom']) ?>" required>
                <span class="err" id="err-nom"></span>
            </div>
            <div class="form-group">
                <label>Nom arabe</label>
                <input type="text" name="nom_arabe"
                       value="<?= htmlspecialchars($cheikh['nom_arabe'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Rôle <span class="req">*</span></label>
            <input type="text" name="role" id="role"
                   value="<?= htmlspecialchars($cheikh['role']) ?>" required>
            <span class="err" id="err-role"></span>
        </div>

        <div class="row">
            <div class="form-group">
                <label>Naissance <span class="req">*</span></label>
                <input type="number" name="naissance" id="naissance"
                       min="1800" max="2025"
                       value="<?= $cheikh['naissance'] ?>" required>
                <span class="err" id="err-naissance"></span>
            </div>
            <div class="form-group">
                <label>Décès (vide si vivant)</label>
                <input type="number" name="deces" min="1800" max="2025"
                       value="<?= $cheikh['deces'] ?? '' ?>">
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <label>Ville <span class="req">*</span></label>
                <input type="text" name="ville" id="ville"
                       value="<?= htmlspecialchars($cheikh['ville']) ?>" required>
                <span class="err" id="err-ville"></span>
            </div>
            <div class="form-group">
                <label>Noubas (0–13)</label>
                <input type="number" name="noubas" min="0" max="13"
                       value="<?= $cheikh['noubas'] ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Biographie</label>
            <textarea name="bio"><?= htmlspecialchars($cheikh['bio'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn">💾 Enregistrer les modifications</button>
    </form>
</div>

<script>
function validerUpdate() {
    let ok = true;
    ['nom','role','naissance','ville'].forEach(function(id) {
        var el = document.getElementById('err-' + id);
        if (el) el.textContent = '';
    });

    if (document.getElementById('nom').value.trim() === '') {
        document.getElementById('err-nom').textContent = 'Le nom est obligatoire.';
        ok = false;
    }
    if (document.getElementById('role').value.trim() === '') {
        document.getElementById('err-role').textContent = 'Le rôle est obligatoire.';
        ok = false;
    }
    let naiss = parseInt(document.getElementById('naissance').value);
    if (isNaN(naiss) || naiss < 1800 || naiss > 2025) {
        document.getElementById('err-naissance').textContent = 'Année invalide.';
        ok = false;
    }
    if (document.getElementById('ville').value.trim() === '') {
        document.getElementById('err-ville').textContent = 'La ville est obligatoire.';
        ok = false;
    }
    return ok;
}
</script>
</body>
</html>
