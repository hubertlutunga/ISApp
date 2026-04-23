<?php

$resetToken = trim((string) ($_GET['token'] ?? ''));
$resetFlash = null;
$resetFlashType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resetToken = trim((string) ($_POST['token'] ?? $resetToken));
    $resetResult = UserAccountService::resetPasswordWithToken(
        $pdo,
        $resetToken,
        $_POST['password'] ?? null,
        $_POST['confirm_password'] ?? null
    );

    $resetFlash = (string) ($resetResult['message'] ?? '');
    $resetFlashType = !empty($resetResult['success']) ? 'success' : 'error';

    if (!empty($resetResult['success'])) {
        echo '<script src="../sweet/sweetalert2.all.min.js"></script>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function () {
                Swal.fire({
                    title: "Mot de passe mis a jour",
                    text: "Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.",
                    icon: "success",
                    confirmButtonText: "Se connecter"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "index.php?page=login";
                    }
                });
            });
        </script>';
    } else {
        echo '<script src="../sweet/sweetalert2.all.min.js"></script>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function () {
                Swal.fire({
                    title: "Reinitialisation impossible",
                    text: ' . json_encode((string) ($resetResult['message'] ?? 'Le lien de reinitialisation est invalide ou a expire.')) . ',
                    icon: "error",
                    confirmButtonText: "Fermer"
                });
            });
        </script>';
    }
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
                            <p class="mb-0 text-fade">Reinitialisation</p>
                        </div>
                        <div class="p-40">
                            <?php if ($resetFlash !== null && $resetFlash !== '') { ?>
                            <div class="alert alert-<?php echo $resetFlashType === 'success' ? 'success' : 'danger'; ?> mb-20">
                                <?php echo htmlspecialchars($resetFlash, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <?php } ?>

                            <?php if ($resetToken === '') { ?>
                            <div class="alert alert-danger mb-20">Le lien de reinitialisation est invalide.</div>
                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    Swal.fire({
                                        title: "Lien invalide",
                                        text: "Le lien de reinitialisation est invalide ou incomplet.",
                                        icon: "error",
                                        confirmButtonText: "Retour"
                                    });
                                });
                            </script>
                            <?php } else { ?>
                            <form method="post">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($resetToken, ENT_QUOTES, 'UTF-8'); ?>">

                                <div class="form-group mb-20">
                                    <p class="text-fade">Choisissez un nouveau mot de passe pour votre compte client.</p>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-lock"></i></span>
                                        <input type="password" name="password" class="form-control ps-15 bg-transparent" placeholder="Nouveau mot de passe" minlength="8" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-lock"></i></span>
                                        <input type="password" name="confirm_password" class="form-control ps-15 bg-transparent" placeholder="Confirmer le mot de passe" minlength="8" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary w-p100 mt-10">Mettre à jour le mot de passe</button>
                                    </div>
                                </div>
                            </form>
                            <?php } ?>

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