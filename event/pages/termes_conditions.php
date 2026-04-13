<style>
  .terms-shell {
    min-height: 100vh;
    padding: 40px 18px 56px;
    background:
      radial-gradient(circle at top left, rgba(15, 118, 110, 0.12), transparent 26%),
      radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 30%),
      linear-gradient(180deg, #f8fbff 0%, #eef6f4 100%);
  }
  .terms-card {
    width: min(920px, 100%);
    margin: 0 auto;
    background: #ffffff;
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(15, 23, 42, 0.14);
  }
  .terms-hero {
    padding: 32px 34px 22px;
    background:
      radial-gradient(circle at top right, rgba(59, 130, 246, 0.16), transparent 34%),
      linear-gradient(135deg, #0f172a 0%, #0f766e 100%);
    color: #f8fafc;
  }
  .terms-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.14);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }
  .terms-hero h1 {
    margin: 16px 0 10px;
    font-size: 34px;
    line-height: 1.1;
    font-weight: 800;
    color: #ffffff;
  }
  .terms-hero p {
    margin: 0;
    max-width: 720px;
    color: rgba(248, 250, 252, 0.84);
    font-size: 15px;
    line-height: 1.7;
  }
  .terms-body {
    padding: 28px 34px 34px;
  }
  .terms-alert {
    margin-bottom: 22px;
    padding: 18px 20px;
    border: 1px solid rgba(217, 119, 6, 0.26);
    border-radius: 18px;
    background: linear-gradient(180deg, #fff7ed 0%, #fffbeb 100%);
    color: #9a3412;
  }
  .terms-alert strong {
    display: block;
    margin-bottom: 6px;
    color: #7c2d12;
    font-size: 15px;
  }
  .terms-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
  }
  .terms-section {
    padding: 20px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 20px;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
  }
  .terms-section.section-wide {
    grid-column: 1 / -1;
  }
  .terms-section h2 {
    margin: 0 0 10px;
    color: #0f172a;
    font-size: 18px;
    font-weight: 800;
  }
  .terms-section p,
  .terms-section li {
    color: #475569;
    font-size: 14px;
    line-height: 1.7;
  }
  .terms-section ul {
    margin: 0;
    padding-left: 18px;
  }
  .terms-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-top: 24px;
    flex-wrap: wrap;
  }
  .terms-actions a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 18px;
    border-radius: 14px;
    text-decoration: none;
    font-weight: 700;
  }
  .terms-back {
    background: #ffffff;
    border: 1px solid rgba(148, 163, 184, 0.2);
    color: #0f172a;
  }
  .terms-home {
    background: linear-gradient(135deg, #0f766e 0%, #0f9f85 100%);
    color: #ffffff;
    box-shadow: 0 16px 30px rgba(15, 118, 110, 0.20);
  }
  @media (max-width: 767px) {
    .terms-hero,
    .terms-body {
      padding-left: 22px;
      padding-right: 22px;
    }
    .terms-hero h1 {
      font-size: 28px;
    }
    .terms-grid {
      grid-template-columns: minmax(0, 1fr);
    }
    .terms-section.section-wide {
      grid-column: auto;
    }
  }
</style>

<div class="terms-shell">
  <div class="terms-card">
    <div class="terms-hero">
      <span class="terms-kicker"><i class="fas fa-file-contract"></i> Cadre Contractuel</span>
      <h1>Termes et conditions</h1>
      <p>Ces conditions encadrent les commandes passées auprès d’Invitation Spéciale pour la création, la personnalisation et la production de supports événementiels, imprimés ou numériques.</p>
    </div>

    <div class="terms-body">
      <div class="terms-alert">
        <strong>Clause importante sur les remboursements</strong>
        Toute somme versée dans le cadre d’une commande n’est remboursable qu’à hauteur de 50 % du montant payé, quelle que soit l’étape d’avancement du dossier, sauf disposition légale impérative contraire.
      </div>

      <div class="terms-grid">
        <section class="terms-section">
          <h2>1. Objet du service</h2>
          <p>Invitation Spéciale propose la conception, la personnalisation et la production de contenus et supports liés aux événements, notamment les invitations imprimées, les invitations électroniques, les chevalets et autres accessoires associés.</p>
        </section>

        <section class="terms-section">
          <h2>2. Validation de commande</h2>
          <p>Une commande est considérée comme engagée dès validation du formulaire, acceptation des présentes conditions et enregistrement du paiement ou de l’acompte demandé.</p>
        </section>

        <section class="terms-section">
          <h2>3. Informations fournies</h2>
          <p>Le client s’engage à transmettre des informations exactes, complètes et exploitables. Toute erreur, omission ou modification tardive peut entraîner des délais supplémentaires ou des ajustements sur la commande.</p>
        </section>

        <section class="terms-section">
          <h2>4. Personnalisation</h2>
          <p>Les contenus générés à partir des informations fournies par le client, y compris les noms des invités et éléments de personnalisation, sont préparés sur la base des données communiquées au moment de la commande.</p>
        </section>

        <section class="terms-section section-wide">
          <h2>5. Paiement et remboursement</h2>
          <ul>
            <li>Les montants versés couvrent les frais de préparation, de conception, de traitement et de mobilisation des ressources liées à la commande.</li>
            <li>En cas d’annulation, de report ou d’abandon de la commande par le client, le remboursement maximum est limité à 50 % de la somme déjà payée.</li>
            <li>Les 50 % non remboursables correspondent aux frais administratifs, techniques, créatifs et opérationnels déjà engagés.</li>
            <li>Aucune demande de remboursement intégral ne pourra être acceptée après validation de la commande, sauf obligation légale spécifique.</li>
          </ul>
        </section>

        <section class="terms-section">
          <h2>6. Délais</h2>
          <p>Les délais de livraison ou de mise à disposition sont donnés à titre indicatif et peuvent varier selon la complexité du projet, la réactivité du client et les validations nécessaires.</p>
        </section>

        <section class="terms-section">
          <h2>7. Responsabilité du client</h2>
          <p>Le client demeure responsable de la vérification finale des informations transmises, notamment les noms, dates, lieux, orthographes et coordonnées avant diffusion ou impression.</p>
        </section>

        <section class="terms-section section-wide">
          <h2>8. Acceptation</h2>
          <p>En cochant la case d’acceptation des termes et conditions sur les formulaires du site, le client reconnaît avoir lu, compris et accepté l’ensemble des présentes dispositions, y compris la limitation de remboursement à 50 % des montants payés.</p>
        </section>
      </div>

      <div class="terms-actions">
        <a href="javascript:history.back()" class="terms-back"><i class="fas fa-arrow-left"></i> Retour</a>
        <a href="index.php?page=commande" class="terms-home"><i class="fas fa-check-circle"></i> Continuer la commande</a>
      </div>
    </div>
  </div>
</div>