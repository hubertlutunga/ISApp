<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>ABC Panel d'administration </title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="../../vendors/feather/feather.css">
  <link rel="stylesheet" href="../../vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="../../vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="../../vendors/typicons/typicons.css">
  <link rel="stylesheet" href="../../vendors/simple-line-icons/css/simple-line-icons.css">
  <link rel="stylesheet" href="../../vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <link rel="stylesheet" href="../../vendors/datatables.net-bs4/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="../../js/select.dataTables.min.css">
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="../../css/vertical-layout-light/style.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="../../images/ico_abcrdc.png" />
</head>
<body >

<script src="html5-qrcode.min.js"></script>
<style>
  .result{
    background-color: green;
    color:#fff;
    padding:20px;
  }
  .row{
    display:flex;
  }
</style>
<div class="row" align="center" style="display: flex;justify-content: center;align-items: center;padding-top: 55px;">
  <div class="col-m-6">
    <div style="width:500px;" id="reader"></div>
  </div>
  <div class="col" style="padding:30px;">
    <div id="result">Placez la verso de la carte sur le WebCam de l'ordinateur <br>pour scanner le Qr Code du client</div>
  </div>
</div>
<script type="text/javascript">
function onScanSuccess(qrCodeMessage) {
    document.getElementById('result');
    // document.getElementById('result').innerHTML = '<span id="mylink" class="result">'+qrCodeMessage+'</span>';
       window.open(qrCodeMessage);
        // window.location.href=qrCodeMessage;
}
function onScanError(errorMessage) {
  //handle scan error
}
var html5QrcodeScanner = new Html5QrcodeScanner(
    "reader", { fps: 10, qrbox: 250 });
html5QrcodeScanner.render(onScanSuccess, onScanError);





</script>




</body>
</html>