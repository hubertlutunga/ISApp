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

                    <a class="dropdown-item fs-16" href="https://wa.me/243810678785">
                        <span class="fab fa-whatsapp me-15"><span class="path1"></span><span class="path2"></span></span>
                        WhatsApp</a>

                   <a class="dropdown-item fs-16" href="tel:243810678785">
                        <span class="icon-Active-call me-15"><span class="path1"></span><span class="path2"></span></span>
                        Appel</a> 

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









<style>
    .chat-input{
        height:70px !important;
        position: relative !important;
    }
    .chat-submit{
        position: absolute !important;
        top:15px !important ;
    }
    #besoin-input{
        width: 250px !important;
        border:none !important;
        padding: 10px 0px 0px 10px !important;
        height:70px !important;
        resize: none !important;
    }
    #besoin-input:focus {
    outline: none;
    box-shadow: none;
    border: none; /* désactiver le border */
}
</style>








<div class="chat-box-body" style="padding-bottom:10px;">

    <div class="chat-box-overlay" ></div>

    <div class="chat-logs" id="chat-logs" style="overflow-y: auto; max-height: 300px;">
 
        <?php 
            $stmtsup= $pdo->prepare("SELECT * FROM support WHERE cod_event = :cod_event ORDER BY cod_sup ASC");
            $stmtsup->execute([':cod_event' => $codevent]);

            if ($stmtsup->rowCount() > 0) {
                while ($row_sup = $stmtsup->fetch(PDO::FETCH_ASSOC)) { 



                $stmtrecus = $pdo->prepare("SELECT * FROM is_users WHERE cod_user = ?");
                $stmtrecus->execute([$row_sup['cod_cli']]);
                $recuser = $stmtrecus->fetch(PDO::FETCH_ASSOC) ?: [];
                $supportName = trim((string) ($recuser['noms'] ?? 'Utilisateur introuvable'));

                    if ($row_sup['type_user'] === '2') {
                        $div1 = "chat-msg self";
                        $div2 = "d-flex align-items-center justify-content-end";
                    } else {
                        $div1 = "chat-msg user";
                        $div2 = "d-flex align-items-center";
                    }

        ?>
                <div class="<?php echo $div1;?>">
                    <div class="<?php echo $div2;?>">


                        <?php if ($row_sup['type_user'] === '2') {?>

                            <div class="mx-10" style="text-align:right;">
                                <a href="#" class="text-dark hover-primary fw-bold"><?php echo htmlspecialchars($supportName, ENT_QUOTES, 'UTF-8');?></a>
                                <p class="text-muted fs-12 mb-0"><?php echo date('d M Y à H:i', strtotime($row_sup['date_env']))?></p>
                            </div>
                            <span class="msg-avatar">
                                <img src="../images/default.jpg" class="avatar avatar-lg" style="border-radius:50%;">
                            </span>

                        <?php } else { ?>

                            <span class="msg-avatar">
                                <img src="../images/default.jpg" class="avatar avatar-lg" style="border-radius:50%;">
                            </span>
                            <div class="mx-10">
                                <a href="#" class="text-dark hover-primary fw-bold"><?php echo htmlspecialchars($supportName, ENT_QUOTES, 'UTF-8');?></a>
                                <p class="text-muted fs-12 mb-0"><?php echo date('d M Y à H:i', strtotime($row_sup['date_env']))?></p>
                            </div>

                        <?php } ?>


                    </div>
                    <div class="cm-msg-text">
                        <?php echo $row_sup['besoin'];?> 
                    </div>
                </div>
        <?php
                }
            } else {
                echo '<em></em>';
            }		
        ?>    


    </div><!-- chat-log -->
</div>
 






 
            <div class="chat-input">      


                <form id="chat-form"> 
 
                    <input type="hidden" name="codevent" value="<?php echo $codevent; ?>">
 
<textarea name="besoin"  id="besoin-input" placeholder="Besoin d'aide ?" autocomplete="off"></textarea>

                    <button type="submit" name="submit" class="chat-submit">
                        <span class="icon-Send fs-22"></span>
                    </button>

                </form> 
                
                
            </div>





		</div>
	</div>















<script>
document.addEventListener("DOMContentLoaded", function () {
    const chatForm = document.getElementById('chat-form');
    const besoinInput = document.getElementById('besoin-input'); // ID unique ici
    const chatLogs = document.getElementById('chat-logs');

    // Scroll au chargement
    chatLogs.scrollTop = chatLogs.scrollHeight;

    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const besoin = besoinInput.value.trim();
        const codevent = "<?php echo $codevent; ?>";

        if (besoin === "") return;

        const formData = new URLSearchParams();
        formData.append('besoin', besoin);
        formData.append('codevent', codevent);

        fetch('pages/support_send.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const html = `
                    <div class="chat-msg self">
                        <div class="d-flex align-items-center justify-content-end">
                            <div class="mx-10">
                                <a href="#" class="text-dark hover-primary fw-bold">${data.noms}</a>
                                <p class="text-muted fs-12 mb-0">${data.date}</p>
                            </div>
                            <span class="msg-avatar">
                                <img src="../images/default.jpg" class="avatar avatar-lg" style="border-radius:50%;">
                            </span>
                        </div>
                        <div class="cm-msg-text">${data.besoin}</div>
                    </div>
                `;

                chatLogs.insertAdjacentHTML('beforeend', html);

                // ✅ Vider champ correctement
                besoinInput.value = '';
                besoinInput.blur();

                // ✅ Scroll en bas après DOM update
                setTimeout(() => {
                    chatLogs.scrollTop = chatLogs.scrollHeight;
                }, 100);
            } else {
                alert("Erreur serveur : " + (data.message || "Échec"));
            }
        })
        .catch(error => {
            console.error("Erreur AJAX :", error);
        });
    });
});
</script>

<script>
    // Scroll automatique quand le chatbox est ouvert
    document.getElementById('chat-circle').addEventListener('click', function () {
        const chatBox = document.querySelector('.chat-box');
        const chatLogs = document.getElementById('chat-logs');

        // Activer la boîte de chat si elle est cachée
        if (chatBox.style.display === 'none' || getComputedStyle(chatBox).display === 'none') {
            chatBox.style.display = 'block';
        }

        // Scroll tout en bas après l'ouverture
        setTimeout(() => {
            chatLogs.scrollTop = chatLogs.scrollHeight;
        }, 100); // petit délai pour garantir l'affichage
    });
</script>

<script>
document.getElementById('chat-box-toggle').addEventListener('click', function () {
    const chatBox = document.querySelector('.chat-box');
    chatBox.style.display = 'none';
});
</script>
