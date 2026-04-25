<?php
// ============================================================
// NoubaTN — traitement_evenement.php
// Reçoit les données du formulaire ajout évènement
// Valide, insère en BD, affiche un récapitulatif
// ============================================================
require_once 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: evenements.html');
    exit;
}

// ============================================================
// 1. RÉCUPÉRATION & NETTOYAGE
// ============================================================
$titre       = trim($_POST['titre']       ?? '');
$date_evt    = trim($_POST['date_evt']    ?? '');
$lieu        = trim($_POST['lieu']        ?? '');
$ville       = trim($_POST['ville']       ?? '');
$type        = trim($_POST['type']        ?? '');
$description = trim($_POST['description'] ?? '');

// ============================================================
// 2. VALIDATION CÔTÉ SERVEUR
// ============================================================
$erreurs = [];

if (empty($titre))                                     $erreurs[] = 'Le titre est obligatoire.';
elseif (strlen($titre) < 5)                           $erreurs[] = 'Le titre doit contenir au moins 5 caractères.';
if (empty($date_evt))                                  $erreurs[] = 'La date est obligatoire.';
elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_evt)) $erreurs[] = 'Format de date invalide (YYYY-MM-DD attendu).';
if (empty($lieu))                                      $erreurs[] = 'Le lieu est obligatoire.';
if (empty($ville))                                     $erreurs[] = 'La ville est obligatoire.';
$typesValides = ['Concert','Festival','Conférence','Atelier','Exposition','Gala'];
if (!in_array($type, $typesValides))                   $erreurs[] = 'Type d\'évènement invalide.';

// ============================================================
// 3. INSERTION EN BASE DE DONNÉES
// ============================================================
$insertOk = false;
if (empty($erreurs)) {
    try {
        $pdo  = getConnexion();

        // prepare() + execute() avec paramètres NOMMÉS (:param)
        $stmt = $pdo->prepare(
            'INSERT INTO evenements (titre, date_evt, lieu, ville, type, description)
             VALUES (:titre, :date_evt, :lieu, :ville, :type, :description)'
        );
        $stmt->execute([
            ':titre'       => $titre,
            ':date_evt'    => $date_evt,
            ':lieu'        => $lieu,
            ':ville'       => $ville,
            ':type'        => $type,
            ':description' => $description ?: null,
        ]);
        $insertOk = true;
    } catch (PDOException $e) {
        $erreurs[] = 'Erreur base de données : ' . htmlspecialchars($e->getMessage());
    }
}

// Formatage date pour affichage
$dateFormatee = '';
if (!empty($date_evt)) {
    $dt = DateTime::createFromFormat('Y-m-d', $date_evt);
    if ($dt) $dateFormatee = $dt->format('d/m/Y');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NoubaTN — Confirmation Évènement</title>
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
        .badge      { background: #3498db; color: #fff; padding: 2px 10px; border-radius: 12px; font-size: 0.85rem; }
        .btn        { display: inline-block; margin-top: 1.5rem; padding: .7rem 1.5rem; background: #3498db; color: #fff; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h1>📅 Traitement du formulaire Évènement</h1>

    <?php if (!empty($erreurs)): ?>
        <div class="error-box">
            <strong>❌ Erreurs détectées :</strong>
            <ul>
                <?php foreach ($erreurs as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <a href="evenements.html" class="btn">← Corriger le formulaire</a>

    <?php else: ?>
        <div class="success">
            ✅ <strong>Évènement ajouté avec succès !</strong>
        </div>

        <h2>Récapitulatif de l'évènement</h2>
        <table>
            <tr><th>Titre</th>        <td><?= htmlspecialchars($titre) ?></td></tr>
            <tr><th>Date</th>         <td><?= htmlspecialchars($dateFormatee) ?></td></tr>
            <tr><th>Lieu</th>         <td><?= htmlspecialchars($lieu) ?></td></tr>
            <tr><th>Ville</th>        <td><?= htmlspecialchars($ville) ?></td></tr>
            <tr><th>Type</th>         <td><span class="badge"><?= htmlspecialchars($type) ?></span></td></tr>
            <tr><th>Description</th>  <td><?= nl2br(htmlspecialchars($description ?: '—')) ?></td></tr>
        </table>

        <a href="evenements.html" class="btn">← Retour aux évènements</a>
        <a href="search_evenements.php" class="btn" style="margin-left:1rem;background:#27ae60;">🔍 Voir tous les évènements</a>
    <?php endif; ?>
</div>
</body>
</html>
