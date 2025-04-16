<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
 

$arrpostbuffer = $_SESSION["arrpostbuffer"];
//print_r($arrpostbuffer);
$status=$_POST['status'];
$dia=$_POST['dia'];
$fdia=$dia+2;
$diadom=$dia+1;
//print_r($arrpostbuffer);	


//função para comparar se a primeira data e maior que a segunda
function comparadata($dataI,$dataII){
	// trabalhando a primeira data
	$I= strtotime($dataI );

	// trabalhando a segunda data
	$II= strtotime($dataII);

	if($I == $II){
		$vretorno="I";
	}elseif($I > $II){
		$vretorno="S";
	}elseif($II > $I){
		$vretorno="N";
	}
	return($vretorno);
}


	
	$qtdreg= count($arrpostbuffer); 
echo($qtdreg);
//die("fim");		
	if($qtdreg > 0){
			
		while (list($key, $value) = each($arrpostbuffer)) {
		
			//QUANDO VIER DA FUNCAO quitar da finextrato
			if($status=="QUITAR" or $status=="PROGRAMAR"){
				
				$idcontapagar = $_SESSION['arrpostbuffer'][$key]['u']['contapagar']['idcontapagar'];
				$sqlreceb="select  datareceb,progpagamento from contapagar where idcontapagar =".$idcontapagar;
				$resreceb=d::b()->query($sqlreceb) or die("Erro 1 ao buscar proxima data de recebimento sql=".$sqlreceb);
				$rowreceb=mysqli_fetch_assoc($resreceb);
				
				$dataatual= date("Y-m-d");
					
				//comparar a data
				$retcomparadata=comparadata($dataatual,$rowreceb['datareceb']);
			
				//if($retcomparadata=="I" or $retcomparadata=="S"){	
				if($status=="QUITAR"){
					//LTM - 31-03-2021: Retorna o Idfluxo ContaPagar
					$idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'QUITADO');

					$_SESSION['arrpostbuffer'][$key]['u']['contapagar']['status'] = 'QUITADO';
					$_SESSION['arrpostbuffer'][$key]['u']['contapagar']['idfluxostatus'] = $idfluxostatus;

					FluxoController::alterarStatus('contapagar', 'idcontapagar', $idcontapagar, $idfluxostatushist, $idstatusf, $statustipo = "", $idfluxostatuspessoa, $ocultar, $idfluxostatus, $idfluxo, $prioridade, $tipobotao);
					
				}else{
					if($rowreceb['progpagamento']=='N'){
						$_SESSION['arrpostbuffer'][$key]['u']['contapagar']['progpagamento'] ='S';
					}else{
						$_SESSION['arrpostbuffer'][$key]['u']['contapagar']['progpagamento'] ='N';
					}
				
				}
				//QUANDO VIER DA FUNCAO maisumdia da finextrato
			}elseif($status=="MAISUMDIA"){
				
				$idcontapagar = $_SESSION['arrpostbuffer'][$key]['u']['contapagar']['idcontapagar'];
				$sqlreceb="select  
								DATE(DATE_ADD(datareceb, INTERVAL ".$dia." DAY)) as datareceb,
								DAYOFWEEK(DATE(DATE_ADD(datareceb, INTERVAL ".$dia." DAY))) as diasemana,
								DATE(DATE_ADD(datareceb, INTERVAL ".$fdia." DAY)) as datareceb1,
								DATE(DATE_ADD(datareceb, INTERVAL ".$diadom." DAY)) as datarecebdom
							from contapagar where idcontapagar =".$idcontapagar;
				$resreceb=d::b()->query($sqlreceb) or die("Erro 2 ao buscar proxima data de recebimento sql=".$sqlreceb);
				$rowreceb=mysqli_fetch_assoc($resreceb);
				
				//se der no sabado pegar a data de segunda
				if($rowreceb['diasemana']==7){
					$datareceb= implode('-', array_reverse(explode('-', substr($rowreceb['datareceb1'], 0, 10)))).substr($rowreceb['datareceb1'], 10);
				}elseif($rowreceb['diasemana']==1){
					$datareceb= implode('-', array_reverse(explode('-', substr($rowreceb['datarecebdom'], 0, 10)))).substr($rowreceb['datarecebdom'], 10);
				}else{				
					$datareceb = implode('-', array_reverse(explode('-', substr($rowreceb['datareceb'], 0, 10)))).substr($rowreceb['datareceb'], 10);
				}
				
				$_SESSION['arrpostbuffer'][$key]['u']['contapagar']['datareceb'] =$datareceb;
				//QUANDO VIER DA FUNCAO menosumdia da finextrato
			}elseif($status=="MENOSUMDIA"){
				$idcontapagar = $_SESSION['arrpostbuffer'][$key]['u']['contapagar']['idcontapagar'];
				$sqlreceb="select  
							DATE(DATE_SUB(datareceb, INTERVAL ".$dia." DAY)) as datareceb,
							DAYOFWEEK(DATE(DATE_SUB(datareceb, INTERVAL ".$dia." DAY))) as diasemana,
							DATE(DATE_SUB(datareceb, INTERVAL ".$fdia." DAY)) as datareceb1,
							DATE(DATE_SUB(datareceb, INTERVAL ".$diadom." DAY)) as datarecebdom
						from contapagar where idcontapagar =".$idcontapagar;
				$resreceb=d::b()->query($sqlreceb) or die("Erro 3 ao buscar proxima data de recebimento sql=".$sqlreceb);
				$rowreceb=mysqli_fetch_assoc($resreceb);
				//se cair no domingo voltar para sexta
				if($rowreceb['diasemana']==1){
					$datareceb = implode('-', array_reverse(explode('-', substr($rowreceb['datareceb1'], 0, 10)))).substr($rowreceb['datareceb1'], 10);
				}elseif($rowreceb['diasemana']==7){
					$datareceb= implode('-', array_reverse(explode('-', substr($rowreceb['datarecebdom'], 0, 10)))).substr($rowreceb['datarecebdom'], 10);
				}else{
					$datareceb = implode('-', array_reverse(explode('-', substr($rowreceb['datareceb'], 0, 10)))).substr($rowreceb['datareceb'], 10);
				}
				
				$_SESSION['arrpostbuffer'][$key]['u']['contapagar']['datareceb'] =$datareceb;
			}			
		}
		
	}else{
		die("Não selecionada conta para alteração.");
	}
//print_r($_SESSION['arrpostbuffer']);
//die;
?>