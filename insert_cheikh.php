<?php
// ============================================================
// NoubaTN — insert_cheikh.php
// Formulaire d'ajout d'un Cheikh + INSERT en base de données
// ============================================================
require_once 'connexion.php';

$message = '';
$erreurs = [];
$succes  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Récupération
    $nom       = trim($_POST['nom']       ?? '');
    $nom_arabe = trim($_POST['nom_arabe'] ?? '');
    $role      = trim($_POST['role']      ?? '');
    $naissance = (int)($_POST['naissance'] ?? 0);
    $deces     = trim($_POST['deces']     ?? '');
    $ville     = trim($_POST['ville']     ?? '');
    $noubas    = (int)($_POST['noubas']   ?? 0);
    $bio       = trim($_POST['bio']       ?? '');
    $decesVal  = ($deces === '' || $deces === '0') ? null : (int)$deces;

    // 2. Validation PHP
    if (empty($nom))                               $erreurs[] = 'Le nom est obligatoire.';
    if (empty($role))                              $erreurs[] = 'Le rôle est obligatoire.';
    if (empty($ville))                             $erreurs[] = 'La ville est obligatoire.';
    if ($naissance < 1800 || $naissance > 2025)   $erreurs[] = 'Année de naissance invalide.';
    if ($decesVal !== null && $decesVal <= $naissance) $erreurs[] = 'L\'année de décès doit être postérieure à la naissance.';
    if ($noubas < 0 || $noubas > 13)              $erreurs[] = 'Nombre de noubas invalide (0–13).';

    // 3. INSERT avec prepare() + execute() NOMMÉ (:param)
    if (empty($erreurs)) {
        try {
            $pdo  = getConnexion();
            $stmt = $pdo->prepare(
                'INSERT INTO cheikhs (nom, nom_arabe, role, naissance, deces, ville, noubas, bio, photo)
                 VALUES (:nom, :nom_arabe, :role, :naissance, :deces, :ville, :noubas, :bio, :photo)'
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
                ':photo'     => 'default_cheikh.jpg',
            ]);
            $succes  = true;
            $message = "✅ Le cheikh \"$nom\" a été ajouté avec succès !";
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
    <title>NoubaTN — Ajouter un Cheikh</title>
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
        .btn       { width: 100%; padding: .85rem; background: #2c3e50; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; margin-top: .5rem; }
        .btn:hover { background: #3498db; }
        .success   { background: #eafaf1; border-left: 4px solid #2ecc71; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .error-box { background: #fdf2f2; border-left: 4px solid #e74c3c; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .nav-links a { color: #3498db; text-decoration: none; margin-right: 1rem; font-size: 0.9rem; }
        span.req   { color: #e74c3c; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav-links" style="margin-bottom:1rem;">
        <a href="search_cheikhs.php">← Liste des cheikhs</a>
        <a href="index.html">🏠 Accueil</a>
    </div>

    <h1>➕ Ajouter un Cheikh</h1>

    <?php if ($succes): ?>
        <div class="success">
            <?= htmlspecialchars($message) ?>
            <br><a href="search_cheikhs.php">→ Voir la liste des cheikhs</a>
        </div>
    <?php endif; ?>

    <?php if (!empty($erreurs)): ?>
        <div class="error-box">
            <strong>❌ Erreurs :</strong>
            <ul><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="insert_cheikh.php"
          onsubmit="return validerFormCheikh()">

        <div class="row">
            <div class="form-group">
                <label>Nom <span class="req">*</span></label>
                <input type="text" name="nom" id="nom" placeholder="Ex: Lotfi Bouchnak"
                       value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                <span id="err-nom" style="color:#e74c3c;font-size:0.8rem;"></span>
            </div>
            <div class="form-group">
                <label>Nom arabe</label>
                <input type="text" name="nom_arabe" placeholder="النسخة العربية"
                       value="<?= htmlspecialchars($_POST['nom_arabe'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Rôle <span class="req">*</span></label>
            <input type="text" name="role" id="role" placeholder="Ex: Chanteur · Oudiste"
                   value="<?= htmlspecialchars($_POST['role'] ?? '') ?>" required>
            <span id="err-role" style="color:#e74c3c;font-size:0.8rem;"></span>
        </div>

        <div class="row">
            <div class="form-group">
                <label>Année de naissance <span class="req">*</span></label>
                <input type="number" name="naissance" id="naissance" min="1800" max="2025"
                       placeholder="Ex: 1954" value="<?= htmlspecialchars($_POST['naissance'] ?? '') ?>" required>
                <span id="err-naissance" style="color:#e74c3c;font-size:0.8rem;"></span>
            </div>
            <div class="form-group">
                <label>Année de décès (vide si vivant)</label>
                <input type="number" name="deces" min="1800" max="2025"
                       placeholder="Laisser vide si vivant"
                       value="<?= htmlspecialchars($_POST['deces'] ?? '') ?>">
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <label>Ville <span class="req">*</span></label>
                <input type="text" name="ville" id="ville" placeholder="Ex: Tunis"
                       value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>" required>
                <span id="err-ville" style="color:#e74c3c;font-size:0.8rem;"></span>
            </div>
            <div class="form-group">
                <label>Nombre de Noubas (0–13)</label>
                <input type="number" name="noubas" min="0" max="13"
                       value="<?= htmlspecialchars($_POST['noubas'] ?? '0') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Biographie</label>
            <textarea name="bio" placeholder="Courte biographie..."><?= htmlspecialchars($_POST['bio'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn">➕ Ajouter le Cheikh</button>
    </form>
</div>

<script>
// Validation JavaScript côté client
function validerFormCheikh() {
    let ok = true;

    // Vider les anciens messages
    ['nom','role','naissance','ville'].forEach(function(id) {
        document.getElementById('err-' + id).textContent = '';
    });

    // Nom obligatoire, min 2 caractères
    let nom = document.getElementById('nom').value.trim();
    if (nom === '') {
        document.getElementById('err-nom').textContent = 'Le nom est obligatoire.';
        ok = false;
    } else if (nom.length < 2) {
        document.getElementById('err-nom').textContent = 'Minimum 2 caractères.';
        ok = false;
    }

    // Rôle obligatoire
    let role = document.getElementById('role').value.trim();
    if (role === '') {
        document.getElementById('err-role').textContent = 'Le rôle est obligatoire.';
        ok = false;
    }

    // Naissance : plage valide
    let naiss = parseInt(document.getElementById('naissance').value);
    if (isNaN(naiss) || naiss < 1800 || naiss > 2025) {
        document.getElementById('err-naissance').textContent = 'Année invalide (1800–2025).';
        ok = false;
    }

    // Ville obligatoire
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
