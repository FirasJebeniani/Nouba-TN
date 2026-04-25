<?php
// ============================================================
// NoubaTN — traitement_contact.php
// Reçoit les données du formulaire contact.html (méthode POST)
// Valide côté serveur, insère en BD, affiche un récapitulatif HTML
// ============================================================
require_once 'connexion.php';

// ---- Sécurité : accepter uniquement les requêtes POST ----
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

// ============================================================
// 1. RÉCUPÉRATION & NETTOYAGE DES DONNÉES
// ============================================================
$prenom       = trim($_POST['prenom']       ?? '');
$nom          = trim($_POST['nom']          ?? '');
$email        = trim($_POST['email']        ?? '');
$sujet        = trim($_POST['sujet']        ?? '');
$message      = trim($_POST['message']      ?? '');
$telephone    = trim($_POST['telephone']    ?? '');
$consentement = isset($_POST['consentement']) ? 1 : 0;

// ============================================================
// 2. VALIDATION CÔTÉ SERVEUR (PHP)
// ============================================================
$erreurs = [];

if (empty($prenom))                                   $erreurs[] = 'Le prénom est obligatoire.';
if (empty($nom))                                      $erreurs[] = 'Le nom est obligatoire.';
if (empty($email))                                    $erreurs[] = "L'email est obligatoire.";
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))   $erreurs[] = "L'adresse email n'est pas valide.";
if (empty($sujet))                                    $erreurs[] = 'Veuillez choisir un sujet.';
if (empty($message))                                  $erreurs[] = 'Le message est obligatoire.';
elseif (strlen($message) < 10)                        $erreurs[] = 'Le message doit contenir au moins 10 caractères.';
if (!$consentement)                                   $erreurs[] = 'Vous devez accepter la politique de confidentialité.';
if (!empty($telephone) && !preg_match('/^\+?[0-9\s\-]{8,20}$/', $telephone))
                                                      $erreurs[] = 'Le numéro de téléphone n\'est pas valide.';

$sujetsValides = ['contribution','evenement','partenariat','presse','recherche','autre'];
if (!in_array($sujet, $sujetsValides))                $erreurs[] = 'Sujet invalide.';

// ============================================================
// 3. INSERTION EN BASE DE DONNÉES (si pas d'erreurs)
// ============================================================
$insertOk = false;
if (empty($erreurs)) {
    try {
        $pdo  = getConnexion();

        // prepare() + execute() avec paramètres NOMMÉS (:param)
        $stmt = $pdo->prepare(
            'INSERT INTO contacts (prenom, nom, email,  sujet, message, telephone, consentement)
             VALUES (:prenom, :nom, :email,  :sujet, :message, :telephone, :consentement)'
        );
        $stmt->execute([
            ':prenom'       => $prenom,
            ':nom'          => $nom,
            ':email'        => $email,
            ':sujet'        => $sujet,
            ':message'      => $message,
            ':telephone'    => $telephone    ?: null,
            ':consentement' => $consentement,
        ]);
        $insertOk = true;
    } catch (PDOException $e) {
        $erreurs[] = 'Erreur base de données : ' . htmlspecialchars($e->getMessage());
    }
}

// Libellés lisibles pour les sujets
$sujetsLibelles = [
    'contribution' => 'Contribution au contenu musical',
    'evenement'    => "Soumission d'un évènement",
    'partenariat'  => 'Proposition de partenariat',
    'presse'       => 'Demande presse / médias',
    'recherche'    => 'Collaboration académique / recherche',
    'autre'        => 'Autre / Question générale',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NoubaTN — Confirmation Contact</title>
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
    <h1>📬 Traitement du formulaire Contact</h1>

    <?php if (!empty($erreurs)): ?>
        <!-- Affichage des erreurs de validation -->
        <div class="error-box">
            <strong>❌ Erreurs détectées :</strong>
            <ul>
                <?php foreach ($erreurs as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <a href="contact.html" class="btn">← Corriger le formulaire</a>

    <?php else: ?>
        <!-- Message de succès -->
        <div class="success">
            ✅ <strong>Message envoyé avec succès !</strong>
            Votre demande a bien été enregistrée. Nous vous répondrons sous 48h.
        </div>

        <!-- Récapitulatif des données reçues -->
        <h2>Récapitulatif de votre message</h2>
        <table>
            <tr><th>Prénom</th>        <td><?= htmlspecialchars($prenom) ?></td></tr>
            <tr><th>Nom</th>           <td><?= htmlspecialchars($nom) ?></td></tr>
            <tr><th>Email</th>         <td><?= htmlspecialchars($email) ?></td></tr>
            <tr><th>Sujet</th>         <td><?= htmlspecialchars($sujetsLibelles[$sujet] ?? $sujet) ?></td></tr>
            <tr><th>Message</th>       <td><?= nl2br(htmlspecialchars($message)) ?></td></tr>
            <tr><th>Téléphone</th>     <td><?= htmlspecialchars($telephone ?: '—') ?></td></tr>
            <tr><th>Consentement</th>  <td><?= $consentement ? '✅ Accepté' : '❌ Refusé' ?></td></tr>
            <tr><th>Date d'envoi</th>  <td><?= date('d/m/Y à H:i') ?></td></tr>
        </table>

        <a href="contact.html" class="btn">← Retour au formulaire</a>
        <a href="index.html" style="margin-left:1rem;" class="btn" style="background:#7f8c8d;">🏠 Accueil</a>
    <?php endif; ?>
</div>
</body>
</html>
