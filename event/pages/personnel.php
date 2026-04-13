
	
<div class="wrapper"> 
	  
	  
	 
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
    
   
<div class="row">
	<div class="col-xxl-12 col-xl-12 col-lg-12">
		<div class="card rounded-4">

        
                                <img src="../images/Logo_invitationSpeciale_1.png">

			   <div class="box-header d-flex b-0 justify-content-between align-items-center">
 
				  











<?php  
  
    if (isset($_POST['submitpersonnel'])) {

        
     
        


        // Récupération des données du formulaire en toute sécurité
$nom        = isset($_POST['nom']) ? trim($_POST['nom']) : '';
$prenom     = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
$postnom    = isset($_POST['postnom']) ? trim($_POST['postnom']) : '';
$phone      = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$adresse    = isset($_POST['adresse']) ? trim($_POST['adresse']) : '';
$date_nais  = isset($_POST['date_nais']) ? trim($_POST['date_nais']) : '';
$num_compte = isset($datasession['num_compte']) ? trim($datasession['num_compte']) : ''; // si provient de la session

// Validation minimale
if ($nom === '' || $prenom === '' || $date_nais === '') {
    die("Les champs Nom, Prénom et Date de naissance sont obligatoires.");
}

try {
    // Requête d'insertion
    $sql = "INSERT INTO personnel (nom, prenom, postnom, phone, num_compte, adresse, date_nais, date_enreg)
            VALUES (:nom, :prenom, :postnom, :phone, :num_compte, :adresse, :date_nais, NOW())";

    $q = $pdo->prepare($sql);
    $q->bindValue(':nom', $nom);
    $q->bindValue(':prenom', $prenom);
    $q->bindValue(':postnom', $postnom);
    $q->bindValue(':phone', $phone);
    $q->bindValue(':num_compte', $num_compte);
    $q->bindValue(':adresse', $adresse);
    $q->bindValue(':date_nais', $date_nais);

    $q->execute();
    $q->closeCursor();

            echo ' <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>
              Swal.fire({
                  title: "Agent !",
                  text: "Enregistrement effectué avec succès.",
                  icon: "success",
                  confirmButtonText: "OK"
              }).then((result) => {
                  if (result.isConfirmed) {
                      window.location.href = "index.php?page=personnel"; // Rédirection vers la page de détails
                  }
              });
              </script>';

        } catch (PDOException $e) {
            echo "❌ Erreur lors de l'insertion : " . $e->getMessage();
        }





           


 
    }

?>










 
                        
			   </div>
   
			   <div class="card-body pt-0" >
				   <div class="table-responsive">
					   


<form action="" method="post" class="row g-3" autocomplete="off">
  <div class="col-md-4">
    <label class="form-label">Nom *</label>
    <input type="text" name="nom" required class="form-control" required maxlength="50">
  </div>

  <div class="col-md-4">
    <label class="form-label">Postnom</label>
    <input type="text" name="postnom" required class="form-control" maxlength="50">
  </div>

  <div class="col-md-4">
    <label class="form-label">Prénom *</label>
    <input type="text" name="prenom" required class="form-control" required maxlength="50">
  </div>

  <div class="col-md-4">
    <label class="form-label">Date de naissance *</label>
    <input type="date" name="date_nais" required class="form-control" required>
  </div>

  <div class="col-md-8">
    <label class="form-label">Adresse</label>
    <textarea name="adresse" class="form-control" required rows="2" maxlength="500"></textarea>
  </div>

  <div class="col-md-4">
    <label class="form-label">Téléphone</label>
    <input type="tel" name="phone" required class="form-control" maxlength="50"
           placeholder="+243 999 999 999">
  </div>
 

  <div class="col-md-4">
    <label class="form-label">N° de compte Equity</label>
    <input type="text" name="num_compte" class="form-control" maxlength="50">
  </div>

  <div class="col-12 text-end">
    <button type="submit" name="submitpersonnel" class="btn btn-primary">
      Enregistrer
    </button>
  </div>
</form>

<div id="formMsg" class="mt-2"></div>








				   </div>
			   </div>	
		   </div>
	   </div>
   </div>
   
   
   
   
   
   
   
   
    
    <script>
(function(){
  const form = document.getElementById('personnelForm');
  const msg  = document.getElementById('formMsg');
  const btn  = document.getElementById('btnSubmit');

  function showMsg(html, ok=true){
    msg.innerHTML = html;
    msg.className = ok ? 'text-success' : 'text-danger';
  }

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    showMsg('', true);

    // validations simples côté client
    const fd = new FormData(form);
    const nom = (fd.get('nom')||'').trim();
    const prenom = (fd.get('prenom')||'').trim();
    const dnais = (fd.get('date_nais')||'').trim();
    const email = (fd.get('email')||'').trim();

    if (!nom || !prenom || !dnais){
      showMsg('Veuillez remplir au minimum Nom, Prénom et Date de naissance.', false);
      return;
    }
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
      showMsg('Adresse email invalide.', false);
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Enregistrement...';

    try {
      const resp = await fetch('pages/store.php', {
        method: 'POST',
        body: fd
      });
      const data = await resp.json().catch(()=> ({}));

      if (!resp.ok || !data.ok){
        throw new Error(data.message || 'Échec de l’enregistrement.');
      }

      showMsg('✅ Personnel enregistré (ID: '+ data.cod_pers +').', true);
      form.reset();

    } catch (err){
      showMsg('❌ ' + (err.message || 'Erreur serveur.'), false);
    } finally {
      btn.disabled = false;
      btn.textContent = 'Enregistrer';
    }
  });
})();
</script>
 
   
   
   
   
   
   
   
   
   
   
   
    
			   <!-- /.content -->
		   </div>
	 <!-- /.content-wrapper -->

     <br><br>
	   <?php include('users/pages/footer.php')?>
	 <!-- Side panel --> 
 
	 <!-- /.control-sidebar -->
 
	 
	 
   </div>
   <!-- ./wrapper -->
	   
	   
 
	   <!-- Page Content overlay -->
	   
	   
	   <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
	   <!-- Vendor JS -->
	   <script src="html/template/horizontal/src/js/vendors.min.js"></script>
	   <script src="html/template/horizontal/src/js/pages/chat-popup.js"></script>
		 <script src="html/assets/icons/feather-icons/feather.min.js"></script>
		 <script src="html/assets/vendor_components/Flot/jquery.flot.js"></script>
	   <script src="html/assets/vendor_components/Flot/jquery.flot.resize.js"></script>
	   <script src="html/assets/vendor_components/Flot/jquery.flot.pie.js"></script>
	   <script src="html/assets/vendor_components/Flot/jquery.flot.categories.js"></script>
	   <script src="html/assets/vendor_components/echarts/dist/echarts-en.min.js"></script>
	   <script src="html/assets/vendor_components/apexcharts-bundle/dist/apexcharts.js"></script>
	   <script src="html/assets/vendor_plugins/bootstrap-slider/bootstrap-slider.js"></script>
	   <script src="html/assets/vendor_components/OwlCarousel2/dist/owl.carousel.js"></script>
	   <script src="html/assets/vendor_components/flexslider/jquery.flexslider.js"></script>
	   <script src="html/assets/vendor_components/Web-Ticker-master/jquery.webticker.min.js"></script>
	   <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
   
	   
	   <!-- Cartiy Admin App -->
	   <script src="html/template/horizontal/src/js/demo.js"></script>
	   <script src="html/template/horizontal/src/js/jquery.smartmenus.js"></script>
	   <script src="html/template/horizontal/src/js/menus.js"></script>
	   <script src="html/template/horizontal/src/js/template.js"></script>
	   <script src="html/template/horizontal/src/js/pages/dashboard.js"></script>
	   <script src="html/template/horizontal/src/js/pages/slider.js"></script>
   
	   
	   <!-- Vendor JS --> 
	   <script src="html/assets/vendor_components/full-calendar/moment.js"></script>
	   <script src="html/assets/vendor_components/full-calendar/fullcalendar.min.js"></script> 
   
	   
	   
	   <!-- selecter JS --> 
	   <script src="html/assets/vendor_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
	   <script src="html/assets/vendor_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.js"></script>
	   <script src="html/assets/vendor_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
	   <script src="html/assets/vendor_components/select2/dist/js/select2.full.js"></script>
	   <script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.js"></script>
	   <script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
	   <script src="html/assets/vendor_plugins/input-mask/jquery.inputmask.extensions.js"></script>
	   <script src="html/assets/vendor_components/moment/min/moment.min.js"></script>
	   <script src="html/assets/vendor_components/bootstrap-daterangepicker/daterangepicker.js"></script>
	   <script src="html/assets/vendor_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
	   <script src="html/assets/vendor_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
	   <script src="html/assets/vendor_plugins/timepicker/bootstrap-timepicker.min.js"></script>
	   <script src="html/assets/vendor_plugins/iCheck/icheck.min.js"></script>
		
	   <script src="html/template/horizontal/src/js/pages/advanced-form-element.js"></script>
		 