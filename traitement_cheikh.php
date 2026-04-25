<?php
// ============================================================
// NoubaTN — traitement_cheikh.php
// Reçoit les données du formulaire ajout cheikh
// Valide, insère en BD, affiche un récapitulatif
// ============================================================
require_once 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cheikhs.html');
    exit;
}

// ============================================================
// 1. RÉCUPÉRATION & NETTOYAGE
// ============================================================
$nom       = trim($_POST['nom']       ?? '');
$nom_arabe = trim($_POST['nom_arabe'] ?? '');
$role      = trim($_POST['role']      ?? '');
$naissance = (int)($_POST['naissance'] ?? 0);
$deces     = trim($_POST['deces']     ?? '');
$ville     = trim($_POST['ville']     ?? '');
$noubas    = (int)($_POST['noubas']   ?? 0);
$bio       = trim($_POST['bio']       ?? '');

$decesVal  = ($deces === '' || $deces === '0') ? null : (int)$deces;

// Gestion de l'upload photo
$photoNom  = 'default_cheikh.jpg';
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $extAutorisees = ['jpg','jpeg','png','webp'];
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $extAutorisees)) {
        $photoNom = 'cheikh_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], $photoNom);
    }
}

// ============================================================
// 2. VALIDATION CÔTÉ SERVEUR
// ============================================================
$erreurs = [];

if (empty($nom))                               $erreurs[] = 'Le nom est obligatoire.';
if (empty($role))                              $erreurs[] = 'Le rôle est obligatoire.';
if (empty($ville))                             $erreurs[] = 'La ville est obligatoire.';
if ($naissance < 1800 || $naissance > 2025)    $erreurs[] = 'L\'année de naissance est invalide (1800–2025).';
if ($decesVal !== null && $decesVal <= $naissance) $erreurs[] = 'L\'année de décès doit être postérieure à la naissance.';
if ($noubas < 0 || $noubas > 13)              $erreurs[] = 'Le nombre de noubas doit être compris entre 0 et 13.';

// ============================================================
// 3. INSERTION EN BASE DE DONNÉES
// ============================================================
$insertOk = false;
if (empty($erreurs)) {
    try {
        $pdo  = getConnexion();

        // prepare() + execute() avec paramètres POSITIONNELS (?)
        $stmt = $pdo->prepare(
            'INSERT INTO cheikhs (nom, nom_arabe, role, naissance, deces, ville, noubas, bio, photo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $nom,
            $nom_arabe ?: null,
            $role,
            $naissance,
            $decesVal,
            $ville,
            $noubas,
            $bio       ?: null,
            $photoNom,
        ]);
        $insertOk = true;
    } catch (PDOException $e) {
        $erreurs[] = 'Erreur base de données : ' . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NoubaTN — Confirmation Cheikh</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body        { font-family: sans-serif; padding: 2rem; background: #f5f5f5; }
        .container  { max-width: 700px; margin: auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .success    { background: #eafaf1; border-left: 4px solid #2ecc71; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px; }
        .error-box  { background: #fdf2f2; border-left: 4px solid #e74c3c; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px; }
        table       { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td      { border: 1px solid #ddd; padding: 10px 14px; text-align: left; }
        th          { background: #2c3e50; color: #fff; width: 35%; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .btn        { display: inline-block; margin-top: 1.5rem; padding: .7rem 1.5rem; background: #3498db; color: #fff; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h1>🎵 Traitement du formulaire Cheikh</h1>

    <?php if (!empty($erreurs)): ?>
        <div class="error-box">
            <strong>❌ Erreurs détectées :</strong>
            <ul>
                <?php foreach ($erreurs as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <a href="cheikhs.html" class="btn">← Corriger le formulaire</a>

    <?php else: ?>
        <div class="success">
            ✅ <strong><?= htmlspecialchars($nom) ?></strong> a été ajouté avec succès !
        </div>

        <h2>Récapitulatif du cheikh ajouté</h2>
        <table>
            <tr><th>Nom</th>          <td><?= htmlspecialchars($nom) ?></td></tr>
            <tr><th>Nom arabe</th>    <td style="font-size:1.2rem;"><?= htmlspecialchars($nom_arabe ?: '—') ?></td></tr>
            <tr><th>Rôle</th>         <td><?= htmlspecialchars($role) ?></td></tr>
            <tr><th>Naissance</th>    <td><?= $naissance ?></td></tr>
            <tr><th>Décès</th>        <td><?= $decesVal ?? 'Encore vivant' ?></td></tr>
            <tr><th>Ville</th>        <td><?= htmlspecialchars($ville) ?></td></tr>
            <tr><th>Noubas</th>       <td><?= $noubas ?></td></tr>
            <tr><th>Biographie</th>   <td><?= nl2br(htmlspecialchars($bio ?: '—')) ?></td></tr>
            <tr><th>Photo</th>        <td><img src="<?= htmlspecialchars($photoNom) ?>" alt="photo" style="height:80px;border-radius:8px;"></td></tr>
        </table>

        <a href="cheikhs.html" class="btn">← Retour aux cheikhs</a>
        <a href="search_cheikhs.php" class="btn" style="margin-left:1rem;background:#27ae60;">🔍 Voir tous les cheikhs</a>
    <?php endif; ?>
</div>
</body>
</html>
