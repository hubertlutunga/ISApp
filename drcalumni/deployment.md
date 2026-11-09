# DRC ALUMNI IMPACT 2026 — DEPLOYMENT & MASTER PROMPT COPILOT

> **Projet :** plateforme officielle de candidatures et de gestion des participants au DRC Alumni Impact 2026  
> **Client :** Ambassade des États-Unis à Kinshasa — Section des Affaires publiques  
> **Dossier cible :** `invitationspeciale/drcalumni/`  
> **Projet de référence :** `invitationspeciale/creatorsbomoko/`  
> **Livrable :** application publique + back-office + notifications + QR codes + contrôle d'accès + documentation technique.

---

## 0. PROMPT MAÎTRE À TRANSMETTRE À GITHUB COPILOT

Tu es un **développeur full-stack senior, architecte logiciel et UI/UX designer de niveau international**. Ta mission est de **DÉVELOPPER réellement**, et pas seulement de proposer une maquette, la plateforme complète **DRC ALUMNI IMPACT 2026** dans le dossier `invitationspeciale/drcalumni/`.

### Référence obligatoire

1. **Analyse d'abord en profondeur** le projet EXISTANT `invitationspeciale/creatorsbomoko/`, y compris les pages publiques, le formulaire, les modèles SQL, l'authentification, le back-office, le processus d'approbation/rejet, les envois d'e-mails, la génération des QR codes, le scan à l'entrée, les exports, la fermeture des inscriptions, les paramètres et la sécurité.
2. Réutilise **les fonctionnalités et les principes d'architecture réellement présents**, en les adaptant au nouvel événement. **N'invente pas** l'existence de modules non trouvés : signale-les puis implémente les modules demandés dans ce document.
3. **Ne modifie, ne supprime et ne casse aucun fichier, configuration, table, donnée, route ou comportement de `creatorsbomoko`**. Le projet `drcalumni` doit être autonome, avec ses propres tables, paramètres, identifiants et fichiers, même si des composants sont copiés et adaptés.
4. Reprends la stack existante si elle est appropriée au serveur (probablement PHP/MySQL, **à vérifier dans le code**). Ne migre pas vers un framework nécessitant une infrastructure absente. Inspecte d'abord versions PHP, dépendances, gestion des e-mails, QR et structure des dossiers.
5. **Design totalement nouveau** et premium, qui ne ressemble pas visuellement à Creators Bomoko, avec des couleurs inspirées du drapeau américain. Le résultat doit être digne d'un site événementiel institutionnel de l'Ambassade des États-Unis : composition haut de gamme, typographie impeccable, cohérence, accessibilité, animations discrètes, performance irréprochable, présentation mobile et desktop soignée.
6. Le formulaire doit reproduire fidèlement **toutes les questions et tous les choix** des documents transmis par l'Ambassade. Les modèles d'e-mails de sélection et de non-sélection figurent ci-dessous : préserver leur rédaction officielle, hormis personnalisation du nom et ajout clairement séparé du QR code/identifiant pratique.
7. Travaille **par étapes**, code les fonctionnalités, réalise les migrations et tests, et fournis un rapport précis des fichiers créés/modifiés, des fonctions testées et des étapes de mise en production. Ne te limite pas à un plan ou à une réponse descriptive.
8. Si une décision métier reste à confirmer (par exemple fermeture des candidatures ou critères de sélection), rends-la **configurable dans le back-office**, sans inventer un choix officiel.

**Priorité absolue : qualité du design + intégralité du parcours de candidature + robustesse du back-office + respect des données et des contenus de l'Ambassade.**

---

## 1. IDENTITÉ DE L'ÉVÉNEMENT

- **Nom officiel :** DRC ALUMNI IMPACT 2026
- **Thème officiel :** « Faire progresser le partenariat stratégique et les opportunités économiques entre les États-Unis et la RDC »
- **Dates :** 1er et 2 décembre 2026
- **Heure de présence demandée :** 9h00
- **Lieu :** The Garden, situé en face de l'entrée de l'hôtel Pullman donnant sur l'avenue Colonel Tshatshi, Kinshasa.
- **Durée :** deux jours
- **Public :** anciens participants aux programmes d'échanges du gouvernement américain, venant des différentes régions de la RDC
- **Capacité annoncée :** 250 participants sélectionnés. Il s'agit d'un objectif/capacité de sélection, **pas d'une raison de fermer automatiquement les candidatures à 250 enregistrements reçus**.
- **Contacts mentionnés dans l'e-mail officiel :** `LungunguVK@state.gov` et `KazadiNN@state.gov`.
- **Fuseau horaire :** `Africa/Kinshasa`.

### Routes proposées (adapter aux conventions du projet de référence)

- Site public : `/drcalumni/`
- Formulaire public : `/drcalumni/` ou `/drcalumni/candidature/`
- Page de confirmation de soumission : `/drcalumni/confirmation/`
- Back-office sécurisé : `/drcalumni/admin/`
- Contrôle des entrées : `/drcalumni/admin/scan/` (ou route équivalente protégée)

Toutes les routes doivent fonctionner sous le préfixe `/drcalumni/` sans casser les chemins des assets et sans redirection involontaire vers `/creatorsbomoko/`.

---

## 2. DIRECTION ARTISTIQUE — EXIGENCE MAJEURE

Créer **une vraie identité digitale distinctive**, différente de Creators Bomoko, inspirée des standards des sites institutionnels internationaux et des belles landing pages de conférences, sans recopier l'habillage d'un autre site.

### Palette graphique

| Usage | Couleur | Code |
|---|---|---|
| Bleu institutionnel principal | U.S. flag navy | `#002868` |
| Rouge américain — accent | U.S. flag red | `#BF0A30` |
| Blanc — fond et respiration | White | `#FFFFFF` |
| Bleu nuit — surfaces premium | Deep navy | `#071B3B` |
| Bleu pâle — sections de contraste | Ice blue | `#F2F6FC` |
| Texte principal | Ink | `#17253B` |
| Gris de bordure | Cool gray | `#DBE3EF` |

Le bleu `#002868` est la dominante. Le rouge `#BF0A30` sert aux accents, états et détails, **pas à tout colorer**. Éviter toute surcharge de drapeaux, les effets kitsch, les ombres exagérées, les dégradés agressifs et le style d'un template générique.

### Logos — chemins impératifs

- **Favicon :** `/images/use-kinshasa-flag-logo-vertical-color-whitetext.png`
- **Header, centré sur fond bleu américain :** `/images/use-kinshasa-flag-logo-vertical-color-whitetext.png`
- **Footer, sur fond blanc, deux logos :**
  - `/images/Freedom 250 Icon - RGB - Full Color - Gold.png`
  - `/images/use-kinshasa-seal-color.png`

**Important sur les chemins :** inspecter où se trouve réellement le dossier `images` dans `drcalumni`. Si les fichiers sont dans `drcalumni/images/`, leur URL publique sera normalement `/drcalumni/images/...` et non `/images/...`. Utiliser un helper `asset_url()` ou une constante `BASE_URL` permettant de générer la bonne URL **sans changer les noms de fichiers ni casser les routes**. Vérifier l'existence effective de chacun des trois assets et afficher une erreur claire si l'un manque. Ne pas substituer de logos non autorisés. Le PNG vertical en favicon est accepté par la demande ; une icône carrée dérivée pourrait être proposée ultérieurement, sans modifier le fichier fourni.

### Composition visuelle attendue

**Header :** fond navy élégant, logo vertical de l'Ambassade **réellement centré**, espace blanc généreux, navigation discrète si utile, bonne lisibilité et comportement mobile maîtrisé. Un motif graphique extrêmement discret inspiré des étoiles / bandes du drapeau américain peut être utilisé en arrière-plan, mais **pas de faux sceau ou faux logo**.

**Hero :** grand titre `DRC ALUMNI IMPACT 2026`, thème en sous-titre, labels raffinés `1–2 DÉCEMBRE 2026` et `KINSHASA`, appel à l'action « Déposer ma candidature ». Hiérarchie typographique forte, alignements précis, excellente lisibilité. Il faut ressentir le prestige de l'événement dès les premières secondes.

**Bloc événement :** présentation du sommet, ses dates et son lieu, infos sous forme de cartes élégantes et aérées. Ne pas ajouter de chiffres ou promesses non validés.

**Formulaire :** pièce maîtresse du site : **wizard en six étapes**, barre de progression, grands titres de section, carte claire sur fond légèrement contrasté, champs larges, labels visibles, réponses radio/checkbox premium, transitions légères, compteur de mots, validation contextuelle, boutons précédent/suivant, récapitulatif final, bouton de soumission clair. Sur mobile, champs pleine largeur, navigation confortable et aucune superposition.

**Footer :** **fond blanc**, les deux logos officiels côte à côte, bien proportionnés (jamais étirés), respect des marges, texte institutionnel sobre. Sur mobile : empilement soigné. Ajouter une ligne discrète indiquant que la plateforme technique est réalisée par Invitation Spéciale **uniquement si cela est autorisé par le client** ; sinon ne rien ajouter.

**Back-office :** design propre, distinct du site public mais cohérent : navigation latérale navy, barre supérieure sobre, tableaux efficaces, vues détaillées bien hiérarchisées, filtres, badges de statut accessibles, tableaux adaptés aux mobiles. Utiliser des icônes vectorielles légères et une police professionnelle chargée de façon fiable avec fallback système.

### Qualité attendue

- Responsive 320 px à grands écrans, sans débordement horizontal.
- Accessibilité : contrastes, focus clavier visible, labels explicites, messages d'erreur associés, navigation clavier, états de chargement, `prefers-reduced-motion`.
- Animations très discrètes, pas d'effets parasites.
- Performance : assets optimisés, images dimensionnées, CSS/JS minimisés selon le stack.
- États visuels complets : chargement, erreurs, succès, formulaire clos, e-mail non envoyé, QR invalide, déjà scanné.
- Ne pas laisser de textes « Lorem ipsum », logos provisoires ou boutons sans action.

---

## 3. FORMULAIRE DE CANDIDATURE — CONTENU EXACT

**Les 20 questions sont obligatoirement présentes dans le bon ordre, avec leurs énoncés et choix ci-dessous.** Les consignes entre crochets des documents sources sont des instructions de configuration, **pas des textes à afficher comme questions**. Respecter le caractère obligatoire selon le sens des champs et les mentions explicites ; les champs annoncés « facultatif » doivent rester facultatifs. Pour les autres, adopter une validation cohérente configurable.

### SECTION 1 — INFORMATIONS PERSONNELLES

**1. Nom et prénom**  
Champ texte court.

**2. Adresse e-mail**  
Champ e-mail avec vérification du format ; unicité des candidatures par e-mail après normalisation, avec message humain et sans divulgation de données.

**3. Numéro de téléphone / WhatsApp**  
Champ téléphone incluant l'indicatif pays.

**4. Quelle est votre province de résidence ?**  
Liste déroulante **à choix unique** des **26 provinces**, sans transformer les options en questions :

- Bas-Uélé ; Équateur ; Haut-Katanga ; Haut-Lomami ; Haut-Uélé ; Ituri ; Kasaï ; Kasaï-Central ; Kasaï-Oriental ; Kinshasa ; Kongo-Central ; Kwango ; Kwilu ; Lomami ; Lualaba ; Mai-Ndombe ; Maniema ; Mongala ; Nord-Kivu ; Nord-Ubangi ; Sankuru ; Sud-Kivu ; Sud-Ubangi ; Tanganyika ; Tshopo ; Tshuapa.

Champ complémentaire : **Ville ou territoire de résidence** (texte libre).

**5. Nom de votre organisation ou institution actuelle**  
Champ texte court.

### SECTION 2 — PROFIL PROFESSIONNEL

**6. Quel secteur correspond le mieux à votre activité professionnelle actuelle ?**  
Sélection **de 2 à 3 choix** conformément au document ; interface multi-sélection accessible (le document mentionne « menu déroulant », mais des cases à cocher peuvent mieux matérialiser le multichoix ; conserver toutes les options et la limite de 2 à 3) :

- Gouvernement / Secteur public
- Secteur privé
- Entrepreneuriat
- Société civile / ONG
- Enseignement / Recherche
- Médias / Communication
- Technologie / Numérique
- Arts / Culture / Industries créatives
- Autre (précisez) — champ de précision conditionnel

**7. Décrivez brièvement vos principales activités ou responsabilités professionnelles actuelles.**  
Zone de texte — **150 mots maximum**.

### SECTION 3 — PARCOURS DANS LES PROGRAMMES D'ÉCHANGE

**8. À quel(s) programme(s) du gouvernement des États-Unis avez-vous participé ?**  
Choix multiples :

- Fulbright
- Hubert H. Humphrey Fellowship
- Mandela Washington Fellowship (YALI)
- International Visitor Leadership Program (IVLP)
- English Access Scholarship Program (Access)
- Pan Africa Youth Leadership Program (PAYLP)
- Autre (précisez) — champ conditionnel

**9. En quelle année avez-vous participé à votre programme d'échange le plus récent ?**  
Année numérique à quatre chiffres, réaliste et non future.

### SECTION 4 — INTÉRÊTS ET CONTRIBUTION AU SOMMET

**10. Quels sont les trois thèmes du Sommet qui vous intéressent le plus ?**  
Cases à cocher, **maximum trois réponses** (la consigne dit « maximum trois », ne pas imposer exactement trois) :

- Partenariat stratégique États-Unis–RDC
- Diplomatie commerciale et investissement
- Minerais critiques et chaînes de valeur
- Gouvernance et État de droit
- Entrepreneuriat et innovation
- Partenariats public-privé
- Économie numérique et technologies

**11. Selon vous, quels sont les principaux domaines de coopération entre les États-Unis et la RDC ?**  
Zone de texte — **80 mots maximum**.

**12. Selon vous, quelle est l'importance des accords de Washington pour l'avenir de la RDC ?**  
Zone de texte — **150 mots maximum**.

**13. Quelles sont vos principales attentes concernant votre participation au Sommet ?**  
Zone de texte — **150 mots maximum**.

**14. Quelle expérience ou expertise pourriez-vous partager avec les autres participants au Sommet ?**  
Zone de texte — **150 mots maximum**.

**15. Souhaitez-vous participer à un groupe de travail thématique pendant le Sommet ?**  
Choix unique : **Oui / Non**.

**16. Si oui, quel groupe de travail vous intéresse le plus ?**  
**Afficher uniquement si la réponse à la question 15 est « Oui ».** Le document source mentionne « si Oui à la question 11 » dans une note de configuration, ce qui est incohérent avec la numérotation : appliquer la dépendance logique à Q15 et consigner cet ajustement dans le rapport.

- Diplomatie commerciale et investissement
- Gouvernance et État de droit
- Entrepreneuriat et innovation

### SECTION 5 — EXPO DES ALUMNI

**17. Souhaitez-vous présenter une entreprise, une organisation, une initiative ou un projet lors de l'Expo des Alumni ?**  
Choix unique : **Oui / Non**.

Si **Oui**, afficher :

- **Nom de l'entreprise, de l'organisation ou de l'initiative** — texte court
- **Décrivez brièvement votre initiative et ce que vous souhaitez présenter lors de l'Expo.** — **80 mots maximum**
- **Site web ou page sur les réseaux sociaux** — **facultatif**

Si **Non**, masquer et ne pas exiger les trois sous-champs.

### SECTION 6 — DISPONIBILITÉ ET BESOINS PARTICULIERS

**18. Serez-vous disponible pour participer au Sommet les 1er et 2 décembre 2026 ?**  
Choix unique :

- Oui, les deux journées
- Uniquement le 1er décembre
- Uniquement le 2 décembre
- Non, je ne serai pas disponible

**19. Avez-vous besoin d'un aménagement particulier pour faciliter votre participation au Sommet ?**  
Choix unique : **Oui / Non**. Si Oui, afficher **« Veuillez préciser l'aménagement nécessaire. »** (texte libre). Ces données peuvent être sensibles : accès restreint aux seuls agents qui doivent en connaître.

**20. Avez-vous un autre commentaire ou une suggestion à partager avec les organisateurs ?**  
Champ facultatif, **80 mots maximum**.

### DÉCLARATION FINALE — OBLIGATOIRE

Deux cases à cocher **distinctes**, toutes deux obligatoires, sans présélection :

1. « Je confirme avoir participé à un programme du gouvernement des États-Unis et certifie que les informations fournies sont exactes. »
2. « Je comprends que la soumission de cette candidature ne garantit pas ma participation au Sommet et qu'une confirmation me sera communiquée séparément par les organisateurs. »

### Comportement du formulaire

- Wizard 6 étapes + récapitulatif avant soumission ; sauvegarde des réponses entre étapes en état local (pas de stockage de données personnelles dans un service externe).
- Validation **côté client ET côté serveur** : formats, année, nombre de réponses, mots maximum, champs conditionnels, cases de déclaration et protection CSRF.
- Compteur des mots vivant sur les champs concernés ; longueur validée côté serveur (mots Unicode, gestion des espaces/apostrophes).
- Après `POST` valide : transaction SQL atomique, identifiant de candidature unique et non devinable, statut `pending`, horodatage Kinshasa, accusé de soumission à l'écran **sans indiquer que la participation est confirmée**.
- Prévenir les doublons et les doubles clics ; retour clair si le serveur ou l'e-mail échoue ; ne jamais créer plusieurs dossiers identiques par erreur de rafraîchissement.
- Accusé de réception par e-mail à la soumission : **texte neutre à proposer comme template administrable**, clairement différent du courrier officiel de sélection. Ne jamais envoyer le modèle de sélection dès la simple inscription.
- Gérer la fermeture des candidatures avec un message administrable ; date/heure de clôture NON indiquée dans les documents, donc configuration back-office, pas de date inventée.
- Politique de confidentialité/consentement de traitement : faire valider par l'Ambassade le texte et la durée de conservation ; éviter de fabriquer une politique prétendument officielle.

---

## 4. BACK-OFFICE COMPLET — FONCTIONNALITÉS

Construire un back-office sécurisé inspiré fonctionnellement de celui de Creators Bomoko, avec les composants suivants.

### 4.1 Connexion et rôles

- Authentification administrateur sécurisée ; pas de login par défaut connu ou secret en dur.
- Comptes individuels et rôles : `super_admin`, `reviewer`, `checkin_operator` (ou système de rôles déjà éprouvé dans Creators Bomoko).
- Seul un rôle autorisé approuve/rejette et déclenche des e-mails ; l'opérateur de scan ne peut pas consulter toutes les données sensibles.
- Sessions sécurisées, régénération de session, protection brute force, déconnexion, traçabilité, droits vérifiés côté serveur.

### 4.2 Tableau de bord

Afficher des cartes statistiques et graphiques utiles :

- Total candidatures
- En attente de traitement
- Approuvées/sélectionnées
- Rejetées/non retenues
- E-mails en attente/envoyés/échoués
- Nombre de places sélectionnées restantes **par rapport à la capacité configurable (250 par défaut)**
- Répartition des candidatures par province, secteur et programme
- Présences / enregistrements à l'entrée, séparés par journée (1er et 2 décembre)
- Graphiques sobres, filtres par statut, période, province et programme.

### 4.3 Gestion des candidatures

- Tableau paginé, recherche par nom/e-mail/téléphone/référence, tri, filtres avancés, sélection multiple.
- Consultation d'une fiche complète avec **toutes** les réponses Q1 à Q20, déclarations et champs conditionnels.
- Statuts métier : `pending`, `selected`, `rejected` ; statuts d'e-mail **séparés** du statut de candidature.
- Approbation ou rejet **unitaire et par lot**, avec confirmation ; historique (qui, quand, quelle décision).
- Empêcher les décisions incohérentes et les doubles envois. Possibilité de correction/réouverture réservée à un rôle autorisé et auditée.
- Respecter la capacité configurée de sélection : bloquer toute approbation qui la dépasserait, sauf action explicite et auditée du super-administrateur si l'Ambassade autorise la modification de la capacité.
- Exports CSV et PDF propres : candidatures filtrées, sélectionnés, rejetés, liste de présence, répartitions ; protéger contre CSV formula injection.
- Ne pas faire fuiter les besoins d'aménagement particulier dans un export standard non autorisé.

### 4.4 Notifications e-mail

Reprendre la logique éprouvée de Creators Bomoko : templates HTML compatibles avec les principaux clients e-mail, queue/outbox, envoi par petits lots **configurables** (20 par défaut si cela correspond au système existant), journal des tentatives, reprise sécurisée, visibilité des erreurs, pas de répétition de messages déjà envoyés.

- **Soumission :** accusé de réception neutre configurable.
- **Sélection :** e-mail officiel de sélection ci-dessous + QR code individuel sécurisé en pièce jointe ou bloc clairement séparé.
- **Non-sélection :** e-mail officiel de non-sélection ci-dessous, sans QR code.
- Prévisualisation avant envoi ; sélection de candidats ; envoi manuel et automatisé en respectant l'état des candidatures.
- Le changement de statut SQL et la programmation d'envoi doivent être transactionnels. Stocker les tentatives et l'issue ; ne pas déclarer « envoyé » avant confirmation du transport.
- Paramètres SMTP/transport dans des variables d'environnement ou fichier privé non versionné. Ne pas utiliser d'adresse `@state.gov` comme expéditeur sans configuration et autorisation réelles.
- Ajouter un écran pour renvoyer uniquement les e-mails échoués ou expressément sélectionnés, avec protections idempotentes.
- Pouvoir partager ultérieurement le programme/agenda aux sélectionnés par e-mail depuis le back-office, comme dans Creators Bomoko, après fourniture du document validé.

### 4.5 QR codes et pointage à l'entrée

- Générer **un QR code unique et opaque par participant sélectionné** ; aucun identifiant séquentiel ni donnée personnelle en clair dans le QR.
- Conserver un jeton sécurisé côté serveur (idéalement hash), révocable et re-générable en cas de nécessité.
- QR généré **à la sélection**, jamais pour une candidature `pending` ou `rejected`.
- Module de scan caméra compatible téléphones, HTTPS obligatoire pour l'accès caméra en production ; solution de saisie manuelle de la référence en secours, sous authentification.
- Vérifier côté serveur l'authenticité du QR, le statut `selected`, la non-révocation, et les doublons de scan.
- **Sommet sur deux jours :** prévoir **une présence indépendante le 1er et le 2 décembre 2026**, pour qu'un QR reste valide le deuxième jour ; afficher « déjà enregistré aujourd'hui » et non une erreur globale. Le modèle de présence doit enregistrer date locale, heure, opérateur, référence et résultat.
- Afficher clairement succès / déjà scanné / refusé / invalide / non sélectionné ; protéger de la double-validation simultanée en base.
- Tableau de présence en direct (rafraîchissement léger), filtres par jour, statistiques et export CSV/PDF.
- Si des parcours VIP, capacités par jour ou badges physiques existent dans Creators Bomoko, les rendre configurables mais ne pas inventer une exigence officielle.

### 4.6 Paramètres

- État des inscriptions : ouvertes/fermées, début/fin éventuellement programmés (date laissée vide tant que non communiquée).
- Capacité des sélectionnés (250 par défaut, modifiable seulement avec droits).
- Date, lieu, heure de présence, titre, thème, e-mails contacts, et textes publics administrables.
- Templates e-mails et prévisualisations, configuration du logo et des assets officiels dans leurs chemins définis.
- Cadence des envois, rapport d'erreurs, utilisateurs et rôles, journal d'audit.

---

## 5. MODÈLES D'E-MAIL OFFICIELS — À REPRODUIRE FIDÈLEMENT

Les contenus suivants proviennent des documents remis par l'Ambassade. **Ne pas les reformuler, traduire, raccourcir ni rajouter une introduction marketing.** Remplacer uniquement la variable de salutation `[Nom de l’alumnus/alumna]` avec un nom échappé pour HTML. L'implémentation peut créer un habillage HTML institutionnel discret. Pour le courrier de sélection, le QR code et les informations techniques doivent apparaître dans une **section ajoutée à part**, sans modifier le texte officiel reproduit ci-dessous.

### 5.1 Confirmation de sélection

**Objet : Confirmation de sélection – DRC ALUMNI IMPACT 2026**

> Cher/Chère [Nom de l’alumnus/alumna],
>
> L’Ambassade des États-Unis à Kinshasa a le plaisir de vous annoncer que votre candidature a été retenue pour participer au DRC ALUMNI IMPACT 2026, placé sous le thème : « Faire progresser le partenariat stratégique et les opportunités économiques entre les États-Unis et la RDC ».
>
> Ce sommet réunira 250 anciens participants aux programmes d’échanges du gouvernement américain, venus des différentes régions de la République démocratique du Congo. Il constituera une plateforme privilégiée de dialogue, de réseautage et de collaboration, permettant de renforcer les liens au sein de la communauté des alumni et d'explorer de nouvelles perspectives de partenariat au service des relations stratégiques entre les États-Unis et la RDC.
>
> **INFORMATIONS PRATIQUES**
>
> Dates : Du 1er au 2 décembre 2026  
> Lieu : The Garden, situé en face de l'entrée de l'hôtel Pullman donnant sur l'avenue Colonel Tshatshi, Kinshasa.  
> Durée : Deux jours
>
> Nous vous prions de bien vouloir prendre vos dispositions afin d'être sur place à 9h00. L'agenda détaillé du Sommet vous sera communiqué prochainement.
>
> Félicitations pour votre sélection ! Nous sommes ravis de vous accueillir au DRC ALUMNI IMPACT 2026.
>
> Pour toute question ou information complémentaire, n'hésitez pas à nous contacter aux adresses suivantes :
>
> LungunguVK@state.gov  
> KazadiNN@state.gov
>
> Cordialement,
>
> Section des Affaires publiques  
> Ambassade des États-Unis à Kinshasa

**Complément technique séparé à prévoir :** encart « Votre code d'accès » avec QR, nom, référence non sensible et consignes pour présenter le QR à l'accueil. Cette partie est une demande fonctionnelle issue du projet de référence, **pas du texte contenu dans l'e-mail officiel fourni**.

### 5.2 Notification de non-sélection

**Objet :** à définir/valider dans les paramètres (le document transmis ne comporte pas d'objet).

> Cher/Chère [Nom de l’alumnus/alumna],
>
> L’Ambassade des États-Unis à Kinshasa tient à vous remercier sincèrement pour l’intérêt que vous avez manifesté à l’égard du DRC ALUMNI IMPACT 2026, placé sous le thème : « Faire progresser le partenariat stratégique et les opportunités économiques entre les États-Unis et la RDC », ainsi que pour le temps consacré à la soumission de votre candidature.
>
> Nous avons reçu un grand nombre de candidatures témoignant de l’engagement et du dynamisme de la communauté des anciens participants aux programmes d’échanges du gouvernement américain en République démocratique du Congo.
>
> Compte tenu du nombre limité de places disponibles, nous regrettons de vous informer que votre candidature n’a malheureusement pas été retenue pour cette édition du Sommet.
>
> Nous vous encourageons vivement à rester actif au sein de la communauté des alumni et à saisir les prochaines occasions de réseautage, de renforcement des capacités et de collaboration proposées par l’Ambassade des États-Unis.
>
> Nous vous remercions une nouvelle fois pour votre intérêt, votre compréhension et votre engagement continu. Nous espérons avoir l’occasion de vous retrouver dans le cadre de prochaines activités et initiatives destinées aux alumni.
>
> Cordialement,
>
> Section des Affaires publiques  
> Ambassade des États-Unis à Kinshasa

---

## 6. STRUCTURE TECHNIQUE & DONNÉES

**D'abord inspecter le dossier Creators Bomoko puis s'aligner sur son architecture réelle.** Proposition de structure indicative, à adapter :

```text
invitationspeciale/
├── creatorsbomoko/          # EXISTANT — ne pas modifier
└── drcalumni/
    ├── index.php            # accueil + accès au formulaire
    ├── images/              # les 3 assets indiqués par le client
    ├── assets/
    │   ├── css/
    │   └── js/
    ├── app/
    │   ├── config/
    │   ├── controllers/
    │   ├── models/
    │   ├── services/
    │   ├── views/
    │   └── helpers/
    ├── admin/
    │   ├── index.php
    │   └── ...
    ├── database/
    │   └── migrations/
    ├── storage/              # non public ou protégé par le serveur
    ├── tests/
    ├── .env.example          # sans valeurs secrètes
    ├── deployment.md
    └── README.md
```

### Modèle de données recommandé

Utiliser **des tables propres au nouvel événement** et les adapter à la convention du projet existant, par exemple :

- `drcalumni_applications` : référence, Q1–Q20, champs conditionnels, déclarations acceptées et horodatées, statuts, timestamps, auteurs de décision.
- `drcalumni_application_choices` si normalisation nécessaire pour secteurs, programmes et thèmes multichoix (ou JSON sûr si le stack le justifie).
- `drcalumni_users` / `drcalumni_roles` : opérateurs et droits.
- `drcalumni_email_outbox` / `drcalumni_email_attempts` : message, destinataire, type, état, tentative, erreur, date, clé d'idempotence.
- `drcalumni_access_tokens` : jeton QR unique, hash, état, rotation, horodatages.
- `drcalumni_checkins` : participant, date de l'événement, horodatage, opérateur, résultat, unicité par participant/jour pour succès.
- `drcalumni_settings` : événements, capacité, ouverture/fermeture, contenus administrables.
- `drcalumni_audit_logs` : changements sensibles et décisions.

**Obligations DB :** migrations versionnées ; clés étrangères ; index recherche et statut ; contrainte d'unicité pour e-mail normalisé selon politique de doublon ; transaction pour décision/queue ; écriture atomique pour scan ; aucun mélange avec les données Creators Bomoko. Prévoir un script de migration et rollback raisonnable (sans effacement accidentel des données en production).

### Sécurité & protection des données

- PDO/prepared statements, validation serveur stricte, échappement HTML, CSRF, XSS, politiques CORS raisonnables, sécurité des sessions/cookies, limitation de débit et protection anti-spam.
- Fichiers `.env`/secrets, logs sensibles et exports hors du webroot si possible ; sinon règles serveur de refus d'accès explicites.
- Pages admin interdites aux anonymes ; contrôles des rôles sur chaque endpoint et API, **pas seulement sur les menus**.
- QR non prédictible, pas de PII dans URL/QR, contrôle en serveur ; HTTPS en production.
- Journaliser les décisions sans enregistrer des secrets dans les logs.
- Prendre les dispositions raisonnables pour sauvegardes chiffrées, conservation et suppression des données après validation de la politique du client.
- Ne pas envoyer des informations personnelles vers des services tiers de télémétrie ou d'analytics sans accord explicite.

---

## 7. ORDRE DE DÉVELOPPEMENT IMPOSÉ À COPILOT

**Phase A — Audit de référence**

1. Inventorier précisément fichiers, bibliothèques, architecture, tables, fonctions et flux de `creatorsbomoko`.
2. Identifier les fonctionnalités existantes à reprendre et les risques de collision.
3. Vérifier les trois logos/images réels et les chemins webroot.
4. Documenter la stack, PHP et MySQL requis, la configuration mail et les dépendances nécessaires.

**Phase B — Base du nouveau projet & direction visuelle**

1. Créer uniquement les nouveaux fichiers de `drcalumni`.
2. Configurer DB isolée/logiquement séparée, variables d'environnement et migrations.
3. Réaliser entièrement le design responsive : header bleu avec logo centré, hero premium, formulaire en six étapes, footer blanc à deux logos.
4. Vérifier rendu mobile/desktop, tailles des logos, alignements, contrastes et focus.

**Phase C — Formulaire & persistence**

1. Implémenter **exactement Q1–Q20**, toutes les options, limites et règles conditionnelles.
2. Validation serveur, anti-doublon, CSRF, transaction et page de succès.
3. Envoi neutre d'accusé de dépôt si configuré.

**Phase D — Back-office & workflows**

1. Connexion, droits, dashboard, tableau, recherche, filtres, fiche candidature.
2. Sélection/rejet unitaire et par lot, capacité 250 configurable, traçabilité.
3. E-mails officiels, file d'envoi, prévisualisation, erreurs et nouvelles tentatives.
4. Exports CSV/PDF et paramètres.

**Phase E — QR code & événement**

1. QR unique uniquement pour sélectionnés.
2. Intégration dans le mail de sélection, vérification et scan authentifié.
3. Entrées distinctes pour les journées du 1er et du 2 décembre.
4. Tableau de présence et exports de présence.

**Phase F — Recette & publication**

1. Tests fonctionnels et de sécurité.
2. Vérifier le déploiement sous `/drcalumni/` sans impact sur `/creatorsbomoko/`.
3. Écrire les instructions et commandes concrètes de déploiement en fonction du serveur trouvé, et renseigner `README.md`.
4. Rendre un rapport de livraison détaillé avec les éventuels points à faire valider par l'Ambassade.

---

## 8. CRITÈRES D'ACCEPTATION / TESTS À EXÉCUTER

Cocher explicitement les résultats dans le rapport final de Copilot :

- [ ] Le projet Creators Bomoko n'a subi **aucune modification** et fonctionne toujours.
- [ ] L'URL `/drcalumni/` charge la bonne application, tous les assets et les bons logos.
- [ ] Le header est **bleu `#002868`**, son logo est **centré**, le favicon utilise le fichier indiqué.
- [ ] Le footer est **blanc** et présente **les deux logos officiels**, sans déformation.
- [ ] Le rendu est premium, distinct de Creators Bomoko, agréable en mobile/desktop.
- [ ] Les **20 questions**, les 26 provinces et toutes les options correspondent au document.
- [ ] Les sélections multichoix Q6 (2–3) et Q10 (max 3) sont validées en client et serveur.
- [ ] Les champs conditionnels de Q15/Q16, Q17 et Q19 fonctionnent.
- [ ] Tous les plafonds de mots et les deux déclarations obligatoires sont appliqués en client et serveur.
- [ ] Les candidatures sont enregistrées une seule fois, même en double clic/rafraîchissement.
- [ ] Un candidat `pending` ne reçoit **jamais** un e-mail d'acceptation ni un QR.
- [ ] Le dashboard affiche les vrais chiffres issus de la base, sans données fictives.
- [ ] Les administrateurs peuvent filtrer, voir les fiches, sélectionner, rejeter et exporter.
- [ ] Les décisions et les envois sont historisés ; les e-mails sont idempotents et les échecs visibles.
- [ ] Les modèles d'e-mails reprennent **fidèlement** les deux textes de l'Ambassade.
- [ ] La sélection respecte la capacité fixée (250 par défaut) et les contrôles d'autorisation.
- [ ] Un QR valide ne donne accès qu'à un candidat sélectionné ; l'invalidité est signalée.
- [ ] Le scan à l'entrée est dédoublonné **par journée**, sans bloquer la deuxième journée.
- [ ] Le scan, les décisions et les exports sont protégés par rôle.
- [ ] Le site résiste aux entrées HTML/SQL malveillantes, erreurs SMTP et pertes de réseau courantes.
- [ ] Les opérations SQL importantes sont transactionnelles, les secrets ne sont pas exposés.
- [ ] Les tests en conditions proches de la production passent sur mobile et desktop.
- [ ] Les étapes de déploiement, migrations, configuration et rollback sont documentées.

---

## 9. CONSIGNES DE MISE EN PRODUCTION

Le serveur et les versions ne sont **pas spécifiés dans les documents officiels**. Copilot doit analyser l'environnement cible avant de proposer les commandes ; **ne pas exécuter de commande de production, de migration destructive ou d'envoi massif d'e-mails sans validation humaine**.

1. Vérifier les versions PHP, MySQL/MariaDB, les extensions requises, les bibliothèques QR, e-mail, export et le chemin absolu du webroot.
2. Configurer `APP_URL`, `APP_ENV=production`, `APP_TIMEZONE=Africa/Kinshasa`, accès DB, secrets, SMTP et chemin des assets via variables privées et `.env.example` non secret.
3. Faire une sauvegarde indépendante avant migration, puis appliquer **uniquement les migrations `drcalumni`**.
4. Créer un administrateur initial via une procédure sécurisée (secret fourni hors Git), jamais un mot de passe codé en dur.
5. Vérifier droits de fichiers, interdiction d'accès direct aux dossiers internes, HTTPS, cookies sécurisés, politique de journaux.
6. Tester les trois images officielles par URL, chaque route, les liens d'e-mail, génération/scan des QR, les deux journées, les sorties PDF/CSV.
7. Tester les e-mails sur une liste fermée de comptes de test avant d'activer le vrai transport ; ne jamais envoyer un e-mail officiel à un vrai candidat depuis un jeu de tests.
8. Faire confirmer les textes publics, l'objet du rejet, les coordonnées de contact, la date de fermeture et les règles de conservation avant le lancement.
9. Vérifier que `creatorsbomoko` reste fonctionnel et que le nouveau site n'a aucune dépendance fragile à son administration.
10. Remettre : URL publique, URL admin, procédure de connexion (sans secrets dans le rapport), procédure de traitement, procédure scan, méthode de sauvegarde et plan de retour arrière.

---

## 10. FORMAT DU RAPPORT FINAL ATTENDU DE COPILOT

À la fin de chaque phase, fournir :

1. **Fichiers effectivement créés / modifiés**, avec chemins exacts.
2. **Résumé de ce qui fonctionne vraiment**, avec renvoi au code pertinent.
3. **Migrations SQL exécutées ou à exécuter**, clairement différenciées.
4. **Tests réalisés**, résultats et problèmes connus.
5. **Instructions de lancement sur le serveur existant**.
6. **Décisions en attente de validation** (objet de rejet, ouverture/fermeture du formulaire, politique de confidentialité, configuration SMTP, accord d'utilisation des marques, etc.).

**Ne pas dire « terminé » si une fonctionnalité demandée n'est pas codée/testée.** Si un problème bloque une phase, expliquer précisément le blocage, fournir une solution et poursuivre les tâches non bloquées.

---

## 11. INSTRUCTION DE DÉPART IMMÉDIATE POUR COPILOT

**Commence maintenant.** Inspecte intégralement `invitationspeciale/creatorsbomoko/` en lecture seule, puis inspecte `invitationspeciale/drcalumni/`. Crée le nouveau projet sans modifier Creators Bomoko. Commence par réaliser la **page publique finale et le wizard complet**, avec le design navy/rouge/blanc et les trois logos indiqués, puis continue jusqu'au **back-office fonctionnel, aux e-mails officiels, aux QR codes et au contrôle d'accès sur deux jours**. Pour tout contenu soumis au client, reste strictement fidèle aux documents ci-dessus. Ne te contente ni d'un prototype ni d'une simple description : **implémente, vérifie et documente**.
