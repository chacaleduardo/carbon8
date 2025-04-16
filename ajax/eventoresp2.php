<?

require_once("../inc/php/validaacesso.php");
require_once("../inc/php/functions.php");
require_once("../model/fluxostatuspessoa.php");

$obs        = filter_input(INPUT_GET, "vobs");
$opcao      = filter_input(INPUT_GET, "vopcao");
$status     = filter_input(INPUT_GET, "vstatus");
$idevento   = filter_input(INPUT_GET, "videvento");
$versao   = filter_input(INPUT_GET, "vversao");

$jsonConfig = $_POST["jsonconfig"];

if (empty($obs)) {
/*
    if ($status == 'warning') {
        $obs = 'executou';
    }

    if ($status == 'info') {
        $obs = 'aguardando';
    }

    if ($status == 'success') {
        $obs = 'concluiu';
    }

    if ($status == 'danger') {
        $obs = 'reabriu o evento';
    }

    if ($status == 'finalizado') {
        $obs = 'finalizou o evento';
    }
	
	if ($status == 'cancelado') {
        $obs = 'evento cancelado';
    }
    */
} else {
    //Caso exista comentário novo irá resetar o visualizado
    $sql = "UPDATE eventoresp
            SET visualizado = 0
            WHERE idevento = ".$idevento.";";

    $res = d::b()->query($sql) or die("Erro ao resetar visualizados do evento: ".mysqli_error(d::b()));
}

//SABER QUAL TOKEN INICIAL DO EVENTO
if (!empty($idevento)){
	
    $sql = "select 
				getEventoStatusConfig(e.ideventotipo,CONCAT('\"',e.versao,'\"'),null, true,'token') AS tokeninicial
			FROM 
				evento e
			WHERE 
				e.idevento = '".$idevento."'";
	
	
    $res = d::b()->query($sql);

    while ($r = mysqli_fetch_assoc($res)) {
        $tokeninicial = $r['tokeninicial'];
    }
}

if (!empty($opcao) && !empty($idevento) && $opcao == 'ocultar') {

    $sql = "UPDATE eventoresp
            SET oculto = 1
            WHERE idevento = ".$idevento."
            AND idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
            AND tipoobjeto = \"pessoa\";";

    $res = d::b()->query($sql) or die("Erro ao ocultar evento: ".mysqli_error(d::b()));
    
    echo('{"status":"success"}');

    return;
}

if (!empty($opcao) && !empty($idevento) && $opcao == 'permissoes') {

    criaPermissoes($idevento, $jsonConfig,$tokeninicial);

    $sql = "SELECT * FROM evento WHERE ideventopai = ".$idevento;
    $res = d::b()->query($sql);

    while ($r = mysqli_fetch_assoc($res)) {
        criaPermissoes($r["idevento"], $jsonConfig,$tokeninicial);
    }

}

if (!empty($opcao) && !empty($idevento) && $opcao == 'desocultar') {

    $sql = "UPDATE eventoresp
            SET oculto = 0
            WHERE idevento = ".$idevento."
            AND idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
            AND tipoobjeto = \"pessoa\";";

    $res = d::b()->query($sql) or die("Erro ao desocultar evento: ".mysqli_error(d::b()));
    
    echo('{"status":"success"}');

    return;
}

if (!empty($opcao) && $opcao == 'change') {
	
	echo $sql = "UPDATE 
				eventoresp 
			SET 
				status = '".$status."'  
			 WHERE 	
				idevento = ".$idevento."
				AND idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
				AND tipoobjeto = \"pessoa\"; ;";
    
	d::b()->query($sql) or die("Erro ao atualizar evento resp ".mysqli_error(d::b()));
    //Criando Objeto inicial no historico
    $sql = "SELECT JSON_TYPE(jsonhistorico) FROM evento WHERE idevento =".$idevento.";";
    $res = d::b()->query($sql) or die("Erro ao verificar jsonhistorico 2: ".mysqli_error(d::b()));
    $r = mysqli_fetch_assoc($res);
   
    if ($r["JSON_TYPE(jsonhistorico)"] != 'OBJECT') {
      
        $historico = "{\"historico\": []}";
        $sql = 'UPDATE evento SET jsonhistorico = CAST(\''.$historico.'\' AS JSON) WHERE idevento ='.$idevento.';';
        $res = d::b()->query($sql) or die("Erro ao atualizar jsonhistorico: ".mysqli_error(d::b()));

    }

    if (!empty($_SESSION["SESSAO"]["IDPESSOA"]) && !empty($status)) {
		

        criaHistoricoStatus($status, $_SESSION["SESSAO"]["NOMECURTO"], $obs, $idevento);
		
		$arrstatuses = json_decode(listaStatusEventos($versao, $idevento));
		print_r($arrstatuses);
		$sql = '';
		$sqlu = '';
		foreach ($arrstatuses as $key => $object) {
			//echo $object->token;
			//echo $object->color;
			//echo $object->prioridade;
			//echo $object->color;
			
			$sql .= $sqlu." SELECT 
									'".$object->inicial."' 		as inicial,
									'".$object->final."' 		as final,
									'".$object->prioridade."' 	as prioridade,
									'".$object->dono."'			as dono,
									'".$object->ocultaind."' 	as ocultaind,
									'".$object->desocultaind."' as desocultaind,
									'".$object->exclui."' 		as exclui,
									'".$object->restaura."' 	as restaura,
									'".$object->oculta."' 		as oculta,
									'".$object->desoculta."' 	as desoculta,
									status  
							FROM 
									eventoresp where idevento = ".$idevento." and status = '".$object->token."' ";
			$sqlu = 'union';
		} 
	
	
		$sqlt = "SELECT inicial, final, dono, ocultaind, desocultaind, exclui, restaura, oculta, desoculta, status from (".$sql.") a order by prioridade asc limit 1;";
		echo '<pre>'.$sqlt .'</pre>';
		$res = d::b()->query($sqlt) or die("Erro ao obter prioridade / status 2: ".mysqli_error(d::b()));

		while ($r = mysqli_fetch_assoc($res)) {
			
			 $sql = "UPDATE evento set status = '".$r['status']."' where idevento = ".$idevento.";";

            $res = d::b()->query($sql) or die("Erro ao Atualizar status do Evento: ".mysqli_error(d::b()));
			$sair = 1;
			
			$cond = '';
			$condind = '';
			$condexc = '';			
			$virg = '';
			if ($r['restaura'] == true){
				$cond .= " status = '".$tokeninicial."'";
				$virg = ',';
			}
			if ($r['ocultaind'] == 1){
				$condind .= $virgind." oculto = 1";
				$virgind = ',';
			}
			if ($r['desocultaind'] == true){
				$condind .= $virgind." oculto = 0";
				$virgind = ',';
			}
			
			if ($r['exclui'] == true){
				$condexc = 1;
			}
			if ($r['oculta'] == true){
				$cond .= $virg." oculto = 1";
				$virg = ',';
			}
			if ($r['desoculta'] == true){
				$cond .= $virg." oculto = 0";
				$virg = ',';
			}
			
				$sqlt = "SELECT status from evento where idevento = ".$idevento.";";
			
				$res = d::b()->query($sqlt) or die("Erro ao obter prioridade / status 3: ".mysqli_error(d::b()));
				while ($r = mysqli_fetch_assoc($res)) {
				  // echo $r['status'];
				  if ($cond != ''){
					  $sqlt = "UPDATE eventoresp SET ".$cond." WHERE idevento = ".$idevento." and tipoobjeto = 'pessoa' ;";
					  $res = d::b()->query($sqlt) or die("Erro ao atualizar eventoresp: ".mysqli_error(d::b()));
					  
				  }
				   if ($condind != ''){
					  $sqlt = "UPDATE eventoresp SET ".$condind." WHERE idevento = ".$idevento." and idobjeto = '".$_SESSION["SESSAO"]["IDPESSOA"]."' and tipoobjeto = 'pessoa';";
					  $res = d::b()->query($sqlt) or die("Erro ao atualizar eventoresp: ".mysqli_error(d::b()));
					  
				  }
				  
				  if ($condexc == 1){
					  $sqlt = "DELETE FROM eventoresp  WHERE idevento = ".$idevento.";";
					  $res = d::b()->query($sqlt) or die("Erro ao excluir evento: ".mysqli_error(d::b()));
					  
					   $sqlt = "DELETE FROM evento  WHERE ideventopai = ".$idevento.";";
					  $res = d::b()->query($sqlt) or die("Erro ao excluir evento: ".mysqli_error(d::b()));
					  
					   $sqlt = "DELETE FROM evento  WHERE idevento = ".$idevento.";";
					  $res = d::b()->query($sqlt) or die("Erro ao excluir evento: ".mysqli_error(d::b()));
				  }
				  
				  
				}
			


		}
		if ($sair != 1){

			if ($status == 'danger') {

				$sql = "SELECT p.nomecurto 
						FROM pessoa p 
						WHERE p.idpessoa 
						IN (SELECT r.idobjeto 
							FROM eventoresp r
							WHERE r.idevento = ".$idevento."
							AND r.status != 'info'
							AND r.tipoobjeto = \"pessoa\");";

				$res = d::b()->query($sql) or die("Erro ao obter usuários: ".mysqli_error(d::b()));

				while ($r = mysqli_fetch_assoc($res)) {
					criaHistoricoStatus('info', ($r["nomecurto"]), $obs, $idevento);
				}

				$sql = "UPDATE eventoresp 
						SET status = '".$tokeninicial."', oculto = 0
						WHERE idevento = ".$idevento."
						AND tipoobjeto = \"pessoa\";";

				$res = d::b()->query($sql) or die("Erro ao alterar status: ".mysqli_error(d::b()));

				$sql = "UPDATE evento 
						SET status = '".$tokeninicial."'
						WHERE idevento = ".$idevento.";";

				$res = d::b()->query($sql) or die("Erro ao alterar status: ".mysqli_error(d::b()));

			}elseif ($status == 'cancelado') {

				$sql = "UPDATE eventoresp 
						SET oculto = 1
						WHERE idevento = ".$idevento."
						AND tipoobjeto = \"pessoa\";";

				$res = d::b()->query($sql) or die("Erro ao alterar status: ".mysqli_error(d::b()));
				
				 $sql = "UPDATE eventoresp 
						SET status = 'cancelado', oculto = 1
						WHERE idevento = ".$idevento."
						AND idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
						AND tipoobjeto = \"pessoa\";";

				$res = d::b()->query($sql) or die("Erro ao alterar status: ".mysqli_error(d::b()));
				

			   $sql2 = "UPDATE evento 
						SET status = 'cancelado'
						WHERE idevento = ".$idevento.";";  

				$res = d::b()->query($sql2) or die("Erro ao alterar status: ".mysqli_error(d::b()));

			}  elseif ($status == 'finalizar') {

				$result = "error";
				$concluido = false;
				$executando = false;

				$sql = "  SELECT r.ideventoresp,
									p.nomecurto,
									r.status,
									p.idpessoa
								FROM eventoresp r, pessoa p
								WHERE p.idpessoa = r.idobjeto
								AND r.tipoobjeto = 'pessoa'
								AND r.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
								AND r.idevento = ".$idevento;
				
				$res = d::b()->query($sql) or die("Erro carregar configuracao Pessoa 2: ".mysqli_error(d::b()));

				while ($r = mysqli_fetch_assoc($res)) {

					if ($r["status"] == 'warning') {
						$executando = true;
					}

					if ($r["status"] == 'success') {
						$concluido = true;
					}
					
				}

				if ($concluido && !$executando) {

					$sql = 'UPDATE evento e
							SET e.status = "FINALIZADO", e.finalizadoem = now()
							WHERE e.idevento = '.$idevento.'
							AND e.idempresa = '.$_SESSION["SESSAO"]["IDEMPRESA"].';';

					$res = d::b()->query($sql) or die("Erro ao finalizar evento: ".mysqli_error(d::b()));                 
					$result = "success";
					
				}

				echo $JSON->encode($result);
				return;

			} elseif ($status == 'success') {

				$sql = "UPDATE eventoresp
						SET oculto = 1, status = '".$status."'
						WHERE idevento = ".$idevento."
						AND idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
						AND tipoobjeto = \"pessoa\";";

				$res = d::b()->query($sql) or die("Erro ao ocultar evento: ".mysqli_error(d::b()));

			} else {

				$sql = "UPDATE eventoresp
						SET status = '".$status."'
						WHERE idevento = ".$idevento."
						AND idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
						AND tipoobjeto = \"pessoa\";";

				$res = d::b()->query($sql) or die("Erro ao alterar status: ".mysqli_error(d::b()));
			}
	
		atualizaStatusEvento($idevento);
		}

    
    } else {
        die('Usuário não encontrado!');
    }

} else if (!empty($opcao) && $opcao == 'adicionar' && (!empty($idevento) and $idevento != 'undefined')) {
    
    //Criando Objeto inicial no historico
     $sql = "SELECT JSON_TYPE(jsonhistorico) FROM evento WHERE idevento =".$idevento.";";
    $res = d::b()->query($sql) or die("Erro ao verificar jsonhistorico 1: ".mysqli_error(d::b()));
    $r = mysqli_fetch_assoc($res);
    
    if ($r["JSON_TYPE(jsonhistorico)"] != 'OBJECT') {
        

        $historico = "{\"historico\": []}";
        $sql = 'UPDATE evento SET jsonhistorico = CAST(\''.$historico.'\' AS JSON) WHERE idevento ='.$idevento.';';
        $res = d::b()->query($sql) or die("Erro ao atualizar jsonhistorico: ".mysqli_error(d::b()));

    }
    
	if ($obs){
		criaHistoricoStatus($status, $_SESSION["SESSAO"]["NOMECURTO"], $obs, $idevento);
	}
}

if (empty($idevento) ) {
    die('Eventoresp: Variável POST não enviada corretamente!');
} else {

    $sqlEvento = "  SELECT e.status
                    FROM evento e
                    WHERE e.idevento = ".$idevento."
                    AND e.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."";
        
    $sqlPessoa = "SELECT r.ideventoresp,
                p.nomecurto,
                r.status,
                p.idpessoa,
                r.idobjetoext,
                r.tipoobjetoext,
                r.visualizado,
                r.inseridomanualmente
            FROM eventoresp r, pessoa p
            WHERE p.idpessoa = r.idobjeto
            AND r.tipoobjeto = 'pessoa'
            AND r.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
            AND r.idevento = ".$idevento;

    $sqlImGrupo = "SELECT r.ideventoresp,
                i.grupo,
                r.status,
                r.visualizado,
                i.idimgrupo
            FROM eventoresp r
            JOIN imgrupo i ON i.idimgrupo = r.idobjetoext AND  r.tipoobjeto = 'imgrupo' AND r.idevento = ".$idevento."
            WHERE r.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."";
           
    $resPessoa = d::b()->query($sqlPessoa) or die("Erro carregar configuracao Pessoa 1: ".mysqli_error(d::b()));
    $resImGrupo = d::b()->query($sqlImGrupo) or die("Erro carregar configuracao Setor: ".mysqli_error(d::b()));
    $resEvento = d::b()->query($sqlEvento) or die("Erro carregar evento: ".mysqli_error(d::b()));
    
    $evento = mysqli_fetch_assoc($resEvento);
    $statusEvento = $evento["status"] ;
    
    $result = array();
    
    $i = 0;

    while($r = mysqli_fetch_assoc($resPessoa)) {

        $result[$i]["value"]                = ($r["idpessoa"]);
        $result[$i]["label"]                = ($r["nomecurto"]);
        $result[$i]["tipo"]                 = "pessoa";
        $result[$i]["idobjetoext"]          = ($r["idobjetoext"]);
        $result[$i]["tipoobjetoext"]        = ($r["tipoobjetoext"]);
        $result[$i]["inseridomanualmente"]  = ($r["inseridomanualmente"]);
        $result[$i]["status"]               = ($r["status"]);
        $result[$i]["visualizado"]          = ($r["visualizado"]);
        $result[$i]["statusevento"]         = ($statusEvento);
        
        $i++;
    }

    while($r = mysqli_fetch_assoc($resImGrupo)) {

        $result[$i]["value"]                = ($r["idimgrupo"]);
        $result[$i]["label"]                = ($r["grupo"]);
        $result[$i]["tipo"]                 = "imgrupo";
        $result[$i]["status"]               = ($r["status"]);
        $result[$i]["statusevento"]         = ($statusEvento);
        $result[$i]["visualizado"]          = ($r["visualizado"]);

        $i++;
    }

    //echo json_encode($result);
    echo $JSON->encode($result);

}

function atualizaStatusEvento($idevento) {

    $sql = "SELECT r.status
            FROM eventoresp r
            WHERE r.idevento = ".$idevento."
            AND r.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."";
           
    $res = d::b()->query($sql) or die("Erro carregar status: ".mysqli_error(d::b()));
   
    $result = array();
    
    $sqlEvento = "  SELECT e.status
                    FROM evento e
                    WHERE e.idevento = ".$idevento."
                    AND e.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."";
           
    $resEvento = d::b()->query($sqlEvento) or die("Erro carregar evento: ".mysqli_error(d::b()));
    $evento = mysqli_fetch_assoc($resEvento);
    $statusEvento = $evento["status"];

    $existeExecutando = false;
    $existeSomenteInfo = true;

    while ($r = mysqli_fetch_assoc($res)) {

        $statusFuncionario = ($r["status"]);

        if ($statusFuncionario == 'warning' && $statusEvento == 'INFO') {
            $existeExecutando = true;
            $existeSomenteInfo = false;
            $statusEvento = 'WARNING';
        }

        if ($statusFuncionario == 'warning' && $statusEvento == 'SUCCESS') {
            $existeExecutando = true;
            $existeSomenteInfo = false;
            $statusEvento = 'WARNING';
        }

        if ($statusFuncionario == 'warning' && $statusEvento == 'WARNING') {
            $existeExecutando = true;
            $existeSomenteInfo = false;
            $statusEvento = 'WARNING';
        }

        if ($statusFuncionario == 'success' && 
            !$existeExecutando && 
            ($statusEvento == 'info' || $statusEvento == 'WARNING')) {
                
            $existeSomenteInfo = false;
            $statusEvento = 'SUCCESS';

        }
		if ($statusFuncionario == 'cancelado' && $statusEvento == 'cancelado') {
            $existeExecutando = true;
            $existeSomenteInfo = false;
            $statusEvento = 'cancelado';
        }
    }

    if ($existeSomenteInfo) {
        $statusEvento = 'INFO';
    }

  $sql = 'UPDATE evento e
            SET e.status = "'.$statusEvento.'"
            WHERE e.idevento = '.$idevento.'
            AND e.idempresa = '.$_SESSION["SESSAO"]["IDEMPRESA"].';';

    $res = d::b()->query($sql) or die("Erro ao alterar status: ".mysqli_error(d::b()));
    
}

function criaHistoricoStatus($status, $nome, $obs, $idevento) {

    if (empty($nome)) {
        $nome = $_SESSION["SESSAO"]["NOMECURTO"];
    }
	
	if ($obs) {
		 $novoHistorico = "{\"nome\": \"".$nome."\",
                       \"status\": \"".$status."\", 
                       \"obs\": \"".$obs."\", 
                       \"data\": \"".(new \DateTime())->format('Y-m-d H:i:s')."\"}";

	
	}else{
		 $novoHistorico = "{\"nome\": \"".$nome."\",
                       \"status\": \"".$status."\", 
                       \"obs\": \"".$status."\", 
                       \"data\": \"".(new \DateTime())->format('Y-m-d H:i:s')."\"}";

	}



        $sql = 'UPDATE evento SET jsonhistorico = (SELECT JSON_ARRAY_APPEND(jsonhistorico, \'$.historico\', CAST(\''.$novoHistorico.'\' AS JSON))) WHERE idevento ='.$idevento.';';

        $res = d::b()->query($sql) or die("Erro ao alterar status: ".mysqli_error(d::b()));
  
}


function criaPermissoes($idEvento, $jsonConfig,$tokeninicial) {

 
    if (!empty($jsonConfig)) {

        $deleter = new EventoResponsavel();
        $deleter->idevento = $idEvento;
        $deleter->deletePorIDEvento();
        
        if (array_key_exists("permissoes", $jsonConfig)) {
            $permissoes = $jsonConfig["permissoes"];
            
            if (array_key_exists("setores", $permissoes) || 
                array_key_exists("funcionarios", $permissoes)) {
                
                if (array_key_exists("setores", $permissoes)) {

                    $imgrupos = $permissoes["setores"];
                    
                    foreach ($imgrupos as $imgrupo) {
                    
                        $responsavel = new EventoResponsavel();

                        $responsavel->idpessoa = $imgrupo["value"];
                        $responsavel->idobjeto = $imgrupo["value"];
                        $responsavel->tipoobjeto = "imgrupo";
                        $responsavel->idobjetoext = $imgrupo["value"];
                        $responsavel->status = $tokeninicial;
                        $responsavel->idevento = $idEvento;
                        $responsavel->idempresa = $_SESSION["SESSAO"]["IDEMPRESA"];
                        $responsavel->criadoem = date('Y-m-d H:i:s');
                        $responsavel->criadopor = $_SESSION["SESSAO"]["USUARIO"];
                        $responsavel->alteradoem = date('Y-m-d H:i:s');
                        $responsavel->alteradopor = $_SESSION["SESSAO"]["USUARIO"];
                        $responsavel->inseridomanualmente = "S";
                        $responsavel->visualizado = 0;

                        $responsavel->create();

                        importaPessoasImGrupo($idEvento, $imgrupo["value"], $permissoes,$tokeninicial);

                    }
                }
                
                if (array_key_exists("funcionarios", $permissoes)) {
                
                    $funcionarios = $permissoes["funcionarios"];
                    
                    foreach ($funcionarios as $funcionario) {
                        
                        $responsavel = new EventoResponsavel();

                        $responsavel->idpessoa = $funcionario["value"];
                        $responsavel->idobjeto = $funcionario["value"];
                        $responsavel->idobjetoext = null;
                        $responsavel->tipoobjeto = "pessoa";
                        $responsavel->status = $tokeninicial;
                        $responsavel->idevento = $idEvento;
                        $responsavel->idempresa = $_SESSION["SESSAO"]["IDEMPRESA"];
                        $responsavel->criadoem = date('Y-m-d H:i:s');
                        $responsavel->criadopor = $_SESSION["SESSAO"]["USUARIO"];
                        $responsavel->alteradoem = date('Y-m-d H:i:s');
                        $responsavel->alteradopor = $_SESSION["SESSAO"]["USUARIO"];
                        $responsavel->inseridomanualmente = "S";
                        $responsavel->visualizado = 0;

                        $responsavel->create();

                    }
                }
            }

        }
    }
}

function importaPessoasImGrupo($idEvento, $imgrupo, $permissoes,$tokeninicial) {
  
    $sql = "SELECT idpessoa, idimgrupo FROM imgrupopessoa WHERE idimgrupo = ".$imgrupo.";";
    
    $res = d::b()->query($sql) or die("Erro ao carregar pessoas: ".mysqli_error(d::b()));
    
    while($r = mysqli_fetch_assoc($res)) {
         
        $adicionado = false;
        
        if (array_key_exists("funcionarios", $permissoes)) {
                
            $funcionarios = $permissoes["funcionarios"];
            
            foreach ($funcionarios as $funcionario) {
                if ($funcionario["value"] == $r['idpessoa']) {
                    $adicionado = true;
                }
            }
        }
        
        if (!$adicionado) {

            $responsavel = new EventoResponsavel();

            $responsavel->idpessoa = $r["idpessoa"];
            $responsavel->idobjeto = $r["idpessoa"];
            $responsavel->tipoobjeto = "pessoa";
            $responsavel->idobjetoext = $r["idimgrupo"];
            $responsavel->tipoobjetoext = "imgrupo";
            $responsavel->status = $tokeninicial;
            $responsavel->idevento = $idEvento;
            $responsavel->idempresa = $_SESSION["SESSAO"]["IDEMPRESA"];
            $responsavel->criadoem = date('Y-m-d H:i:s');
            $responsavel->criadopor = $_SESSION["SESSAO"]["USUARIO"];
            $responsavel->alteradoem = date('Y-m-d H:i:s');
            $responsavel->alteradopor = $_SESSION["SESSAO"]["USUARIO"];
            $responsavel->inseridomanualmente = "N";
            $responsavel->visualizado = 0;

            $responsavel->create();

        }
    }
}
 
 
 function listaStatusEventos($versao, $idevento){
	 
	  $sql = "	select 
					JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(jconfig,concat('$[*].','\"','$versao','\"')), '$[0]'), '$.statuses') as status
				from 
					eventotipo et
				join 
					evento e on e.ideventotipo = et.ideventotipo
				where 
					e.idevento = '".$idevento."'";
	
	$res = d::b()->query($sql) or die("Erro carregar listaStatusEventos eventoresp2: ".mysqli_error(d::b()));

        $result = array();

	while($r = mysqli_fetch_assoc($res)) {
		
		$result = $r["status"];
	}	
	
	 $novo = json_encode($result,JSON_UNESCAPED_UNICODE);
	 
	 return ($result);
	 
 }
?>