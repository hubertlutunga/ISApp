# Audit structurel ISApp

## 1. Lecture globale

ISApp est une application PHP historique construite par agrégation de plusieurs sous-sites et back-offices autour d'un meme coeur metier :

- creation de compte client
- creation d'evenement
- personnalisation d'invitation
- generation de lien court et QR code
- gestion des invites
- confirmation de presence RSVP
- gestion d'acces sur site
- suivi de production / paiement / support

Le projet fonctionne, mais sa structure est fragilisee par de fortes duplications de logique, plusieurs points d'entree paralleles et des dossiers qui melangent templates, code metier et variantes de produit.

## 2. Cartographie reelle des dossiers

### Racine

- index.php : page marketing principale + redirection via url_shortener
- pages/bdd.php : ancien point d'entree DB, maintenant redirige vers config/database.php
- config/database.php : configuration DB centralisee
- qrscan/ : librairie QR code + scanner HTML5
- PHPMailer/ : envoi email
- twilio-php-main/ : integration SMS/WhatsApp potentielle

### event/

Sous-application d'entree client et d'authentification.

Fichiers clefs :

- event/index.php
- event/pages/login.php
- event/pages/inscription.php
- event/pages/commande.php
- event/pages/scriptconnexion.php

Role reel :

- inscription / connexion
- commande initiale
- passage vers le back-office utilisateur

### event/users/

Vrai coeur du back-office.

Fichiers clefs :

- event/users/index.php
- event/users/pages/mb_accueil.php
- event/users/pages/addevent.php
- event/users/pages/addinvite.php
- event/users/pages/addtable.php
- event/users/pages/addmenu.php
- event/users/pages/conf_siteweb.php
- event/users/pages/paiement.php
- event/users/pages/clients.php
- event/users/pages/admin_accueil.php
- event/users/pages/admin_filcom.php
- event/users/pages/admin_filcomcrea.php

Role reel :

- espace client
- espace creation / production
- espace admin
- gestion commerciale minimale
- gestion des fichiers et de la personnalisation site

### site/

Front public des evenements et gestion des confirmations / acces.

Fichiers clefs :

- site/index.php
- site/pages/accueil.php
- site/pages/enreg_conf.php
- site/pages/confscript_wedding.php
- site/pages/access.php
- site/pages/access_cible.php
- site/pages/search_invites.php
- site/pages/scanqr.php

Role reel :

- mini-site evenement public
- RSVP
- page d'acces par invite
- scan ou recherche d'invite pour controle a l'entree

### menu/

Sous-application parallele pour menu / restauration d'evenement.

Fichiers clefs :

- menu/index.php
- menu/pages/commande.php
- menu/pages/inscription.php
- menu/pages/login.php

Role reel :

- variante de front pour menu d'evenement
- re-implemente une partie de la logique de event/

### couple/

Ancienne sous-application mariage, largement template-driven.

Fichiers clefs :

- couple/index.php
- couple/pages/accueil.php
- couple/pages/weddetail.php
- couple/pages/wedliste.php

Role reel :

- ancien front mariage
- historique fonctionnel partiellement depasse par site/

## 3. Flux metier observes

### Flux 1 : acquisition et creation de compte

1. Le client arrive via la racine ou event/
2. Il s'inscrit via event/pages/inscription.php ou event/pages/commande.php
3. Un enregistrement est cree dans is_users
4. Une session est ouverte avec user_phone / user_email
5. Le client est redirige vers le back-office event/users/

### Flux 2 : creation d'evenement

1. Le client cree son evenement via event/users/pages/addevent.php
2. Un enregistrement est cree dans events
3. Les accessoires sont lies via accessoires_event
4. Les photos sont liees via photos_event
5. Un lien court est cree dans url_shortener
6. Le mini-site public devient accessible via site/index.php?page=accueil&cod=...

### Flux 3 : invitations et RSVP

1. Les invites sont ajoutes via addinvite.php ou generer_invit2.php
2. Les donnees sont enregistrees dans invite
3. Le public confirme sa presence via site/pages/enreg_conf.php
4. Les reponses sont stockees dans confirmation
5. Le back-office affiche les invites confirmes / non confirmes / non repondus

### Flux 4 : acces et QR code

1. Un lien court est cree dans url_shortener
2. La racine index.php redirige via ?site=...
3. Le controle d'acces se fait via site/pages/access.php et access_cible.php
4. Le scan QR semble utiliser qrscan/ et la recherche d'invite via search_invites.php

### Flux 5 : production et suivi interne

1. L'admin / createur consulte les evenements dans admin_filcom.php ou admin_filcomcrea.php
2. Les statuts de creation / paiement / livraison sont mis a jour dans events
3. Des fichiers d'impression et contenus de personnalisation sont ajoutes

## 4. Duplications et odeurs d'architecture

### A. Multiples front controllers identiques

On retrouve la meme logique de routage par ?page= dans :

- index.php
- event/index.php
- menu/index.php
- site/index.php
- couple/index.php
- event/users/index.php

Probleme : aucun noyau partage, chaque sous-site charge ses propres dependances et conventions.

### B. Duplication des flux d'inscription / commande

La logique d'inscription existe en parallele dans :

- event/pages/inscription.php
- menu/pages/inscription.php
- event/pages/commande.php
- menu/pages/commande.php

Probleme : divergence fonctionnelle inevitable, maintenance chere, risque de bugs incoherents.

### C. Duplication de generation de lien court

La creation de short link apparait notamment dans :

- event/users/pages/addevent.php
- event/users/pages/addlink.php

Probleme : meme regle metier, plusieurs implementations.

### D. Deux generations de fronts evenementiels

Les pages publiques sont reparties entre :

- site/
- couple/
- menu/

Probleme : trois variantes qui se recouvrent partiellement, sans separation claire entre produit actif et heritage.

### E. Metier, vue et traitement dans le meme fichier

Exemples :

- event/users/pages/addevent.php
- event/pages/commande.php
- site/pages/accueil.php
- couple/images/eventdata.php

Probleme : requetes SQL, uploads, regles metier, HTML, JS et CSS melanges dans le meme fichier.

### F. Structure de roles implicites

type_user pilote plusieurs experiences :

- 1 admin
- 2 client
- 3 createur / production

Probleme : controle d'acces diffuse dans les fichiers plutot que centralise.

### G. Dette securite encore presente

Points encore sensibles :

- beaucoup de code legacy avec variables GET/POST directement utilisees
- absence de couche unique de validation
- URLs absolues en dur vers invitationspeciale.com
- stockage historique de recpass dans la base, meme si les nouveaux flux corriges n'y ecrivent plus

## 5. Modules a conserver, fusionner, reecrire ou supprimer

### A conserver

- config/database.php
- event/users/ comme base du futur back-office
- site/ comme base du futur front public evenementiel
- qrscan/ comme brique technique a encapsuler
- tables coeur : is_users, events, invite, confirmation, url_shortener, accessoires_event, photos_event, galeriephotos, tableevent

### A fusionner

- event/pages/inscription.php + menu/pages/inscription.php
- event/pages/commande.php + menu/pages/commande.php
- logique addlink / shortener dispersee
- blocs eventbloc.php et blocsearchevent.php

### A reecrire progressivement

- event/users/pages/addevent.php
- site/pages/accueil.php
- site/pages/access.php
- site/pages/access_cible.php
- event/users/pages/clients.php

### A declasser puis supprimer a terme

- couple/ si site/ remplace totalement le front mariage
- menu/ comme sous-app separee si son metier est absorbe dans le front evenement unique
- archives ZIP et templates non relies au produit actif

## 6. Decoupage cible recommande

### Module 1 : Authentification et comptes

Responsabilites :

- inscription
- connexion
- mot de passe oublie
- roles
- profil client

Tables : is_users

### Module 2 : Evenements

Responsabilites :

- creation d'evenement
- type d'evenement
- informations generales
- statut de production
- personnalisation de base

Tables : events, evenement, accessoires_event, photos_event

### Module 3 : Invitations et listes d'invites

Responsabilites :

- ajout manuel / import d'invites
- attribution de table
- gestion des accompagnants
- historique d'envoi

Tables : invite, tableevent

### Module 4 : Site evenementiel public

Responsabilites :

- page publique de l'evenement
- galerie
- love story
- informations pratiques
- page RSVP

Tables : websitewedgeneral, lovestory, lovestory_etap, galeriephotos, websiteconference, websitesection

### Module 5 : RSVP et acces

Responsabilites :

- confirmation presence
- reponse negative
- scan / recherche QR
- point d'acces
- controle a l'entree

Tables : confirmation, invite

### Module 6 : Lien court et QR

Responsabilites :

- generation URL courte
- regeneration si conflit
- tracking basique
- generation QR server-side

Tables : url_shortener

### Module 7 : Back-office operations

Responsabilites :

- suivi de production
- assignation createur
- paiement
- facturation
- support

Tables : events, facture, detailfacture, support, ticke_support, fichiers_impression, creaevent

## 7. Plan concret de refonte par phases

### Phase 1 : stabilisation

- centraliser DB, deja commence
- retirer les mots de passe en clair, deja commence
- figer les variables d'environnement
- ajouter un vrai .gitignore
- supprimer les URLs absolues du code applicatif et les centraliser

### Phase 2 : normalisation du coeur

- creer une couche commune bootstrap.php
- creer des fonctions / services partages pour auth, short links, events, invites
- deplacer les traitements SQL hors des vues HTML
- unifier les formulaires inscription / commande

### Phase 3 : consolidation fonctionnelle

- faire du back-office event/users/ le seul back-office
- faire de site/ le seul front public
- marquer menu/ et couple/ comme legacy
- migrer les fonctions utiles de couple/ et menu/ vers les modules cibles

### Phase 4 : professionnalisation UX

- tableau de bord client simple et coherent
- tunnel de commande en etapes
- page evenement premium responsive
- ecran RSVP propre
- ecran check-in QR mobile-first

### Phase 5 : dette technique et donnees

- script SQL de reference du schema
- migrations incrementales
- nettoyage des donnees legacy
- index DB sur cod_event, cod_user, cod_mar, short_code

## 8. Quick wins immediats

1. Ajouter un bootstrap commun pour tous les index.
2. Extraire un service ShortUrl reutilisable.
3. Extraire un service EventUrlResolver pour ne plus dupliquer les URLs site / anniversaire / conference.
4. Fusionner inscription et commande entre event/ et menu/.
5. Isoler la logique RSVP dans un seul endpoint propre.
6. Nettoyer les archives et templates non utilises du depot.
7. Ajouter un export SQL documente du schema actuel.

## 9. Conclusion

La meilleure base pour une version professionnelle est :

- conserver event/users/ comme socle back-office
- conserver site/ comme socle front public
- absorber progressivement menu/ et couple/
- transformer le projet en application modulaire unique plutot qu'en ensemble de mini-sites paralleles

La refonte ne doit pas etre un big bang. Elle peut etre menee sans casser l'existant en migrant d'abord les fonctions critiques : auth, creation evenement, invitations, RSVP, liens courts.