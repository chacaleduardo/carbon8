<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

$idrhtipoevento = $_SESSION['arrpostbuffer'][1]['i']['rhevento']['idrhtipoevento'];
$dataevento = $_SESSION['arrpostbuffer'][1]['i']['rhevento']['dataevento'];
$valor = $_SESSION['arrpostbuffer'][1]['i']['rhevento']['valor'];
$idpessoa = $_SESSION['arrpostbuffer'][1]['i']['rhevento']['idpessoa'];
//$idempresa = $_SESSION['arrpostbuffer'][1]['i']['rhevento']['idempresa'];
$datafim = $_POST['datafim'];
$idempresa = $_POST['idempresa'];
$idpessoamulti = $_POST['idpessoamulti'];
 
if(!empty($idrhtipoevento) and !empty($dataevento) and !empty($valor) and empty($idpessoa) and empty($datafim)){
      //print_r($_SESSION['arrpostbuffer']) ; die; 

    $sqlAnd = '';
    
    /*if(!empty($idempresa))
    {
       $sqlAnd .= ' AND idempresa in ('.$idempresa.')';
    }
    */
    if(!empty($idpessoa))
    {
       $sqlAnd .= ' AND idpessoa IN ('.$idpessoa.')';
    }elseif(!empty($idpessoamulti))
    {
       $sqlAnd .= ' AND idpessoa IN ('.$idpessoamulti.')';
    }


    $sqlm="select idpessoa,nomecurto from pessoa where idtipopessoa = 1 and status ='ATIVO' $sqlAnd order by nomecurto";

    //die($sqlm);

   
    $resm =  d::b()->query($sqlm)  or die("Erro configura _mtotabcol campo 	Prompt Drop sql:".$sqlm);
    $l=0;
    while ($rowm = mysqli_fetch_assoc($resm)) {
        if($_POST["funcionario_".$rowm['idpessoa']]){

            // print_r($_POST);die; 
            $l=$l+1;
            $_SESSION['arrpostbuffer'][$l]['i']['rhevento']['idpessoa']=$rowm['idpessoa'];
            $_SESSION['arrpostbuffer'][$l]['i']['rhevento']['valor']=$valor;
            
            if($l>1){
                $_SESSION['arrpostbuffer'][$l]['i']['rhevento']['idrhtipoevento']=  $_SESSION['arrpostbuffer'][1]['i']['rhevento']['idrhtipoevento'];
                $_SESSION['arrpostbuffer'][$l]['i']['rhevento']['dataevento']=$dataevento;                       

                $_SESSION['arrpostbuffer'][$l]['i']['rhevento']['alteradoem']=  $_SESSION['arrpostbuffer'][1]['i']['rhevento']['alteradoem'];
                $_SESSION['arrpostbuffer'][$l]['i']['rhevento']['alteradopor']=$_SESSION['arrpostbuffer'][1]['i']['rhevento']['alteradopor'];
                $_SESSION['arrpostbuffer'][$l]['i']['rhevento']['criadoem']=$_SESSION['arrpostbuffer'][1]['i']['rhevento']['criadoem'];
                $_SESSION['arrpostbuffer'][$l]['i']['rhevento']['criadopor']=$_SESSION['arrpostbuffer'][1]['i']['rhevento']['criadopor'];
                $_SESSION['arrpostbuffer'][$l]['i']['rhevento']['idempresa']=$_SESSION['arrpostbuffer'][1]['i']['rhevento']['idempresa'];

                $idfluxostatus = FluxoController::getIdFluxoStatus('rhevento', 'PENDENTE');
                $_SESSION['arrpostbuffer'][$l]['i']['rhevento']['idfluxostatus'] = $idfluxostatus;

            }
        }
    }
}
//print_r($_SESSION['arrpostbuffer']) ; die; 

//Lança os Eventos por Pessoa Conforme o Prazo Selecionado. Este terá um ínicio e Fim
if(!empty($idrhtipoevento) and !empty($dataevento) and !empty($valor) and !empty($idpessoa) and !empty($datafim))
{   
    $data1 = $dataevento;
	$data2 = validadate($datafim);
    $j = 1;
    $grupo = rstr(8);
    for ($i=0;;$i++) 
    {
        $sqlm = "SELECT DATE_ADD('$data1', INTERVAL ".$i." DAY) AS diabusca,
                        DATE_FORMAT(DATE_ADD('$data1', INTERVAL ".$i." DAY),'%W') AS semana,
                        VERIFICAFERIADOFDS(DATE_ADD('$data1', INTERVAL ".$i." DAY)) AS diautil,
                        CASE WHEN DATE_ADD('$data1', INTERVAL ".$i." DAY) = '$data2' THEN 'FIM' ELSE 'N' END AS datafinal,
                        CASE WHEN DATE_ADD('$data1', INTERVAL ".$i." DAY) > '$data2' THEN 'Y' ELSE 'N' END AS maior;";
        $re = d::b()->query($sqlm) or die("Erro ao buscar Datas  sql=".$sqlm);
		$rw = mysqli_fetch_assoc($re);

		if($rw['maior'] == 'Y') {
			break;
		}elseif($rw['diautil'] == 0){
			$_SESSION['arrpostbuffer'][$j]['i']['rhevento']['idpessoa'] = $idpessoa;
            $_SESSION['arrpostbuffer'][$j]['i']['rhevento']['valor'] = $valor;
            $_SESSION['arrpostbuffer'][$j]['i']['rhevento']['grupo'] = $grupo;
            $_SESSION['arrpostbuffer'][$j]['i']['rhevento']['idrhtipoevento'] = $_SESSION['arrpostbuffer'][1]['i']['rhevento']['idrhtipoevento'];
            $_SESSION['arrpostbuffer'][$j]['i']['rhevento']['dataevento'] = dma($rw['diabusca']);                      
            $_SESSION['arrpostbuffer'][$j]['i']['rhevento']['alteradoem'] =  $_SESSION['arrpostbuffer'][1]['i']['rhevento']['alteradoem'];
            $_SESSION['arrpostbuffer'][$j]['i']['rhevento']['alteradopor'] = $_SESSION['arrpostbuffer'][1]['i']['rhevento']['alteradopor'];
            $_SESSION['arrpostbuffer'][$j]['i']['rhevento']['criadoem'] = $_SESSION['arrpostbuffer'][1]['i']['rhevento']['criadoem'];
            $_SESSION['arrpostbuffer'][$j]['i']['rhevento']['criadopor'] = $_SESSION['arrpostbuffer'][1]['i']['rhevento']['criadopor'];
            $_SESSION['arrpostbuffer'][$j]['i']['rhevento']['idempresa'] = $_SESSION['arrpostbuffer'][1]['i']['rhevento']['idempresa'];
            $idfluxostatus = FluxoController::getIdFluxoStatus('rhevento', 'PENDENTE');
            $_SESSION['arrpostbuffer'][$j]['i']['rhevento']['idfluxostatus'] = $idfluxostatus;
            $j++;
		}
    }
}  
