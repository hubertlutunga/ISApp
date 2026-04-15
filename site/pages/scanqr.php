

<!-- Librairie QR Scanner -->
<script src="https://unpkg.com/html5-qrcode"></script>




<!-- ===== SCAN QR CODE ===== -->
<div id="qrWrap" style="margin:10px 0 10px 0;">
  <div style="display:flex; gap:10px; justify-content:center; align-items:center;">
    <button type="button" id="btnStartQr"
      style="padding:10px 16px;border-radius:10px;border:1px solid #ccc;cursor:pointer;">
      📷 Scanner le QrCode
    </button>

    <button type="button" id="btnStopQr"
      style="padding:10px 16px;border-radius:10px;border:1px solid #ccc;cursor:pointer;display:none;">
      ✖ Arrêter
    </button>
  </div>

  <div id="qrStatus" style="text-align:center;color:#bbb;margin-top:6px;">
    Caméra arrêtée
  </div>

  <div id="qr-reader"
       style="max-width:420px;margin:12px auto 0 auto;display:none;margin-bottom:25px;">
  </div>
</div>
<!-- ===== /SCAN QR CODE ===== -->







<script>
(function(){

  /* =========================
     QR CODE SCANNER
  ========================== */
  const $qrReader = document.getElementById('qr-reader');
  const $btnStart = document.getElementById('btnStartQr');
  const $btnStop  = document.getElementById('btnStopQr');
  const $status   = document.getElementById('qrStatus');

  let qr = null;
  let isRunning = false;

  async function startWithCamera(cameraConfig, config){
    await qr.start(
      cameraConfig,
      config,
      (decodedText) => {
        if (!isRunning) return;
        isRunning = false;

        const url = normalizeUrl(decodedText);
        if (!url) {
          setStatus("QR détecté mais lien invalide ❌");
          return;
        }

        setStatus("QR détecté ✅ Redirection…");

        stopQr();

        window.location.href = url;
      },
      () => {}
    );
  }

  function setStatus(txt){
    if ($status) $status.textContent = txt;
  }

  function normalizeUrl(text){
    const t = (text || "").trim();
    if (/^https?:\/\//i.test(t)) return t;
    if (/^[\w-]+\.[\w.-]+(\/.*)?$/i.test(t)) return "https://" + t;
    return null;
  }

  async function startQr(){
    try{
      if (!window.Html5Qrcode) {
        setStatus("Librairie QR non chargée ❌");
        return;
      }

      if (!qr) qr = new Html5Qrcode("qr-reader");

      $qrReader.style.display = "block";
      $btnStart.style.display = "none";
      $btnStop.style.display  = "inline-block";
      setStatus("Ouverture caméra...");

      const config = { fps: 10, qrbox: { width: 260, height: 260 } };

      isRunning = true;

      try {
        await startWithCamera({ facingMode: { exact: "environment" } }, config);
      } catch (environmentError) {
        const cameras = await Html5Qrcode.getCameras();
        const backCam = cameras.length
          ? (cameras.find(c => /back|rear|environment|traseira|arriere/i.test(c.label)) || cameras[0])
          : null;

        if (backCam) {
          try {
            await startWithCamera({ deviceId: { exact: backCam.id } }, config);
          } catch (deviceError) {
            await startWithCamera({ facingMode: "environment" }, config);
          }
        } else {
          await startWithCamera({ facingMode: "environment" }, config);
        }
      }

      setStatus("Caméra active. Scanne un QR…");

    }catch(e){
      console.error(e);
      setStatus("Caméra refusée ou indisponible ❌");
      stopQr();
    }
  }

  async function stopQr(){
    try{
      if (qr) {
        await qr.stop();
        await qr.clear();
      }
    }catch(e){}

    isRunning = false;
    $qrReader.style.display = "none";
    $btnStart.style.display = "inline-block";
    $btnStop.style.display  = "none";
    setStatus("Caméra arrêtée");
  }

  if ($btnStart) $btnStart.addEventListener('click', startQr);
  if ($btnStop)  $btnStop.addEventListener('click', stopQr);

})();
</script>
