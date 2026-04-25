<?php
// ============================================================
// NoubaTN — classe_cheikh.php
// Section 3c : Classe + tableau d'objets + fonction d'affichage
// ============================================================

// ============================================================
// 1. DÉFINITION DE LA CLASSE Cheikh
// ============================================================
class Cheikh {

    // --- Attributs privés ---
    private int    $id;
    private string $nom;
    private string $nomArabe;
    private string $role;
    private int    $naissance;
    private ?int   $deces;       // nullable : null si encore vivant
    private string $ville;
    private int    $noubas;
    private string $bio;
    private string $photo;

    // --- Constructeur ---
    public function __construct(
        int    $id,
        string $nom,
        string $nomArabe,
        string $role,
        int    $naissance,
        ?int   $deces,
        string $ville,
        int    $noubas,
        string $bio,
        string $photo = 'default_cheikh.jpg'
    ) {
        $this->id        = $id;
        $this->nom       = $nom;
        $this->nomArabe  = $nomArabe;
        $this->role      = $role;
        $this->naissance = $naissance;
        $this->deces     = $deces;
        $this->ville     = $ville;
        $this->noubas    = $noubas;
        $this->bio       = $bio;
        $this->photo     = $photo;
    }

    // --- Getters ---
    public function getId():        int     { return $this->id;        }
    public function getNom():       string  { return $this->nom;       }
    public function getNomArabe():  string  { return $this->nomArabe;  }
    public function getRole():      string  { return $this->role;      }
    public function getNaissance(): int     { return $this->naissance; }
    public function getDeces():     ?int    { return $this->deces;     }
    public function getVille():     string  { return $this->ville;     }
    public function getNoubas():    int     { return $this->noubas;    }
    public function getBio():       string  { return $this->bio;       }
    public function getPhoto():     string  { return $this->photo;     }

    // --- Setters ---
    public function setNom(string $nom):           void { $this->nom       = $nom;       }
    public function setRole(string $role):         void { $this->role      = $role;      }
    public function setVille(string $ville):       void { $this->ville     = $ville;     }
    public function setNoubas(int $noubas):        void { $this->noubas    = $noubas;    }
    public function setBio(string $bio):           void { $this->bio       = $bio;       }
    public function setPhoto(string $photo):       void { $this->photo     = $photo;     }
    public function setDeces(?int $deces):         void { $this->deces     = $deces;     }

    // --- Méthodes métier ---

    /**
     * Retourne la période de vie formatée (ex: "1894 – 1964" ou "1963 – Présent")
     */
    public function getPeriode(): string {
        return $this->naissance . ' – ' . ($this->deces ?? 'Présent');
    }

    /**
     * Retourne true si le cheikh est encore vivant
     */
    public function estVivant(): bool {
        return $this->deces === null;
    }

    /**
     * Retourne le statut sous forme de badge HTML
     */
    public function getBadgeStatut(): string {
        if ($this->estVivant()) {
            return '<span style="background:#2ecc71;color:#fff;padding:2px 10px;border-radius:12px;font-size:0.8rem;">Vivant</span>';
        }
        return '<span style="background:#95a5a6;color:#fff;padding:2px 10px;border-radius:12px;font-size:0.8rem;">Décédé</span>';
    }
}

// ============================================================
// 2. TABLEAU D'OBJETS Cheikh
// Données correspondant aux 6 cheikhs du site NoubaTN
// ============================================================
$lesCheikhs = [
    new Cheikh(1, 'Khemaïs Tarnane',  'خميس ترنان',   'Fondateur · Chanteur · Oudiste',               1894, 1964, 'Bizerte', 13, 'Cofondateur de La Rachidia (1934), voix tutélaire du Malouf tunisien.',                 'cheikh1.jpg'),
    new Cheikh(2, 'Ali Sriti',        'علي السريتي',  'Virtuose · Oudiste · Pédagogue',               1919, 2007, 'Tunis',   11, 'Considéré comme l\'un des plus grands oudistes du monde arabe.',                       'cheikh2.jpg'),
    new Cheikh(3, 'Salah El Mahdi',   'صالح المهدي',  'Musicologue · Compositeur · Chef d\'orchestre', 1925, 2014, 'Tunis',   13, 'Fondateur de l\'Orchestre symphonique tunisien, compositeur de l\'hymne national.',    'cheikh3.jpg'),
    new Cheikh(4, 'Zied Gharsa',      'زياد غرسة',    'Ténor · Compositeur · Oudiste',                 1963, null, 'Tunis',   13, 'Héritier direct de la lignée Tarnane, ténor d\'une rare pureté.',                     'cheikh4.jpg'),
    new Cheikh(5, 'Lotfi Bouchnak',   'لطفي بوشناق',  'Chanteur · Oudiste · Ambassadeur ONU',          1954, null, 'Tunis',   13, 'La voix tunisienne la plus célèbre dans le monde arabe, Ambassadeur de paix ONU.',   'cheikh5.jpg'),
    new Cheikh(6, 'Hédi Jouini',      'الهادي الجويني','Chanteur · Oudiste · Compositeur',              1909, 1990, 'Tunis',   10, 'Légende de la chanson tunisienne, plus de 1070 compositions à son actif.',            'cheikh6.jpg'),
];

// ============================================================
// 3. FONCTION D'AFFICHAGE — parcourt le tableau et génère un tableau HTML
// ============================================================

/**
 * Parcourt un tableau d'objets Cheikh et affiche un tableau HTML.
 *
 * @param Cheikh[] $cheikhs  Tableau d'objets Cheikh à afficher
 * @param string   $titre    Titre affiché au-dessus du tableau
 */
function afficherTableauCheikhs(array $cheikhs, string $titre = 'Les Cheikhs du Malouf'): void {
    if (empty($cheikhs)) {
        echo '<p style="color:#e74c3c;">Aucun cheikh à afficher.</p>';
        return;
    }
    ?>
    <h2 style="font-family:sans-serif;color:#2c3e50;margin-bottom:1rem;">
        <?= htmlspecialchars($titre) ?>
        <span style="font-size:0.8rem;color:#7f8c8d;">(<?= count($cheikhs) ?> résultat(s))</span>
    </h2>

    <table border="1" cellpadding="10" cellspacing="0"
           style="border-collapse:collapse;width:100%;font-family:sans-serif;font-size:0.9rem;">
        <thead style="background:#2c3e50;color:#fff;">
            <tr>
                <th>Photo</th>
                <th>Nom</th>
                <th>Nom arabe</th>
                <th>Rôle</th>
                <th>Ville</th>
                <th>Période</th>
                <th>Noubas</th>
                <th>Statut</th>
                <th>Biographie</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // --- Boucle d'itération sur le tableau d'objets ---
        foreach ($cheikhs as $c) {
            // --- Sélection : ligne colorée si cheikh encore vivant ---
            $bgColor = $c->estVivant() ? '#eafaf1' : '#f9f9f9';
            echo '<tr style="background:' . $bgColor . ';">';
            echo '<td style="text-align:center;">
                    <img src="' . htmlspecialchars($c->getPhoto()) . '"
                         alt="' . htmlspecialchars($c->getNom()) . '"
                         style="width:60px;height:60px;object-fit:cover;border-radius:50%;">
                  </td>';
            echo '<td><strong>' . htmlspecialchars($c->getNom())      . '</strong></td>';
            echo '<td style="font-size:1.1rem;">'  . htmlspecialchars($c->getNomArabe())  . '</td>';
            echo '<td>' . htmlspecialchars($c->getRole())      . '</td>';
            echo '<td>' . htmlspecialchars($c->getVille())     . '</td>';
            echo '<td>' . htmlspecialchars($c->getPeriode())   . '</td>';
            echo '<td style="text-align:center;">' . $c->getNoubas() . '</td>';
            echo '<td style="text-align:center;">' . $c->getBadgeStatut() . '</td>';
            echo '<td style="font-size:0.82rem;color:#555;">' . htmlspecialchars($c->getBio()) . '</td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
    <?php
}

// ============================================================
// 4. AFFICHAGE DE LA PAGE
// ============================================================
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NoubaTN — Cheikhs (Classe PHP)</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: sans-serif; padding: 2rem; background: #f5f5f5; }
        h1   { color: #2c3e50; margin-bottom: 2rem; }
        .container { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎵 NoubaTN — Les Cheikhs du Malouf</h1>
        <p style="color:#7f8c8d;margin-bottom:2rem;">
            Affichage via une classe PHP, un tableau d'objets et une fonction d'itération.
        </p>

        <?php
        // Appel de la fonction avec le tableau complet
        afficherTableauCheikhs($lesCheikhs);

        // Exemple de filtre : afficher uniquement les cheikhs vivants
        $cheikhsVivants = array_filter($lesCheikhs, fn($c) => $c->estVivant());
        echo '<br>';
        afficherTableauCheikhs(array_values($cheikhsVivants), 'Cheikhs encore vivants');
        ?>

        <p style="margin-top:2rem;">
            <a href="index.html" style="color:#3498db;">← Retour à l'accueil</a>
        </p>
    </div>
</body>
</html>
