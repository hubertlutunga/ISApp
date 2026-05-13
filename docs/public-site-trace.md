# Trace serveur des pages publiques

Cette trace sert a isoler un HTTP 500 live sur une page publique sans afficher de diagnostic au visiteur.

## Activation

1. Definir sur le serveur:
   - `ISAPP_PUBLIC_TRACE_ENABLED=1`
   - `ISAPP_PUBLIC_TRACE_KEY=une-cle-secrete-longue`
2. Rejouer la requete en ajoutant `trace=1` et `trace_key`:

```text
https://invitationspeciale.com/site/index.php?page=accueil&cod=828&trace=1&trace_key=une-cle-secrete-longue
```

Option alternative: envoyer les headers `X-ISAPP-Trace: 1` et `X-ISAPP-Trace-Key: ...`.

## Ce qui est trace

- point d'entree `site/index.php`
- resolution du contenu
- jalons du template mariage `site/pages/accueil.php`
- anomalies de chargement des reglages mariage
- exceptions catchables
- fatal final via shutdown handler avec le dernier jalon atteint

## Ou lire la trace

- Dossier: `storage/traces/public-site`
- Fichier journalier: `YYYY-MM-DD.log`
- Format: une ligne JSON par evenement
- Header de reponse: `X-ISAPP-Trace-Id`

## Lecture recommandee

Filtrer par `trace_id`, puis regarder:

1. `site_event_loaded`
2. `site_content_resolved`
3. le dernier jalon `wedding_accueil_*`
4. `site_wedding_accueil_exception` ou `php_fatal`

Si la reponse live est encore `500`, l'entree `php_fatal` indiquera le dernier bloc atteint et le fichier/ligne du fatal.