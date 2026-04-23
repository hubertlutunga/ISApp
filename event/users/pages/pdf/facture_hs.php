	<?php			
																				
				
		
		
	require __DIR__ . '/fpdf.php';
	require_once dirname(__DIR__, 4) . '/bootstrap/app.php';
    //include('../phpqrcode/qrlib.php'); 

	$invoicePdfDir = __DIR__;
	$invoiceAssetsDir = dirname(__DIR__, 4) . '/images';
	
	function isFpdfCompatiblePng(string $filePath): bool
	{
		$handle = @fopen($filePath, 'rb');
		if ($handle === false) {
			return false;
		}

		$header = fread($handle, 29);
		fclose($handle);

		if ($header === false || strlen($header) < 29) {
			return false;
		}

		if (substr($header, 0, 8) !== chr(137) . 'PNG' . chr(13) . chr(10) . chr(26) . chr(10)) {
			return false;
		}

		return ord($header[28]) === 0;
	}

	function resolveInvoiceLogoPath(string $assetsDir): ?string
	{
		$candidatePaths = [
			$assetsDir . '/Logo_invitationSpeciale_2.png',
			$assetsDir . '/Logo_invitationSpeciale_1.png',
			$assetsDir . '/Logo_invitationSpeciale_5.png',
		];

		foreach ($candidatePaths as $candidatePath) {
			if (!is_file($candidatePath)) {
				continue;
			}

			$extension = strtolower((string) pathinfo($candidatePath, PATHINFO_EXTENSION));
			if ($extension === 'png' && !isFpdfCompatiblePng($candidatePath)) {
				continue;
			}

			return $candidatePath;
		}

		return null;
	}

	$invoiceLogoPath = resolveInvoiceLogoPath($invoiceAssetsDir);
	$invoiceFactureStampPath = $invoicePdfDir . '/cach_IS_pmarci.png';
	$invoiceDevisStampPath = $invoicePdfDir . '/cach_IS_proforma.png';
	
	function renderInvoiceErrorPage($title, $message)
	{
		http_response_code(404);
		?>
		<!DOCTYPE html>
		<html lang="fr">
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
			<style>
				body {
					margin: 0;
					min-height: 100vh;
					display: flex;
					align-items: center;
					justify-content: center;
					padding: 24px;
					font-family: Arial, sans-serif;
					background: linear-gradient(135deg, #f8fafc 0%, #dbeafe 100%);
					color: #0f172a;
				}

				.invoice-error-card {
					width: min(100%, 580px);
					padding: 32px;
					border-radius: 24px;
					background: #ffffff;
					box-shadow: 0 24px 60px rgba(15, 23, 42, 0.16);
					text-align: center;
				}

				.invoice-error-card h1 {
					margin: 0 0 12px;
					font-size: 28px;
					line-height: 1.2;
				}

				.invoice-error-card p {
					margin: 0;
					font-size: 16px;
					line-height: 1.7;
					color: #475569;
				}
			</style>
		</head>
		<body>
			<div class="invoice-error-card">
				<h1><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
				<p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
			</div>
		</body>
		</html>
		<?php
		exit();
	}

	$reference = isset($_GET['cod']) ? trim((string) $_GET['cod']) : '';
	$documentType = (isset($_GET['type']) && $_GET['type'] === 'devis') ? 'Devis' : 'Facture';

	if ($reference === '') {
		renderInvoiceErrorPage('Reference introuvable', 'Aucune reference de facture n a ete fournie dans la requete.');
	}

	$initialFactureStmt = $pdo->prepare("SELECT * FROM facture WHERE reference = :reference AND type_fact = :type_fact ORDER BY date_enreg DESC, cod_fact DESC LIMIT 1");
	$initialFactureStmt->execute([
		':reference' => $reference,
		':type_fact' => $documentType,
	]);
	$initialFacture = $initialFactureStmt->fetch(PDO::FETCH_ASSOC) ?: null;

	if (!$initialFacture) {
		renderInvoiceErrorPage('Document indisponible', 'Aucun ' . strtolower($documentType) . ' n a ete trouve pour la reference ' . $reference . '.');
	}



	class myPDF extends FPDF{

		private function documentType(): string
		{
			return (isset($_GET['type']) && $_GET['type'] === 'devis') ? 'Devis' : 'Facture';
		}

		private function latestFacture(PDO $pdo): ?array
		{
			$sqlc = $pdo->prepare("SELECT * FROM facture WHERE reference = :reference AND type_fact = :type_fact ORDER BY date_enreg DESC, cod_fact DESC LIMIT 1");
			$sqlc->execute([
				':reference' => $_GET['cod'],
				':type_fact' => $this->documentType(),
			]);

			$data = $sqlc->fetch(PDO::FETCH_ASSOC);

			return $data ?: null;
		}

		private function paymentHistory(PDO $pdo): array
		{
			if ($this->documentType() !== 'Facture') {
				return [];
			}

			$stmt = $pdo->prepare("SELECT type_paie, montant_paye, devise, date_enreg FROM facture WHERE reference = :reference ORDER BY date_enreg ASC, cod_fact ASC");
			$stmt->execute([
				':reference' => $_GET['cod']
			]);

			return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
		}

		private function shouldShowPaymentHistory(array $paymentHistory): bool
		{
			if (count($paymentHistory) > 1) {
				return true;
			}

			if (count($paymentHistory) === 1) {
				return (($paymentHistory[0]['type_paie'] ?? '') === 'acompte');
			}

			return false;
		}

		private function paymentTotals(PDO $pdo): array
		{
			if ($this->documentType() === 'Devis') {
				$stmt = $pdo->prepare("SELECT MAX(montant_total) AS montant_total FROM facture WHERE reference = :reference AND type_fact = 'Devis'");
				$stmt->execute([
					':reference' => $_GET['cod']
				]);
				$data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

				return [
					'total' => isset($data['montant_total']) ? (float) $data['montant_total'] : 0.0,
					'paid' => 0.0,
				];
			}

			$stmt = $pdo->prepare("SELECT MAX(montant_total) AS montant_total, SUM(COALESCE(montant_paye, 0)) AS montant_paye FROM facture WHERE reference = :reference AND type_fact = 'Facture'");
			$stmt->execute([
				':reference' => $_GET['cod']
			]);

			$data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

			return [
				'total' => isset($data['montant_total']) ? (float) $data['montant_total'] : 0.0,
				'paid' => isset($data['montant_paye']) ? (float) $data['montant_paye'] : 0.0,
			];
		}


		
		function infodf($pdo){

			
            $cod = $_GET['cod'];

			$datacomp = $this->latestFacture($pdo);
			if (!$datacomp) {
				return;
			}
            
			                    
			$stmtus = $pdo->prepare("SELECT * FROM is_users WHERE cod_user = :cod_user");
			$stmtus->execute(['cod_user' => $datacomp['cod_cli']]); 
			$dataclient = $stmtus->fetch(PDO::FETCH_ASSOC) ?: [];
			$clientName = trim((string) ($dataclient['noms'] ?? 'Client introuvable'));
			$clientPhone = trim((string) ($dataclient['phone'] ?? ''));
			$clientEmail = trim((string) ($dataclient['email'] ?? ''));
  
            

			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',9);
			$this->SetMargins(7,5,0,0,true);
			
          
			$this->Cell(15,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Cell(150,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(0);	
			$this->Cell(135,8,mb_convert_encoding('Date: '.date('d/m/Y',strtotime($datacomp['date_enreg'])), 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(5); 			
			$this->Cell(135,8,mb_convert_encoding($datacomp['type_fact'].' - Référence: '.$datacomp['reference'], 'ISO-8859-1', 'UTF-8'),0,0,'R');
			
			
			$this->SetFillColor(255, 255, 255, 0);
			$this->SetTextColor(0, 0, 0);
            $this->Ln(8); 

			global $invoiceLogoPath;
			if (is_file($invoiceLogoPath)) {
				$this->Image($invoiceLogoPath,0,-8,50);
			}
			$this->Ln(1);	
			$this->SetFont('Arial','',6);
            $this->MultiCell(50, 3, mb_convert_encoding("Filiale de Hubert Solutions, dédiée à la création d'invitations haut de gamme, alliant élégance et sophistication.", 'ISO-8859-1', 'UTF-8'), 0, 0,true);
  
			$this->SetMargins(14,20,0,0,true);
			/*
		
	     	$this->Ln(5);			
			$this->Cell(195,8,mb_convert_encoding('RCCM: ', 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(5);			
			$this->Cell(195,8,mb_convert_encoding('Tél.: ', 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(12);         

            */
             
            $this->Ln(3); 


 
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',10);
			$this->SetMargins(7,5,0,0,true);	 
			$this->Cell(130,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(1);
			$this->Cell(60,7,mb_convert_encoding($datacomp['type_fact'], 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Cell(75,7,mb_convert_encoding('Détail Client', 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(5);
			$this->SetMargins(7,5,0,0,true);
			$this->SetFont('Arial','',8);	
			$this->Cell(60,7,mb_convert_encoding('HUBERT SOLUTIONS', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Cell(75,7,mb_convert_encoding($clientName, 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(4);
			$this->Cell(60,7,mb_convert_encoding('2e Niv, Immeuble Interfina, Kinshasa - Gombe', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Cell(75,7,mb_convert_encoding($clientPhone, 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(4);
			$this->Cell(60,7,mb_convert_encoding('+243 810 678 785', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Cell(75,7,mb_convert_encoding($clientEmail, 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(10);	 
			  

		}


		function head_t($pdo){
			
		  
            $cod = $_GET['cod'];

			$datacomp = $this->latestFacture($pdo);
			if (!$datacomp) {
				return;
			}
            
			$stmtus = $pdo->prepare("SELECT * FROM is_users WHERE cod_user = :cod_user");
			$stmtus->execute(['cod_user' => $datacomp['cod_cli']]); 
			$dataclient = $stmtus->fetch(PDO::FETCH_ASSOC) ?: [];
		
 	

			global $invoiceFactureStampPath, $invoiceDevisStampPath;
			if($datacomp['type_fact'] === "Facture"){

				if (is_file($invoiceFactureStampPath)) {
					$this->Image($invoiceFactureStampPath,80,160,45);
				}
				$this->Ln(1);

			}else{
				
				if (is_file($invoiceDevisStampPath)) {
					$this->Image($invoiceDevisStampPath,140,220,50);
				}
				$this->Ln(1);
			}
		/*
			
			$this->Image('../../../../img/cachet2.png',150,145,40);
			$this->SetMargins(0,0,0,0);
			$this->Ln(1);	
		*/

		}


		function Infofact($pdo){
			
				 
			$this->SetFillColor(72, 78, 154, 0);
			$this->SetTextColor(255, 255, 255);
			$this->SetFont('Arial','B',9);
			$this->SetMargins(7,0,0,0,true);		
			$this->Cell(15,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Cell(120,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(0);	
			$this->Cell(7,10,mb_convert_encoding('#', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(68,10,mb_convert_encoding('Désignation', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(10,10,mb_convert_encoding('Qte', 'ISO-8859-1', 'UTF-8'),0,0,'C',true);
			$this->Cell(25,10,mb_convert_encoding('PU', 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Cell(25,10,mb_convert_encoding('PT', 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Ln(10);

 
			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',8);	

 

            $id = 0;


            
            $cod = $_GET['cod'];

			$datacomp = $this->latestFacture($pdo);
			if (!$datacomp) {
				return;
			}
			$invoiceSummary = EventOrderService::buildInvoiceSummaryForEvent($pdo, (int) $datacomp['reference']);
			$paymentHistory = $this->paymentHistory($pdo);
			$paymentTotals = $this->paymentTotals($pdo);
            
			$stmtus = $pdo->prepare("SELECT * FROM is_users WHERE cod_user = :cod_user");
			$stmtus->execute(['cod_user' => $datacomp['cod_cli']]); 
			$dataclient = $stmtus->fetch(PDO::FETCH_ASSOC) ?: [];

            $sqldf = $pdo->prepare("SELECT * from details_fact where cod_event = :cod_event order by cod_df DESC");
            $sqldf->execute([
                ':cod_event' => $datacomp['reference']
            ]);
    
			while($datadfact = $sqldf->fetch()){ 
                                   
                
            $prix_t = $datadfact['qtecom'] * $datadfact['pu'];

            $id++;

            
			$this->SetFont('Arial','',8);	
			
			$this->Cell(7,8,mb_convert_encoding($id, 'ISO-8859-1', 'UTF-8'),"B",0,'L');
			$this->Cell(68,8,mb_convert_encoding($datadfact['libelle'], 'ISO-8859-1', 'UTF-8'),"B",0,'L');
			$this->Cell(10,8,mb_convert_encoding($datadfact['qtecom'], 'ISO-8859-1', 'UTF-8'),"B",0,'C');
			$this->Cell(25,8,mb_convert_encoding(number_format($datadfact['pu'], 2,'.','').' '.$datacomp['devise'], 'ISO-8859-1', 'UTF-8'),"B",0,'R');
			$this->Cell(25,8,mb_convert_encoding(number_format($prix_t, 2,'.','').' '.$datacomp['devise'], 'ISO-8859-1', 'UTF-8'),"B",0,'R');
 
			$this->Ln(7);

			// $this->Line(50, 45, 210-50, 45);
      			


      	   }
	  
             $this->Ln(10);
 






			 

			 $this->SetTextColor(255, 255, 255); 
			 $this->SetTextColor(0, 0, 0); 
			 $this->SetMargins(7,20,0,0,true);
			 //premiere ligne de paiement  
			 
						 $this->SetFont('Arial','B',8);	
						 $this->Cell(5,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
						 $this->Cell(30,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
						 $this->Cell(10,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
						 
			 

			$summaryTableWidth = 73;
			$summaryTableX = ($this->GetPageWidth() - $summaryTableWidth) / 2;

			 //premiere ligne de total 
			$this->SetX($summaryTableX);
			$this->SetFillColor(186, 217, 244); 
			$this->Cell(5,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->SetFont('Arial','B',9);
			$this->Cell(38,7,mb_convert_encoding('Sous-total :', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->SetFont('Arial','',9);
			$this->Cell(25,7,mb_convert_encoding(number_format((float) ($invoiceSummary['subtotal'] ?? 0), 2,'.','').' '.$datacomp['devise'], 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Cell(5,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);


 
			$this->Ln(7);

			//deuxième ligne mode paiement
			 $this->SetFillColor(255, 255, 255);
			$this->SetFont('Arial','',8);
            $this->Cell(5,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(30,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(10,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
		
			
		 
			 

//deuxieme ligne de tax 
			$this->SetX($summaryTableX);
			$this->SetFillColor(255, 255, 255); 
			$this->Cell(5,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->SetFont('Arial','B',9);
			$this->Cell(38,7,mb_convert_encoding('Remise :', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->SetFont('Arial','',9);
			$this->Cell(25,7,mb_convert_encoding(number_format((float) ($invoiceSummary['discount_amount'] ?? 0), 2,'.','').' '.$datacomp['devise'], 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Cell(5,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);




			$this->Ln(7);
			
$this->SetTextColor(255, 255, 255); 
//troisieme ligne banche	 
			$this->SetFont('Arial','B',8);
            $this->Cell(5,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(30,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(10,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			



			

//troisieme ligne de total gen 
 
			$this->SetTextColor(255, 255, 255); 
			$this->SetX($summaryTableX);
			$this->SetFillColor(72, 78, 154, 0);
			$this->Cell(5,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->SetFont('Arial','B',9);
			$this->Cell(38,7,mb_convert_encoding('A payer :', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->SetFont('Arial','B',9);
			$this->Cell(25,7,mb_convert_encoding(number_format((float) ($invoiceSummary['total'] ?? 0), 2,'.','').' '.$datacomp['devise'], 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Cell(5,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);








$this->Ln(7);

$paidTotal = min($paymentTotals['paid'], (float) ($invoiceSummary['total'] ?? 0));
$rest = max((float) ($invoiceSummary['total'] ?? 0) - $paidTotal, 0);

if ($this->shouldShowPaymentHistory($paymentHistory)) {
	$historyTableWidth = 68;
	$historyTableX = ($this->GetPageWidth() - $historyTableWidth) / 2;

	$this->SetTextColor(0, 0, 0);
	$this->SetFont('Arial','B',9);
	$this->SetX($historyTableX);
	$this->Cell($historyTableWidth,6,mb_convert_encoding('Historique des paiements', 'ISO-8859-1', 'UTF-8'),0,1,'C');

	foreach ($paymentHistory as $index => $payment) {
		$this->SetFillColor(255, 255, 255);
		$this->SetTextColor(0, 0, 0);
		$this->SetX($historyTableX);
		$this->SetFont('Arial','',8);
		$this->Cell(10,7,mb_convert_encoding((string) ($index + 1), 'ISO-8859-1', 'UTF-8'),0,0,'C',true);
		$this->Cell(20,7,mb_convert_encoding(ucfirst((string) $payment['type_paie']), 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
		$this->Cell(18,7,mb_convert_encoding(date('d/m/Y', strtotime((string) $payment['date_enreg'])), 'ISO-8859-1', 'UTF-8'),0,0,'C',true);
		$this->SetFont('Arial','',8);
		$this->Cell(20,7,mb_convert_encoding(number_format((float) $payment['montant_paye'], 2,'.','').' '.$datacomp['devise'], 'ISO-8859-1', 'UTF-8'),0,1,'R',true);
	}

	$this->Ln(2);
}

$this->SetFillColor(72, 78, 154, 0);
$this->SetTextColor(255, 255, 255);
$summaryWideWidth = 73;
$summaryWideX = ($this->GetPageWidth() - $summaryWideWidth) / 2;
$this->SetX($summaryWideX);
$this->Cell(5,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
$this->SetFont('Arial','B',9);
$this->Cell(38,7,mb_convert_encoding('Total encaissé :', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
$this->SetFont('Arial','B',9);
$this->Cell(25,7,mb_convert_encoding(number_format($paidTotal, 2,'.','').' '.$datacomp['devise'], 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
$this->Cell(5,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);

$this->Ln(7);

$this->SetTextColor(255, 255, 255);
$this->SetX($summaryWideX);
$this->SetFillColor(198, 64, 64);
$this->Cell(5,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
$this->SetFont('Arial','B',9);
$this->Cell(38,7,mb_convert_encoding('Reste :', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
$this->SetFont('Arial','B',9);
$this->Cell(25,7,mb_convert_encoding(number_format($rest, 2,'.','').' '.$datacomp['devise'], 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
$this->Cell(5,7,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);



			$this->SetY(-38);
			$this->SetFillColor(255, 255, 255); 
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',8);
			$this->Cell(0,6,mb_convert_encoding('En votre aimable règlement, Cordialement', 'ISO-8859-1', 'UTF-8'),0,0,'C',true);
	  
 
	 
        }



		
		function Infosign($pdo){

			$datacomp = $this->latestFacture($pdo);
			global $invoiceFactureStampPath, $invoiceDevisStampPath;
			if ($datacomp) {
				if($datacomp['type_fact'] === "Facture"){
					if (is_file($invoiceFactureStampPath)) {
						$this->Image($invoiceFactureStampPath,80,160,45);
					}
				}else{
					if (is_file($invoiceDevisStampPath)) {
						$this->Image($invoiceDevisStampPath,140,220,50);
					}
				}
			}

			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','I',10);
			$this->SetMargins(14,20,0,0,true);
			$this->Cell(70,8,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			  
		}


 


		function footer(){
			$this->SetY(-30);
			
			
			$this->SetFont('Arial','',6);
			$this->SetMargins(7,0,0,0,true); 
			$this->Cell(0,-3,mb_convert_encoding('', 'UTF-8'),0,0,'L');
			$this->Ln(1);
			$this->Cell(0,-3,mb_convert_encoding('_________________________________________________________________________________________________________________', 'UTF-8'),0,0,'L');
			
			$this->Ln(4);
			$this->SetFont('Arial','B',9);
			$this->Cell(0,-3,mb_convert_encoding('Hubert Solutions', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(4);
			
			$this->SetFont('Arial','',6); 
			$this->Cell(0,-3,mb_convert_encoding('2e Niv, Immeuble Interfina, Kinshasa - Gombe', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(3);
			$this->Cell(0,-3,mb_convert_encoding('E–mail : contact@invitationspeciale.com', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(3);
			$this->Cell(0,-3,mb_convert_encoding('Contact: +243 810 678 785', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(3);
			$this->Cell(0,-3,mb_convert_encoding('Site web: www.invitationspeciale.com', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(3);
			//$this->Image('frame.png',40,3,11);
		}

	
	
 
    
    
	}
    












	$pdf = new myPDF();
	$pdf->AliasNbPages();
	$pdf->AddPage('P','A5','0');
	$pdf->infodf($pdo);
	$pdf->head_t($pdo);
	$pdf->Infofact($pdo);
	$pdf->Infosign($pdo);

	$documentPrefix = $documentType === 'Devis' ? 'DEVIS-HS-' : 'FACT-HS-';
	$nomFichier = $documentPrefix . ($_GET['cod'] ?? 'document') . '.pdf';
	$datacomp = $initialFacture;
	if (is_array($datacomp) && !empty($datacomp['cod_cli'])) {
		$stmtus = $pdo->prepare("SELECT noms FROM is_users WHERE cod_user = :cod_user");
		$stmtus->execute(['cod_user' => $datacomp['cod_cli']]);
		$clientName = (string) $stmtus->fetchColumn();
		if ($clientName !== '') {
			$nomFichier = $documentPrefix . str_replace(' ', '_', $clientName) . '.pdf';
		}
	}

	$pdf->Output('I', $nomFichier);
?>