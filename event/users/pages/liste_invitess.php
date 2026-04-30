   
   
   <?php 
   
    
   if (!$dataevent) {
	   $linkallinv = "#";
   }else{
	   $linkallinv = "../pages/liste_invites.php?event=".$codevent;
   } 
	$audienceLabels = EventWorkspaceService::audienceLabels((string) ($type_event ?? ''));
   
   ?>

  
 <div class="row" id="mesinv">
    <div class="col-xxl-12 col-xl-12 col-lg-12">
        <div class="card rounded-4">
            <div class="box-header d-flex b-0 justify-content-between align-items-center">
				<h4 class="box-title"><?php echo htmlspecialchars($audienceLabels['mine'], ENT_QUOTES, 'UTF-8'); ?></h4>
                <ul class="m-0" style="list-style: none;">
                    <li class="dropdown">
                        <!-- Bouton pour ouvrir la modale -->
                        <a href="#" 
                           class="waves-effect waves-light btn btn-outline btn-rounded btn-primary btn-sm" 
                           data-bs-toggle="modal" 
                           data-bs-target="#modalPdfInvites">
                            <i class="fa fa-fw fa-arrow-down"></i> Exporter en PDF
                        </a>
                    </li>
				   </ul>
			   </div>

			<style>
				.modal{z-index: 8999 !important;}
			</style>

			<!-- ================= MODALE PDF INVITES ================= -->
			<div class="modal fade" id="modalPdfInvites" tabindex="-1" aria-labelledby="modalPdfInvitesLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content rounded-3">
				<div class="modal-header">
					<h5 class="modal-title" id="modalPdfInvitesLabel"><?php echo htmlspecialchars($audienceLabels['pdf_title'], ENT_QUOTES, 'UTF-8'); ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
				</div>
				<div class="modal-body text-center">
					<p>Choisissez le mode d’affichage :</p>
					<a href="../pages/liste_invites.php?event=<?php echo $codevent;?>" target="_blank" class="btn btn-primary m-2">
						<?php echo htmlspecialchars($audienceLabels['pdf_by_name'], ENT_QUOTES, 'UTF-8'); ?>
					</a>
					<a href="../pages/liste_invites_partb.php?event=<?php echo $codevent;?>" target="_blank" class="btn btn-success m-2">
						Classé par nom des Tables
					</a>
				</div> 
				</div>
			</div>
			</div>







   
			   <div class="card-body pt-0">
				   <div class="table">
					   <table class="table mb-0">
						   <tbody id="inviteList">
 
								 <input type="text" id="searchInput" class="form-control ps-15 bg-transparent" placeholder="<?php echo htmlspecialchars($audienceLabels['search'], ENT_QUOTES, 'UTF-8'); ?>" style="height:40px;font-size:16px;border:1px solid #ccc;margin-bottom:10px;">
   
								</td> 
						   

							  <?php 

								if (isset($_GET['page']) && $_GET['page'] === 'addinvite') {
									$tri = 'ORDER BY date_inv DESC';
								} else {
									$tri = 'ORDER BY nom ASC';
								}

								$stmt = $pdo->prepare("SELECT * FROM invite WHERE cod_mar = :codevent $tri");
								$stmt->execute([':codevent' => $codevent]);

   
							   if ($stmt->rowCount() > 0) {
								   while ($row_inv = $stmt->fetch(PDO::FETCH_ASSOC)) { 
									   $linkpdf = $dataevent['invit_religieux'] ? "../pages/invitation_speciale.php?cod=".$row_inv['id_inv']."&event=".$codevent : "#";
   
									   $siege = $row_inv['siege'] ? ucfirst($pdo->query("SELECT nom_tab FROM tableevent WHERE cod_tab = '".$row_inv['siege']."'")->fetchColumn()) : '<em>Non défini</em>';
									   $sing = $row_inv['sing'] === 'C' ? 'Couple' : ($row_inv['sing'] ? 'Singleton' : '<em>Non défini</em>');
							   


									   //----------------------verifier qui a confirmé-----------------------
										$stmtconf2 = $pdo->prepare("SELECT * FROM confirmation WHERE noms = :noms AND cod_mar = :cod_mar");
										$stmtconf2->execute([':noms' => $row_inv['nom'],':cod_mar' => $codevent]);
										$row_conf2 = $stmtconf2->fetch(PDO::FETCH_ASSOC);

										$styleconf = $row_conf2 ? 'color:#28a745;' : '';

										if ($row_conf2) {
											if ($row_inv['sing'] === "C") {
												$reponseconf = '<em style="color:#28a745;">Ont repondu</em>';
											}else{
												$reponseconf = '<em style="color:#28a745;">A repondu</em>';
											}
										}else{ 
												$reponseconf = '<em style="color:#ddd;">Reponse en attente</em>';
										}
										//-------------------------------------------------------------------

							 
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
  
 
                       
					   <a class="dropdown-item" href="#" onclick="XXopenModal('<?php echo htmlspecialchars(ucfirst($row_inv['nom'])); ?>', '<?php echo $row_inv['id_inv']; ?>')" style="color:#aaa;">
					   <i class="fa fa-share"></i> <?php echo htmlspecialchars($audienceLabels['notify'], ENT_QUOTES, 'UTF-8'); ?></a> 

					   
<?php if ($dataevent['invit_religieux'] !== NULL): ?>
    <a class="dropdown-item" target="_blank"
       href="../pages/invitation_speciale.php?cod=<?= htmlspecialchars($row_inv['id_inv']); ?>&event=<?= htmlspecialchars($codevent); ?>">
        <i class="fa fa-download"></i> Partager l'invitation
    </a>
<?php endif; ?>
 
 



											   <a class="dropdown-item" href="index.php?page=modinv&idinv=<?php echo $row_inv['id_inv'];?>"><i class="fa fa-edit"></i> <?php echo htmlspecialchars($audienceLabels['edit'], ENT_QUOTES, 'UTF-8'); ?></a>
   
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
					  <i class="fa fa-remove"></i> <?php echo htmlspecialchars($audienceLabels['delete'], ENT_QUOTES, 'UTF-8'); ?>
</a>  




      
       
                        </div>
                         </div>
                       </div>



										   </td>
									   </tr>
   
							   <?php 
   
								   }
   
							   } else {
								   echo '<tr><td colspan="3" class="text-left" style="font-style:italic;">' . htmlspecialchars($audienceLabels['empty_reaction'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
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
   require_once __DIR__ . '/whatsapp_template_sender.php';
	$sharePreviewContext = isapp_whatsapp_sender_preview_context($pdo, $codevent);

  if (isset($_POST['submitwhat'])) {
	   $shareErrorMessage = null;
	   $shareSuccessMessage = null;
	   try {
		   $result = isapp_whatsapp_send_template_invitation($pdo, [
			   'event_code' => $codevent,
			   'invite_id' => $_POST['inviteId'] ?? null,
			   'phone' => $_POST['phoneinv'] ?? '',
			   'invite_name' => $_POST['inviteName'] ?? 'Invite',
			   'pdf_link' => $_POST['pdf_link'] ?? '',
			   'success_redirect' => 'index.php?page=mb_accueil',
		   ]);
		   $shareSuccessMessage = $result['success_message'];
	   } catch (\Throwable $exception) {
		   $shareErrorMessage = (string) $exception->getMessage();
		   if ($shareErrorMessage === '') {
			   $shareErrorMessage = 'L’envoi de l’invitation WhatsApp a echoue.';
		   }
	   }

	   if ($shareSuccessMessage !== null) {
		   echo '<script>Swal.fire({title:"Notification !",text:' . json_encode($shareSuccessMessage) . ',icon:"success",confirmButtonText:"OK"}).then((result)=>{if(result.isConfirmed){window.location.href="index.php?page=mb_accueil";}});</script>';
	   }

	   if ($shareErrorMessage !== null) {
		   echo '<script>Swal.fire({title:"Échec de l’envoi",text:' . json_encode($shareErrorMessage) . ',icon:"error",confirmButtonText:"OK"});</script>';
	   }
   }
   ?>
			   <div class="form-group"> 
				   <span class="close" onclick="closeModal()" style="cursor: pointer; float: right; font-size: 24px;">&times;</span><br>
				   <h4 id="modalTitle">Envoyer l'invitation</h4> <br><br>
				   <input type="text" required pattern="^\+\d{1,3}\d{9,}$" 
				   title="Veuillez entrer un numéro au format international (ex: +243810678785)" id="whatsappNumber" name="phoneinv" class="input-group-text bg-transparent" style="border-radius:7px 7px 0px 0px;height:45px;width:100%;" placeholder="Numéro WhatsApp" />
				   <input type="hidden" id="inviteName" name="inviteName" />
				   <input type="hidden" id="inviteId" name="inviteId" />
				   <input type="hidden" id="pdfLink" name="pdf_link" />
				   <button class="btn btn-primary" type="submit" name="submitwhat" style="width:100%;">Envoyer l'invitation</button>
			   </div>
				   <p style="margin:12px 0 0;color:#475569;font-size:13px;">En validant cette action, l'invitation PDF sera envoyee sur WhatsApp au numero indique pour cet invite.</p>
				   <div style="margin-top:12px;padding:12px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;color:#334155;font-size:13px;line-height:1.6;">
					   <strong style="display:block;margin-bottom:6px;color:#0f172a;">Exemple de message automatique</strong>
					   Bonjour <span id="previewInviteName">votre invite</span>,<br>
					   Nous avons le plaisir de vous transmettre votre invitation a <?php echo htmlspecialchars($sharePreviewContext['event_label'], ENT_QUOTES, 'UTF-8'); ?>.<br><br>
					   Nous vous remercions de bien vouloir confirmer votre presence.<br><br>
					   Cordialement,<br>
					   <?php echo htmlspecialchars($sharePreviewContext['signature'], ENT_QUOTES, 'UTF-8'); ?>.<br>
					   Merci.
				   </div>
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
			   document.getElementById('modalTitle').innerText = "Envoyer l'invitation a " + inviteName;
			   document.getElementById('previewInviteName').innerText = inviteName;
			   document.getElementById('shareModal').style.display = 'flex';
			   const linkpdf = "../pages/invitation_speciale.php?cod=" + inviteId + "&event=<?php echo $codevent; ?>";
			   document.getElementById('inviteName').value = inviteName;
			   document.getElementById('inviteId').value = inviteId;
			   document.getElementById('pdfLink').value = linkpdf;
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
   
   
   
   