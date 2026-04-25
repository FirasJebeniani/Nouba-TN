// Auteurs : May Hamrouni & Firas Jebiniani
/*
 * ============================================================
 * NoubaTN — js/cheikhs.js
 * Auteurs : [Membres de l'équipe]
 * Description : Gestion dynamique du tableau des Cheikhs du Malouf
 *               - Définition du constructeur Cheikh
 *               - Tableau de données initialisé
 *               - Génération dynamique du tableau HTML (DOM)
 *               - Fonction d'ajout de ligne
 *               - Fonction de recherche / filtrage
 * ============================================================
 */

/* ============================================================
   1. CONSTRUCTEUR D'OBJET — Cheikh
   Chaque Cheikh est un objet avec plusieurs propriétés.
   ============================================================ */
function Cheikh(nom, nomArabe, role, naissance, deces, ville, noubas, bio) {
  this.nom      = nom;        // Nom français du Cheikh
  this.nomArabe = nomArabe;   // Nom en arabe
  this.role     = role;       // Rôle principal (chanteur, oudiste...)
  this.naissance = naissance; // Année de naissance
  this.deces    = deces;      // Année de décès (null si vivant)
  this.ville    = ville;      // Ville d'origine
  this.noubas   = noubas;     // Nombre de noubas maîtrisées
  this.bio      = bio;        // Courte biographie

  /* Méthode : retourne la période de vie formatée */
  this.getPeriode = function () {
    return this.naissance + " – " + (this.deces ? this.deces : "Présent");
  };

  /* Méthode : indique si le Cheikh est encore vivant */
  this.estVivant = function () {
    return this.deces === null;
  };
}

/* ============================================================
   2. TABLEAU (ARRAY) DE DONNÉES
   Initialisé avec les 6 Cheikhs du site, chaque élément
   est un objet Cheikh créé via le constructeur.
   ============================================================ */
var lesCheikhs = [
  new Cheikh(
    "Khemaïs Tarnane",
    "خميس ترنان",
    "Fondateur · Chanteur · Oudiste",
    1894, 1964,
    "Bizerte",
    13,
    "Cofondateur de La Rachidia (1934), voix tutélaire du Malouf tunisien."
  ),
  new Cheikh(
    "Ali Sriti",
    "علي السريتي",
    "Virtuose · Oudiste · Pédagogue",
    1919, 2007,
    "Tunis",
    11,
    "Considéré comme l'un des plus grands oudistes du monde arabe."
  ),
  new Cheikh(
    "Salah El Mahdi",
    "صالح المهدي",
    "Musicologue · Compositeur · Chef d'orchestre",
    1925, 2014,
    "Tunis",
    13,
    "Fondateur de l'Orchestre symphonique tunisien, compositeur de l'hymne national."
  ),
  new Cheikh(
    "Zied Gharsa",
    "زياد غرسة",
    "Ténor · Compositeur · Oudiste",
    1963, null,
    "Tunis",
    13,
    "Héritier direct de la lignée Tarnane, ténor d'une rare pureté."
  ),
  new Cheikh(
    "Lotfi Bouchnak",
    "لطفي بوشناق",
    "Chanteur · Oudiste · Ambassadeur ONU",
    1954, null,
    "Tunis",
    13,
    "La voix tunisienne la plus célèbre dans le monde arabe, Ambassadeur de paix ONU."
  ),
  new Cheikh(
    "Hédi Jouini",
    "الهادي الجويني",
    "Chanteur · Oudiste · Compositeur",
    1909, 1990,
    "Tunis",
    10,
    "Légende de la chanson tunisienne, plus de 1070 compositions à son actif."
  )
];

/* ============================================================
   3. GÉNÉRATION DYNAMIQUE DU TABLEAU HTML
   Vide le <tbody> puis le remplit à partir du tableau JS.
   @param {Array} donnees - tableau de Cheikhs à afficher
   ============================================================ */
function afficherTableau(donnees) {
  // Récupération du corps du tableau dans le DOM
  var tbody = document.getElementById("tbody-cheikhs");

  // On vide le tableau avant de le remplir
  tbody.innerHTML = "";

  // Si aucun résultat : afficher un message
  if (donnees.length === 0) {
    var trVide = document.createElement("tr");
    trVide.innerHTML = '<td colspan="7" style="text-align:center; color:var(--gray-mid); padding:2rem; font-family:var(--font-ui); font-size:0.85rem;">Aucun cheikh trouvé.</td>';
    tbody.appendChild(trVide);
    return;
  }

  /* Boucle sur chaque objet Cheikh du tableau de données */
  for (var i = 0; i < donnees.length; i++) {
    var c = donnees[i]; // raccourci vers l'objet courant

    // Création de la ligne <tr>
    var tr = document.createElement("tr");

    // Badge "Vivant" ou "Décédé" selon la méthode estVivant()
    var statutBadge = c.estVivant()
      ? '<span class="badge-statut vivant">Vivant</span>'
      : '<span class="badge-statut decede">Décédé</span>';

    // Construction du HTML de chaque cellule <td>
    tr.innerHTML =
      "<td><strong>" + c.nom + "</strong><br><span class='cheikh-nom-ar'>" + c.nomArabe + "</span></td>" +
      "<td>" + c.role + "</td>" +
      "<td>" + c.ville + "</td>" +
      "<td>" + c.getPeriode() + "</td>" +
      "<td style='text-align:center;'>" + c.noubas + "</td>" +
      "<td>" + statutBadge + "</td>" +
      "<td><span class='bio-courte'>" + c.bio + "</span></td>";

    // Ajout de la ligne au <tbody>
    tbody.appendChild(tr);
  }

  // Mise à jour du compteur de résultats
  var compteur = document.getElementById("compteur-cheikhs");
  if (compteur) {
    compteur.textContent = donnees.length + " cheikh(s) affiché(s)";
  }
}

/* ============================================================
   4. FONCTION D'AJOUT DYNAMIQUE D'UN CHEIKH
   Lit les valeurs du formulaire, crée un nouvel objet Cheikh,
   l'ajoute au tableau lesCheikhs, puis rafraîchit l'affichage.
   ============================================================ */
function ajouterCheikh() {
  // Lecture des champs du formulaire d'ajout
  var nom      = document.getElementById("add-nom").value.trim();
  var nomAr    = document.getElementById("add-nom-ar").value.trim();
  var role     = document.getElementById("add-role").value.trim();
  var naiss    = parseInt(document.getElementById("add-naissance").value);
  var decesVal = document.getElementById("add-deces").value.trim();
  var ville    = document.getElementById("add-ville").value.trim();
  var noubas   = parseInt(document.getElementById("add-noubas").value);
  var bio      = document.getElementById("add-bio").value.trim();

  // --- Validation basique ---
  if (!nom || !role || !ville || isNaN(naiss)) {
    afficherMessage("form-message-ajout", "Veuillez remplir tous les champs obligatoires.", "erreur");
    return;
  }

  // Conversion du champ décès : vide = null (vivant)
  var deces = (decesVal === "" || decesVal === "0") ? null : parseInt(decesVal);

  // Création du nouvel objet Cheikh via le constructeur
  var nouveauCheikh = new Cheikh(nom, nomAr, role, naiss, deces, ville, noubas || 0, bio);

  // Ajout au tableau principal
  lesCheikhs.push(nouveauCheikh);

  // Rafraîchissement de l'affichage avec le tableau complet mis à jour
  afficherTableau(lesCheikhs);

  // Message de confirmation
  afficherMessage("form-message-ajout", "✓ " + nom + " a été ajouté avec succès !", "succes");

  // Réinitialisation du formulaire
  document.getElementById("form-ajout-cheikh").reset();
}

/* ============================================================
   6. FONCTION UTILITAIRE — Afficher un message contextuel
   @param {string} idElement - l'id du div message
   @param {string} texte     - le texte à afficher
   @param {string} type      - "succes" | "erreur" | ""
   ============================================================ */
function afficherMessage(idElement, texte, type) {
  var el = document.getElementById(idElement);
  if (!el) return;

  el.textContent = texte;
  el.className   = "form-message " + type; // classe CSS selon le type

  // Si vide → masquer
  el.style.display = texte === "" ? "none" : "block";
}

/* ============================================================
   7. INITIALISATION AU CHARGEMENT DE LA PAGE
   Appel de afficherTableau() dès que le DOM est prêt.
   ============================================================ */
document.addEventListener("DOMContentLoaded", function () {
  // Remplissage initial du tableau avec toutes les données
  afficherTableau(lesCheikhs);

  /* --- Recherche en temps réel sur frappe clavier --- */
  var champRecherche = document.getElementById("champ-recherche");
  if (champRecherche) {
    champRecherche.addEventListener("input", function () {
      rechercherCheikh();
    });
  }

  /* --- Bouton "Réinitialiser la recherche" --- */
  var btnReset = document.getElementById("btn-reset-recherche");
  if (btnReset) {
    btnReset.addEventListener("click", function () {
      document.getElementById("champ-recherche").value = "";
      afficherTableau(lesCheikhs);
      afficherMessage("form-message-recherche", "", "");
    });
  }
});
