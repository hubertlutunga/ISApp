	<?php			
																				
				
		
		
	require('fpdf.php');
	include("../../pages/bdd.php");
    //include('../phpqrcode/qrlib.php'); 
	



	class myPDF extends FPDF{


		
		function infodf($pdo){

 

			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',11);
			$this->SetMargins(7,20,0,0,true);		 
			$this->Ln(0);						 
			$this->Ln(12);





			$this->Ln(5);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',13);
			$this->SetMargins(7,20,0,0,true);	 
			$this->Cell(280,8,mb_convert_encoding('LISTE DE PARTICIPANTS', 'ISO-8859-1', 'UTF-8'),0,0,'C');
			$this->Ln(5);	
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',11);		
			$this->Ln(10);	

		}


		function head_t($pdo){
			
		 

		
			$this->Image('../../images/logos/logo-mudimu_1.png',8,7,40);
			$this->SetMargins(0,0,0,0);
			$this->Ln(1);	
			
		}


		function Infofact($pdo){
			
				 


			$this->SetFillColor(238, 238, 238, 1);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','B',12);
			$this->SetMargins(11,7,0,0,true);		
			$this->Cell(15,15,'',0,0,'L');
			$this->Cell(150,15,'',0,0,'L');
			$this->Ln(0);	
            $this->Cell(10,8,mb_convert_encoding('#', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
            $this->Cell(60,8,mb_convert_encoding('Prénom', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
            $this->Cell(70,8,mb_convert_encoding('Nom', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
            $this->Cell(70,8,mb_convert_encoding('Téléphone', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
            $this->Cell(70,8,mb_convert_encoding('Email', 'ISO-8859-1', 'UTF-8'),0,0,'L',true);
			 
			$this->Ln(10);


			$this->SetFillColor(255, 255, 255, 255);
			$this->SetTextColor(0, 0, 0);
			$this->SetFont('Arial','',12);	


            $id = 0;

            $sqlx = "SELECT DISTINCT email FROM participants order by email";
            $reqx = $pdo->query($sqlx);  
            while ($row_part = $reqx->fetch()) {
      
      
            $sqlxc = "SELECT * FROM participants where email = '{$row_part['email']}'";
            $reqxc = $pdo->query($sqlxc);  
            $row_partlist = $reqxc->fetch();
      

                
    $id++;            

			$this->SetFont('Arial','',10);	
			$this->Cell(10,8,mb_convert_encoding($id, 'ISO-8859-1', 'UTF-8'),"B",0,'L',true);
			$this->Cell(60,8,mb_convert_encoding($row_partlist['prenom'], 'ISO-8859-1', 'UTF-8'),"B",0,'L',true);
			$this->Cell(70,8,mb_convert_encoding($row_partlist['nom'], 'ISO-8859-1', 'UTF-8'),"B",0,'L',true);
			$this->Cell(70,8,mb_convert_encoding($row_partlist['phone'], 'ISO-8859-1', 'UTF-8'),"B",0,'L',true);
			$this->Cell(70,8,mb_convert_encoding($row_partlist['email'], 'ISO-8859-1', 'UTF-8'),"B",0,'L',true);
			 
			$this->Ln(9);

			// $this->Line(50, 45, 210-50, 45);
      			


      	}


			$this->Ln(4);











		}
	






		function footer(){
			$this->SetY(-10);
			
			
			$this->SetFont('Arial','',11);
			$this->Cell(0,-3,'Mudimu Ed.1, Participants, Page '.$this->PageNo().'/{nb}',0,0,'L');
			$this->Ln();
			//$this->Image('frame.png',40,3,11);
		}

	}
	















	$pdf=new myPDF();
	$pdf->AliasNbPages();
	$pdf->AddPage('L','A4','0');
	$pdf->infodf($pdo);
	$pdf->head_t($pdo);
	$pdf->Infofact($pdo);
	$pdf->Output();
	

?>