// Auteurs : May Hamrouni & Firas Jebiniani
/*
 * ============================================================
 * NoubaTN — js/funpage.js
 * Auteurs : [Membres de l'équipe]
 * Description : Quiz musical interactif sur le Malouf tunisien
 *               - Questions générées dynamiquement (tableau d'objets)
 *               - Gestion des événements avec addEventListener()
 *               - Démonstration de la propagation des événements (bubbling)
 *               - Utilisation de event.stopPropagation()
 *               - Score, progression et animations
 * ============================================================
 */

/* ============================================================
   1. CONSTRUCTEUR D'OBJET — Question
   Chaque question du quiz est un objet avec ses propriétés.
   ============================================================ */
function Question(enonce, choix, bonneReponse, explication) {
  this.enonce      = enonce;       // Texte de la question
  this.choix       = choix;        // Tableau des 4 choix possibles
  this.bonneReponse = bonneReponse; // Index de la bonne réponse (0 à 3)
  this.explication = explication;  // Explication affichée après la réponse
}

/* ============================================================
   2. TABLEAU DE QUESTIONS
   10 questions sur les Cheikhs, Noubas et instruments.
   Chaque élément est un objet Question.
   ============================================================ */
var lesQuestions = [
  new Question(
    "En quelle année La Rachidia a-t-elle été fondée ?",
    ["1920", "1934", "1956", "1948"],
    1,
    "La Rachidia a été fondée en 1934 par Khemaïs Tarnane et d'autres maîtres du Malouf."
  ),
  new Question(
    "Quel est le nombre de Noubas dans le Malouf tunisien ?",
    ["9", "11", "13", "15"],
    2,
    "Le Malouf tunisien est organisé en 13 Noubas, chacune correspondant à un mode musical distinct."
  ),
  new Question(
    "Qui est surnommé 'Le Pavarotti tunisien' ?",
    ["Zied Gharsa", "Ali Sriti", "Lotfi Bouchnak", "Hédi Jouini"],
    2,
    "Lotfi Bouchnak est surnommé 'le Pavarotti tunisien' pour son ténor d'exception."
  ),
  new Question(
    "Quel instrument est considéré comme le 'roi' du Malouf ?",
    ["La Darbouka", "Le Kanoun", "L'Oud", "Le Nay"],
    2,
    "L'Oud (لعود) est le luth à cordes pincées, instrument roi de la musique arabo-andalouse."
  ),
  new Question(
    "Salah El Mahdi a composé quel hymne national ?",
    ["Ala Khallidi", "Humat Al Hima", "Biladi", "Mawtini"],
    0,
    "Salah El Mahdi a composé Ala Khallidi (الا خلدي), l'hymne national tunisien, en 1958."
  ),
  new Question(
    "De quelle ville Khemaïs Tarnane est-il originaire ?",
    ["Tunis", "Sfax", "Bizerte", "Testour"],
    2,
    "Khemaïs Tarnane (1894–1964) est originaire de Bizerte, au nord de la Tunisie."
  ),
  new Question(
    "Quelle ville tunisienne est considérée comme le berceau du Malouf ?",
    ["Tunis", "Testour", "Kairouan", "Hammamet"],
    1,
    "Testour, dans la région de Béja, est le berceau historique du Malouf tunisien, peuplée de descendants andalous."
  ),
  new Question(
    "Que signifie le mot 'Malouf' (المألوف) en arabe ?",
    ["Mélodie ancienne", "Ce qui est familier, cher", "Musique de fête", "Chant sacré"],
    1,
    "'Malouf' signifie 'ce qui est habituel, familier, ce qui nous est cher' en arabe."
  ),
  new Question(
    "Quel philosophe est associé à la codification du Malouf ?",
    ["Ibn Khaldoun", "Avicenne", "Ziryab", "Al-Farabi"],
    2,
    "Ziryab (789–857), musicologue et philosophe arabe, est associé à la codification des 13 Noubas."
  ),
  new Question(
    "Combien de compositions Hédi Jouini a-t-il signées approximativement ?",
    ["200", "500", "1070", "350"],
    2,
    "Hédi Jouini a signé près de 1 070 compositions et 56 opérettes tout au long de sa carrière."
  )
];

/* ============================================================
   3. VARIABLES GLOBALES D'ÉTAT DU QUIZ
   ============================================================ */
var indexQuestionCourante = 0;  // Index de la question actuellement affichée
var score = 0;                   // Score du joueur (nombre de bonnes réponses)
var reponduCourante = false;     // Empêche de répondre deux fois à la même question

/* ============================================================
   4. AFFICHAGE D'UNE QUESTION
   Génère dynamiquement le HTML d'une question et ses boutons réponse.
   @param {number} index - index de la question dans lesQuestions
   ============================================================ */
function afficherQuestion(index) {
  var question = lesQuestions[index];

  /* Réinitialisation du flag de réponse pour la nouvelle question */
  reponduCourante = false;

  /* --- Mise à jour de l'en-tête du quiz --- */
  document.getElementById("numero-question").textContent = (index + 1) + " / " + lesQuestions.length;
  document.getElementById("texte-question").textContent  = question.enonce;
  document.getElementById("explication-quiz").style.display = "none";

  /* Mise à jour de la barre de progression */
  var pourcentage = ((index) / lesQuestions.length) * 100;
  document.getElementById("barre-progression").style.width = pourcentage + "%";

  /* --- Génération des boutons de réponse --- */
  var conteneurChoix = document.getElementById("conteneur-choix");
  conteneurChoix.innerHTML = ""; // On vide les anciens boutons

  for (var i = 0; i < question.choix.length; i++) {
    var btn = document.createElement("button");
    btn.className   = "btn-reponse";
    btn.textContent = question.choix[i];
    btn.dataset.index = i; // Stocke l'index dans un attribut data-

    /*
     * === GESTION DES ÉVÉNEMENTS avec addEventListener() ===
     * Chaque bouton écoute l'événement "click".
     * On utilise une IIFE pour capturer la valeur correcte de i.
     */
    (function (indexChoix) {
      btn.addEventListener("click", function (event) {
        /*
         * === PROPAGATION DES ÉVÉNEMENTS (Event Bubbling) ===
         * Sans stopPropagation(), l'événement remonterait :
         *   button.btn-reponse → div.conteneur-choix → div.zone-quiz → div.quiz-wrapper
         * Ici, on LAISSE le bubbling se produire pour le montrer,
         * sauf sur le div parent où on l'arrête (voir plus bas).
         */
        traiterReponse(indexChoix, this, event);
      });
    })(i);

    conteneurChoix.appendChild(btn);
  }
}

/* ============================================================
   5. TRAITEMENT D'UNE RÉPONSE
   Vérifie la réponse, met à jour le score, affiche le résultat.
   @param {number}      indexChoix - index du choix cliqué (0 à 3)
   @param {HTMLElement} btnClique  - le bouton qui a été cliqué
   @param {Event}       event      - l'événement click original
   ============================================================ */
function traiterReponse(indexChoix, btnClique, event) {
  /* Sécurité : si déjà répondu, on ignore le clic */
  if (reponduCourante) return;
  reponduCourante = true;

  var question = lesQuestions[indexQuestionCourante];

  /* --- Désactivation de tous les boutons --- */
  var tousLesBoutons = document.querySelectorAll(".btn-reponse");
  for (var i = 0; i < tousLesBoutons.length; i++) {
    tousLesBoutons[i].disabled = true;
  }

  /* --- Vérification de la réponse --- */
  if (indexChoix === question.bonneReponse) {
    /* Bonne réponse */
    score++;
    btnClique.classList.add("correct");
    document.getElementById("feedback-icone").textContent = "";
    document.getElementById("feedback-texte").textContent = "Bonne réponse !";
    document.getElementById("feedback-quiz").className    = "feedback-quiz correct";
  } else {
    /* Mauvaise réponse : marquer le bon choix en vert aussi */
    btnClique.classList.add("incorrect");
    tousLesBoutons[question.bonneReponse].classList.add("correct");
    document.getElementById("feedback-icone").textContent = "";
    document.getElementById("feedback-texte").textContent = "Raté ! La bonne réponse était : " + question.choix[question.bonneReponse];
    document.getElementById("feedback-quiz").className    = "feedback-quiz incorrect";
  }

  /* Affichage du feedback */
  document.getElementById("feedback-quiz").style.display = "flex";

  /* Affichage de l'explication */
  var explication = document.getElementById("explication-quiz");
  explication.textContent = " " + question.explication;
  explication.style.display = "block";

  /* Mise à jour du score affiché */
  document.getElementById("score-actuel").textContent = score;
}

/* ============================================================
   6. PASSAGE À LA QUESTION SUIVANTE
   ============================================================ */
function questionSuivante() {
  indexQuestionCourante++;

  if (indexQuestionCourante < lesQuestions.length) {
    /* Il reste des questions → on affiche la suivante */
    afficherQuestion(indexQuestionCourante);
    document.getElementById("feedback-quiz").style.display = "none";
  } else {
    /* Plus de questions → afficher les résultats finaux */
    afficherResultats();
  }
}

/* ============================================================
   7. AFFICHAGE DES RÉSULTATS FINAUX
   ============================================================ */
function afficherResultats() {
  /* Masquer la zone de quiz */
  document.getElementById("zone-quiz").style.display      = "none";
  /* Afficher la zone de résultats */
  document.getElementById("zone-resultats").style.display = "block";

  /* Barre de progression à 100% */
  document.getElementById("barre-progression").style.width = "100%";

  /* Calcul du pourcentage */
  var pct = Math.round((score / lesQuestions.length) * 100);

  /* Affichage du score final */
  document.getElementById("score-final").textContent    = score + " / " + lesQuestions.length;
  document.getElementById("pct-final").textContent      = pct + "%";

  /* Message selon le score */
  var message;
  if      (pct === 100) message = " Parfait ! Tu es un véritable maître du Malouf !";
  else if (pct >= 80)   message = " Excellent ! Tu connais très bien le patrimoine tunisien !";
  else if (pct >= 60)   message = " Bien joué ! Tu as de bonnes bases sur le Malouf.";
  else if (pct >= 40)   message = " Pas mal ! Explore le site pour en apprendre davantage.";
  else                  message = " Continue d'explorer NoubaTN pour découvrir le Malouf !";

  document.getElementById("message-final").textContent = message;
}

/* ============================================================
   8. REDÉMARRAGE DU QUIZ
   Réinitialise toutes les variables et repart du début.
   ============================================================ */
function redemarrerQuiz() {
  /* Remise à zéro des variables d'état */
  indexQuestionCourante = 0;
  score = 0;
  reponduCourante = false;

  /* Mise à jour de l'affichage du score */
  document.getElementById("score-actuel").textContent = "0";

  /* Réaffichage de la zone de quiz */
  document.getElementById("zone-quiz").style.display      = "block";
  document.getElementById("zone-resultats").style.display = "none";
  document.getElementById("feedback-quiz").style.display  = "none";

  /* Réinitialisation de la barre de progression */
  document.getElementById("barre-progression").style.width = "0%";

  /* Affichage de la première question */
  afficherQuestion(0);
}

/* ============================================================
   9. DÉMONSTRATION DE LA PROPAGATION DES ÉVÉNEMENTS
   Illustre le bubbling sur 3 niveaux imbriqués :
   div#demo-outer → div#demo-milieu → button#demo-inner
   ============================================================ */
function initialiserDemoPropagation() {
  var journal = document.getElementById("journal-propagation");

  /**
   * Ajoute une entrée dans le journal de propagation.
   * @param {string} message - texte à afficher
   * @param {string} couleur - couleur de l'entrée
   */
  function logPropagation(message, couleur) {
    var ligne = document.createElement("div");
    ligne.className   = "log-ligne";
    ligne.style.borderLeftColor = couleur;
    ligne.textContent = "→ " + message;
    journal.appendChild(ligne);

    /* Auto-scroll vers le bas du journal */
    journal.scrollTop = journal.scrollHeight;
  }

  /* --- Niveau 1 : div extérieur (demo-outer) --- */
  var outer = document.getElementById("demo-outer");
  if (outer) {
    outer.addEventListener("click", function (event) {
      /*
       * Cet événement reçoit le clic qui "remonte" (bubble)
       * depuis les éléments enfants OU vient directement de ce div.
       */
      logPropagation(
        "Niveau 3 — DIV EXTÉRIEUR (#demo-outer) a reçu l'événement",
        "#E0A458" /* couleur dorée du site */
      );
    });
  }

  /* --- Niveau 2 : div du milieu (demo-milieu) --- */
  var milieu = document.getElementById("demo-milieu");
  if (milieu) {
    milieu.addEventListener("click", function (event) {
      logPropagation(
        "Niveau 2 — DIV INTERMÉDIAIRE (#demo-milieu) a reçu l'événement",
        "#3498db" /* bleu */
      );
      /*
       * Ici on N'appelle PAS stopPropagation() → l'événement continue
       * de remonter vers #demo-outer.
       */
    });
  }

  /* --- Niveau 3 : bouton intérieur (demo-inner) SANS stopPropagation --- */
  var btnSans = document.getElementById("demo-inner-sans");
  if (btnSans) {
    btnSans.addEventListener("click", function (event) {
      logPropagation(
        "Niveau 1 — BOUTON (#demo-inner-sans) a reçu l'événement → propagation ACTIVE",
        "#2ecc71" /* vert */
      );
      /*
       * Sans stopPropagation() : l'événement va remonter vers
       * #demo-milieu puis #demo-outer. Voir le journal !
       */
    });
  }

  /* --- Niveau 3 : bouton intérieur (demo-inner) AVEC stopPropagation --- */
  var btnAvec = document.getElementById("demo-inner-avec");
  if (btnAvec) {
    btnAvec.addEventListener("click", function (event) {
      logPropagation(
        "Niveau 1 — BOUTON (#demo-inner-avec) a reçu l'événement → stopPropagation() appelé !",
        "#e74c3c" /* rouge */
      );

      /*
       * === event.stopPropagation() ===
       * Empêche l'événement de remonter vers les parents.
       * Après cette ligne, #demo-milieu et #demo-outer NE recevront PAS l'événement.
       */
      event.stopPropagation();

      logPropagation(
        " Propagation STOPPÉE — les parents ne recevront pas l'événement",
        "#e74c3c"
      );
    });
  }

  /* --- Bouton pour effacer le journal --- */
  var btnEffacer = document.getElementById("btn-effacer-journal");
  if (btnEffacer) {
    btnEffacer.addEventListener("click", function (event) {
      journal.innerHTML = "";
      /* stopPropagation pour ne pas déclencher les autres listeners */
      event.stopPropagation();
    });
  }
}

/* ============================================================
   10. INITIALISATION AU CHARGEMENT DE LA PAGE
   ============================================================ */
document.addEventListener("DOMContentLoaded", function () {

  /* --- Lancement du quiz --- */
  afficherQuestion(0);

  /* --- Bouton "Question suivante" --- */
  var btnSuivant = document.getElementById("btn-suivant");
  if (btnSuivant) {
    btnSuivant.addEventListener("click", function () {
      /* On ne peut passer à la suite que si on a répondu */
      if (reponduCourante) {
        questionSuivante();
      } else {
        /* Message d'invitation à répondre */
        var feedback = document.getElementById("feedback-quiz");
        feedback.style.display = "flex";
        document.getElementById("feedback-icone").textContent = "";
        document.getElementById("feedback-texte").textContent = "Veuillez d'abord choisir une réponse !";
        feedback.className = "feedback-quiz avertissement";
      }
    });
  }

  /* --- Bouton "Recommencer" --- */
  var btnRestart = document.getElementById("btn-restart");
  if (btnRestart) {
    btnRestart.addEventListener("click", redemarrerQuiz);
  }

  /* --- Initialisation de la démo de propagation --- */
  initialiserDemoPropagation();

  /* --- Effet hover sur le titre avec événements souris --- */
  var titreQuiz = document.getElementById("titre-quiz");
  if (titreQuiz) {
    titreQuiz.addEventListener("mouseover", function () {
      this.style.color = "var(--lime)";
    });
    titreQuiz.addEventListener("mouseout", function () {
      this.style.color = "var(--blue-primary)";
    });
  }
});
