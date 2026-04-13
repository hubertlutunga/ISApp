<div class="wrapper"> 
	<?php include('header.php');?>
	<?php
	$menuId = isset($_GET['cod']) ? (int) $_GET['cod'] : 0;
	$menu = $menuId > 0 ? MenuCatalogService::findById($pdo, $menuId) : null;
	$categories = MenuCatalogService::listCategories($pdo);

	if (!$menu) {
		echo '<script>window.location.href = "index.php?page=addmenu";</script>';
		return;
	}

	if (isset($_POST['submitmenu'])) {
		$catmenu = (string) (@$_POST['catmenu']);
		$nom = (string) (@$_POST['nom']);
		$descMenu = (string) (@$_POST['desc_menu']);

		if ($catmenu === '' || $nom === '') {
			$message = "Le champ catégorie et nom sont obligatoires";
			$messageType = 'error';
		} else {
			MenuCatalogService::update($pdo, $menuId, (int) ($menu['cod_event'] ?? 0), $catmenu, $nom, $descMenu);
			$menu = MenuCatalogService::findById($pdo, $menuId);
			$message = 'Menu modifié avec succès.';
			$messageType = 'success';
		}
	}
	?>
	<style>
		.mb-action-page{ padding:24px 0 42px; }
		.mb-action-hero{ padding:28px 30px; border-radius:30px; background:linear-gradient(135deg,#1f2937 0%,#0f172a 55%,#d97706 100%); box-shadow:0 24px 50px rgba(15,23,42,.18); color:#f8fafc; margin-bottom:26px; }
		.mb-action-kicker{ display:inline-flex; align-items:center; gap:8px; padding:7px 12px; border-radius:999px; background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.16); font-size:12px; font-weight:800; text-transform:uppercase; }
		.mb-action-title{ margin:16px 0 10px; font-size:34px; line-height:1.05; font-weight:800; color:#fff; }
		.mb-action-subtitle{ margin:0; max-width:700px; color:rgba(226,232,240,.88); font-size:15px; line-height:1.7; }
		.mb-action-card{ border:0; border-radius:28px; overflow:hidden; background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); box-shadow:0 22px 48px rgba(15,23,42,.10); }
		.mb-action-card .content-top-agile{ padding:26px 28px 10px !important; }
		.mb-action-card .p-40{ padding:18px 28px 30px !important; }
		.mb-action-heading{ margin:0; font-size:28px; font-weight:800; color:#0f172a; }
		.mb-action-copy{ margin:8px 0 0; font-size:14px; color:#64748b; }
		.mb-action-card .form-group{ margin-bottom:16px; }
		.mb-action-card .input-group{ border:1px solid #fde7c7; border-radius:18px; background:#fffaf2; overflow:hidden; }
		.mb-action-card .input-group-text,
		.mb-action-card .form-control,
		.mb-action-card textarea.form-control,
		.mb-action-card select.form-control{ border:0 !important; background:transparent !important; box-shadow:none !important; min-height:56px; }
		.mb-action-card .input-group-text{ color:#d97706; padding-left:16px; padding-right:8px; }
		.mb-action-card textarea.form-control{ padding-top:16px; padding-bottom:16px; }
		.mb-action-submit{ display:inline-flex; align-items:center; justify-content:center; min-height:58px; border:0; border-radius:18px; background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%); box-shadow:0 18px 34px rgba(217,119,6,.20); font-size:15px; font-weight:800; }
		@media only screen and (max-width: 769px) {
			.mb-action-page{ padding:18px 0 34px; }
			.mb-action-hero{ padding:22px 20px; border-radius:24px; }
			.mb-action-title{ font-size:28px; }
			.mb-action-card .content-top-agile,
			.mb-action-card .p-40{ padding-left:20px !important; padding-right:20px !important; }
		}
	</style>

	<div class="content-wrapper">
		<div class="container-full">
			<div class="container h-p100 mb-action-page">
				<div class="mb-action-hero">
					<span class="mb-action-kicker"><i class="mdi mdi-food-edit-outline"></i> Modification du menu</span>
					<h1 class="mb-action-title">Affinez votre menu sans casser sa coherence</h1>
					<p class="mb-action-subtitle">Mettez a jour la categorie, le nom ou la description d'un element en gardant une interface claire et homogene.</p>
				</div>
				<div class="row align-items-center justify-content-md-center h-p100">
					<div class="col-12">
						<div class="row justify-content-center g-4">
							<div class="col-xl-6 col-lg-7 col-12 boxcontent">
								<div class="bg-white rounded10 shadow-lg mb-action-card">
									<div class="content-top-agile p-20 pb-0"> 
										<p class="mb-0 text-fade">Modifier <?php echo htmlspecialchars((string) ($menu['nom'] ?? '')); ?></p>
										<h2 class="mb-action-heading">Element du menu</h2>
										<p class="mb-action-copy">Ajustez le contenu de cet element sans perdre la logique de categorie ni la qualite de presentation.</p>

										<?php if (isset($message, $messageType)) { ?>
											<div class="<?php echo $messageType; ?>" align="left" style="color:<?php echo $messageType === 'success' ? 'green' : 'red'; ?>;font-weight:bold;text-align:center;"><?php echo htmlspecialchars($message); ?></div>
										<?php } ?>
									</div>

									<div class="p-40">
										<form action="" method="post">
											<div class="form-group">
												<div class="input-group mb-3">
													<span class="input-group-text bg-transparent"><i class="fas fa-chair"></i></span>
													<select class="form-control ps-15 bg-transparent" name="catmenu">
														<option style="color:#eee;" value="">Catégorie</option>
														<?php foreach ($categories as $category) { ?>
															<option value="<?php echo $category['cod_cm']; ?>" <?php echo (string) ($menu['cat_menu'] ?? '') === (string) $category['cod_cm'] ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) $category['nom']); ?></option>
														<?php } ?>
													</select>
												</div>
											</div>

											<div class="form-group">
												<div class="input-group mb-3">
													<span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
													<input type="text" class="form-control ps-15 bg-transparent" name="nom" placeholder="Nom" value="<?php echo htmlspecialchars((string) ($menu['nom'] ?? '')); ?>">
												</div>
											</div>

											<div class="form-group">
												<div class="input-group mb-3">
													<span class="input-group-text bg-transparent"><i class="fas fa-align-center"></i></span>
													<textarea name="desc_menu" class="form-control ps-15 bg-transparent" placeholder="Description"><?php echo htmlspecialchars((string) ($menu['desc_menu'] ?? '')); ?></textarea>
												</div>
											</div>

											<div class="row">
												<div class="col-12 text-center">
													<button type="submit" name="submitmenu" class="btn btn-primary w-p100 mt-10 mb-action-submit">Enregistrer les modifications</button>
												</div>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php include('footer.php')?>