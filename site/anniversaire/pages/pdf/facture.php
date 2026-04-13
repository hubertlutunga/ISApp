	<?php			
																				
				
		
		
	require('fpdf.php');
	include("../../../pages/bdd.php");
    //include('../phpqrcode/qrlib.php'); 
	



	class myPDF extends FPDF{


		
		function infodf($pdo){

			
            $cod = $_GET['cod'];

            $sqlc = $pdo->prepare("SELECT * from facture  WHERE cod_fact = :cod_fact");
            $sqlc->execute([ 
                ':cod_fact' => $cod
            ]);
            
            $datacomp = $sqlc->fetch();
            
            $sqlcli = $pdo->prepare("SELECT * from client  WHERE cod_cli = :cod_cli");
            $sqlcli->execute([ 
                ':cod_cli' => $datacomp['cod_cli']
            ]);
            
            $dataclient = $sqlcli->fetch(); 
            

			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',11);
			$this->SetMargins(7,20,0,0,true);
			
          
			$this->Cell(15,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Cell(150,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(0);	
			$this->Cell(193,8,mb_convert_encoding('Date: '.date('d/m/Y',strtotime($datacomp['date_enreg'])), 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(5); 			
			$this->Cell(193,8,mb_convert_encoding($datacomp['type_fact'].' - Référence: '.$datacomp['reference'], 'ISO-8859-1', 'UTF-8'),0,0,'R');
			
			
			$this->SetMargins(14,20,0,0,true);
			$this->SetFillColor(72, 78, 154, 0);
			$this->SetFillColor(255, 255, 255, 0);
            $this->Ln(8); 
			$this->SetFont('Arial','',6);
            $this->MultiCell(45, 3, mb_convert_encoding('Agence des créations des applications, des conceptions des supports de la communication visuelle et de la photographie.', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L',true);
 
			/*
		
	     	$this->Ln(5);			
			$this->Cell(195,8,mb_convert_encoding('RCCM: ', 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(5);			
			$this->Cell(195,8,mb_convert_encoding('Tél.: ', 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(12);         

            */
             
            $this->Ln(10); 


 
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',16);
			$this->SetMargins(14,20,0,0,true);	
			$this->Cell(85,8,mb_convert_encoding($datacomp['type_fact'], 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Cell(100,8,mb_convert_encoding('Détail Client', 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(7);
			$this->SetFont('Arial','',10);	
			$this->Cell(85,8,mb_convert_encoding('HUBERT SOLUTIONS', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Cell(100,8,mb_convert_encoding($dataclient['denom'], 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(5);
			$this->Cell(85,8,mb_convert_encoding('63 Bis, Kindu, Q/ Kinshasa', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Cell(100,8,mb_convert_encoding($dataclient['phone'], 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(5);
			$this->Cell(85,8,mb_convert_encoding('+243 810 678 785', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Cell(100,8,mb_convert_encoding($dataclient['email'], 'ISO-8859-1', 'UTF-8'),0,0,'R');
			$this->Ln(15);	 
			  

		}


		function head_t($pdo){
			
		  
            $cod = $_GET['cod'];

            $sqlc = $pdo->prepare("SELECT * from facture  WHERE cod_fact = :cod_fact");
            $sqlc->execute([ 
                ':cod_fact' => $cod
            ]);
            
            $datacomp = $sqlc->fetch();
            
            $sqlcli = $pdo->prepare("SELECT * from client  WHERE cod_cli = :cod_cli");
            $sqlcli->execute([ 
                ':cod_cli' => $datacomp['cod_cli']
            ]);
            
            $dataclient = $sqlcli->fetch(); 
		
			$this->Image('../../../img/Logo_hubert_solutions.png',15,10,40); 
			$this->Ln(1);	

			$this->Image('../../images/filfact_01.png',1,1,210); 
			$this->Ln(1);	

			if($datacomp['type_fact'] === "Facture"){

				$this->Image('../../images/hs_pmarci.png',140,220,50); 
				$this->Ln(1);

			}else{
				
				$this->Image('../../images/cach_hs.png',140,220,50); 
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
			$this->SetFont('Arial','B',12);
			$this->SetMargins(11,0,0,0,true);		
			$this->Cell(15,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Cell(150,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(0);	
			$this->Cell(10,12,mb_convert_encoding('#', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(90,12,mb_convert_encoding('Désignation', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(15,12,mb_convert_encoding('Qte', 'ISO-8859-1', 'UTF-8'),0,0,'C',true);
			$this->Cell(30,12,mb_convert_encoding('PU', 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Cell(45,12,mb_convert_encoding('PT', 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
			$this->Ln(15);

 
			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	

 

            $id = 0;


            
            $cod = $_GET['cod'];

            $sqlc = $pdo->prepare("SELECT * from facture  WHERE cod_fact = :cod_fact");
            $sqlc->execute([ 
                ':cod_fact' => $cod
            ]);
            
            $datacomp = $sqlc->fetch();
            
            $sqlcli = $pdo->prepare("SELECT * from client  WHERE cod_cli = :cod_cli");
            $sqlcli->execute([ 
                ':cod_cli' => $datacomp['cod_cli']
            ]);
            
            $dataclient = $sqlcli->fetch(); 

            $sqldf = $pdo->prepare("SELECT * from detailfacture where reference = :reference order by cod_df DESC");
            $sqldf->execute([
                ':reference' => $datacomp['reference']
            ]);
    
            while($datadfact = $sqldf->fetch()){ 
                                   
                
    
            $sqladdtot = $pdo->prepare("SELECT sum(prix_t) as total_n from detailfacture where reference = :reference");
            $sqladdtot->execute([
                        ':reference' => $datacomp['reference']
            ]);
    
            $datasomme = $sqladdtot->fetch();

            $prix_t = $datadfact['qte'] * $datadfact['prix_u'];

            $id++;

            
			$this->SetFont('Arial','',11);	
			
			$this->Cell(10,8,mb_convert_encoding($id, 'ISO-8859-1', 'UTF-8'),"B",0,'L');
			$this->Cell(90,8,mb_convert_encoding($datadfact['designation'], 'ISO-8859-1', 'UTF-8'),"B",0,'L');
			$this->Cell(15,8,mb_convert_encoding($datadfact['qte'], 'ISO-8859-1', 'UTF-8'),"B",0,'C');
			$this->Cell(30,8,mb_convert_encoding($datadfact['prix_u'].' '.$datacomp['devise'], 'ISO-8859-1', 'UTF-8'),"B",0,'R');
			$this->Cell(45,8,mb_convert_encoding($prix_t.' '.$datacomp['devise'], 'ISO-8859-1', 'UTF-8'),"B",0,'R');
 
			$this->Ln(9);

			// $this->Line(50, 45, 210-50, 45);
      			


      	   }
	  
             $this->Ln(18);
 






			 

			 $this->SetFillColor(186, 217, 244);
			 $this->SetTextColor(0, 0, 0); 
			 $this->SetMargins(11,20,0,0,true);
			 //premiere ligne de paiement  
			 
						 $this->SetFont('Arial','B',13);	
						 $this->Cell(10,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
						 $this->Cell(50,15,mb_convert_encoding('Paiement accepté', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
						 $this->Cell(10,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
						 
			 

//premiere ligne de total 
			 $this->SetFillColor(255, 255, 255);
$this->Cell(60,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L');

			$this->SetFillColor(186, 217, 244); 
$this->Cell(5,10,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
$this->SetFont('Arial','B',11);
$this->Cell(15,10,mb_convert_encoding('PT:', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
$this->SetFont('Arial','',11);
$this->Cell(35,10,mb_convert_encoding($datasomme['total_n'].' '.$datacomp['devise'], 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
$this->Cell(5,10,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);


 
			$this->Ln(10);

			//deuxième ligne mode paiement
			$this->SetFont('Arial','',11);
            $this->Cell(10,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(50,15,mb_convert_encoding('Cash ou Virement', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(10,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
		
			
		 
			 

//deuxieme ligne de tax 
$this->SetFillColor(255, 255, 255);
$this->SetFont('Arial','',8);
$this->Cell(60,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L');

			$this->SetFillColor(255, 255, 255); 
$this->Cell(5,10,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
$this->SetFont('Arial','B',11);
$this->Cell(15,10,mb_convert_encoding('Tax:', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
$this->SetFont('Arial','',11);
$this->Cell(35,10,mb_convert_encoding('0 '.$datacomp['devise'], 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
$this->Cell(5,10,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);




			$this->Ln(10);
			
$this->SetFillColor(186, 217, 244); 
//troisieme ligne banche	 
			$this->SetFont('Arial','B',8);
            $this->Cell(10,8,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(50,8,mb_convert_encoding('Comptes bancaires', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(10,8,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			



			

//troisieme ligne de total gen 

$this->SetFillColor(255, 255, 255); 
$this->SetTextColor(255, 255, 255); 
$this->Cell(60,15,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L');
 
$this->SetFillColor(72, 78, 154, 0);
$this->Cell(5,10,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
$this->SetFont('Arial','B',13);
$this->Cell(15,10,mb_convert_encoding('Total:', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
$this->SetFont('Arial','',11);
$this->Cell(35,10,mb_convert_encoding($datasomme['total_n'].' '.$datacomp['devise'], 'ISO-8859-1', 'UTF-8'),0,0,'R',true);
$this->Cell(5,10,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);




			$this->Ln(6);
	 
			$this->SetTextColor(0, 0, 0); 
			$this->SetFillColor(186, 217, 244); 
			$this->SetFont('Arial','',8);
            $this->Cell(10,8,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(50,8,mb_convert_encoding('Rawbank : 01038015001', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(10,8,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			

			$this->Ln(5);
	 
			$this->SetFont('Arial','',8);
            $this->Cell(10,8,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(50,8,mb_convert_encoding('Equity-BCDC : 066200102921325', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			$this->Cell(10,8,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			


			$this->Ln(5);
	  
			$this->Cell(70,6,mb_convert_encoding('', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			 


 
			$this->Ln(30);
	  
 
	 
        }



		
		function Infosign($pdo){

			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','I',10);
			$this->SetMargins(14,20,0,0,true);
			$this->Cell(70,8,mb_convert_encoding('En votre aimable règlement, Cordialement', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			  
		}


 


		function footer(){
			$this->SetY(-30);
			
			
			$this->SetFont('Arial','',6);
			$this->SetMargins(14,0,0,0,true); 
			$this->Cell(0,-3,mb_convert_encoding('______________________________________________________________________________________________________________________________________________________', 'UTF-8'),0,0,'L');
			
			$this->Ln(4);
			$this->SetFont('Arial','B',9);
			$this->Cell(0,-3,mb_convert_encoding('Hubert Solutions', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(4);
			
			$this->SetFont('Arial','',6); 
			$this->Cell(0,-3,mb_convert_encoding('63 Bis, Avenue Kindu, Quartier Pende, Commune de Kinshasa - Kinshasa-RDC', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(3);
			$this->Cell(0,-3,mb_convert_encoding('E–mail : contact@hubertlutunga.com; admilutunga@gmail.com', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(3);
			$this->Cell(0,-3,mb_convert_encoding('Contact: +243 810 678 785 - +243 852 266 590', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(3);
			$this->Cell(0,-3,mb_convert_encoding('Site web: www.hubertlutunga.com', 'ISO-8859-1', 'UTF-8'),0,0,'L');
			$this->Ln(3);
			//$this->Image('frame.png',40,3,11);
		}

	
	
 
    
    
	}
    












	$pdf = new myPDF();
	$pdf->AliasNbPages();
	$pdf->AddPage('P','A4','0');
	$pdf->infodf($pdo);
	$pdf->head_t($pdo);
	$pdf->Infofact($pdo);
	$pdf->Infosign($pdo);
	$pdf->Output();
	

?>