// Auteurs : May Hamrouni & Firas Jebiniani
/*
 * ============================================================
 * NoubaTN — evenements.js
 * ============================================================
 */
 
/* ============================================================
   1. CONSTRUCTEUR D'OBJET — Evenement
   ============================================================ */
function Evenement(titre, date, lieu, ville, type, description, photo) {
  this.titre       = titre;
  this.date        = date;
  this.lieu        = lieu;
  this.ville       = ville;
  this.type        = type;
  this.description = description;
  this.photo       = photo || null;
 
  this.getDateFormatee = function () {
    var mois = ["Janv.","Févr.","Mars","Avr.","Mai","Juin","Juil.","Août","Sept.","Oct.","Nov.","Déc."];
    var parts = this.date.split("-");
    if (parts.length !== 3) return this.date;
    return parseInt(parts[2], 10) + " " + mois[parseInt(parts[1], 10) - 1] + " " + parts[0];
  };
 
  this.estAVenir = function () {
    return new Date(this.date) >= new Date();
  };
}
 
/* ============================================================
   2. TABLEAU DE DONNÉES
   ============================================================ */
var lesEvenements = [
  new Evenement(
    "Festival International du Malouf de Testour — 44e édition",
    "2026-03-14", "Ville de Testour", "Testour, Béja", "Festival",
    "Le plus ancien et prestigieux festival du Malouf tunisien, avec des ensembles venus de toute la Méditerranée.",
    "testour.jpg"),
  new Evenement(
    "Nuit du Malouf — Palais Bayram",
    "2026-04-22", "Palais Bayram", "Médina de Tunis", "Concert",
    "L'orchestre de la Rachidia interprétera l'intégrale de la Noubat el-Hsin, suivie d'une improvisation sur le mode Bayat.",
    "palais.jpg"),
  new Evenement(
    "Conférence internationale : Malouf et patrimoine méditerranéen",
    "2026-05-10", "ENSI", "La Manouba", "Conférence",
    "Journée d'étude internationale réunissant chercheurs tunisiens et étrangers. Communications en français et arabe.",
    "images.jpg"),
  new Evenement(
    "Concert de l'Ensemble Maqam — Journée Mondiale de la Musique",
    "2026-06-18", "Théâtre Municipal de Tunis", "Tunis", "Concert",
    "Programme croisant le Malouf classique avec des compositions contemporaines. Entrée libre.",
    "jour_mondiale.jpg"),
  new Evenement(
    "Masterclass Oud — École Supérieure de Musique",
    "2026-07-05", "ÉPalais Habib Bourguiba", "Sidi Bou Saïd", "Atelier",
    "Cinq sessions intensives animées par le maître oudiste Anouar Brahem. Places limitées.",
    "class.jpg"),
  new Evenement(
    "Exposition : \"Les instruments du Malouf — 10 siècles d'histoire\"",
    "2026-09-20", "Musée National du Bardo", "Tunis", "Exposition",
    "Exposition muséale présentant des instruments authentiques du XVIIe au XXe siècle. Durée : 6 semaines.",
    "expo.jpg"),
  new Evenement(
    "Gala annuel de la Rachidia — 92e anniversaire",
    "2026-11-15", "Théâtre de l'Opéra, Cité de la Culture", "Tunis", "Gala",
    "Soirée de prestige consacrée à l'intégrale des Noubas du mode Dhil, interprétées en deux parties.",
    "rachidia.jpg")
];
 
/* ============================================================
   3. GÉNÉRATION DU TABLEAU HTML
   ============================================================ */
function afficherTableau(donnees) {
  var tbody = document.getElementById("tbody-evenements");
  tbody.innerHTML = "";
 
  if (donnees.length === 0) {
    var trVide = document.createElement("tr");
    trVide.innerHTML =
      '<td colspan="8" style="text-align:center;color:#999;padding:2rem;">Aucun évènement trouvé.</td>';
    tbody.appendChild(trVide);
    mettreAJourCompteur(0);
    return;
  }
 
  for (var i = 0; i < donnees.length; i++) {
    var e   = donnees[i];
 
    var tr = document.createElement("tr");
 
    var statutBadge = e.estAVenir()
      ? '<span class="badge-statut a-venir">À venir</span>'
      : '<span class="badge-statut passe">Passé</span>';
 
    var typeBadge =
      '<span class="badge-type badge-type-' + e.type.toLowerCase().replace('é','e').replace('è','e') + '">' +
      e.type + '</span>';
 
    var photoHtml = e.photo
      ? '<img src="' + e.photo + '" alt="Affiche" style="width:70px;height:100px;object-fit:cover;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,0.2);">'
      : '<span style="color:#aaa;font-size:0.8rem;">—</span>';
 
    tr.innerHTML =
      "<td><strong>" + e.titre + "</strong></td>" +
      "<td>" + e.getDateFormatee() + "</td>" +
      "<td>" + e.lieu + "</td>" +
      "<td>" + e.ville + "</td>" +
      "<td>" + typeBadge + "</td>" +
      "<td>" + statutBadge + "</td>" +
      "<td><span class='evt-desc'>" + e.description + "</span></td>" +
      "<td>" + photoHtml + "</td>";
 
    tbody.appendChild(tr);
  }
 
  mettreAJourCompteur(donnees.length);
}
 
/* ============================================================
   4. COMPTEUR
   ============================================================ */
function mettreAJourCompteur(nb) {
  var compteur = document.getElementById("compteur-evenements");
  if (compteur) compteur.textContent = nb + " évènement(s) affiché(s)";
}
 
/* ============================================================
   8. INITIALISATION
   ============================================================ */
document.addEventListener("DOMContentLoaded", function () {
 
  lesEvenements.sort(function (a, b) { return new Date(a.date) - new Date(b.date); });
 
  afficherTableau(lesEvenements);
 
  var champRecherche = document.getElementById("champ-recherche");
  if (champRecherche) {
    champRecherche.addEventListener("input", rechercherEvenement);
  }
 
  var btnReset = document.getElementById("btn-reset-recherche");
  if (btnReset) {
    btnReset.addEventListener("click", function () {
      document.getElementById("champ-recherche").value = "";
      afficherTableau(lesEvenements);
      afficherMessage("form-message-recherche", "", "");
    });
  }
});