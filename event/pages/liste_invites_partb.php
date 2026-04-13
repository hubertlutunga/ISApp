	<?php		

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	include('../../pages/bdd.php');
	require_once('pdf/fpdf.php');

  	//include('../phpqrcode/qrlib.php'); 

	function buildInviteHostFilterContext(PDO $pdo, int $eventCode): array {
		$filterSessionKey = 'invite_hote_filter';
		$eventFilterKey = (string) $eventCode;

		if (!isset($_SESSION[$filterSessionKey]) || !is_array($_SESSION[$filterSessionKey])) {
			$_SESSION[$filterSessionKey] = [];
		}

		$hostStmt = $pdo->prepare("SELECT DISTINCT u.cod_user FROM invite i LEFT JOIN is_users u ON u.cod_user = i.hote WHERE i.cod_mar = :codevent AND i.hote IS NOT NULL");
		$hostStmt->execute([':codevent' => $eventCode]);
		$allowedHostIds = array_map(static function ($hostRow) {
			return (string) ($hostRow['cod_user'] ?? '');
		}, $hostStmt->fetchAll(PDO::FETCH_ASSOC));

		$selectedHostFilter = isset($_GET['hote_filter'])
			? trim((string) $_GET['hote_filter'])
			: (isset($_SESSION[$filterSessionKey][$eventFilterKey]) ? (string) $_SESSION[$filterSessionKey][$eventFilterKey] : 'all');

		$queryParams = [':event_code' => $eventCode];
		$whereClause = '';

		if ($selectedHostFilter !== 'all' && in_array($selectedHostFilter, $allowedHostIds, true)) {
			$whereClause = ' AND i.hote = :host_user';
			$queryParams[':host_user'] = (int) $selectedHostFilter;
		} else {
			$selectedHostFilter = 'all';
		}

		$_SESSION[$filterSessionKey][$eventFilterKey] = $selectedHostFilter;

		return [
			'selected_filter' => $selectedHostFilter,
			'where_clause' => $whereClause,
			'params' => $queryParams,
		];
	}
	
	class myPDF extends FPDF{


		
		function infodf($pdo){
 

        $stmtev = $pdo->prepare("SELECT * FROM events WHERE cod_event = ?");
        $stmtev->execute([$_GET['event']]);
        $dataevent = $stmtev->fetch();




	 



        if ($dataevent['type_event'] == "1") {
            $typeevent = 'du mariage ' . ($dataevent['type_mar'] ?? 'Inconnu');
            $fetard = (($dataevent['prenom_epouse'] ?? '') . ' & ' . ($dataevent['prenom_epoux'] ?? '')) ?: 'Inconnu';
        
		} elseif ($dataevent['type_event'] == "2") {
			
			$stmtnv = $pdo->prepare("SELECT * FROM evenement WHERE cod_event = ?");
			$stmtnv->execute([$dataevent['type_event']]); // Correction ici pour utiliser $codevent
			$data_evenement = $stmtnv->fetch();

            $typeevent = "de l'".$data_evenement['nom'] ?? 'Inconnu';
            $fetard = $dataevent['nomfetard'] ?? 'Inconnu'; 

        } elseif ($dataevent['type_event'] == "3") {
			
			$stmtnv = $pdo->prepare("SELECT * FROM evenement WHERE cod_event = ?");
			$stmtnv->execute([$dataevent['type_event']]); // Correction ici pour utiliser $codevent
			$data_evenement = $stmtnv->fetch();
            $typeevent = "pour la ".$data_evenement['nom'] ?? 'Inconnu';
            $fetard = $dataevent['nomfetard'] ?? 'Inconnu';
        }
		
			$this->Ln(5);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',20);
			
			$this->Image('../images/Logo_invitationSpeciale_1.png',68,5,70);
			$this->Ln(20);




			if ($dataevent['type_event'] == "1") {
				$this->Cell(185,8,mb_convert_encoding($dataevent['prenom_epoux'].' & '.$dataevent['prenom_epouse'], 'ISO-8859-1', 'UTF-8'),0,0,'C');
			 
			} elseif ($dataevent['type_event'] == "2") {
				
				$this->Cell(185,8,mb_convert_encoding($fetard, 'ISO-8859-1', 'UTF-8'),0,0,'C');
			 
			} elseif ($dataevent['type_event'] == "3") {
				$this->Cell(185,8,mb_convert_encoding($dataevent['themeconf'], 'ISO-8859-1', 'UTF-8'),0,0,'C');
			 
			}

			
			
			$this->SetFont('Arial','',13);
			
			$this->Ln(8);	
			$this->Cell(185,8,mb_convert_encoding('Les invités '. $typeevent.', classés par ordre de Table', 'ISO-8859-1', 'UTF-8'),0,0,'C');
			$this->Ln(5);	
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',11);		
			$this->Ln(10);	

		}


		function head_t($pdo){
			
            $stmt2 = $pdo->prepare("SELECT * FROM events WHERE cod_event = ?");
            $stmt2->execute([$_GET['event']]);
            $dataevent = $stmt2->fetch();
		//	$this->Image("../../../couple/images/".$dataevent['logo']."",75,8,60);
		
		
			
			$this->SetFont('Arial','B',12);	
			$this->Ln(1);					
			
		}


		function Infofact($pdo){
			
				


			$this->Cell(15,15,'',0,0,'L');
			$this->Cell(150,15,'',0,0,'L');
			$this->Ln(0);	
            $cod = $_GET['event'];
			$filterContext = buildInviteHostFilterContext($pdo, (int) $cod);


            
			$reqtab = $pdo->prepare("
				SELECT * 
				FROM tableevent 
				WHERE cod_event = :cod 
				ORDER BY CAST(nom_tab AS UNSIGNED) ASC
			");
			$reqtab->execute(['cod' => $cod]); 
			while($data_table = $reqtab->fetch()){

                
			$siege = !empty($data_table['nom_tab']) ? ucfirst($data_table['nom_tab']) : 'Non définie';
 

            
                    $this->SetFont('Arial', 'B', 16);
                    $this->Cell(190, 8, mb_convert_encoding('Table : '.$siege, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
                    $this->Ln(12);



                    
			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);	 
			$this->Cell(100,8,'Noms',0,0,'L',true);
			$this->Cell(45,8,'Type',0,0,'C',true); 
			$this->Cell(45,8,'Hote',0,0,'R',true);
			$this->Ln(7);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

            // Utilisation de préparations de requêtes pour éviter les injections SQL
            $stmt1 = $pdo->prepare("SELECT i.*, u.noms AS hote_nom FROM invite i LEFT JOIN is_users u ON u.cod_user = i.hote WHERE i.cod_mar = :event_code AND i.siege = :siege" . $filterContext['where_clause'] . " ORDER BY i.nom ASC");
			$stmt1->execute($filterContext['params'] + [':siege' => $data_table['cod_tab']]);
            $invitations = $stmt1->fetchAll();
                

            if (empty($invitations)) {
                // Aucune invitation trouvée 
                    $this->Cell(185, 8, mb_convert_encoding('Aucun invité trouvé', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L', true);
            } else {
                foreach ($invitations as $row_inv) {

				$hote_nom = (string) ($row_inv['hote_nom'] ?? '');
				$hote_nom = strtok($hote_nom, ' ');
             
 
			if (!$row_inv['sing']) {
				$sing = '';
			}elseif ($row_inv['sing'] == 'C') {
				$sing = 'Couple';
			}else{
				$sing = 'Singléton';
			}

                    $this->SetFont('Arial', '', 12);
                    $this->Cell(100, 8, mb_convert_encoding($row_inv['nom'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'L', true);
                    $this->Cell(45, 8, mb_convert_encoding($sing, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C', true);
                    $this->Cell(45, 8, mb_convert_encoding($hote_nom, 'ISO-8859-1', 'UTF-8'), 0, 0, 'R', true); 
                    $this->Ln(7);

                }
            }
      	
			
			$this->Ln(13);

            }




















		}
	






		function footer(){
			$this->SetY(-10);
			
			
			$this->SetFont('Arial','',11);
			$this->Cell(0,-3,'INVITES SPECIALS, Page '.$this->PageNo().'/{nb}',0,0,'L');
			$this->Ln();
			//$this->Image('frame.png',40,3,11);
		}

	}
	















	$pdf=new myPDF();
	$pdf->AliasNbPages();
	$pdf->AddPage('P','A4','0');
	$pdf->infodf($pdo);
	$pdf->head_t($pdo);
	$pdf->Infofact($pdo);
	$pdf->Output();
	

?>