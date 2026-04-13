<!-- Fenêtre modale pour le Formulaire 1 -->
<div id="shareModal4" class="modalinv" style="display: none;">
    <div class="modal-content">
        <div class="modal-header" style="padding:0;">
            <h4 id="modalTitle4">Effectuer une sortie</h4>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>

        <br>
        <div id="status" style="margin-top: 10px;"></div>

        <form id="sortieForm" action="" method="post" enctype="multipart/form-data">

            <!-- Sélection du motif -->
            <div class="form-group mb-3">
                <label for="motif" class="fw-bold">Motif</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="fas fa-list"></i></span>
                    <select name="motif" id="motif" class="form-control bg-transparent">
                        <option value="">— Sélectionner —</option>
                        <option value="CAT-01">Frais administratifs</option>
                        <option value="CAT-02">Logistique</option>
                        <option value="CAT-03">Communication</option>
                    </select>
                </div>
            </div>

            <!-- Sous motif -->
            <div class="form-group mb-3">
                <label for="sousmotif" class="fw-bold">Sous motif</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="fas fa-list-ul"></i></span>
                    <select name="sousmotif" id="sousmotif" class="form-control bg-transparent">
                        <option value="">— Sélectionner —</option>
                    </select>
                </div>
            </div>

            <!-- Montant -->
            <div class="form-group mb-3">
                <label for="montant" class="fw-bold">Montant ($)</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="fas fa-dollar-sign"></i></span>
                    <input type="number" name="montant" id="montant" class="form-control bg-transparent" placeholder="Ex: 150.00" step="0.01" min="0">
                </div>
            </div>

            <!-- Justification -->
            <div class="form-group mb-3">
                <label for="justification" class="fw-bold">Justification</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="fas fa-edit"></i></span>
                    <textarea name="justification" id="justification" class="form-control ps-15 bg-transparent" rows="4" placeholder="Détaillez la raison de la sortie..."></textarea>
                </div>
            </div>

            <!-- Bouton -->
            <div class="row">
                <div class="col-12 text-center">
                    <button type="button" id="btnSaveSortie" class="btn btn-primary w-p100 mt-10">Enregistrer</button>
                </div>
            </div>

        </form>
    </div>
</div>


<script>
 
    function openModal3(codEvent) {
        document.getElementById('modalTitle4').innerText = 'Evénement N° ' + codEvent; 
        document.getElementById('shareModal4').style.display = 'flex';
    }

    function closeModal3() {
        document.getElementById('shareModal4').style.display = 'none';
    }


</script>