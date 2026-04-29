
  <?php
  $impersonationFlash = null;

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['impersonate_user_id'])) {
    $result = UserAccountService::startImpersonation($pdo, (int) $_POST['impersonate_user_id']);

    if (!empty($result['success'])) {
      header('Location: index.php?page=mb_accueil');
      exit();
    }

    $impersonationFlash = [
      'type' => 'danger',
      'message' => (string) ($result['message'] ?? 'Impossible de changer de compte.'),
    ];
  }
  ?>

	<div class="wrapper"> 
	 

  <?php include('header_admin.php');?>
   
 

  <!-- Content Wrapper. Contains page content -->
 	<div class="content-wrapper">
			<div class="container-full">
			<!-- Main content -->

<!-- 

		<div class="content-header text-center">
			<div class="d-flex align-items-center">
				<div class="me-auto">
					<h3 class="page-title">Weather widgets</h3>
					<div class="d-inline-block align-items-center">
						<nav>
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
								<li class="breadcrumb-item" aria-current="page">Widgets</li>
								<li class="breadcrumb-item active" aria-current="page">Weather widgets</li>
							</ol>
						</nav>
					</div>
				</div>
				
			</div>
		</div> -->

















				
<div class="row salut">

<?php 

$heure = date('H');

if ($heure < 12) {
$salut = 'Bonjour';
}elseif ($heure > 11 AND $heure < 15){
$salut = 'Bon Après-midi';
}elseif ($heure > 12){
$salut = 'Bonsoir';
}

 
?>

<p style="text-align:center;">
	<?php  // echo "La valeur de codevent est : " . $codevent; 
	echo $salut;?> <b>
	<?php echo mb_convert_case($datasession['noms'], MB_CASE_TITLE, "UTF-8");?> </b>!
</p>



  
</div>


 









<?php 


		 

		// ----------------tous les invités confirmés--------------------
		$stmtccli = $pdo->prepare("SELECT COUNT(*) as total_client FROM is_users");
		$stmtccli->execute();

		// Récupération du résultat
		$row_ccli = $stmtccli->fetch(PDO::FETCH_ASSOC);

		// Retourne 0 si aucun invité n'est trouvé, sinon retourne le total
		$total_ccli = $row_ccli ? (int)$row_ccli['total_client'] : 0;


  
?>






			<section class="content">
        <?php if ($impersonationFlash !== null) { ?>
        <div class="alert alert-<?php echo htmlspecialchars($impersonationFlash['type'], ENT_QUOTES, 'UTF-8'); ?>">
          <?php echo htmlspecialchars($impersonationFlash['message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php } ?>
				<div class="box box-body">
					<div class="row"> 
						<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-12">
							<div class="box-body rounded-0 p-0 pb-lg-0 pb-sm-15 pb-xs-15 be-1 fill-icon">
								<div class="d-flex align-items-center">
									<div class="w-70 h-70 me-15 bg-info-light rounded-circle text-center p-10">
										<div class="w-50 h-50 bg-info rounded-circle">
										  <i class="fas fa-user fs-24 l-h-50"></i>
										</div>		
									</div>
									<div class="d-flex flex-column">
                                        <a href="index.php?page=mb_conf_list">
										<span class="text-fade fs-12">Clients</span>
										<h2 class="text-dark hover-primary m-0 fw-600"><?php echo $total_ccli; ?></h2>
                                        </a>
									</div>
								</div>
							</div>
						</div>  
					</div>
				</div>









































 
<div class="row" id='mesinv'>
    <div class="col-xxl-12 col-xl-12 col-lg-12">
        <div class="card rounded-4">
            <div class="box-header d-flex b-0 justify-content-between align-items-center">
                <h4 class="box-title">Nos Clients</h4>
                
            </div>

            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <tbody>
                            <?php 
                            $stmtclient = $pdo->prepare("SELECT * FROM is_users ORDER BY cod_user DESC");
                            $stmtclient->execute();

                            if ($stmtclient->rowCount() > 0) {
                                while ($row_client = $stmtclient->fetch(PDO::FETCH_ASSOC)) { 

                             ?> 
                                    <tr>
                                        <td class="pt-0 px-0 b-0">
                                            <a class="d-block fw-500 fs-14" href="#"><?php echo htmlspecialchars(ucfirst($row_client['noms'])); ?></a>
                                            <!-- <em class="text-fade"><?php //echo $row_conf['phone']; ?> / <?php //echo $row_conf['email']; ?> </em><br> --> 
                                            <p><em style="color:#888;"><?php echo $row_client['email'].'<br> '.$row_client['phone']; ?> </em></p>

                                        <?php if ((string) ($row_client['type_user'] ?? '') === '2') { ?>
                                        <form action="" method="post" class="mt-10">
                                          <input type="hidden" name="impersonate_user_id" value="<?php echo (int) $row_client['cod_user']; ?>">
                                          <button type="submit" class="btn btn-sm btn-primary">
                                            Se connecter comme ce client
                                          </button>
                                        </form>
                                        <?php } ?>
                                        </td>  
                                    </tr>

                            <?php 

                                }

                            } else {
                                echo '<tr><td colspan="3" class="text-left" style="font-style:italic;">Aucune confirmation trouvée</td></tr>';
                            }

                            ?>

                        </tbody>
                    </table>
                </div>
            </div>	
        </div>
    </div>

    <!-- Fenêtre modale -->
    <div id="shareModal" class="modalinv" style="display: none;">
        <div class="modal-content">
			<form action="" method="post">
<?php 
require_once '../../twilio-php-main/src/Twilio/autoload.php'; 
use Twilio\Rest\Client;

require_once __DIR__ . '/whatsapp_template_sender.php';

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
      $shareErrorMessage = 'L’envoi WhatsApp via template approuve a echoue.';
    }
  }

  if ($shareSuccessMessage !== null) {
    echo '<script>
    Swal.fire({
      title: "Notification !",
      text: ' . json_encode($shareSuccessMessage) . ',
      icon: "success",
      confirmButtonText: "OK"
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = "index.php?page=mb_accueil";
      }
    });
    </script>';
  }

  if ($shareErrorMessage !== null) {
    echo '<script>
    Swal.fire({
      title: "Échec de l’envoi",
      text: ' . json_encode($shareErrorMessage) . ',
      icon: "error",
      confirmButtonText: "OK"
    });
    </script>';
  }
}
?>
            <div class="form-group"> 
                <span class="close" onclick="closeModal()" style="cursor: pointer; float: right; font-size: 24px;">&times;</span><br>
                <h4 id="modalTitle">Partager avec </h4> <br><br>
                <input type="text" required pattern="^\+\d{1,3}\d{9,}$" 
				title="Veuillez entrer un numéro au format international (ex: +243810678785)" id="whatsappNumber" name="phoneinv" class="input-group-text bg-transparent" style="border-radius:7px 7px 0px 0px;height:45px;width:100%;" placeholder="Numéro WhatsApp" />
                <input type="hidden" id="inviteName" name="inviteName" />
                <input type="hidden" id="inviteId" name="inviteId" />
                <input type="hidden" id="pdfLink" name="pdf_link" />
                <button class="btn btn-primary" type="submit" name="submitwhat" style="border-radius:0px 0px 7px 7px;width:100%;">Envoyer avec le template WhatsApp approuvé</button>
            </div>
			<br>
            <a href="#" id="downloadLink">Télécharger le PDF</a>
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
            document.getElementById('modalTitle').innerText = 'Partager avec ' + inviteName;
            document.getElementById('shareModal').style.display = 'flex';
            const linkpdf = "../pages/invitation_elect.php?cod=" + inviteId + "&event=<?php echo $codevent; ?>";
            document.getElementById('downloadLink').setAttribute('href', linkpdf);
            document.getElementById('downloadLink').setAttribute('target', "_blank");
          document.getElementById('inviteName').value = inviteName;
          document.getElementById('inviteId').value = inviteId;
          document.getElementById('pdfLink').value = linkpdf;
        }

        function closeModal() {
            document.getElementById('shareModal').style.display = 'none';
        }
    </script>
</div>















 






























  
					</div>

					
				</div> 
			</section>
			<!-- /.content -->
		</div>
  <!-- /.content-wrapper -->
	<?php include('footer.php')?>
  <!-- Side panel --> 
  <!-- quick_user_toggle -->
  <div class="modal modal-right fade" id="quick_user_toggle" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content slim-scroll3">
		  <div class="modal-body p-30 bg-white">
			<div class="d-flex align-items-center justify-content-between pb-30">
				<h4 class="m-0">User Profile
				<small class="text-fade fs-12 ms-5">12 messages</small></h4>
				<a href="#" class="btn btn-icon btn-danger-light btn-sm no-shadow" data-bs-dismiss="modal">
					<span class="fa fa-close"></span>
				</a>
			</div>
            <div>
                <div class="d-flex flex-row">
                    <div class=""><img src="html/images/avatar/avatar-2.png" alt="user" class="rounded bg-danger-light w-150" width="100"></div>
                    <div class="ps-20">
                        <h5 class="mb-0">Nil Yeager</h5>
                        <p class="my-5 text-fade">Web Designer</p>
                        <a href="mailto:dummy@gmail.com"><span class="icon-Mail-notification me-5 text-success"><span class="path1"></span><span class="path2"></span></span> dummy@gmail.com</a>
                        <button class="btn btn-success-light btn-sm mt-5"><i class="ti-plus"></i> Follow</button>
                    </div>
                </div>
			</div>
              <div class="dropdown-divider my-30"></div>
              <div>
                <div class="d-flex align-items-center mb-30">
                    <div class="me-15 bg-primary-light h-50 w-50 l-h-60 rounded text-center">
                          <span class="icon-Library fs-24"><span class="path1"></span><span class="path2"></span></span>
                    </div>
                    <div class="d-flex flex-column fw-500">
                        <a href="extra_profile.html" class="text-dark hover-primary mb-1 fs-16">My Profile</a>
                        <span class="text-fade">Account settings and more</span>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-30">
                    <div class="me-15 bg-danger-light h-50 w-50 l-h-60 rounded text-center">
                        <span class="icon-Write fs-24"><span class="path1"></span><span class="path2"></span></span>
                    </div>
                    <div class="d-flex flex-column fw-500">
                        <a href="mailbox.html" class="text-dark hover-danger mb-1 fs-16">My Messages</a>
                        <span class="text-fade">Inbox and tasks</span>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-30">
                    <div class="me-15 bg-success-light h-50 w-50 l-h-60 rounded text-center">
                        <span class="icon-Group-chat fs-24"><span class="path1"></span><span class="path2"></span></span>
                    </div>
                    <div class="d-flex flex-column fw-500">
                        <a href="setting.html" class="text-dark hover-success mb-1 fs-16">Settings</a>
                        <span class="text-fade">Accout Settings</span>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-30">
                    <div class="me-15 bg-info-light h-50 w-50 l-h-60 rounded text-center">
                        <span class="icon-Attachment1 fs-24"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>
                    </div>
                    <div class="d-flex flex-column fw-500">
                        <a href="extra_taskboard.html" class="text-dark hover-info mb-1 fs-16">Project</a>
                        <span class="text-fade">latest tasks and projects</span>
                    </div>
                </div>
              </div>
              <div class="dropdown-divider my-30"></div>
              <div>
                <div class="media-list">
                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">10:10</h4>
                      <div class="media-body ps-15 bs-5 rounded border-primary">
                        <p>Morbi quis ex eu arcu auctor sagittis.</p>
                        <span class="text-fade">by Johne</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">08:40</h4>
                      <div class="media-body ps-15 bs-5 rounded border-success">
                        <p>Proin iaculis eros non odio ornare efficitur.</p>
                        <span class="text-fade">by Amla</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">07:10</h4>
                      <div class="media-body ps-15 bs-5 rounded border-info">
                        <p>In mattis mi ut posuere consectetur.</p>
                        <span class="text-fade">by Josef</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">01:15</h4>
                      <div class="media-body ps-15 bs-5 rounded border-danger">
                        <p>Morbi quis ex eu arcu auctor sagittis.</p>
                        <span class="text-fade">by Rima</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">23:12</h4>
                      <div class="media-body ps-15 bs-5 rounded border-warning">
                        <p>Morbi quis ex eu arcu auctor sagittis.</p>
                        <span class="text-fade">by Alaxa</span>
                      </div>
                    </a>
                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">10:10</h4>
                      <div class="media-body ps-15 bs-5 rounded border-primary">
                        <p>Morbi quis ex eu arcu auctor sagittis.</p>
                        <span class="text-fade">by Johne</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">08:40</h4>
                      <div class="media-body ps-15 bs-5 rounded border-success">
                        <p>Proin iaculis eros non odio ornare efficitur.</p>
                        <span class="text-fade">by Amla</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">07:10</h4>
                      <div class="media-body ps-15 bs-5 rounded border-info">
                        <p>In mattis mi ut posuere consectetur.</p>
                        <span class="text-fade">by Josef</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">01:15</h4>
                      <div class="media-body ps-15 bs-5 rounded border-danger">
                        <p>Morbi quis ex eu arcu auctor sagittis.</p>
                        <span class="text-fade">by Rima</span>
                      </div>
                    </a>

                    <a class="media media-single px-0" href="#">
                      <h4 class="w-50 text-gray fw-500">23:12</h4>
                      <div class="media-body ps-15 bs-5 rounded border-warning">
                        <p>Morbi quis ex eu arcu auctor sagittis.</p>
                        <span class="text-fade">by Alaxa</span>
                      </div>
                    </a>
                  </div>
            </div>
		  </div>
		</div>
	  </div>
  </div>
  <!-- /quick_user_toggle --> 
    

  <!-- Control Sidebar -->
  <aside class="control-sidebar">
	  
	<div class="rpanel-title"><span class="pull-right btn btn-circle btn-danger" data-toggle="control-sidebar"><i class="ion ion-close text-white" ></i></span> </div>  <!-- Create the tabs -->
    <ul class="nav nav-tabs control-sidebar-tabs">
      <li class="nav-item"><a href="#control-sidebar-home-tab" data-bs-toggle="tab" ><i class="mdi mdi-message-text"></i></a></li>
      <li class="nav-item"><a href="#control-sidebar-settings-tab" data-bs-toggle="tab"><i class="mdi mdi-playlist-check"></i></a></li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
      <!-- Home tab content -->
      <div class="tab-pane" id="control-sidebar-home-tab">
          <div class="flexbox">
			<a href="javascript:void(0)" class="text-grey">
				<i class="ti-more"></i>
			</a>	
			<p>Users</p>
			<a href="javascript:void(0)" class="text-end text-grey"><i class="ti-plus"></i></a>
		  </div>
		  <div class="lookup lookup-sm lookup-right d-none d-lg-block">
			<input type="text" name="s" placeholder="Search" class="w-p100">
		  </div>
          <div class="media-list media-list-hover mt-20">
			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-success" href="#">
				<img src="html/images/avatar/1.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Tyler</strong></a>
				</p>
				<p>Praesent tristique diam...</p>
				  <span>Just now</span>
			  </div>
			</div>

			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-danger" href="#">
				<img src="html/images/avatar/2.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Luke</strong></a>
				</p>
				<p>Cras tempor diam ...</p>
				  <span>33 min ago</span>
			  </div>
			</div>

			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-warning" href="#">
				<img src="html/images/avatar/3.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Evan</strong></a>
				</p>
				<p>In posuere tortor vel...</p>
				  <span>42 min ago</span>
			  </div>
			</div>

			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-primary" href="#">
				<img src="html/images/avatar/4.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Evan</strong></a>
				</p>
				<p>In posuere tortor vel...</p>
				  <span>42 min ago</span>
			  </div>
			</div>			
			
			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-success" href="#">
				<img src="html/images/avatar/1.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Tyler</strong></a>
				</p>
				<p>Praesent tristique diam...</p>
				  <span>Just now</span>
			  </div>
			</div>

			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-danger" href="#">
				<img src="html/images/avatar/2.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Luke</strong></a>
				</p>
				<p>Cras tempor diam ...</p>
				  <span>33 min ago</span>
			  </div>
			</div>

			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-warning" href="#">
				<img src="html/images/avatar/3.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Evan</strong></a>
				</p>
				<p>In posuere tortor vel...</p>
				  <span>42 min ago</span>
			  </div>
			</div>

			<div class="media py-10 px-0">
			  <a class="avatar avatar-lg status-primary" href="#">
				<img src="html/images/avatar/4.jpg" alt="...">
			  </a>
			  <div class="media-body">
				<p class="fs-16">
				  <a class="hover-primary" href="#"><strong>Evan</strong></a>
				</p>
				<p>In posuere tortor vel...</p>
				  <span>42 min ago</span>
			  </div>
			</div>
			  
		  </div>

      </div>
      <!-- /.tab-pane -->
      <!-- Settings tab content -->
      <div class="tab-pane" id="control-sidebar-settings-tab">
          <div class="flexbox">
			<a href="javascript:void(0)" class="text-grey">
				<i class="ti-more"></i>
			</a>	
			<p>Todo List</p>
			<a href="javascript:void(0)" class="text-end text-grey"><i class="ti-plus"></i></a>
		  </div>
        <ul class="todo-list mt-20">
			<li class="py-15 px-5 by-1">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_1" class="filled-in">
			  <label for="basic_checkbox_1" class="mb-0 h-15"></label>
			  <!-- todo text -->
			  <span class="text-line">Nulla vitae purus</span>
			  <!-- Emphasis label -->
			  <small class="badge bg-danger"><i class="fa fa-clock-o"></i> 2 mins</small>
			  <!-- General tools such as edit or delete-->
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_2" class="filled-in">
			  <label for="basic_checkbox_2" class="mb-0 h-15"></label>
			  <span class="text-line">Phasellus interdum</span>
			  <small class="badge bg-info"><i class="fa fa-clock-o"></i> 4 hours</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5 by-1">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_3" class="filled-in">
			  <label for="basic_checkbox_3" class="mb-0 h-15"></label>
			  <span class="text-line">Quisque sodales</span>
			  <small class="badge bg-warning"><i class="fa fa-clock-o"></i> 1 day</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_4" class="filled-in">
			  <label for="basic_checkbox_4" class="mb-0 h-15"></label>
			  <span class="text-line">Proin nec mi porta</span>
			  <small class="badge bg-success"><i class="fa fa-clock-o"></i> 3 days</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5 by-1">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_5" class="filled-in">
			  <label for="basic_checkbox_5" class="mb-0 h-15"></label>
			  <span class="text-line">Maecenas scelerisque</span>
			  <small class="badge bg-primary"><i class="fa fa-clock-o"></i> 1 week</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_6" class="filled-in">
			  <label for="basic_checkbox_6" class="mb-0 h-15"></label>
			  <span class="text-line">Vivamus nec orci</span>
			  <small class="badge bg-info"><i class="fa fa-clock-o"></i> 1 month</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5 by-1">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_7" class="filled-in">
			  <label for="basic_checkbox_7" class="mb-0 h-15"></label>
			  <!-- todo text -->
			  <span class="text-line">Nulla vitae purus</span>
			  <!-- Emphasis label -->
			  <small class="badge bg-danger"><i class="fa fa-clock-o"></i> 2 mins</small>
			  <!-- General tools such as edit or delete-->
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_8" class="filled-in">
			  <label for="basic_checkbox_8" class="mb-0 h-15"></label>
			  <span class="text-line">Phasellus interdum</span>
			  <small class="badge bg-info"><i class="fa fa-clock-o"></i> 4 hours</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5 by-1">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_9" class="filled-in">
			  <label for="basic_checkbox_9" class="mb-0 h-15"></label>
			  <span class="text-line">Quisque sodales</span>
			  <small class="badge bg-warning"><i class="fa fa-clock-o"></i> 1 day</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
			<li class="py-15 px-5">
			  <!-- checkbox -->
			  <input type="checkbox" id="basic_checkbox_10" class="filled-in">
			  <label for="basic_checkbox_10" class="mb-0 h-15"></label>
			  <span class="text-line">Proin nec mi porta</span>
			  <small class="badge bg-success"><i class="fa fa-clock-o"></i> 3 days</small>
			  <div class="tools">
				<i class="fa fa-edit"></i>
				<i class="fa fa-trash-o"></i>
			  </div>
			</li>
		  </ul>
      </div>
      <!-- /.tab-pane -->
    </div>
  </aside>
  <!-- /.control-sidebar -->
  
  <!-- Add the sidebar's background. This div must be placed immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>     
  

  
  
</div>
<!-- ./wrapper -->
	
	
		
	<div id="chat-box-body">
		<div id="chat-circle" class="waves-effect waves-circle btn btn-circle btn-sm btn-warning l-h-50">
            <div id="chat-overlay"></div>
            <span class="icon-Group-chat fs-18"><span class="path1"></span><span class="path2"></span></span>
		</div>

		<div class="chat-box">
            <div class="chat-box-header p-15 d-flex justify-content-between align-items-center">
                <div class="btn-group">
                  <button class="waves-effect waves-circle btn btn-circle btn-primary-light h-40 w-40 rounded-circle l-h-45" type="button" data-bs-toggle="dropdown">
                      <span class="icon-Add-user fs-22"><span class="path1"></span><span class="path2"></span></span>
                  </button>
                  <div class="dropdown-menu min-w-200">
                    <a class="dropdown-item fs-16" href="#">
                        <span class="icon-Color me-15"></span>
                        New Group</a>
                    <a class="dropdown-item fs-16" href="#">
                        <span class="icon-Clipboard me-15"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>
                        Contacts</a>
                    <a class="dropdown-item fs-16" href="#">
                        <span class="icon-Group me-15"><span class="path1"></span><span class="path2"></span></span>
                        Groups</a>
                    <a class="dropdown-item fs-16" href="#">
                        <span class="icon-Active-call me-15"><span class="path1"></span><span class="path2"></span></span>
                        Calls</a>
                    <a class="dropdown-item fs-16" href="#">
                        <span class="icon-Settings1 me-15"><span class="path1"></span><span class="path2"></span></span>
                        Settings</a>
                    <div class="dropdown-divider"></div>
					<a class="dropdown-item fs-16" href="#">
                        <span class="icon-Question-circle me-15"><span class="path1"></span><span class="path2"></span></span>
                        Help</a>
					<a class="dropdown-item fs-16" href="#">
                        <span class="icon-Notifications me-15"><span class="path1"></span><span class="path2"></span></span> 
                        Privacy</a>
                  </div>
                </div>
                <div class="text-center flex-grow-1">
                    <div class="text-dark fs-18">Support</div>
                    <div>
                        <span class="badge badge-sm badge-dot badge-primary"></span>
                        <span class="text-muted fs-12">Active</span>
                    </div>
                </div>
                <div class="chat-box-toggle">
                    <button id="chat-box-toggle" class="waves-effect waves-circle btn btn-circle btn-danger-light h-40 w-40 rounded-circle l-h-45" type="button">
                      <span class="icon-Close fs-22"><span class="path1"></span><span class="path2"></span></span>
                    </button>                    
                </div>
            </div>
            <div class="chat-box-body">
                
				<?php // include ('chatsupport.php')?>

            </div>
            <div class="chat-input">      
                <form>
                    <input type="text" id="chat-input" placeholder="Besoin d'aide ?"/>
                    <button type="submit" class="chat-submit" id="chat-submit">
                        <span class="icon-Send fs-22"></span>
                    </button>
                </form>      
            </div>
		</div>
	</div>
	
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
	  