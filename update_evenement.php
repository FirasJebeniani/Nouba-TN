<?php
// ============================================================
// NoubaTN — update_evenement.php
// Modification d'un Évènement existant
// Utilise prepare() + execute() POSITIONNEL (?) pour UPDATE
// ============================================================
require_once 'connexion.php';

$id      = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$erreurs = [];
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
    die('<p>Évènement introuvable.</p><a href="search_evenements.php">← Retour</a>');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre       = trim($_POST['titre']       ?? '');
    $date_evt    = trim($_POST['date_evt']    ?? '');
    $lieu        = trim($_POST['lieu']        ?? '');
    $ville       = trim($_POST['ville']       ?? '');
    $type        = trim($_POST['type']        ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validation PHP
    if (empty($titre))                                     $erreurs[] = 'Le titre est obligatoire.';
    elseif (strlen($titre) < 5)                           $erreurs[] = 'Minimum 5 caractères pour le titre.';
    if (empty($date_evt))                                  $erreurs[] = 'La date est obligatoire.';
    if (empty($lieu))                                      $erreurs[] = 'Le lieu est obligatoire.';
    if (empty($ville))                                     $erreurs[] = 'La ville est obligatoire.';
    $typesValides = ['Concert','Festival','Conférence','Atelier','Exposition','Gala'];
    if (!in_array($type, $typesValides))                   $erreurs[] = 'Type invalide.';

    // UPDATE avec prepare() + execute() POSITIONNEL (?)
    if (empty($erreurs)) {
        try {
            $stmt = $pdo->prepare(
                'UPDATE evenements
                 SET titre=?, date_evt=?, lieu=?, ville=?, type=?, description=?
                 WHERE id=?'
            );
            $stmt->execute([$titre, $date_evt, $lieu, $ville, $type, $description ?: null, $id]);
            $succes = true;
            // Recharger
            $s2  = $pdo->prepare('SELECT * FROM evenements WHERE id = :id');
            $s2->execute([':id' => $id]);
            $evt = $s2->fetch();
        } catch (PDOException $e) {
            $erreurs[] = 'Erreur BD : ' . htmlspecialchars($e->getMessage());
        }
    }
}

$typesEvenements = ['Concert','Festival','Conférence','Atelier','Exposition','Gala'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NoubaTN — Modifier un Évènement</title>
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
        <a href="search_evenements.php">← Retour à la liste</a>
        <a href="delete_evenement.php?id=<?= $id ?>" style="color:#e74c3c;">🗑 Supprimer</a>
    </div>

    <h1>✏ Modifier l'Évènement #<?= $id ?></h1>

    <?php if ($succes): ?>
        <div class="success">✅ L'évènement a été modifié avec succès !</div>
    <?php endif; ?>

    <?php if (!empty($erreurs)): ?>
        <div class="error-box">
            <strong>❌ Erreurs :</strong>
            <ul><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="update_evenement.php?id=<?= $id ?>" onsubmit="return validerUpdateEvt()">

        <div class="form-group">
            <label>Titre <span class="req">*</span></label>
            <input type="text" name="titre" id="titre"
                   value="<?= htmlspecialchars($evt['titre']) ?>" required>
            <span class="err" id="err-titre"></span>
        </div>

        <div class="row">
            <div class="form-group">
                <label>Date <span class="req">*</span></label>
                <input type="date" name="date_evt" id="date_evt"
                       value="<?= htmlspecialchars($evt['date_evt']) ?>" required>
                <span class="err" id="err-date"></span>
            </div>
            <div class="form-group">
                <label>Type <span class="req">*</span></label>
                <select name="type" id="type" required>
                    <?php foreach ($typesEvenements as $t): ?>
                        <option value="<?= $t ?>" <?= $evt['type']===$t ? 'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <label>Lieu <span class="req">*</span></label>
                <input type="text" name="lieu" id="lieu"
                       value="<?= htmlspecialchars($evt['lieu']) ?>" required>
                <span class="err" id="err-lieu"></span>
            </div>
            <div class="form-group">
                <label>Ville <span class="req">*</span></label>
                <input type="text" name="ville" id="ville"
                       value="<?= htmlspecialchars($evt['ville']) ?>" required>
                <span class="err" id="err-ville"></span>
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description"><?= htmlspecialchars($evt['description'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn">💾 Enregistrer les modifications</button>
    </form>
</div>

<script>
function validerUpdateEvt() {
    let ok = true;
    ['titre','date','lieu','ville'].forEach(function(id) {
        var el = document.getElementById('err-' + id);
        if (el) el.textContent = '';
    });
    if (document.getElementById('titre').value.trim().length < 5) {
        document.getElementById('err-titre').textContent = 'Minimum 5 caractères.';
        ok = false;
    }
    if (!document.getElementById('date_evt').value) {
        document.getElementById('err-date').textContent = 'Date obligatoire.';
        ok = false;
    }
    if (document.getElementById('lieu').value.trim() === '') {
        document.getElementById('err-lieu').textContent = 'Lieu obligatoire.';
        ok = false;
    }
    if (document.getElementById('ville').value.trim() === '') {
        document.getElementById('err-ville').textContent = 'Ville obligatoire.';
        ok = false;
    }
    return ok;
}
</script>
</body>
</html>
