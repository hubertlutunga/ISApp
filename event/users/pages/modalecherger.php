    <style>
        #shareModal2 {
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        #shareModal2 .modal-content {
            width: min(680px, 100%);
            max-height: 88vh;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 18px;
            scrollbar-width: thin;
        }

        .qr-choice-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .qr-choice-option {
            position: relative;
            margin: 0;
            cursor: pointer;
        }

        .qr-choice-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .qr-choice-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 110px;
            padding: 10px 18px;
            border: 1px solid #d7dbe7;
            border-radius: 999px;
            background: linear-gradient(180deg, #ffffff 0%, #f5f7fb 100%);
            color: #42526e;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 6px 16px rgba(31, 45, 61, 0.08);
        }

        .qr-choice-option input[type="radio"]:checked + .qr-choice-pill {
            background: linear-gradient(180deg, #1e88e5 0%, #1565c0 100%);
            border-color: #1565c0;
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(21, 101, 192, 0.28);
            transform: translateY(-1px);
        }

        .qr-choice-option input[type="radio"]:focus + .qr-choice-pill {
            outline: 2px solid rgba(30, 136, 229, 0.25);
            outline-offset: 2px;
        }

        .qr-choice-label {
            display: block;
            margin-bottom: 10px;
            color: #24324a;
            font-weight: 700;
            text-align: center;
            letter-spacing: 0.01em;
        }
    </style>

    <!-- Fenêtre modale 2 -->
    <div id="shareModal2" class="modalinv" style="display: none;">
    <div class="modal-content">
        <div class="modal-header" style="padding:0;">
            <h4 id="modalTitle2">Evénement N°</h4>
            <span class="close" onclick="closeModal2()">&times;</span>
        </div>
        <br>

        <div id="progressContainer2" style="width: 100%; background: #f3f3f3; border: 1px solid #ccc; display: none; margin-top: 10px; margin-bottom: 50px;">
            <div id="progressBar2" style="width: 0; height: 30px; background: #4caf50;"></div>
            <span id="progressPercentage2" style="display: block; text-align: center; margin-top: 5px;">Téléchargement des photos : 0%</span>
        </div>
        <div id="status2" style="margin-top: 10px;"></div>


 
        <form id="eventForm2" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="codevent" id="codevent2"> <!-- Champ caché pour codEvent -->

            <div class="form-group">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-transparent"><i class="fas fa-file"></i></span>
                    <input type="file" name="fichers[]" class="form-control ps-15 bg-transparent" placeholder="Invitation" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <small id="currentInvitation2" class="text-muted">Aucun fichier actuel</small>
            </div>

            <div class="form-group">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-transparent"><i class="fas fa-text-width"></i></span>
                    <input type="number" name="ajustenom" class="form-control ps-15 bg-transparent" placeholder="Ajustement nom" step="1">
                </div>
            </div>

            <div class="form-group">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-transparent"><i class="fas fa-font"></i></span>
                    <input type="number" name="taillenominv" class="form-control ps-15 bg-transparent" placeholder="Font size nom" min="1" step="1">
                </div>
            </div>

            <div class="form-group">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-transparent"><i class="fas fa-align-left"></i></span>
                    <select name="alignnominv" class="form-control ps-15 bg-transparent">
                        <option value="" disabled selected>Alignement nom</option>
                        <option value="left">Left</option>
                        <option value="center">Center</option>
                        <option value="right">Right</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-transparent"><i class="fas fa-file"></i></span>
                    <input type="number" name="pagenom" class="form-control ps-15 bg-transparent" placeholder="Page numéro" min="0" step="1">
                </div>
            </div>

            <div class="form-group">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-transparent"><i class="fas fa-file-alt"></i></span>
                    <input type="number" name="pagebouton" class="form-control ps-15 bg-transparent" placeholder="Page boutons" min="0" step="1">
                </div>
            </div>

            <div class="form-group">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-transparent"><i class="ion ion-paintbucket"></i></span>
                    <input type="text" name="colornom" class="form-control ps-15 bg-transparent" placeholder="Couleur du texte">
                </div>
            </div>

            <div class="form-group">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-transparent"><i class="fas fa-indent"></i></span>
                    <input type="number" name="bordgauchenominv" class="form-control ps-15 bg-transparent" placeholder="Marge page" min="0" step="1">
                </div>
            </div>

            <div class="form-group">
                <label class="qr-choice-label">QrCode</label>
                <div class="qr-choice-group">
                    <label class="qr-choice-option">
                        <input type="radio" name="qrcode" value="oui" onchange="toggleQrCodeFields2(this.value)">
                        <span class="qr-choice-pill">Oui</span>
                    </label>
                    <label class="qr-choice-option">
                        <input type="radio" name="qrcode" value="non" checked onchange="toggleQrCodeFields2(this.value)">
                        <span class="qr-choice-pill">Non</span>
                    </label>
                </div>
            </div>

            <div id="qrCodeFields2" style="display: none;">
                <div class="form-group">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-transparent"><i class="fas fa-file"></i></span>
                        <input type="number" name="pageqr" class="form-control ps-15 bg-transparent" placeholder="Page QrCode" min="0" step="1">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-transparent"><i class="fas fa-arrows-alt-v"></i></span>
                        <input type="number" name="hautqr" class="form-control ps-15 bg-transparent" placeholder="Marge Haut" min="0" step="1">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-transparent"><i class="fas fa-arrows-alt-h"></i></span>
                        <input type="number" name="gaucheqr" class="form-control ps-15 bg-transparent" placeholder="Marge Gauche" min="0" step="1">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-transparent"><i class="fas fa-qrcode"></i></span>
                        <input type="number" name="tailleqr" class="form-control ps-15 bg-transparent" placeholder="Taille Qrcode" min="1" step="1">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-transparent"><i class="fas fa-language"></i></span>
                    <select name="lang" class="form-control ps-15 bg-transparent">
                        <option value="fr" selected>fr</option>
                        <option value="eng">eng</option>
                    </select>
                </div>
            </div>
 

            <div class="row">
                <div class="col-12 text-center">
                    <button type="submit" name="submitinvelect" class="btn btn-primary w-p100 mt-10">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>

 
