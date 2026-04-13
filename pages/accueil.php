 
 
 <style>
    /* Styles par défaut (bureau/tablette) */
.cta .btn {
  display: inline-block;
  width: auto;
}

/* Sur mobile : les boutons prennent 100% de largeur */
@media (max-width: 768px) {
  .cta {
    display: flex;
    flex-direction: column;
    gap: 10px; /* petit espace entre les boutons */
  }

  .cta .btn {
    width: 100%;
    text-align: center;
  }
}
 </style>
 <header>
    <div class="container nav">
      <a class="brand" href="index.php?page=accueil" aria-label="Accueil Invitation Spéciale">
        <img src="images/Logo_invitationSpeciale_4.png" width="250px">
      </a>
      <nav aria-label="Navigation principale" class="menu">
        <a href="#services">Services</a>
        <a href="#galerie">Galerie</a>
        <a href="#tarifs">Tarifs</a>
        <a href="#contact">Contact</a>
        <a href="#contact">Mon Compte</a>
      </nav>
      <button class="btn hamb" aria-expanded="false" aria-controls="mobileMenu" style="color:#eab308;" id="hamb">Menu</button>
      <a  href="https://wa.me/243000000000" target="_blink" rel="noopener" aria-label="Discuter sur WhatsApp" style="color:#eab308;"><i class="fab fa-whatsapp icon" style="font-size:40px;margin-right:25px;"></i></a>
    </div>
  </header>

  <main id="accueil" class="hero">
    <div class="container grid">
      <div>
        <span class="eyebrow">Design • E‑invites • QR Codes</span>
        <h2>Des invitations haut de gamme et des e‑invitations qui rendent vos événements <em>inoubliables</em>.</h2>
        <p class="hero-card">Notre équipe conçoit des invitations imprimées d’exception, des sites de mariage élégants et des e‑invites avec QR code pour un contrôle d’accès fluide. De Kinshasa au monde, nous personnalisons chaque détail.</p>
        <div class="cta">
          <a class="btn primary" target="_blink" href="event/index.php?page=commande">Passer une commande</a>
          <a class="btn" target="_blink" href="event/index.php?page=login"><i class="fab fa-account icon"></i> Mon Compte</a>
        </div>

        
        <?php
        
        
     // Total des événements
     $stmtne = $pdo->prepare("
     SELECT COUNT(*) as total_event
     FROM events");
     $stmtne->execute(); 
     $datane = $stmtne->fetch(PDO::FETCH_ASSOC); 
     $datanbevent = $datane ? (int)$datane['total_event'] : 0;
     
     
     
     ?>


        <div class="stats" aria-label="Chiffres clés">
          <div class="stat"><div class="n"><?php echo $datanbevent; ?></div><div>projets réalisés</div></div>
          <div class="stat"><div class="n">99%</div><div>clients satisfaits</div></div>
          <div class="stat"><div class="n">24h</div><div>réponse moyenne</div></div>
        </div>
      </div>
      <div>
        <figure class="mock" aria-label="Aperçu maquette">
          <img src="images/isimage.PNG" alt="Show Room Invitation Spéciale"/>
        </figure>
      </div>
    </div>
    
  </main>

  <section id="services">
    <div class="container">
      <div class="section-title">
        <h3>Nos services</h3>
        <span class="pill">Sur mesure</span>
      </div>
      <div class="grid-3">
        <article class="card">
          <h4>Invitations imprimées premium</h4>
          <p>Letterpress, dorure à chaud, vernis sélectif, papiers de création — pour un rendu luxueux et durable.</p>
          <div class="tags"><span class="tag">Dorure</span><span class="tag">Letterpress</span><span class="tag">Papier 700g</span></div>
        </article>
        <article class="card">
          <h4>E‑invitations & QR codes</h4>
          <p>E‑invites personnalisées avec QR code unique, suivi des confirmations (RSVP) et contrôle d’accès à l’événement.</p>
          <div class="tags"><span class="tag">QR unique</span><span class="tag">RSVP</span><span class="tag">Dashboard</span></div>
        </article>
        <article class="card">
          <h4>Sites d’événement / mariage</h4>
          <p>Pages élégantes pour raconter votre histoire, gérer l’itinéraire, les photos et les remerciements.</p>
          <div class="tags"><span class="tag">Story</span><span class="tag">Galerie</span><span class="tag">Google Maps</span></div>
        </article>
      </div>
    </div>
  </section>

  <section id="galerie">
    <div class="container">
      <div class="section-title">
        <h3>Nos Modèles</h3>
        <a class="btn" href="https://www.tiktok.com/@invitationspeciale?_t=8qY4kfvmgrN&_r=1"> <i class="fab fa-tiktok icon"></i> Tout voir</a>
      </div>
      <div class="masonry" aria-label="Exemples de réalisations">

        <?php 
            $reqmod = $pdo->prepare("SELECT * FROM modele_is where siteposition IS NOT NULL ORDER by siteposition ASC");
            $reqmod->execute();  
            while ($data_mod = $reqmod->fetch()) {
            ?> 

              <a href="#"><img src="event/images/modeleis/<?php echo $data_mod['image']?>" alt="Carte d’invitation typographique"/></a>
      
            <?php } ?> 

      
<!--         
        <a href="#"><img src="https://images.unsplash.com/photo-1529336953121-ad5a0d43d0d2?q=80&w=1200&auto=format&fit=crop" alt="Papeterie dorée"/></a>
        <a href="#"><img src="https://images.unsplash.com/photo-1519681390220-8f785ba67e45?q=80&w=1200&auto=format&fit=crop" alt="Menu et carton réponse"/></a>
        <a href="#"><img src="https://images.unsplash.com/photo-1531928351158-2f736078e0a1?q=80&w=1200&auto=format&fit=crop" alt="Faire-part minimaliste"/></a>
        <a href="#"><img src="https://images.unsplash.com/photo-1520697222868-9408f655a4c0?q=80&w=1200&auto=format&fit=crop" alt="Découpe laser"/></a>
        <a href="#"><img src="https://images.unsplash.com/photo-1519682337058-a94d519337bc?q=80&w=1200&auto=format&fit=crop" alt="E‑invite sur smartphone"/></a>
-->
    
    </div>
    </div>
  </section>

  <section id="avantages">
    <div class="container">
      <div class="section-title">
        <h3>Pourquoi nous choisir</h3>
        <span class="pill">Impact réel</span>
      </div>
      <div class="features">
        <div class="feature"><b>Contrôle d’accès</b><br>QR unique par invité, scan rapide à l’entrée, statistiques en direct.</div>
        <div class="feature"><b>Qualité premium</b><br>Finitions haut de gamme, papiers épais, couleurs maîtrisées.</div>
        <div class="feature"><b>Accompagnement</b><br>Conseil créatif, maquettage, validation visuelle et suivi.</div>
        <div class="feature"><b>Délais tenus</b><br>Organisation millimétrée et communication transparente.</div>
      </div>
    </div>
  </section>

  <section id="temoignages">
    <div class="container">
      <div class="section-title">
        <h3>Ils nous ont fait confiance</h3>
        <span class="pill">Avis clients</span>
      </div>
      <div class="testis">
        <div class="testi">
          « Un service impeccable et des invitations sublimes. Le QR code a fluidifié l’accueil des invités. »
          <div class="name"><span class="avatar" aria-hidden="true"></span> Sarah & Daniel</div>
        </div>
        <div class="testi">
          « L’e‑invite a permis d’atteindre tout le monde rapidement, et le suivi des RSVP est top. »
          <div class="name"><span class="avatar" aria-hidden="true"></span> Laura M.</div>
        </div>
        <div class="testi">
          « Excellent accompagnement du début à la fin. Je recommande fortement. »
          <div class="name"><span class="avatar" aria-hidden="true"></span> Société K.</div>
        </div>
      </div>
    </div>
  </section>

  <section id="tarifs">
    <div class="container">
      <div class="section-title">
        <h3>Pack & tarifs</h3>
        <span class="pill">Transparence</span>
      </div>
      <div class="pricing">
        <div class="price">
          <h4>Essentiel</h4>
          <div class="big">99$</div>
          <ul>
            <li>Design invitation (imprimée ou e‑invite)</li>
            <li>2 propositions visuelles</li>
            <li>QR code statique</li>
          </ul>
          <a class="btn" href="#contact">Choisir</a>
        </div>
        <div class="price best">
          <h4>Pro</h4>
          <div class="big">249$</div>
          <ul>
            <li>Design complet + déclinaisons</li>
            <li>E‑invite avec QR unique par invité</li>
            <li>RSVP + tableau de suivi</li>
          </ul>
          <a class="btn primary" href="#contact">Choisir</a>
        </div>
        <div class="price">
          <h4>Prestige</h4>
          <div class="big">Sur devis</div>
          <ul>
            <li>Papeterie premium (dorure, letterpress)</li>
            <li>Site d’événement personnalisé</li>
            <li>Contrôle d’accès avancé</li>
          </ul>
          <a class="btn" href="#contact">Demander un devis</a>
        </div>
      </div>
    </div>
  </section>

  <section id="contact">
    <div class="container">
      <div class="section-title">
        <h3>Parlons de votre événement</h3>
        <span class="pill">Réponse sous 24h</span>
      </div>
      <div class="cta-bar">
        <form id="contactForm" aria-label="Formulaire de contact" style="display:grid; gap:12px; width:100%; max-width:760px">
          <label class="sr-only" for="name">Nom</label>
          <input id="name" name="name" required placeholder="Votre nom" style="padding:12px 14px; border-radius:12px; border:1px solid var(--line); background:#0f1424; color:var(--text)">
          <label class="sr-only" for="email">Email</label>
          <input id="email" name="email" type="email" required placeholder="Email" style="padding:12px 14px; border-radius:12px; border:1px solid var(--line); background:#0f1424; color:var(--text)">
          <label class="sr-only" for="msg">Message</label>
          <textarea id="msg" name="msg" rows="4" required placeholder="Parlez-nous de votre projet…" style="padding:12px 14px; border-radius:12px; border:1px solid var(--line); background:#0f1424; color:var(--text)"></textarea>
          <div style="display:flex; gap:10px; flex-wrap:wrap">
            <button type="submit" class="btn primary">Envoyer</button>
            <a class="btn" href="https://tiktok.com/@invitationspeciale" target="_blank" rel="noopener">Voir TikTok</a>
            <a class="btn" href="https://wa.me/243000000000" target="_blank" rel="noopener">WhatsApp</a>
          </div>
        </form>
        <div>
          <div class="chip">Kinshasa, RDC</div>
          <div class="chip">Dispo: Lun‑Sam 8h‑19h</div>
          <div class="chip">Délai maquette: 48‑72h</div>
        </div>
      </div>
    </div>
  </section>

  <footer>
    <div class="container foot">
      <small>© <span id="y"></span> Invitation Spéciale. Tous droits réservés.</small>
      <div class="social">
        <a class="chip" href="https://wa.me/243000000000" target="_blank" rel="noopener">WhatsApp</a>
        <a class="chip" href="https://www.instagram.com/invitationspeciale/" target="_blank" rel="noopener">Instagram</a>
        <a class="chip" href="https://www.tiktok.com/@invitationspeciale" target="_blank" rel="noopener">TikTok</a>
      </div>
    </div>
  </footer>

  <script>
    // Année
    document.getElementById('y').textContent = new Date().getFullYear();
    // Menu mobile basique
    const hamb = document.getElementById('hamb');
    hamb?.addEventListener('click', () => {
      const open = hamb.getAttribute('aria-expanded') === 'true';
      hamb.setAttribute('aria-expanded', String(!open));
      document.querySelector('.menu')?.classList.toggle('open');
    });
  </script>
  <!-- Optionnel: SweetAlert2 pour la validation UX -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const form = document.getElementById('contactForm');
    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(form).entries());
      // TODO: POST vers votre endpoint PHP (ex: /contact_send.php)
      try {
        // Démo sans backend
        await Swal.fire({
          title: 'Merci !',
          text: 'Votre message a été envoyé. Nous revenons vers vous rapidement.',
          icon: 'success',
          confirmButtonColor: '#111',
          confirmButtonText: 'Fermer',
          background: '#0f1424',
          color: '#e6e8ee'
        });
        form.reset();
      } catch (err) {
        Swal.fire({
          title: 'Oups…',
          text: "Une erreur est survenue. Veuillez réessayer.",
          icon: 'error',
          confirmButtonColor: '#111',
          background: '#0f1424',
          color: '#e6e8ee'
        });
      }
    });
  </script>