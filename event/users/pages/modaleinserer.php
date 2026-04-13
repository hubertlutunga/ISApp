<!-- Fenêtre modale pour le Formulaire 1 -->
<div id="shareModal3" class="modalinv" style="display: none;">
    <div class="modal-content">
        <div class="modal-header" style="padding:0;">
            <h4 id="modalTitle3">Evénement N°</h4>
            <span class="close" onclick="closeModal3()">&times;</span>
        </div>

        <br>
        <!-- barre de progression -->
        <div id="progressContainer3" style="width: 100%; background: #f3f3f3; border: 1px solid #ccc; display: none; margin-top: 10px; margin-bottom: 50px;">
            <div id="progressBar3" style="width: 0; height: 30px; background: #4caf50;"></div>
            <span id="progressPercentage3" style="display: block; text-align: center; margin-top: 5px;">Téléchargement des photos : 0%</span>
        </div>
        <div id="status3" style="margin-top: 10px;"></div>

        <form id="eventForm3" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="codevent" id="codevent3"> <!-- Champ caché pour codEvent -->

            <div class="form-group"> 
                <div class="input-group mb-3"> 
                    <span class="input-group-text bg-transparent"><i class="fas fa-file"></i></span>
                    <input type="file" multiple name="fichers" class="form-control ps-15 bg-transparent" placeholder="Fichiers">
                </div>
            </div>

            <div class="form-group">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-transparent"><i class="fas fa-edit"></i></span> 
                    <textarea name="observation" class="form-control ps-15 bg-transparent" rows='5' placeholder="Legender la photo"></textarea>
                </div>
            </div>

            <div class="row"> 
                <div class="col-12 text-center">
                    <button type="submit" name="submitinserer" class="btn btn-primary w-p100 mt-10">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>