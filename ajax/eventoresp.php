<?

require_once("../inc/php/validaacesso.php");
require_once("../inc/php/functions.php");
require_once("../model/fluxostatuspessoa.php");

$obs        = filter_input(INPUT_GET, "vobs");
$opcao      = filter_input(INPUT_GET, "vopcao");
$assina      = filter_input(INPUT_GET, "vassina");
$status     = filter_input(INPUT_GET, "vstatus");
$idevento   = filter_input(INPUT_GET, "videvento");
$versao   = filter_input(INPUT_GET, "vversao");
$datahora   = filter_input(INPUT_GET, "vdatahora");

$jsonConfig = $_POST["jsonconfig"];

if ($obs or $opcao == 'change' ) {
    //Caso exista comentário novo irá resetar o visualizado
    $sql = "UPDATE fluxostatuspessoa
               SET visualizado = 0
             WHERE idmodulo = ".$idevento." AND modulo = 'evento';";

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

    $sql = "UPDATE fluxostatuspessoa
            SET oculto = 1
            WHERE idmodulo = ".$idevento." AND modulo = 'evento'
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

    $sql = "UPDATE fluxostatuspessoa
            SET oculto = 0
            WHERE idmodulo = ".$idevento." AND modulo = 'evento'
            AND idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
            AND tipoobjeto = \"pessoa\";";

    $res = d::b()->query($sql) or die("Erro ao desocultar evento: ".mysqli_error(d::b()));
    
    echo('{"status":"success"}');

    return;
}

if (!empty($opcao) && $opcao == 'change') {

		if($assina == 'true'){
		$sql = "SELECT e.modulo, e.idmodulo FROM evento e JOIN carrimbo c on c.idobjeto = e.idmodulo and e.modulo = c.tipoobjeto and c.idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"]." and c.status = 'ATIVO' WHERE idevento = ".$idevento;
		$res = d::b()->query($sql);

		$total = mysqli_num_rows($res); 
		
		if($total == 0){
			echo 'pendente';
			return;
		}
			
	}
	
	
	 $sql = "UPDATE fluxostatuspessoa 
			    SET status = '".$status."'  
			  WHERE idmodulo = ".$idevento." AND modulo = 'evento'
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
		$sql = '';
		$sqlu = '';
		foreach ($arrstatuses as $key => $object) {			
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
									'".$object->ocultacri."' 	as ocultacri,
									'".$object->desocultacri."' as desocultacri,
									status  
							FROM 
									fluxostatuspessoa where idmodulo = ".$idevento." 
                                AND modulo = 'evento'
                                and status = '".$object->token."' ";
			$sqlu = 'union';
		} 
	
	
		$sqlt = "SELECT inicial, final, dono, ocultaind, desocultaind, exclui, restaura, oculta, desoculta, ocultacri, desocultacri, status from (".$sql.") a order by prioridade asc limit 1;";
		
		$res = d::b()->query($sqlt) or die("Erro ao obter prioridade / status 2: ".mysqli_error(d::b()));

		while ($r = mysqli_fetch_assoc($res)) {
			
			 $sql2 = "UPDATE evento set status = '".$r['status']."' where idevento = ".$idevento.";";

            $res = d::b()->query($sql2) or die("Erro ao Atualizar status do Evento: ".mysqli_error(d::b()));
			$sair = 1;
		}
		
			$sqlx = "SELECT inicial, final, dono, ocultaind, desocultaind, exclui, restaura, oculta, desoculta, status, ocultacri, desocultacri from (".$sql.")  a where status = '".$status."';";
		
			
			$res = d::b()->query($sqlx) or die("Erro ao Atualizar status do Evento: ".mysqli_error(d::b()));
			
			while ($r = mysqli_fetch_assoc($res)) {
					
				$cond = '';
				$condind = '';
				$condexc = '';			
				$virg = '';
				$virgind = '';
				$virgcri = '';
				
				if ($r['restaura'] == true){
					$cond .= " status = '".$tokeninicial."'";
					$virg = ',';
					$sqlt = "UPDATE evento SET ".$cond." WHERE idevento = ".$idevento." ;";
					$res = d::b()->query($sqlt) or die("Erro ao restaurar evento: ".mysqli_error(d::b()));
				}
				if ($r['ocultaind'] == true){
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
				
				if ($r['ocultacri'] == true){
					$condcri .= $virgcri." oculto = 1";
					$virgcri = ',';
				}
				if ($r['desocultacri'] == true){
					$condcri .= $virgcri." oculto = 0";
					$virgcri = ',';
				}
				
				// echo $r['status'];
				if ($cond != ''){
					$sqlt = "UPDATE fluxostatuspessoa SET ".$cond." WHERE idmodulo = ".$idevento." AND modulo = 'evento' and tipoobjeto = 'pessoa' ;";
					$res = d::b()->query($sqlt) or die("Erro ao atualizar fluxostatuspessoa1: ".mysqli_error(d::b()));
				  
				}
				if ($condind != ''){
					$sqlt = "UPDATE fluxostatuspessoa SET ".$condind." WHERE idmodulo = ".$idevento." AND modulo = 'evento' and idobjeto = '".$_SESSION["SESSAO"]["IDPESSOA"]."' and tipoobjeto = 'pessoa';";
					$res = d::b()->query($sqlt) or die("Erro ao atualizar fluxostatuspessoa2: ".mysqli_error(d::b()));
				  
				}
				if ($condcri != ''){
					$sqlt = "UPDATE evento e join fluxostatuspessoa r on e.idevento = r.idmodulo AND modulo = 'evento' and e.idpessoa = r.idobjeto and r.tipoobjeto = 'pessoa'  SET ".$condcri." WHERE e.idevento = ".$idevento.";";
					$res = d::b()->query($sqlt) or die("Erro ao atualizar fluxostatuspessoa3: ".mysqli_error(d::b()));
				  
				}

				if ($condexc == 1){
					$sqlt = "DELETE FROM fluxostatuspessoa  WHERE idmodulo = ".$idevento." AND modulo = 'evento';";
					$res = d::b()->query($sqlt) or die("Erro ao excluir evento: ".mysqli_error(d::b()));

					$sqlt = "DELETE FROM evento  WHERE ideventopai = ".$idevento.";";
					$res = d::b()->query($sqlt) or die("Erro ao excluir evento: ".mysqli_error(d::b()));

					$sqlt = "DELETE FROM evento  WHERE idevento = ".$idevento.";";
					$res = d::b()->query($sqlt) or die("Erro ao excluir evento: ".mysqli_error(d::b()));
				}
			}


		
		if ($sair != 1){

			if ($status == 'danger') {

				$sql = "SELECT p.nomecurto 
						FROM pessoa p 
						WHERE p.idpessoa 
						IN (SELECT r.idobjeto 
							FROM fluxostatuspessoa r
							WHERE r.idmodulo = ".$idevento."
                            AND r.modulo = 'evento'
							AND r.status != 'info'
							AND r.tipoobjeto = \"pessoa\");";

				$res = d::b()->query($sql) or die("Erro ao obter usuários: ".mysqli_error(d::b()));

				while ($r = mysqli_fetch_assoc($res)) {
					criaHistoricoStatus('info', ($r["nomecurto"]), $obs, $idevento);
				}

				$sql = "UPDATE fluxostatuspessoa 
						SET status = '".$tokeninicial."', oculto = 0
						WHERE idmodulo = ".$idevento." AND r.modulo = 'evento'
						AND tipoobjeto = \"pessoa\";";

				$res = d::b()->query($sql) or die("Erro ao alterar status: ".mysqli_error(d::b()));

				$sql = "UPDATE evento 
						SET status = '".$tokeninicial."'
						WHERE idevento = ".$idevento.";";

				$res = d::b()->query($sql) or die("Erro ao alterar status: ".mysqli_error(d::b()));

			}elseif ($status == 'cancelado') {

				$sql = "UPDATE fluxostatuspessoa 
						SET oculto = 1
						WHERE idmodulo = ".$idevento." AND modulo = 'evento'
						AND tipoobjeto = \"pessoa\";";

				$res = d::b()->query($sql) or die("Erro ao alterar status: ".mysqli_error(d::b()));
				
				 $sql = "UPDATE fluxostatuspessoa 
						SET status = 'cancelado', oculto = 1
						WHERE idmodulo = ".$idevento." AND modulo = 'evento'
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

				$sql = "  SELECT r.idfluxostatuspessoa,
									p.nomecurto,
									r.status,
									p.idpessoa
								FROM fluxostatuspessoa r, pessoa p
								WHERE p.idpessoa = r.idobjeto
								AND r.tipoobjeto = 'pessoa'
								AND r.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
								AND r.idmodulo = ".$idevento." AND r.modulo = 'evento'";
				
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

				$sql = "UPDATE fluxostatuspessoa
						SET oculto = 1, status = '".$status."'
						WHERE idmodulo = ".$idevento." AND modulo = 'evento'
						AND idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
						AND tipoobjeto = \"pessoa\";";

				$res = d::b()->query($sql) or die("Erro ao ocultar evento: ".mysqli_error(d::b()));

			} else {

				$sql = "UPDATE fluxostatuspessoa
						SET status = '".$status."'
						WHERE idmodulo = ".$idevento." AND modulo = 'evento'
						AND idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
						AND tipoobjeto = \"pessoa\";";

				$res = d::b()->query($sql) or die("Erro ao alterar status: ".mysqli_error(d::b()));
			}
	
		atualizaStatusEvento($idevento);
		}

    
    } else {
        die('Usuário não encontrado!');
    }

}else if (!empty($opcao) && $opcao == 'excluicomentario' && (!empty($datahora)) && !empty($idevento)) {

	$sql = "UPDATE 
				evento
			SET 
				jsonhistorico = JSON_REMOVE(jsonhistorico, replace(REPLACE(JSON_SEARCH(jsonhistorico, 'one', '".$datahora."'), '.data',''),'\"',''))

            where idevento = ".$idevento.";";

		$res = d::b()->query($sql) or die("Erro ao verificar excluir comentário: ".mysqli_error(d::b()));
}else if (!empty($opcao) && $opcao == 'adicionar' && (!empty($idevento) and $idevento != 'undefined')) {
    
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
    die('fluxostatuspessoa: Variável POST não enviada corretamente!');
} else {

    $sqlEvento = "  SELECT e.status
                    FROM evento e
                    WHERE e.idevento = ".$idevento."
                    AND e.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."";
        
    $sqlPessoa = "SELECT r.idfluxostatuspessoa,
                p.nomecurto,
                r.status,
                p.idpessoa,
                r.idobjetoext,
                r.tipoobjetoext,
                r.visualizado,
                r.inseridomanualmente
            FROM fluxostatuspessoa r, pessoa p
            WHERE p.idpessoa = r.idobjeto
            AND r.tipoobjeto = 'pessoa'
            AND r.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
            AND r.idmodulo = ".$idevento." AND modulo = 'evento'";

    $sqlImGrupo = "SELECT r.idfluxostatuspessoa,
                i.grupo,
                r.status,
                r.visualizado,
                i.idimgrupo
            FROM fluxostatuspessoa r
            JOIN imgrupo i ON i.idimgrupo = r.idobjetoext AND  r.tipoobjeto = 'imgrupo' AND r.idmodulo = ".$idevento."  ANDr. modulo = 'evento'
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
            FROM fluxostatuspessoa r
            WHERE r.idmodulo = ".$idevento." AND modulo = 'evento'
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

   $sql = "select 
				getEventoStatusConfig(e.ideventotipo,CONCAT('\"',e.versao,'\"'),'".$status."', false,'status') AS status
			FROM 
				evento e
			WHERE 
				e.idevento = '".$idevento."'";
	
	
    $res = d::b()->query($sql);

    while ($r = mysqli_fetch_assoc($res)) {
        $statusdesc = $r['status'];
    }
	
	
    if (empty($nome)) {
        $nome = $_SESSION["SESSAO"]["NOMECURTO"];
    }
	
	if ($obs) {
		 $novoHistorico = "{\"nome\": \"".$nome."\",
                       \"status\": \"".$statusdesc."\", 
                       \"obs\": \"".$obs."\", 
                       \"data\": \"".(new \DateTime())->format('Y-m-d H:i:s')."\"}";

	
	}else{
		 $novoHistorico = "{\"nome\": \"".$nome."\",
                       \"status\": \"".$statusdesc."\", 
                       \"obs\": \"".$statusdesc."\", 
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
	
	$res = d::b()->query($sql) or die("Erro carregar listaStatusEventos eventoresp: ".mysqli_error(d::b()));

        $result = array();

	while($r = mysqli_fetch_assoc($res)) {
		
		$result = $r["status"];
	}	
	
	 $novo = json_encode($result,JSON_UNESCAPED_UNICODE);
	 
	 return ($result);
	 
 }
?>