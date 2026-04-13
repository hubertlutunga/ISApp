<?php
// modalreponse.php
?>

<style>
.modalinv{position:fixed;inset:0;background:rgba(0,0,0,.5);display:none;justify-content:center;align-items:center;z-index:77000}
.modalinv.show{display:flex}
.modal-content{background:#fff;padding:20px;border-radius:5px;box-shadow:0 4px 10px rgba(0,0,0,.3);position:relative;width:50vw;max-width:50%;margin:auto}
.close{position:absolute;top:10px;right:15px;color:#aaa;font-size:24px;cursor:pointer}
.close:hover{color:#000}
@media(max-width:769px){.modal-content{width:95vw;max-width:95%}}
@media(min-width:770px) and (max-width:1024px){.modal-content{width:60vw;max-width:60%}}
</style>

<div id="shareModal" class="modalinv">
  <div class="modal-content">
    <span class="close" onclick="closeModal()" aria-label="Fermer">&times;</span>
    <h4 id="modalTitle"></h4><br>

    <!-- MODE INFO -->
    <div id="modalInfoContainer" style="display:none;">
      <div class="alert alert-info" role="alert" style="font-size:16px;">
        Votre réponse a déjà été enregistrée.
      </div>
      <div class="text-right">
        <button type="button" class="btn btn-primary" onclick="closeModal()">OK</button>
      </div>
    </div>

    <!-- MODE FORM -->
    <div id="modalFormContainer" style="display:none;">
      <form id="confirmForm" method="post"
        action="pages/enreg_conf.php?cod=<?php echo urlencode($_GET['cod'] ?? '') ?>&idinv=<?php echo urlencode($_GET['idinv'] ?? '') ?>&presence=<?php echo urlencode($_GET['presence'] ?? '') ?>"
        novalidate>

        <input type="hidden" name="submiconf" value="1">

        <!-- champs cachés -->
        <input type="hidden" id="inviteName" name="inviteName">
        <input type="hidden" id="cod_mar" name="cod_mar" value="<?php echo $_GET['cod'] ?? '' ?>">
        <input type="hidden" id="idinv" name="idinv" value="<?php echo $_GET['idinv'] ?? '' ?>">

        <div class="form-group">
          <label>Téléphone <span style="color:#e35d5d;">(Obligatoire)</span></label>
          <input type="text" name="phone" required class="form-control form-control-lg" placeholder="Ex: +243810678785">
        </div>

        <div class="form-group">
          <label>Email (Facultatif)</label>
          <input type="email" name="email" class="form-control form-control-lg" placeholder="Ex : contact@invitationspeciale.com">
        </div>

        <div class="form-group">
          <label>Message (Facultatif)</label>
          <textarea name="note" class="form-control form-control-lg" rows="3"></textarea>
        </div>

        <button class="btn btn-primary" type="button" id="btnSubConf"
                style="width:100%;"
                onclick="submitConfirmForm()">Confirmer</button>
      </form>
    </div>
  </div>
</div>

<script>
function openModal(inviteName, codevent, idinv, mode){

  // ✅ PAS de transformation HTML ici
  document.getElementById('modalTitle').innerText = inviteName || '';

  // champs cachés
  document.getElementById('inviteName').value = inviteName || '';
  document.getElementById('cod_mar').value = codevent || '';
  document.getElementById('idinv').value = idinv || '';

  const info  = document.getElementById('modalInfoContainer');
  const formc = document.getElementById('modalFormContainer');

  if ((mode || '').toLowerCase() === 'already') {
    info.style.display  = 'block';
    formc.style.display = 'none';
  } else {
    info.style.display  = 'none';
    formc.style.display = 'block';
  }

  document.getElementById('shareModal').classList.add('show');
}

function closeModal(){
  document.getElementById('shareModal').classList.remove('show');
}

function submitConfirmForm(){
  const form = document.getElementById('confirmForm');
  const btn  = document.getElementById('btnSubConf');

  btn.disabled = true;
  btn.textContent = 'Enregistrement...';

  HTMLFormElement.prototype.submit.call(form);
}
</script>