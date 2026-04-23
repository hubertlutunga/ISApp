<?php

$forgotFlash = null;
$forgotFlashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $forgotResult = UserAccountService::requestPasswordReset(
        $pdo,
        $mail,
        $isAppConfig,
        $_POST['email'] ?? null
    );

    $forgotFlash = (string) ($forgotResult['message'] ?? '');
    $forgotFlashType = !empty($forgotResult['success']) ? 'success' : 'error';
}
?>

<div class="container h-p100" style="margin-top:20px;">
    <div class="row align-items-center justify-content-md-center h-p100">
        <div class="col-12">
            <div class="row justify-content-center g-0">
                <div class="col-lg-5 col-md-6 col-12 boxcontent">
                    <div class="bg-white rounded10 shadow-lg">
                        <div class="content-top-agile p-20 pb-0">
                            <img src="../images/Logo_invitationSpeciale_1.png">
                            <p class="mb-0 text-fade">Mot de passe oublié</p>
                        </div>
                        <div class="p-40">
                            <?php if ($forgotFlash !== null && $forgotFlash !== '') { ?>
                            <div class="alert alert-<?php echo $forgotFlashType === 'success' ? 'success' : 'danger'; ?> mb-20">
                                <?php echo htmlspecialchars($forgotFlash, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <?php } ?>

                            <form method="post">
                                <div class="form-group mb-20">
                                    <p class="text-fade">Entrez l adresse email de votre compte. Si elle existe, nous vous enverrons un lien de reinitialisation.</p>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="email" class="form-control ps-15 bg-transparent" placeholder="Votre adresse email" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary w-p100 mt-10">Envoyer le lien</button>
                                    </div>
                                </div>
                            </form>

                            <div class="text-center">
                                <p class="mt-15 mb-0 text-fade"><a href="index.php?page=login" class="text-primary ms-5">Retour à la connexion</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="users/src/js/vendors.min.js"></script>
<script src="users/src/js/pages/chat-popup.js"></script>
<script src="users/assets/icons/feather-icons/feather.min.js"></script>