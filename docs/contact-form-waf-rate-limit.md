# Protection WAF + Rate Limit pour le formulaire contact

Ce guide complete la protection deja activee dans l application (CSRF, honeypot, delai, rate-limit session/IP, CAPTCHA).

## 1) Variables d environnement CAPTCHA

Ajouter dans `.isapp.env` (ou variables serveur):

### Option A - Cloudflare Turnstile (recommande)

```env
ISAPP_CONTACT_CAPTCHA_ENABLED=1
ISAPP_CONTACT_CAPTCHA_PROVIDER=turnstile
ISAPP_TURNSTILE_SITE_KEY=VOTRE_SITE_KEY
ISAPP_TURNSTILE_SECRET_KEY=VOTRE_SECRET_KEY
```

### Option B - Google reCAPTCHA v3

```env
ISAPP_CONTACT_CAPTCHA_ENABLED=1
ISAPP_CONTACT_CAPTCHA_PROVIDER=recaptcha_v3
ISAPP_RECAPTCHA_V3_SITE_KEY=VOTRE_SITE_KEY
ISAPP_RECAPTCHA_V3_SECRET_KEY=VOTRE_SECRET_KEY
ISAPP_RECAPTCHA_V3_MIN_SCORE=0.5
ISAPP_RECAPTCHA_V3_ACTION=home_contact
```

## 2) Cloudflare WAF (fortement recommande)

Creer une `Custom WAF Rule` pour bloquer les bots agressifs sur la cible contact.

Expression conseillee (mode `Managed Challenge`):

```txt
(http.request.method eq "POST" and http.request.uri.path eq "/index.php" and http.request.uri.query contains "page=accueil")
```

Action: `Managed Challenge`

Ensuite creer une `Rate Limiting Rule` Cloudflare:
- Filter expression:

```txt
(http.request.method eq "POST" and http.request.uri.path eq "/index.php" and http.request.uri.query contains "page=accueil")
```

- Threshold: `10` requests
- Period: `1 minute`
- Action: `Block` (ou `Managed Challenge`)
- Mitigation timeout: `10 minutes`
- Characteristics: `IP`

## 3) Nginx (alternative serveur)

Exemple dans le bloc `http`:

```nginx
limit_req_zone $binary_remote_addr zone=contact_form_zone:10m rate=10r/m;
```

Exemple dans le `server`:

```nginx
location = /index.php {
    if ($request_method = POST) {
        if ($query_string ~* "(^|&)page=accueil(&|$)") {
            limit_req zone=contact_form_zone burst=5 nodelay;
        }
    }

    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root/index.php;
    fastcgi_pass php-fpm;
}
```

## 4) Apache (alternative serveur)

Apache ne fournit pas un rate limit applicatif IP aussi fin nativement sur un endpoint PHP sans modules additionnels.
Pour un vrai controle, utiliser:
- Cloudflare Rate Limiting (recommande)
- ou `mod_evasive` / `mod_security` avec regles ciblees

Exemple `mod_evasive` (global):

```apache
DOSHashTableSize    3097
DOSPageCount        10
DOSPageInterval     1
DOSSiteCount        80
DOSSiteInterval     1
DOSBlockingPeriod   600
```

## 5) Ordre de priorite recommande

1. Cloudflare Turnstile + WAF + Rate Limiting
2. Protection applicative (deja activee)
3. Durcissement Nginx/Apache selon votre hebergement

## 6) Verification rapide

1. Soumission normale: doit passer.
2. Soumission immediate (< 4s): doit etre refusee.
3. Soumissions en rafale: blocage apres plusieurs essais.
4. Token CAPTCHA absent/invalide: doit etre refuse.
