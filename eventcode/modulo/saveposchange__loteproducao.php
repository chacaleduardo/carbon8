<?
// GERA AS DEMAIS INFORMAÇÕES DA ORDEM DE PRODUCAO APOS CRIAR A A MESMA NA TABELA wfxfluxo
//print_r($_SESSION['arrpostbuffer']);die;

$lote_statusold=$_POST['statusant'];
$iu = $_SESSION['arrpostbuffer']['1']['i']['lote']['idempresa'] ? 'i' : 'u';
$idlote=$_SESSION['arrpostbuffer']['1'][$iu]['lote']['idlote'];



$status=$_SESSION['arrpostbuffer']['1'][$iu]['lote']['status'];
$idloteU=$_SESSION['arrpostbuffer']['1'][$iu]['lote']['idlote'];
$exercicio=$_SESSION['arrpostbuffer']['1'][$iu]['lote']['exercicio'];
$partida=$_SESSION['arrpostbuffer']['1'][$iu]['lote']['partida'];

if($status=='APROVADO' and $lote_statusold !='APROVADO' and $idlote and !empty($lote_statusold)){
	
	//verifica se o lote possui solcom
	$sql="SELECT c.idcotacao,l.idempresa,l.idunidade,s.idunidade as idunidadedest,sci.qtdsolmatautomatica as qtdc,
			p.idprodserv,if(p.descrcurta = '',p.descr,p.descrcurta) as descr,sci.un,s.criadopor,lf.idlotefracao,sci.idsolcomitem,lf.qtd
		FROM lote l
			JOIN nfitem ni ON (ni.idnfitem = l.idnfitem)
			JOIN nf n ON (n.idnf = ni.idnf)
			JOIN cotacao c ON (c.idcotacao = n.idobjetosolipor and n.tipoobjetosolipor = 'cotacao')
			JOIN solcomitem sci ON (sci.idcotacao = c.idcotacao and sci.solmatautomatica = 'Y' and sci.idsolmatitem is null)
			JOIN solcom s ON (s.idsolcom = sci.idsolcom)
			JOIN prodserv p on (p.idprodserv = sci.idprodserv and p.idprodserv = l.idprodserv)
			JOIN lotefracao lf ON (lf.idlote = l.idlote and lf.idunidade = l.idunidade)
		WHERE l.idlote = ".$idlote;

	$res = d::b()->query($sql) or die("Erro ao buscar se o lote possui solcom: ". mysql_error() . "<p>SQL: ".$sql);
	$qtdres= mysqli_num_rows($res);
	$podepedir = true;
	if($qtdres > 1){
		$totalped = 0;
		while($row=mysqli_fetch_assoc($res)){
			$totalped = $totalped+$row['qtdc'];
			$qtddisp = $row['qtd'];
		}
		//verifica se o total pedido é igual ao total do lote
		if($totalped > $qtddisp){
			$podepedir = false;
			$_POST['alertacomprador'] = 'Não foi possível solicitar os materiais automaticamente, há mais de uma solicitação de compra para o mesmo lote e a quantidade total solicitada é maior que a quantidade disponível no lote.\nPor favor, entre em contato com o comprador para verificar a situação.';
		}
	}
	if($qtdres > 0 and $podepedir){
		$res = d::b()->query($sql) or die("Erro ao buscar se o lote possui solcom: ". mysql_error() . "<p>SQL: ".$sql);
		while($row=mysqli_fetch_assoc($res)){
			if($row["qtdc"] > 0){
				$idcotacao=$row['idcotacao'];
				$idsolcomitem=$row['idsolcomitem'];
				$idempresa=$row['idempresa']; 
				$idunidadefracao=$row['idunidade'];
				$idunidadedest=$row['idunidadedest'];
				$qtdc=$row['qtdc'];
				$idprodserv=$row['idprodserv'];
				$descr=$row['descr'];
				$un=$row['un'];
				$criadopor=$row['criadopor'];
				$idlotefracao=$row['idlotefracao'];
				$idfluxostatus = FluxoController::getIdFluxoStatus('solmat', 'SOLICITADO');
				$idfluxostatusAberto = FluxoController::getIdFluxoStatus('solmat', 'ABERTO');

				if($row["qtdc"] > $row["qtd"]){
					$qtdc = $row["qtd"];
				}

				//Cria solmat para o lote
				$inssolmat = new Insert();
				$inssolmat->setTable("solmat");   
				$inssolmat->idempresa=$idempresa;
				$inssolmat->tipo="MATERIAL";
				$inssolmat->unidade=$idunidadefracao;
				$inssolmat->idunidade=$idunidadedest;
				$inssolmat->status="SOLICITADO";
				$inssolmat->idfluxostatus=$idfluxostatus;
				$inssolmat->criadopor=$criadopor;
				$idsolmat=$inssolmat->save();

				

				FluxoController::inserirFluxoStatusHist('solmat', $idsolmat, $idfluxostatusAberto, 'ATIVO',0, $criadopor);
				FluxoController::inserirFluxoStatusHist('solmat', $idsolmat, $idfluxostatus, 'PENDENTE',0, $criadopor);

				//Cria solmatitem para o lote
				$inssolmatitem = new Insert();
				$inssolmatitem->setTable("solmatitem");
				$inssolmatitem->idsolmat=$idsolmat;
				$inssolmatitem->idempresa=$idempresa;
				$inssolmatitem->qtdc=$qtdc;
				$inssolmatitem->idprodserv=$idprodserv;
				$inssolmatitem->descr=$descr;
				$inssolmatitem->un=$un;
				$inssolmatitem->criadopor=$criadopor;

				$idsolmatitem=$inssolmatitem->save();

				$idtransacao = SolmatController::buscarRandomico()['idtransacao'];
				$idunidade = $idunidadedest;
				$qtdpedida = $qtdc;
				$idloteorigem = $idlote;
				$idlotefracaoori = $idlotefracao;
				// $idunidade = $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idunidade'];
				// $qtdpedida = $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['qtd'];
				// $idloteorigem = $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idlote'];
				// $idlotefracaoori = $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idlotefracaoorigem'];
				// $idsolmatitem = $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idobjeto'];
				
				if(!empty($idlotefracaoori))
				{
					$consomeun = traduzid('unidade', 'idunidade', 'consomeun', $idunidade);  
					$listarLoteFracao = SolmatController::buscarLotefracaoPorIdloteIdunidade($idloteorigem, $idunidade);  
					$qtdr = count($listarLoteFracao);
					if($qtdr>0)
					{
						
						$qtdpedidaori = $qtdpedida;
						$qtdpedidadest = $qtdpedida;
						//ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
						$rowconv = SolmatController::buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao($idlotefracaoori);        
						$arrayInsertLoteCons = [
							"idempresa" => $idempresa,
							"idlote" => $idloteorigem,
							"idlotefracao" => $idlotefracaoori,
							"idobjeto" => $listarLoteFracao['idlotefracao'],
							"tipoobjeto" => 'lotefracao',
							"obs" => 'Lote Fracionado.',
							"idtransacao" => $idtransacao,
							"idobjetoconsumoespec" => $idsolmatitem,
							"tipoobjetoconsumoespec" => 'solmatitem',
							"qtdd" => str_replace(",", ".", $qtdpedidaori),
							"usuario" => $criadopor,
							"status" => 'PENDENTE'
						];
						SolmatController::inserirLoteCons($arrayInsertLoteCons);
						// //gerar rateio

						// // adiciona um credItio na destino
						// $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idlote'] = $idloteorigem;
						// $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idlotefracao'] = $listarLoteFracao['idlotefracao'];
						// $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['qtdc'] = $qtdpedidadest; 
						// $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idobjeto'] = $idlotefracaoori;
						// $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['tipoobjeto']='lotefracao';
						// $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['obs']='crédito via solicitação de materiais';
						// $_SESSION['arrpostbuffer']['ufr']['i']['lotecons']['idtransacao'] = $idtransacao;
						// //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
						// if(($rowconv["consometransf"] == 'Y' || $consomeun == 'Y') && $rowconv["imobilizado"] != 'Y')
						// {
						//     $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlote'] = $idloteorigem;
						//     $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlotefracao'] = $listarLoteFracao['idlotefracao'];
						//     $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idobjeto'] = $listarLoteFracao['idlotefracao'];
						//     $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['tipoobjeto'] = 'lotefracao';
						//     $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idtransacao'] = $idtransacao;
						//     $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['obs'] = 'Lote consumido na transferência da solicitacão de materiais.';
						//     $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['qtdd'] = $qtdpedidadest;
						// }
							
						// debug(1);
						
						// debug(2);
					}else{ //se não tiver fracao
						$qtdpedidaori = $qtdpedida;
						$qtdpedidadest = $qtdpedida;
						// unset($_SESSION['arrpostbuffer']);                       
						$rowconv = SolmatController::buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao($idlotefracaoori);
						//debug(3);
						$_idempresa = traduzid('unidade', 'idunidade', 'idempresa', $idunidade);
						$arrayInsertLoteFracao = [
							"idempresa" => $_idempresa,
							"idunidade" => $idunidade,
							"qtd" => 0,
							"qtdini" => 0,         
							"idlote" => $idloteorigem,
							"idtransacao" => $idtransacao,
							"idlotefracaoorigem" => $idlotefracaoori,         
							"usuario" => $criadopor
						];
						$_idlotefracao = SolmatController::inserirLoteFracao($arrayInsertLoteFracao);

						//debug(4);

						$arrayInsertLoteCons = [
							"idempresa" => $idempresa,
							"idlote" => $idloteorigem,
							"idlotefracao" => $idlotefracaoori,
							"idobjeto" => $_idlotefracao,
							"tipoobjeto" => 'lotefracao',
							"obs" => 'Transferência na solicitacão de materiais.',
							"idtransacao" => $idtransacao,
							"idobjetoconsumoespec" => $idsolmatitem,
							"tipoobjetoconsumoespec" => 'solmatitem',
							"qtdd" => str_replace(",", ".", $qtdpedidaori),
							"qtdc" => 0,
							'status' => 'PENDENTE',
							"usuario" => $criadopor
						];
						SolmatController::inserirLoteCons($arrayInsertLoteCons);
						//gerar rateio

						// // não adiciona um credItio no destino pois e inserido o valor na quantidade inicial da fracao  
						// if(($rowconv["consometransf"] == 'Y' || $consomeun == 'Y') && $rowconv["imobilizado"] != 'Y'){
						//     $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlote'] = $idloteorigem;
						//     $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idlotefracao'] = $_idlotefracao;
						//     $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['tipoobjeto'] = 'lotefracao';
						//     $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['idobjeto'] = $_idlotefracao;
						//     $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['obs'] = 'Lote consumido na transferência da solicitacão de materiais';
						//     $_SESSION['arrpostbuffer']['ulc2']['i']['lotecons']['qtdd'] = $qtdpedidaori;
						// }
						// debug(5);
						// debug(6);
					}        
				}
				$sqlupdate="update solcomitem set idsolmatitem=".$idsolmatitem." where idsolcomitem=".$idsolcomitem;
				$resupdate = d::b()->query($sqlupdate) or die("Erro ao atualizar idsolmatitem na solcomitem: ". mysql_error() . "<p>SQL: ".$sqlupdate);
				// $_SESSION['arrpostbuffer']["solcomitem".$idsolcomitem]['u']['solcomitem']['idsolcomitem'] = $idsolcomitem;
				// $_SESSION['arrpostbuffer']["solcomitem".$idsolcomitem]['u']['solcomitem']['idsolmatitem'] = $idsolmatitem;
			}
			montatabdef();
		}
	}

}


/*
 if($status=='APROVADO' and $lote_statusold!='APROVADO' AND $iu=='u' AND !empty($exercicio) AND !empty($partida) ){
       
    $s="update lote set status='APROVADO' 
        where partida='".$partida."'
            and exercicio='".$exercicio."'
        and idlote <> ".$idloteU;
    $re = d::b()->query($s) or die("Erro ao alterar lotes da mesma partida para aprovado : ". mysql_error() . "<p>SQL: ".$s);  
   
 } 
 
 */


 /*   
$idunidade=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idunidade'];
$qtdpedida= $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['qtd'];
$idloteorigem=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idlote'];
$idlotefracaoori=$_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idlotefracaoorigem'];

if(!empty($idlotefracaoori)){
    
    
    $s="select u.convestoque,p.valconv
			from lotefracao f join lote l on(l.idlote=f.idlote)
            join unidade u on(u.idunidade=f.idunidade)
            join prodserv p on(p.idprodserv=l.idprodserv)
        where f.idlotefracao=".$idlotefracaoori;
    $re = d::b()->query($s) or die("Erro ao buscar informacoes da unidade de origem : ". mysql_error() . "<p>SQL: ".$s);  
    $rorig=mysqli_fetch_assoc($re);
    
    $convestdest=traduzid("unidade","idunidade","convestoque",$idunidade);
    if($rorig["valconv"]>1){
        if($rorig["convestoque"]=='Y' and $convestdest=='N'){
            $qtdpedida=  $qtdpedida*$rorig["valconv"];      
        }elseif($rorig["convestoque"]=='N' and $convestdest=='Y'){
             
             $qtdpedida= $qtdpedida/$rorig["valconv"]; 
             
        }
        //die($qtdpedida);
    } 
     
     
   
    $sqlf="INSERT INTO lotecons
	(idempresa,idlote,idlotefracao,idobjeto,tipoobjeto,qtdd,obs,criadopor,criadoem)
	values(".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idloteorigem.",".$idlotefracaoori.",".$_SESSION["_pkid"].",'lotefracao',".$qtdpedida.",'Lote Fracionado.','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
//die($sqlf);
    $resif = d::b()->query($sqlf) or die("Erro ao gerar partilha : ". mysql_error() . "<p>SQL: ".$sqlf);   
}
*/


/* Ao transferir um produto de venda da produção para o almoxarifado
 * Deve ser feita a transferencia da reserva para o lote do almoxarifado em forma de consumo
 */
/*
if (!empty(trim($idloteorigem)) and !empty(trim($idunidade))){
    
    //verifica se unidade de destino e almoxarifado
    $sql="select * from unidade where idunidade = ".$idunidade." and idtipounidade=3";
    $res = d::b()->query($sql) or die("Erro ao buscar se a unidade e almoxarifado : ". mysql_error() . "<p>SQL: ".$sql);  
    $qtdres= mysqli_num_rows($res);
    if($qtdres>0){
        //verificar se tem solicitação para o lote da formalizacao
        $sqls="select c.idlotecons,c.tipoobjeto,c.idobjeto,c.qtdsol 
                        from lote l join unidade u on(u.idunidade=l.idunidade and u.idtipounidade=5)
                    join lotecons c on(c.idlote = l.idlote and c.tipoobjeto='nfitem' and c.qtdsol is not null and  c.qtdsol !='' )
                where l.idlote=".$idloteorigem."
                and l.tipoobjetoprodpara = 'nfitem'";
        $ress = d::b()->query($sqls) or die("Erro ao buscar se o lote de origem e de produção: ". mysql_error() . "<p>SQL: ".$sqls);  
        $qtdress= mysqli_num_rows($ress); 
        if($qtdress>0){
            while($rowl=mysqli_fetch_assoc($ress)){
                
                $inslotecons = new Insert();
				$inslotecons->setTable("lotecons");				
				$inslotecons->idlote=$_SESSION["_pkid"];
				$inslotecons->tipoobjeto=$rowl['tipoobjeto'];
				$inslotecons->idobjeto=$rowl['idobjeto'];
				$inslotecons->qtdd=$rowl['qtdsol'];
				$inslotecons->qtdsol=$rowl['qtdsol'];
				$idlotecons=$inslotecons->save();   
           
    
            
            
            $sqlu="delete from lotecons where idlotecons=".$rowl['idlotecons'];
            $resu = d::b()->query($sqlu) or die("Erro ao retirar a reserva do lote da producao para o almoxarifado: ". mysql_error() . "<p>SQL: ".$sqlu); 
     
          // die($sqlux);
            }
            
        }
        
    }
    
    
}
*/
//echo($idloteorigem);die;

//echo($idlote);die;
/*if($iu=="i"){
	//echo("entrou: <br>");
	
	$vidprxproc =$_SESSION["insertid"];	

	//gera uma fraçao do produto com sua localização
	$sqlf="INSERT INTO lotefracao
	(idempresa,idlote,criadopor,criadoem)
	values(".$_SESSION["SESSAO"]["IDEMPRESA"].",".$_SESSION["_pkid"].",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

	$resif = d::b()->query($sqlf) or die("Erro ao gerar a Fração : ". mysql_error() . "<p>SQL: ".$sqlf);

}else
	if($iu=="u" and !empty($idlote)){
	$sql="select * from lotefracao where idlote =".$idlote;
	$res=d::b()->query($sql) or die("Erro ao gerar a Fração 2 : ". mysql_error() . "<p>SQL: ".$sql);
	$qtd= mysqli_num_rows($res);
	
	if($qtd<1){
		//gera uma fraçao do produto com sua localização
		$sqlf="INSERT INTO lotefracao
		(idempresa,idlote,criadopor,criadoem)
		values(".$_SESSION["SESSAO"]["IDEMPRESA"].",".$_SESSION["_pkid"].",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

		$resif = d::b()->query($sqlf) or die("Erro ao gerar a Fração : ". mysql_error() . "<p>SQL: ".$sqlf);
		
	}
}
*/

/*hermes volta da fracao

if (!empty(trim($idloteorigem)) and !empty(trim($idunidade))){

		$sqlmodunidade = "select m.modulo
		from "._DBCARBON."._modulo m 
		join unidadeobjeto o on(	
									m.modulo = o.idobjeto and 
									o.tipoobjeto='modulo' and 
									o.idobjeto like ('lote%') and 
									o.idunidade = ".$idunidade.")
		where m.ready='FILTROS';";

	//	die($sqlmodunidade);
	$res = d::b()->query($sqlmodunidade) or die("getProdutosFormalizacao: Erro: ".mysqli_error(d::b())."\n".$sql);

	while($r = mysqli_fetch_assoc($res)){
	//monta 2 estruturas json para finalidades (loops) d
		if (!empty($r['modulo'])){
			//$_GET['_modulo'] = $r['modulo'];
			$novaurl = './?_modulo='. $r['modulo'].'&_acao=u&idlote='.$_SESSION["_pkid"];
			?>
			<script>alert("Lote deslocado com sucesso.\nClique em OK para fechar essa janela.");window.close();window.opener.location.reload();</script>
			<?
			//exit();
		}
	}
}
 
 */
?>