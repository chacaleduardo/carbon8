<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

$rhfolha_idrhtipoevento = $_POST['rhfolha_idrhtipoevento'];
$rhfolha_datafim = $_POST['rhfolha_datafim'];
$rhfolha_situacao = $_POST['rhfolha_situacao'];

$idempresa = (!empty($_GET["_idempresa"])) ? $_GET["_idempresa"] : $_SESSION["SESSAO"]["IDEMPRESA"];

if(!empty($rhfolha_idrhtipoevento) and !empty($rhfolha_datafim) and !empty($rhfolha_situacao)){
    
    if($rhfolha_situacao=='A'){
    $sql="select idrhevento from  rhevento e
            where e.idrhtipoevento = ".$rhfolha_idrhtipoevento."
            and e.dataevento <= '".$rhfolha_datafim."'
            and e.situacao='P'
            and e.idempresa=".$idempresa."
            and e.status='PENDENTE'";
    }else{
         $sql="select idrhevento from  rhevento e
            where e.idrhtipoevento = ".$rhfolha_idrhtipoevento."
            and e.dataevento <= '".$rhfolha_datafim."'
            and e.idempresa=".$idempresa."
            and e.situacao='A'
            and e.status='PENDENTE'";
    }
    $res = d::b()->query($sql) or die("Erro ao buscar eventos para alteracao saveprechange: ".mysqli_error(d::b()));
    $l=0;
    while($row=mysqli_fetch_assoc($res)){
        $l=$l+1;
        $_SESSION["arrpostbuffer"][$l]["u"]["rhevento"]["idrhevento"]=$row['idrhevento'];
        $_SESSION["arrpostbuffer"][$l]["u"]["rhevento"]["situacao"]=$rhfolha_situacao;
        
    }
}

$idrhfolha= $_SESSION["arrpostbuffer"]["x"]["i"]["rhfolhaitem"]["idrhfolha"];
$idpessoa= $_SESSION["arrpostbuffer"]["x"]["i"]["rhfolhaitem"]["idpessoa"];
if(!empty($idpessoa) and !empty($idrhfolha)){

	$regime= traduzid('pessoa', 'idpessoa', 'contrato', $idpessoa);
	if($regime=='PD'){$regime='CLT';}

	$_SESSION["arrpostbuffer"]["x"]["i"]["rhfolhaitem"]["regime"]=$regime;

    $sql1="select * from rhfolha where idrhfolha=".$idrhfolha;
    $res1 = d::b()->query($sql1) or die("Erro ao informacoes da folha no saveprechange: ".mysqli_error(d::b()));
    $row1=mysqli_fetch_assoc($res1);
    if($row1["tipofolha"]=='FOLHA'){
		$idfluxostatus = FluxoController::getIdFluxoStatus('rhevento', 'PENDENTE');
		$sql = " INSERT INTO rhevento
						(idempresa,
						idrhtipoevento,
						idrhfolha,
						idpessoa,
						idfluxostatus,
						dataevento,
						valor,criadopor,criadoem,alteradopor,alteradoem
						)
			(select 
					p.idempresa,
					e.idrhtipoevento,
					".$idrhfolha.",
					p.idpessoa,
					$idfluxostatus,
					'".$row1['datafim']."',
					e.valor,
					'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),
					'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
				from rheventopessoa e, pessoa p 
				where p.status='ATIVO'
				and e.status='ATIVO'			
               	and p.idpessoa = e.idpessoa 
				and p.idpessoa = ".$idpessoa.")";

		$res = d::b()->query($sql);
	}
}  

//print_r($_SESSION["arrpostbuffer"]);
//die();

$idrhfolhaitem= $_SESSION["arrpostbuffer"]["ajax"]["d"]["rhfolhaitem"]["idrhfolhaitem"];
// ao retirar uma pessoa da folha retirar os eventos fixos criados pela mesma
if(!empty($idrhfolhaitem)){
	
	$sql ="delete e.* 
	from rhfolhaitem fi,rheventopessoa ep,rhevento e
	where fi.idrhfolhaitem = ".$idrhfolhaitem."
	and ep.idpessoa = fi.idpessoa
	and ep.idrhtipoevento = e.idrhtipoevento
    and e.idpessoa = fi.idpessoa
	and e.idrhfolha = fi.idrhfolha";
	$res = d::b()->query($sql);
}


$Xidrhfolha= $_SESSION["arrpostbuffer"]["xx"]["u"]["rhfolha"]["idrhfolha"];
$listarocutar=$_POST['listarocutar'];
if(!empty($Xidrhfolha) and !empty($listarocutar) ){
	unset($_SESSION['arrpostbuffer']);

	if($listarocutar=='listar'){
		$sql="select * from rhfolhaconf where idrhfolha=".$Xidrhfolha;
		$res= d::b()->query($sql);
		$qtd=mysqli_num_rows($res);
		if($qtd < 1){
			die('Não tem campos invisíveis');
		}else{
			$l=0;
			while($row=mysqli_fetch_assoc($res)){
				$l++;
				$_SESSION["arrpostbuffer"]["xx".$l]["d"]["rhfolhaconf"]["idrhfolhaconf"]=$row["idrhfolhaconf"];
			}

		}


	}

}
//print_r($_SESSION["arrpostbuffer"]); die();
