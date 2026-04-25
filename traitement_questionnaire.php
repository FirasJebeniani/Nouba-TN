<?php
// ============================================================
// NoubaTN — traitement_questionnaire.php
// Reçoit les données du formulaire questionnaire.html
// Valide côté serveur, insère en BD, affiche un récapitulatif
// ============================================================
require_once 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: questionnaire.html');
    exit;
}

// ============================================================
// 1. RÉCUPÉRATION & NETTOYAGE
// ============================================================
$nom              = trim($_POST['nom']        ?? '');
$email            = trim($_POST['email']      ?? '');
$niveau           = trim($_POST['niveau']     ?? '');
$cheikhsCoches = isset($_POST['cheikh']) ? (array)$_POST['cheikh'] : [];
$nouba_preferee   = trim($_POST['nouba']      ?? '');
$note             = (int)($_POST['note']      ?? 0);
$commentaire      = trim($_POST['commentaire'] ?? '');

// Cheikhs préférés → chaîne de caractères séparée par des virgules
$cheikhs_preferes = implode(', ', array_map('trim', $cheikhsCoches));

// ============================================================
// 2. VALIDATION CÔTÉ SERVEUR
// ============================================================
$erreurs = [];

if (empty($nom))                                        $erreurs[] = 'Le nom est obligatoire.';
elseif (strlen($nom) < 2)                              $erreurs[] = 'Le nom doit contenir au moins 2 caractères.';
if (empty($email))                                      $erreurs[] = "L'email est obligatoire.";
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))     $erreurs[] = "L'adresse email n'est pas valide.";
$niveauxValides = ['Débutant','Amateur','Passionné','Expert'];
if (!in_array($niveau, $niveauxValides))                $erreurs[] = 'Veuillez sélectionner votre niveau de connaissance.';
if (empty($cheikhsCoches))                             $erreurs[] = 'Veuillez sélectionner au moins un Cheikh préféré.';
if ($note < 1 || $note > 10)                           $erreurs[] = 'La note doit être comprise entre 1 et 10.';
if (empty($commentaire))                               $erreurs[] = 'Le commentaire est obligatoire.';
elseif (strlen($commentaire) < 10)                     $erreurs[] = 'Le commentaire doit contenir au moins 10 caractères.';

// ============================================================
// 3. INSERTION EN BASE DE DONNÉES
// ============================================================
$insertOk = false;
if (empty($erreurs)) {
    try {
        $pdo  = getConnexion();

        // prepare() + execute() avec paramètres POSITIONNELS (?)
        $stmt = $pdo->prepare(
            'INSERT INTO questionnaires (nom, email, niveau, cheikhs_preferes, nouba_preferee, note, commentaire)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $nom,
            $email,
            $niveau,
            $cheikhs_preferes ?: null,
            $nouba_preferee   ?: null,
            $note,
            $commentaire,
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
    <title>NoubaTN — Résultat Questionnaire</title>
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
        .note-bar   { display: inline-block; background: #f39c12; color: #fff; padding: 3px 12px; border-radius: 12px; font-weight: bold; }
        .btn        { display: inline-block; margin-top: 1.5rem; padding: .7rem 1.5rem; background: #3498db; color: #fff; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h1>📝 Résultat du Questionnaire</h1>

    <?php if (!empty($erreurs)): ?>
        <div class="error-box">
            <strong>❌ Erreurs détectées :</strong>
            <ul>
                <?php foreach ($erreurs as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <a href="questionnaire.html" class="btn">← Corriger le questionnaire</a>

    <?php else: ?>
        <div class="success">
            ✅ <strong>Merci <?= htmlspecialchars($nom) ?> !</strong>
            Votre questionnaire a bien été enregistré.
        </div>

        <h2>Vos réponses</h2>
        <table>
            <tr><th>Nom</th>               <td><?= htmlspecialchars($nom) ?></td></tr>
            <tr><th>Email</th>             <td><?= htmlspecialchars($email) ?></td></tr>
            <tr><th>Niveau</th>            <td><?= htmlspecialchars($niveau) ?></td></tr>
            <tr><th>Cheikhs préférés</th>  <td><?= htmlspecialchars($cheikhs_preferes ?: '—') ?></td></tr>
            <tr><th>Nouba préférée</th>    <td><?= htmlspecialchars($nouba_preferee ?: 'Non renseignée') ?></td></tr>
            <tr><th>Note du site</th>      <td><span class="note-bar"><?= $note ?> / 10</span></td></tr>
            <tr><th>Commentaire</th>       <td><?= nl2br(htmlspecialchars($commentaire)) ?></td></tr>
            <tr><th>Date de soumission</th><td><?= date('d/m/Y à H:i') ?></td></tr>
        </table>

        <a href="questionnaire.html" class="btn">← Retour au questionnaire</a>
        <a href="index.html" class="btn" style="margin-left:1rem;">🏠 Accueil</a>
    <?php endif; ?>
</div>
</body>
</html>
