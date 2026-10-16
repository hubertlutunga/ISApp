<?php
$billetWhatsappNumber = '243810678785';
$billetWhatsappMessage = rawurlencode('Bonjour Invitation Speciale, je souhaite lancer une commande Billet Special.');
$billetWhatsappUrl = 'https://wa.me/' . $billetWhatsappNumber . '?text=' . $billetWhatsappMessage;
$billetCommandeUrl = 'index.php?page=commande&service=billet-special';
?>

<style>
  .bs-page {
    --bs-bg: #f8f2e9;
    --bs-surface: #fffdf9;
    --bs-text: #231910;
    --bs-muted: #6f6256;
    --bs-accent: #9a6a30;
    --bs-accent-2: #c59b62;
    --bs-line: rgba(100, 69, 36, 0.16);
    --bs-shadow: 0 26px 64px rgba(74, 45, 18, 0.14);
    min-height: 100vh;
    background:
      radial-gradient(circle at 12% 8%, rgba(197, 155, 98, 0.28), transparent 25%),
      radial-gradient(circle at 88% 20%, rgba(154, 106, 48, 0.2), transparent 26%),
      linear-gradient(180deg, #fff8ef 0%, var(--bs-bg) 100%);
    color: var(--bs-text);
    padding-bottom: 52px;
  }

  .bs-wrap {
    width: min(1160px, calc(100% - 32px));
    margin: 0 auto;
  }

  .bs-topbar {
    padding: 24px 0 10px;
    display: flex;
    justify-content: space-between;
    gap: 14px;
    align-items: center;
  }

  .bs-back,
  .bs-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0 16px;
    border-radius: 999px;
    border: 1px solid var(--bs-line);
    text-decoration: none;
    color: var(--bs-text);
    background: rgba(255, 255, 255, 0.84);
    font-weight: 600;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .bs-back:hover,
  .bs-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 24px rgba(74, 45, 18, 0.12);
  }

  .bs-btn-primary {
    background: linear-gradient(180deg, var(--bs-accent-2) 0%, var(--bs-accent) 100%);
    border-color: transparent;
    color: #fffaf1;
    font-weight: 700;
  }

  .bs-hero {
    margin-top: 8px;
    padding: 32px;
    border-radius: 30px;
    border: 1px solid var(--bs-line);
    background: linear-gradient(130deg, rgba(65, 41, 19, 0.96), rgba(123, 80, 37, 0.92));
    color: #fff7ee;
    box-shadow: var(--bs-shadow);
  }

  .bs-hero-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr);
    gap: 28px;
    align-items: center;
  }

  .bs-kicker {
    display: inline-flex;
    align-items: center;
    min-height: 32px;
    padding: 0 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid rgba(255, 255, 255, 0.18);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
  }

  .bs-hero h1 {
    margin: 14px 0 12px;
    font-size: clamp(32px, 4vw, 48px);
    line-height: 1.05;
    letter-spacing: -0.03em;
  }

  .bs-hero p {
    margin: 0;
    max-width: 640px;
    color: rgba(255, 244, 230, 0.84);
    line-height: 1.7;
  }

  .bs-actions {
    margin-top: 24px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
  }

  .bs-visual {
    position: relative;
    border-radius: 22px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.2);
    min-height: 340px;
    box-shadow: 0 20px 42px rgba(17, 9, 3, 0.3);
  }

  .bs-visual img {
    width: 100%;
    height: 100%;
    min-height: 340px;
    object-fit: cover;
    display: block;
  }

  .bs-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    min-height: 34px;
    padding: 0 12px;
    border-radius: 999px;
    background: linear-gradient(180deg, #f4d8a6 0%, #d4a86d 100%);
    color: #3e2811;
    display: inline-flex;
    align-items: center;
    font-size: 11px;
    letter-spacing: 0.12em;
    font-weight: 800;
    text-transform: uppercase;
  }

  .bs-section {
    margin-top: 24px;
    display: grid;
    grid-template-columns: minmax(0, 1.04fr) minmax(0, 0.96fr);
    gap: 20px;
  }

  .bs-card {
    background: var(--bs-surface);
    border: 1px solid var(--bs-line);
    border-radius: 24px;
    box-shadow: 0 14px 34px rgba(74, 45, 18, 0.09);
    padding: 26px;
  }

  .bs-card h2,
  .bs-card h3 {
    margin: 0 0 12px;
    font-size: 24px;
    line-height: 1.2;
  }

  .bs-card p {
    margin: 0 0 12px;
    color: var(--bs-muted);
    line-height: 1.7;
  }

  .bs-list {
    list-style: none;
    margin: 16px 0 0;
    padding: 0;
    display: grid;
    gap: 10px;
  }

  .bs-list li {
    border-radius: 14px;
    border: 1px solid rgba(122, 82, 44, 0.16);
    background: #fcf6ee;
    padding: 12px 14px;
    font-weight: 600;
  }

  .bs-price {
    margin-top: 14px;
    padding: 18px;
    border-radius: 18px;
    border: 1px solid rgba(122, 82, 44, 0.16);
    background: #fdf4e7;
  }

  .bs-price strong {
    display: block;
    font-size: 30px;
    letter-spacing: -0.03em;
    line-height: 1;
    margin-bottom: 8px;
  }

  @media (max-width: 960px) {
    .bs-hero-grid,
    .bs-section {
      grid-template-columns: 1fr;
    }

    .bs-hero,
    .bs-card {
      padding: 20px;
    }

    .bs-actions {
      flex-direction: column;
    }

    .bs-btn,
    .bs-back {
      width: 100%;
    }
  }
</style>

<div class="bs-page">
  <div class="bs-wrap">
    <div class="bs-topbar">
      <a class="bs-back" href="index.php?page=accueil#billet-special">Retour a l'accueil</a>
      <a class="bs-btn" href="index.php?page=catalogue">Voir le catalogue</a>
    </div>

    <section class="bs-hero" aria-label="Service Billet Special">
      <div class="bs-hero-grid">
        <div>
          <span class="bs-kicker">Nouveau service premium</span>
          <h1>Billet Special</h1>
          <p>
            Le billet premium signe Invitation Speciale: design editorial, QR code individuel et controle d'acces fluide le jour de votre evenement.
            Ce format est pense pour les organisateurs qui veulent une presentation elegante et une logistique d'entree maitrisee.
          </p>

          <div class="bs-actions">
            <a class="bs-btn bs-btn-primary" href="<?php echo htmlspecialchars($billetCommandeUrl, ENT_QUOTES, 'UTF-8'); ?>">Commander Billet Special</a>
            <a class="bs-btn" href="<?php echo htmlspecialchars($billetWhatsappUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Demander une simulation WhatsApp</a>
          </div>
        </div>

        <div class="bs-visual" aria-hidden="true">
          <span class="bs-badge">Nouveau</span>
          <img src="images/IMG_2415.png" alt="Visuel Billet Special" />
        </div>
      </div>
    </section>

    <section class="bs-section">
      <article class="bs-card">
        <h2>Ce que vous obtenez</h2>
        <p>
          Billet Special unit l'impact visuel d'une invitation premium et l'efficacite d'un parcours digital: billet imprime, version partageable,
          QR individuel et suivi des validations.
        </p>

        <ul class="bs-list">
          <li>Numero de billet et QR unique par invite</li>
          <li>Version imprimee et version digitale synchronisees</li>
          <li>Parcours de verification rapide a l'arrivee</li>
          <li>Segmentation invite: VIP, famille, presse, prestataires</li>
          <li>Export de liste d'arrivee pour equipe d'accueil</li>
        </ul>
      </article>

      <aside class="bs-card">
        <h3>Parcours de commande prefiltre</h3>
        <p>
          Le bouton Commander active automatiquement le parcours Billet Special dans votre tunnel de commande, avec options recommandees deja preselectionnees.
        </p>

        <div class="bs-price">
          <strong>Billet Special</strong>
          <p>
            Direction artistique, personnalisation, generation QR et accompagnement de mise en place.
          </p>
        </div>

        <div class="bs-actions">
          <a class="bs-btn bs-btn-primary" href="<?php echo htmlspecialchars($billetCommandeUrl, ENT_QUOTES, 'UTF-8'); ?>">Lancer la commande</a>
          <a class="bs-btn" href="index.php?page=contact">Parler a l'equipe</a>
        </div>
      </aside>
    </section>
  </div>
</div>
