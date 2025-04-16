<?
require_once("../inc/php/functions.php");

$idcontapagar= $_GET["idcontapagar"];


		
	if(empty($idcontapagar)){
		
		die("ID da conta não informado");
	}else{

			$sql = "select datareceb,idagencia
					from contapagar
					where  idcontapagar = ".$idcontapagar;
					
			$res =  d::b()->query($sql) or die("A Consulta das informações da conta falhou[1] : " . mysqli_error(d::b()) . "<p>SQL: $sql"); 
			
			$row = mysqli_fetch_assoc($res);
			
		/*	
			$sql_1 = "select max(quitadoemseg) as quitadoemseg 
			from contapagar 
			where status='QUITADO' 
			and datareceb = '".$row["datareceb"]."' 
			and idagencia = ".$row["idagencia"];
			$res_1 =  d::b()->query($sql_1) or die("A Consulta das informações da conta falhou[2] : " . mysqli_error(d::b()) . "<p>SQL: $sql_1"); 			
			$row_1 = mysqli_fetch_assoc($res_1);		
		*/

	
			$sql1 = "update contapagar set saldook = 'Y' 
			where idagencia=".$row["idagencia"]."
			 and status = 'QUITADO' 
			 and datareceb <= '".$row["datareceb"]."' 
			 and (saldook is null or saldook='N')";
			
				
			$res1 =  d::b()->query($sql1) or die("Erro ao dar Ok nas contas : " . mysqli_error(d::b()) . "<p>SQL: $sql1"); 	
			
			echo "OK";
	}
?>