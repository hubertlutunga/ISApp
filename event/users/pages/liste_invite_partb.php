   
   
   <?php 
   
    
   if (!$dataevent) {
	   $linkallinv = "#";
   }else{
	   $linkallinv = "../pages/liste_invites.php?event=".$codevent;
   } 
   
   ?>
   
   <div class="row" id='mesinv'>
	   <div class="col-xxl-12 col-xl-12 col-lg-12">
		   <div class="card rounded-4">
			   <div class="box-header d-flex b-0 justify-content-between align-items-center">
				   <h4 class="box-title">Mes invités</h4>
				   <ul class="m-0" style="list-style: none;">
					   <li class="dropdown">
						   <a target="_blank" href="<?php echo $linkallinv; ?>" class="waves-effect waves-light btn btn-outline btn-rounded btn-primary btn-sm">
							   <i class="fa fa-fw fa-arrow-down"></i> Obtenir le PDF
						   </a>
					   </li>
				   </ul>
			   </div>
   
			   <div class="card-body pt-0">
				   <div class="table">
					   <table class="table mb-0">
						   <tbody id="inviteList">
 
								 <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un nom..." style="height:40px;font-size:16px;border:1px solid #ccc;margin-bottom:10px;">
   
								</td> 
						   

							  <?php 

								if (isset($_GET['page']) && $_GET['page'] === 'addinvite') {
									$tri = 'ORDER BY date_inv DESC';
								} else {
									$tri = 'ORDER BY nom ASC';
								}

								$confirmedNames = InviteStatusService::confirmedNamesIndex($pdo, (int) $codevent);

								$stmt = $pdo->prepare("SELECT * FROM invite WHERE cod_mar = :codevent $tri");
								$stmt->execute([':codevent' => $codevent]);

   
							   if ($stmt->rowCount() > 0) {
								   while ($row_inv = $stmt->fetch(PDO::FETCH_ASSOC)) { 
									   $linkpdf = $dataevent['invit_religieux'] ? "../pages/invitation_elect.php?cod=".$row_inv['id_inv']."&event=".$codevent : "#";
   
									   $seatName = EventTableService::findNameById($pdo, isset($row_inv['siege']) ? (int) $row_inv['siege'] : null);
									   $siege = $seatName ? ucfirst($seatName) : '<em>Non défini</em>';
									   $sing = $row_inv['sing'] === 'C' ? 'Couple' : ($row_inv['sing'] ? 'Singleton' : '<em>Non défini</em>');
									   $confirmed = isset($confirmedNames[InviteStatusService::normalizeName((string) $row_inv['nom'])]);
									   $reponseconf = InviteStatusService::confirmationLabel($confirmed, $row_inv['sing'] ?? null);

							 
							 ?>
							    
											<tr id="inv-<?= (int)$row_inv['id_inv'] ?>" class="invite-item">

											<td class="pt-0 px-0 b-0">
												<a style="padding-top:10px;" class="d-block fw-500 fs-14 invite-name" href="index.php?page=modinv&idinv=<?php echo $row_inv['id_inv'];?>"><?php echo htmlspecialchars(ucfirst($row_inv['nom'])); ?></a>
 
											   <span class="text-fade"><?php echo $siege; ?> - <?php echo $sing; ?><br><em><?php echo $reponseconf;?></em></span>
										   </td> 
   
										   <td class="text-end b-0 pt-0 px-0" width="15%"> 
											  



 
 
 
 
 
  
 
 
  
 
 
 
											   <div class="list-icons d-inline-flex">
                          <div class="list-icons-item dropdown">
                                          
  
                           <a href="#" class="waves-effect waves-light btn btn-outline btn-rounded btn-warning mb-0 btn-sm list-icons-item dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h" style="font-size:20px;"></i></a>
                           
                                                     <div class="dropdown-menu dropdown-menu-end">
  
 
                       
					   <a class="dropdown-item" href="#" onclick="openModal('<?php echo htmlspecialchars(ucfirst($row_inv['nom'])); ?>', '<?php echo $row_inv['id_inv']; ?>')" style="color:#aaa;">
					   <i class="fa fa-share"></i> Notifier l'invité</a> 
  
<a class="dropdown-item" target="_blank"
   href="../pages/invitation_elect.php?cod=<?= $row_inv['id_inv'];?>&event=<?= $codevent; ?>">
  <i class="fa fa-download"></i> Partager l'invitation
</a>
 
 
 



											   <a class="dropdown-item" href="index.php?page=modinv&idinv=<?php echo $row_inv['id_inv'];?>"><i class="fa fa-edit"></i> Modifier l'invité</a>
   
 <a class="dropdown-item"
   href="#"
   style="color:red;"
   title="Suppression"
   onclick="confirmSuppInv(
     event,
     '<?= (int)$row_inv['id_inv'] ?>',
     '<?= htmlspecialchars($codevent, ENT_QUOTES) ?>',
     '<?= htmlspecialchars(ucfirst($row_inv['nom']), ENT_QUOTES) ?>'
   )">
  <i class="fa fa-remove"></i> Supprimer l'invité
</a>  




      
       
                        </div>
                         </div>
                       </div>



										   </td>
									   </tr>
   
							   <?php 
   
								   }
   
							   } else {
								   echo '<tr><td colspan="3" class="text-left" style="font-style:italic;">Aucun invité trouvé</td></tr>';
							   }
   
							   ?>
   
						   </tbody>
					   </table>
				   </div>
			   </div>	
		   </div>
	   </div>





 



<script>
async function confirmSuppInv(e, idInv, codEvent, nom) {
  e.preventDefault();

  Swal.fire({
    title: "Supprimer ?",
    html: "Voulez-vous vraiment supprimer <b>" + nom + "</b> ?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Oui, supprimer",
    cancelButtonText: "Annuler",
    reverseButtons: true,
    showLoaderOnConfirm: true,
    allowOutsideClick: () => !Swal.isLoading(),
    preConfirm: async () => {
      try {
        const res = await fetch("pages/ajax_supprimer_invite.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ idinv: idInv, cod: codEvent })
        });
        if (!res.ok) throw new Error("Erreur serveur (" + res.status + ")");
        const data = await res.json();
        if (!data.success) throw new Error(data.message || "Suppression impossible.");
        return data; // passe à then(result)
      } catch (err) {
        Swal.showValidationMessage(err.message);
      }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      // Retirer la ligne sans recharger
      const row = document.getElementById("inv-" + idInv);
      if (row) row.remove();

      Swal.fire({
        title: "Supprimé",
        text: nom + " a été supprimé.",
        icon: "success",
        timer: 1800,
        showConfirmButton: false
      });
    }
  });
}
</script>


	   <!-- Fenêtre modale -->
	   <div id="shareModal" class="modalinv" style="display: none;">
		   <div class="modal-content">
			   <form action="" method="post">
   <?php 
   require_once '../../twilio-php-main/src/Twilio/autoload.php'; 
   use Twilio\Rest\Client;
   
   if (isset($_POST['submitwhat'])) {
	   // Préparation de la requête pour récupérer les événements
	   $inviteName = $_POST['inviteName'];
	   $stmtevz = $pdo->prepare("SELECT * FROM events WHERE cod_event = :cod_event");
	   $stmtevz->execute(['cod_event' => $codevent]);
	   $dataeventv = $stmtevz->fetch();
   
	   if (!$dataeventv) {
		   $codevent = '';
		   $date_event = '';
		   $type_event = '';
		   $display = 'none';
	   } else {  
		   $codevent = $dataeventv['cod_event'];
		   $date_event = $dataeventv['date_event'];
		   $type_event = $dataeventv['type_event'];
		   $display = 'block';
	   }
   
	   // Détermination du type d'événement
	   if ($type_event == "1") {
		   $fetard = (($dataeventv['prenom_epouse'] ?? '') . ' & ' . ($dataeventv['prenom_epoux'] ?? '')) ?: 'Inconnu';
		   $typeevent = 'au Mariage ' . $dataeventv['type_mar'] .' de '.$fetard. ', le ' . date('d M Y à H:i', strtotime($dataeventv['date_event']));
	   } elseif ($type_event == "2") {
		   $fetard = $dataeventv['nomfetard'] ?? 'Inconnu';
		   $typeevent = "à l'anniversaire de " . $fetard . ', le ' . date('d m Y à H:i', strtotime($dataeventv['date_event']));
	   } elseif ($type_event == "3") {
		   $fetard = $dataeventv['nomfetard'] ?? 'Inconnu';
		   $typeevent = "à la conférence de " . $fetard . ', le ' . date('d m Y à H:i', strtotime($dataeventv['date_event']));
	   }
   

	    

		// Vos identifiants Twilio
		$accountSid = 'AC5cbb94f85695ce16d97ce2ca2c3f7db0';
		$authToken = '2fc99f87d42f61c691c01df995fb8290'; 
		$twilionumber = 'whatsapp:+17167403177'; // Votre Messaging Service SID
		$recipientNumber = 'whatsapp:+243852266590'; // Numéro du destinataire

		// Créer une instance du client Twilio
		$client = new Client($accountSid, $authToken);

		// Envoi d'un message avec un modèle
		$messageTemplate = $client->messages->create(
			$recipientNumber,
			[
				'from' => $twilionumber,
				//'template' => 'tempinvitation', // Nom du modèle
				//'contentSid' => 'HX89f7cc8f64f0b71ade4a22cdffcb744d', // NEW
				'contentSid' => 'HX5e527d4a4e566b51065fcebade782c17',
				'templateData' => json_encode([
					'params' => [
						'1' => $inviteName, // Nom
						'2' => $typeevent, // Événement
						'3' => 'www.invitationspeciale.com/site/index.php?page=accueil&cod='.$codevent, // Lien
						'4' => 'www.invitationspeciale.com/site/index.php?page=accueil&cod='.$codevent // Invitation
					],
				]),
				'language' => 'fr', // Langue du modèle
			]
		);
   
	   echo '<script>
	   Swal.fire({
		   title: "Notification !",
		   text: "Notification envoyée avec succès.",
		   icon: "success",
		   confirmButtonText: "OK"
	   }).then((result) => {
		   if (result.isConfirmed) {
			   window.location.href = "index.php?page=mb_accueil"; // Rédirection vers la page de détails
		   }
	   });
	   </script>';
   }
   ?>
			   <div class="form-group"> 
				   <span class="close" onclick="closeModal()" style="cursor: pointer; float: right; font-size: 24px;">&times;</span><br>
				   <h4 id="modalTitle">Notifier </h4> <br><br>
				   <input type="text" required pattern="^\+\d{1,3}\d{9,}$" 
				   title="Veuillez entrer un numéro au format international (ex: +243810678785)" id="whatsappNumber" name="phoneinv" class="input-group-text bg-transparent" style="border-radius:7px 7px 0px 0px;height:45px;width:100%;" placeholder="Numéro WhatsApp" />
				   <input type="hidden" id="inviteName" name="inviteName" />
				   <button class="btn btn-primary" type="submit" name="submitwhat" style="border-radius:0px 0px 7px 7px;width:100%;">Notifier</button>
			   </div>
			   <br>
			   <a href="#" id="downloadLink">Télécharger l'invitation</a>
			   </form>
		   </div>
	   </div>
   
	   <style>
		   .modalinv {
			   position: fixed;
			   top: 0;
			   left: 0;
			   width: 100%;
			   height: 100%;
			   background-color: rgba(0, 0, 0, 0.5);
			   display: flex;
			   justify-content: center;
			   align-items: center;
			   z-index: 3000;
		   }
   
		   .modal-content {
			   background-color: white;
			   padding: 20px;
			   border-radius: 5px;
			   box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
			   position: relative;
		   }
   
		   .close {
			   position: absolute;
			   top: 10px;
			   right: 15px;
			   color: #aaa;
			   font-size: 24px;
		   }
   
		   .close:hover {
			   color: #000;
		   }
	   </style>
   
	   <script>
		   function openModal(inviteName, inviteId) {
			   document.getElementById('modalTitle').innerText = 'Notifier ' + inviteName;
			   document.getElementById('shareModal').style.display = 'flex';
			   const linkpdf = "../pages/invitation_elect.php?cod=" + inviteId + "&event=<?php echo $codevent; ?>";
			   document.getElementById('downloadLink').setAttribute('href', linkpdf);
			   document.getElementById('downloadLink').setAttribute('target', "_blank");
			   document.getElementById('inviteName').value = inviteName; // Récupération du nom de l'invité
		   }
   
		   function closeModal() {
			   document.getElementById('shareModal').style.display = 'none';
		   }
	   </script>





	<!-- filtrer la recherche en temps reel -->

		<script>
		document.getElementById("searchInput").addEventListener("input", function () {
			let filter = this.value.toLowerCase();
			let rows = document.querySelectorAll("#inviteList .invite-item");

			rows.forEach(function (row) {
				let name = row.querySelector(".invite-name").textContent.toLowerCase();
				row.style.display = name.includes(filter) ? "" : "none";
			});
		});
		</script>


   </div>
   
   
   
   