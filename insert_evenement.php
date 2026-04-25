<?php
// ============================================================
// NoubaTN — insert_evenement.php
// Formulaire d'ajout d'un Évènement + INSERT en base de données
// ============================================================
require_once 'connexion.php';

$erreurs = [];
$succes  = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Récupération des champs
    $titre       = trim($_POST['titre']       ?? '');
    $date_evt    = trim($_POST['date_evt']    ?? '');
    $lieu        = trim($_POST['lieu']        ?? '');
    $ville       = trim($_POST['ville']       ?? '');
    $type        = trim($_POST['type']        ?? '');
    $description = trim($_POST['description'] ?? '');

    // 2. Traitement de la photo (optionnelle)
    $photo = null;
    if (!empty($_FILES['photo']['name'])) {
        $extAutorisees = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $extAutorisees)) {
            $erreurs[] = 'Format de photo invalide (JPG, PNG, WEBP uniquement).';
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $erreurs[] = 'La photo ne doit pas dépasser 2 Mo.';
        } else {
            $dossier = 'uploads/evenements/';
            if (!is_dir($dossier)) mkdir($dossier, 0755, true);
            $nomFichier = uniqid('evt_') . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dossier . $nomFichier)) {
                $photo = $dossier . $nomFichier;
            } else {
                $erreurs[] = 'Erreur lors de l\'upload de la photo.';
            }
        }
    }

    // 3. Validation PHP
    $typesValides = ['Concert','Festival','Conférence','Atelier','Exposition','Gala'];

    if (empty($titre))                          $erreurs[] = 'Le titre est obligatoire.';
    elseif (strlen($titre) < 5)                $erreurs[] = 'Le titre doit contenir au moins 5 caractères.';
    if (empty($date_evt))                       $erreurs[] = 'La date est obligatoire.';
    if (empty($lieu))                           $erreurs[] = 'Le lieu est obligatoire.';
    if (empty($ville))                          $erreurs[] = 'La ville est obligatoire.';
    if (!in_array($type, $typesValides))        $erreurs[] = 'Type invalide.';

    // 4. INSERT uniquement si pas d'erreurs
    if (empty($erreurs)) {
        try {
            $pdo  = getConnexion();
            $stmt = $pdo->prepare(
                'INSERT INTO evenements (titre, date_evt, lieu, ville, type, description, photo)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $titre,
                $date_evt,
                $lieu,
                $ville,
                $type,
                $description ?: null,
                $photo
            ]);
            $succes  = true;
            $message = "✅ L'évènement \"$titre\" a été ajouté avec succès !";
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
    <title>NoubaTN — Ajouter un Évènement</title>
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
        .btn       { width: 100%; padding: .85rem; background: #9b59b6; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; margin-top: .5rem; }
        .btn:hover { background: #8e44ad; }
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
        <a href="search_evenements.php">← Liste des évènements</a>
        <a href="index.html">🏠 Accueil</a>
    </div>

    <h1>➕ Ajouter un Évènement</h1>

    <?php if ($succes): ?>
        <div class="success">
            <?= htmlspecialchars($message) ?>
            <br><a href="search_evenements.php">→ Voir la liste des évènements</a>
        </div>
    <?php endif; ?>

    <?php if (!empty($erreurs)): ?>
        <div class="error-box">
            <strong>❌ Erreurs :</strong>
            <ul><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="insert_evenement.php" enctype="multipart/form-data" onsubmit="return validerFormEvenement()">

        <div class="form-group">
            <label>Titre <span class="req">*</span></label>
            <input type="text" name="titre" id="titre"
                   placeholder="Ex: Festival du Malouf de Testour"
                   value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>
            <span class="err" id="err-titre"></span>
        </div>

        <!-- CHAMP DATE MANQUANT — ajouté ici -->
        <div class="form-group">
            <label>Date de l'événement <span class="req">*</span></label>
            <input type="date" name="date_evt" id="date_evt"
                   value="<?= htmlspecialchars($_POST['date_evt'] ?? '') ?>" required>
            <span class="err" id="err-date"></span>
        </div>

        <div class="row">
            <div class="form-group">
                <label>Photo de l'événement</label>
                <input type="file" name="photo" id="photo" accept="image/*">
                <small style="color:#888;">Formats acceptés : JPG, PNG, WEBP (max 2 Mo). Optionnel.</small>
                <span class="err" id="err-photo"></span>
            </div>
            <div class="form-group">
                <label>Type <span class="req">*</span></label>
                <select name="type" id="type" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach ($typesEvenements as $t): ?>
                        <option value="<?= $t ?>" <?= ($_POST['type'] ?? '') === $t ? 'selected' : '' ?>>
                            <?= $t ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="err" id="err-type"></span>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <label>Lieu <span class="req">*</span></label>
                <input type="text" name="lieu" id="lieu"
                       placeholder="Ex: Théâtre Municipal"
                       value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>" required>
                <span class="err" id="err-lieu"></span>
            </div>
            <div class="form-group">
                <label>Ville <span class="req">*</span></label>
                <input type="text" name="ville" id="ville"
                       placeholder="Ex: Tunis"
                       value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>" required>
                <span class="err" id="err-ville"></span>
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Description courte de l'évènement..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn">➕ Ajouter l'Évènement</button>
    </form>
</div>

<script>
function validerFormEvenement() {
    let ok = true;

    // Réinitialiser les messages d'erreur
    ['titre', 'date', 'type', 'lieu', 'ville'].forEach(function(id) {
        var el = document.getElementById('err-' + id);
        if (el) el.textContent = '';
    });

    let titre = document.getElementById('titre').value.trim();
    if (titre === '') {
        document.getElementById('err-titre').textContent = 'Le titre est obligatoire.';
        ok = false;
    } else if (titre.length < 5) {
        document.getElementById('err-titre').textContent = 'Minimum 5 caractères.';
        ok = false;
    }

    let date = document.getElementById('date_evt').value;
    if (date === '') {
        document.getElementById('err-date').textContent = 'La date est obligatoire.';
        ok = false;
    }

    let type = document.getElementById('type').value;
    if (type === '') {
        document.getElementById('err-type').textContent = 'Veuillez choisir un type.';
        ok = false;
    }

    let lieu = document.getElementById('lieu').value.trim();
    if (lieu === '') {
        document.getElementById('err-lieu').textContent = 'Le lieu est obligatoire.';
        ok = false;
    }

    let ville = document.getElementById('ville').value.trim();
    if (ville === '') {
        document.getElementById('err-ville').textContent = 'La ville est obligatoire.';
        ok = false;
    }

    return ok;
}
</script>
</body>
</html>