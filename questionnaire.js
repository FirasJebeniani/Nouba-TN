// Auteurs : May Hamrouni & Firas Jebiniani
/*
 * ============================================================
 * NoubaTN — js/questionnaire.js
 * Auteurs : [Membres de l'équipe]
 * Description : Validation JavaScript du formulaire questionnaire
 *               - Validation du nom (non vide, min 2 caractères)
 *               - Validation de l'email (format valide)
 *               - Validation d'au moins 1 checkbox cochée
 *               - Affichage des erreurs en temps réel
 *               - Affichage du récapitulatif après soumission
 * ============================================================
 */

/* ============================================================
   1. FONCTIONS DE VALIDATION INDIVIDUELLES
   Chaque fonction retourne true si valide, false sinon,
   et affiche le message d'erreur approprié.
   ============================================================ */

/**
 * Valide le champ Nom : non vide et au moins 2 caractères.
 * @returns {boolean} true si valide
 */
function validerNom() {
  var champ   = document.getElementById("q-nom");
  var erreur  = document.getElementById("err-nom");
  var valeur  = champ.value.trim();

  if (valeur === "") {
    // Cas 1 : champ vide
    afficherErreur(champ, erreur, "Le nom est obligatoire.");
    return false;
  } else if (valeur.length < 2) {
    // Cas 2 : trop court
    afficherErreur(champ, erreur, "Le nom doit contenir au moins 2 caractères.");
    return false;
  } else {
    // Cas valide
    effacerErreur(champ, erreur);
    return true;
  }
}

/**
 * Valide le champ Email : non vide et format valide.
 * Utilise une expression régulière pour vérifier le format.
 * @returns {boolean} true si valide
 */
function validerEmail() {
  var champ  = document.getElementById("q-email");
  var erreur = document.getElementById("err-email");
  var valeur = champ.value.trim();

  /* Expression régulière pour vérifier un email valide */
  var regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (valeur === "") {
    afficherErreur(champ, erreur, "L'adresse e-mail est obligatoire.");
    return false;
  } else if (!regexEmail.test(valeur)) {
    afficherErreur(champ, erreur, "Veuillez entrer une adresse e-mail valide (ex: nom@domaine.tn).");
    return false;
  } else {
    effacerErreur(champ, erreur);
    return true;
  }
}

/**
 * Valide que le niveau de connaissance (radio) est sélectionné.
 * @returns {boolean} true si au moins un radio est coché
 */
function validerNiveau() {
  var radios = document.querySelectorAll('input[name="niveau"]');
  var erreur = document.getElementById("err-niveau");
  var selectionne = false;

  /* Parcours de tous les boutons radio pour vérifier si l'un est coché */
  for (var i = 0; i < radios.length; i++) {
    if (radios[i].checked) {
      selectionne = true;
      break;
    }
  }

  if (!selectionne) {
    erreur.textContent  = "Veuillez sélectionner votre niveau de connaissance.";
    erreur.style.display = "block";
    return false;
  } else {
    erreur.textContent  = "";
    erreur.style.display = "none";
    return true;
  }
}

/**
 * Valide que l'utilisateur a coché au moins un Cheikh préféré.
 * @returns {boolean} true si au moins une checkbox est cochée
 */
function validerCheikhs() {
  var cases  = document.querySelectorAll('input[name="cheikh"]');
  var erreur = document.getElementById("err-cheikhs");
  var nbCoches = 0;

  /* Comptage des cases cochées */
  for (var i = 0; i < cases.length; i++) {
    if (cases[i].checked) nbCoches++;
  }

  if (nbCoches === 0) {
    erreur.textContent   = "Veuillez sélectionner au moins un Cheikh préféré.";
    erreur.style.display = "block";
    return false;
  } else {
    erreur.textContent   = "";
    erreur.style.display = "none";
    return true;
  }
}

/**
 * Valide le commentaire : obligatoire et au moins 10 caractères.
 * @returns {boolean} true si valide
 */
function validerCommentaire() {
  var champ  = document.getElementById("q-commentaire");
  var erreur = document.getElementById("err-commentaire");
  var valeur = champ.value.trim();

  if (valeur === "") {
    afficherErreur(champ, erreur, "Le commentaire est obligatoire.");
    return false;
  } else if (valeur.length < 10) {
    afficherErreur(champ, erreur, "Le commentaire doit contenir au moins 10 caractères.");
    return false;
  } else {
    effacerErreur(champ, erreur);
    return true;
  }
}

/* ============================================================
   2. FONCTIONS UTILITAIRES D'AFFICHAGE D'ERREUR
   ============================================================ */

/**
 * Affiche un message d'erreur et marque le champ en rouge.
 * @param {HTMLElement} champ  - le champ input concerné
 * @param {HTMLElement} erreur - le div d'affichage de l'erreur
 * @param {string}      msg    - le message à afficher
 */
function afficherErreur(champ, erreur, msg) {
  champ.style.borderColor  = "#c0392b";  // rouge
  champ.style.boxShadow    = "0 0 0 3px rgba(192,57,43,0.15)";
  erreur.textContent       = msg;
  erreur.style.display     = "block";
}

/**
 * Efface le message d'erreur et remet le style normal du champ.
 * @param {HTMLElement} champ  - le champ input concerné
 * @param {HTMLElement} erreur - le div d'affichage de l'erreur
 */
function effacerErreur(champ, erreur) {
  champ.style.borderColor = "#2ecc71";   // vert = valide
  champ.style.boxShadow   = "0 0 0 3px rgba(46,204,113,0.15)";
  erreur.textContent      = "";
  erreur.style.display    = "none";
}

/* ============================================================
   3. AFFICHAGE DU RÉCAPITULATIF APRÈS SOUMISSION
   Construit et affiche un résumé des réponses de l'utilisateur.
   ============================================================ */

/**
 * Construit et affiche le récapitulatif des réponses.
 */
function afficherRecapitulatif() {
  /* Lecture des valeurs du formulaire */
  var nom      = document.getElementById("q-nom").value.trim();
  var email    = document.getElementById("q-email").value.trim();
  var note     = document.getElementById("q-note").value;
  var nouba    = document.getElementById("q-nouba").value;
  var commentaire = document.getElementById("q-commentaire").value.trim();

  /* Niveau sélectionné (radio) */
  var niveauRadio = document.querySelector('input[name="niveau"]:checked');
  var niveau = niveauRadio ? niveauRadio.value : "Non renseigné";

  /* Cheikhs cochés (checkboxes) → liste formatée */
  var casesCheikhsCochees = document.querySelectorAll('input[name="cheikh"]:checked');
  var cheikhs = [];
  for (var i = 0; i < casesCheikhsCochees.length; i++) {
    cheikhs.push(casesCheikhsCochees[i].value);
  }
  var cheikshStr = cheikhs.length > 0 ? cheikhs.join(", ") : "Aucun";

  /* Injection dans le div récapitulatif */
  document.getElementById("recap-nom").textContent    = nom;
  document.getElementById("recap-email").textContent  = email;
  document.getElementById("recap-niveau").textContent = niveau;
  document.getElementById("recap-cheikhs").textContent = cheikshStr;
  document.getElementById("recap-nouba").textContent  = nouba || "Non renseignée";
  document.getElementById("recap-note").textContent   = note + " / 10";
  document.getElementById("recap-comment").textContent = commentaire;

  /* Affichage de la section récapitulatif et masquage du formulaire */
  document.getElementById("section-formulaire").style.display = "none";
  document.getElementById("section-recap").style.display      = "block";

  /* Scroll vers le récapitulatif */
  document.getElementById("section-recap").scrollIntoView({ behavior: "smooth" });
}

/* ============================================================
   4. SOUMISSION DU FORMULAIRE — Validation globale
   ============================================================ */

/**
 * Gère la soumission du formulaire :
 * - Lance toutes les validations
 * - Si tout est valide → affiche le récapitulatif
 * - Sinon → affiche les erreurs
 */
function soumettreQuestionnaire(event) {
  /* Empêche le rechargement de la page (comportement par défaut du form) */
  event.preventDefault();

  /* Exécution de toutes les validations — on les stocke pour tout valider
     même si la première échoue (pour afficher toutes les erreurs d'un coup) */
  var nomOk       = validerNom();
  var emailOk     = validerEmail();
  var niveauOk    = validerNiveau();
  var cheikhsOk   = validerCheikhs();
  var commentOk   = validerCommentaire();

  /* Si toutes les validations passent → afficher le récapitulatif */
  if (nomOk && emailOk && niveauOk && cheikhsOk && commentOk) {
    afficherRecapitulatif();
  } else {
    /* Sinon, scroll vers la première erreur visible */
    var premiereErreur = document.querySelector('.msg-erreur[style*="block"]');
    if (premiereErreur) {
      premiereErreur.scrollIntoView({ behavior: "smooth", block: "center" });
    }
  }
}

/* ============================================================
   5. MISE À JOUR DE L'AFFICHAGE DU SLIDER DE NOTE
   ============================================================ */

/**
 * Met à jour l'affichage de la valeur du slider en temps réel.
 */
function mettreAJourNote() {
  var slider  = document.getElementById("q-note");
  var affiche = document.getElementById("valeur-note");
  if (slider && affiche) {
    affiche.textContent = slider.value + " / 10";
  }
}

/* ============================================================
   6. INITIALISATION AU CHARGEMENT DE LA PAGE
   ============================================================ */
document.addEventListener("DOMContentLoaded", function () {

  /* --- Attacher la fonction de soumission au formulaire --- */
  var formulaire = document.getElementById("form-questionnaire");
  if (formulaire) {
    formulaire.addEventListener("submit", soumettreQuestionnaire);
  }

  /* --- Validation en temps réel sur les champs texte --- */
  var champNom = document.getElementById("q-nom");
  if (champNom) {
    /* "blur" = quand l'utilisateur quitte le champ */
    champNom.addEventListener("blur", validerNom);
    /* "input" = à chaque frappe, pour effacer l'erreur si on corrige */
    champNom.addEventListener("input", function () {
      if (this.value.trim().length >= 2) effacerErreur(this, document.getElementById("err-nom"));
    });
  }

  var champEmail = document.getElementById("q-email");
  if (champEmail) {
    champEmail.addEventListener("blur", validerEmail);
    champEmail.addEventListener("input", function () {
      var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (regex.test(this.value.trim())) effacerErreur(this, document.getElementById("err-email"));
    });
  }

  /* --- Mise à jour du slider de note --- */
  var slider = document.getElementById("q-note");
  if (slider) {
    slider.addEventListener("input", mettreAJourNote);
    mettreAJourNote(); // Initialisation
  }

  /* --- Bouton "Modifier mes réponses" dans le récapitulatif --- */
  var btnModifier = document.getElementById("btn-modifier");
  if (btnModifier) {
    btnModifier.addEventListener("click", function () {
      document.getElementById("section-recap").style.display      = "none";
      document.getElementById("section-formulaire").style.display = "block";
    });
  }
});
