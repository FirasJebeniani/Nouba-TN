// Auteurs : May Hamrouni & Firas Jebiniani
/* ============================================================
   BANNIÈRE ANIMÉE — date et heure en temps réel
   ============================================================ */

/**
 * Met à jour le texte de la bannière défilante avec la date
 * et l'heure actuelles.
 */
function mettreAJourBanniere() {
  var banniere = document.getElementById("banniere-texte");
  if (!banniere) return;

  var maintenant = new Date();

  /* Formatage de la date : jour/mois/année */
  var jour  = String(maintenant.getDate()).padStart(2, "0");
  var mois  = String(maintenant.getMonth() + 1).padStart(2, "0");
  var annee = maintenant.getFullYear();
  var date  = jour + "/" + mois + "/" + annee;

  /* Formatage de l'heure : hh:mm:ss */
  var heures   = String(maintenant.getHours()).padStart(2, "0");
  var minutes  = String(maintenant.getMinutes()).padStart(2, "0");
  var secondes = String(maintenant.getSeconds()).padStart(2, "0");
  var heure    = heures + ":" + minutes + ":" + secondes;

  /* Mise à jour du contenu de la bannière */
  banniere.textContent =
    "🎵 Bienvenu au site web NoubaTN ! " +
    "Aujourd'hui " + date +
    ", et l'heure actuelle est " + heure +
    "     •••     ";
}

/* Appel immédiat + mise à jour chaque seconde */
document.addEventListener("DOMContentLoaded", function () {
  mettreAJourBanniere();
  setInterval(mettreAJourBanniere, 1000);
});




/* ============================================================
   GALERIE D'IMAGES 
   ============================================================ */

/* Tableau des images de la galerie avec leur légende associée */
var imagesGalerie = [
  { src: "cheikh1.jpg", legende: "Khemaïs Tarnane — Père de La Rachidia (1894–1964)" },
  { src: "cheikh2.jpg", legende: "Ali Sriti — Maître de l'Oud (1919–2007)" },
  { src: "cheikh3.jpg", legende: "Salah El Mahdi — Le Ziriab tunisien (1925–2014)" },
  { src: "cheikh4.jpg", legende: "Zied Gharsa — Ténor contemporain du Malouf" },
  { src: "cheikh5.jpg", legende: "Lotfi Bouchnak — Ambassadeur du Malouf dans le monde" },
  { src: "cheikh6.jpg", legende: "Hédi Jouini — Légende de la chanson tunisienne (1909–1990)" }
];

var indexImageCourante = 0; // Index de l'image actuellement affichée

/**
 * Affiche l'image à l'index donné avec une transition de fondu.
 * @param {number} index - index dans le tableau imagesGalerie
 */
function afficherImage(index) {
  var imgEl    = document.getElementById("galerie-img");
  var legendeEl = document.getElementById("galerie-legende");
  var pointsEl  = document.getElementById("galerie-points");

  if (!imgEl) return; // Sécurité : si l'élément n'existe pas, on sort

  /* Effet de fondu : on rend l'image invisible avant de la changer */
  imgEl.style.opacity = "0";

  /* Après 400ms (durée de la transition CSS), on change l'image */
  setTimeout(function () {
    imgEl.src             = imagesGalerie[index].src;
    imgEl.alt             = imagesGalerie[index].legende;
    legendeEl.textContent = imagesGalerie[index].legende;

    /* On rend l'image visible à nouveau */
    imgEl.style.opacity = "1";

    /* Mise à jour des points indicateurs */
    mettreAJourPoints(index);
  }, 400);
}

/**
 * Passe à l'image suivante dans la galerie (boucle en fin de tableau).
 */
function imagesSuivante() {
  indexImageCourante = (indexImageCourante + 1) % imagesGalerie.length;
  afficherImage(indexImageCourante);
}

/**
 * Passe à l'image précédente dans la galerie.
 */
function imagePrecedente() {
  indexImageCourante = (indexImageCourante - 1 + imagesGalerie.length) % imagesGalerie.length;
  afficherImage(indexImageCourante);
}

/**
 * Met à jour l'état des points indicateurs de navigation (• actif / • inactif).
 * @param {number} indexActif - index de l'image actuellement affichée
 */
function mettreAJourPoints(indexActif) {
  var points = document.querySelectorAll(".galerie-point");
  for (var i = 0; i < points.length; i++) {
    // Active la classe "actif" uniquement pour le point correspondant
    points[i].classList.toggle("actif", i === indexActif);
  }
}

/**
 * Génère dynamiquement les points de navigation de la galerie.
 * Un point par image, cliquable pour afficher l'image correspondante.
 */
function creerPointsGalerie() {
  var conteneur = document.getElementById("galerie-points");
  if (!conteneur) return;

  /* Création d'un point <span> pour chaque image */
  for (var i = 0; i < imagesGalerie.length; i++) {
    var point = document.createElement("span");
    point.className = "galerie-point";
    if (i === 0) point.classList.add("actif"); // Premier point actif par défaut

    /* Utilisation d'une IIFE pour capturer la valeur de i dans la closure */
    (function (idx) {
      point.addEventListener("click", function () {
        indexImageCourante = idx;
        afficherImage(idx);
        /* Réinitialisation du timer automatique pour éviter un saut */
        clearInterval(timerGalerie);
        timerGalerie = setInterval(imagesSuivante, 3000);
      });
    })(i);

    conteneur.appendChild(point);
  }
}

/* Variable globale du timer pour pouvoir le réinitialiser */
var timerGalerie;

/* ============================================================
   3. INITIALISATION AU CHARGEMENT DE LA PAGE
   ============================================================ */
document.addEventListener("DOMContentLoaded", function () {
  creerPointsGalerie();
  afficherImage(0); // affiche la première image au chargement

  /* Rotation automatique toutes les 3 secondes (3000 millisecondes) */
  timerGalerie = setInterval(imagesSuivante, 3000);

  /* --- Boutons de navigation manuelle de la galerie --- */
  var btnPrev = document.getElementById("galerie-prev");
  var btnNext = document.getElementById("galerie-next");

  if (btnPrev) {
    btnPrev.addEventListener("click", function () {
      imagePrecedente();
      /* On remet le compteur à zéro pour ne pas sauter une image */
      clearInterval(timerGalerie);
      timerGalerie = setInterval(imagesSuivante, 3000);
    });
  }

  if (btnNext) {
    btnNext.addEventListener("click", function () {
      imagesSuivante();
      clearInterval(timerGalerie);
      timerGalerie = setInterval(imagesSuivante, 3000);
    });
  }
});
