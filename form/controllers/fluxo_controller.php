<?
require_once(__DIR__."/_controller.php");
require_once(__DIR__."/evento_controller.php");
require_once(__DIR__."/../../model/evento.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/log_query.php");
require_once(__DIR__."/../querys/lote_query.php");
require_once(__DIR__."/../querys/fluxo_query.php");
require_once(__DIR__."/../querys/sgdoc_query.php");
require_once(__DIR__."/../querys/evento_query.php");
require_once(__DIR__."/../querys/pessoa_query.php");
require_once(__DIR__."/../querys/nfitem_query.php");
require_once(__DIR__."/../querys/amostra_query.php");
require_once(__DIR__."/../querys/carimbo_query.php");
require_once(__DIR__."/../querys/imgrupo_query.php");
require_once(__DIR__."/../querys/carimbo_query.php");
require_once(__DIR__."/../querys/loteobj_query.php");
require_once(__DIR__."/../querys/loteativ_query.php");
require_once(__DIR__."/../querys/resultado_query.php");
require_once(__DIR__."/../querys/_auditoria_query.php");
require_once(__DIR__."/../querys/eventotipo_query.php");
require_once(__DIR__."/../querys/fluxostatus_query.php");
require_once(__DIR__."/../querys/fluxoobjeto_query.php");
require_once(__DIR__."/../querys/fluxostatus_query.php");
require_once(__DIR__."/../querys/formalizacao_query.php");
require_once(__DIR__."/../querys/unidadeobjeto_query.php");
require_once(__DIR__."/../querys/objetovinculo_query.php");
require_once(__DIR__."/../querys/fluxostatuslp_query.php");
require_once(__DIR__."/../querys/imgrupopessoa_query.php");
require_once(__DIR__."/../querys/fluxostatushist_query.php");
require_once(__DIR__."/../querys/fluxostatuspessoa_query.php");
require_once(__DIR__."/../querys/fluxostatushistobs_query.php");
require_once(__DIR__."/../querys/fluxostatushistmotivo_query.php");
require_once(__DIR__."/../querys/lotecons_query.php");
require_once(__DIR__."/../querys/_status_query.php");

//Controllers
require_once(__DIR__."/../controllers/_modulo_controller.php");
require_once(__DIR__."/../controllers/amostra_controller.php");

class FluxoController extends Controller {

	public static function inserirLog($idobjeto,$tipoobjeto,$tipolog)
	{
		$dadosLog = Array(
				'idempresa' => $_SESSION["SESSAO"]["IDEMPRESA"],
                'sessao' => session_id(),
                'log' => 'SUCESSO',
                'status' => 'SUCESSO',
                'info' => '',
                'criadoem' => sysdate(),
                'data' => sysdate(),
				"idobjeto" => $idobjeto,
				"tipoobjeto" => $tipoobjeto,
				"tipolog" => $tipolog);
		$results = LogController::inserir($dadosLog);

		if($results->error()){
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return 1;
		}

		return $results->numRows();
	}


	public static function validaModuloReal( $mod ){

		$idunidade = getUnidadePadraoModulo($mod);
		$moduloFormalizacao = getModuloTipo($mod, $idunidade);
	
		if(validaStatus($mod) == false && $moduloFormalizacao != 'formalizacao') {
			$moduloreal = getModReal($mod);
	
			if($mod != $moduloreal && !empty($moduloreal)){ 
				$mod = $moduloreal;
			}
		}
	
		return $mod;
	}
	
	public static function mostrarBotao ( $mod, $inId ) {
		$mostraBotao = true;
	
		if(strpos($mod, 'lote') !== false && $_POST['_primary'] == 'idlote') {
	
			$moduloPai = self::getDadosModuloPrincipal($inId);
	
			if($mod != $moduloPai && !empty($moduloPai)){ 
				$mod = $moduloPai; 
				$mostraBotao = false;
			} else {
				$mostraBotao = true;
			}
		}
	
		return [
			'mod' => $mod,
			'mostrarBotao' => $mostraBotao
		];
	}

	public static function buscarRotuloStatusFluxo($tabela, $primarykey, $idobjeto) {
		$results = SQL::ini(FluxoStatusQuery::buscarRotuloStatusFluxo(), [
			"tabela" => $tabela,
			"primarykey" => $primarykey,
			"idobjeto" => $idobjeto
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			return (count($results->data) > 0) ? $results->data[0] : "";
		}
	}

	//evento - insere os participantes definidos no eventotipo
	public static function insereParticipantes($idevento, $ideventotipo, $tokeninicial, $idcriador)
	{
		if(empty($ideventotipo))
		{
			$ideventotipo= traduzid('evento', 'idevento', 'ideventotipo', $idevento);
		}
			
		$participantes = SQL::ini(FluxoQuery::buscarParticipantesPorIdEventoEIdeventoTipo(),[
			'idevento' => $idevento,
			'ideventotipo' => $ideventotipo
		])::exec();
		
		$resultsetor = array();
		$resultpessoa = array();
		$i = 0;
		$j = 0;
				
		foreach($participantes->data as $participante)
		{
			if($participante['tipoobjeto'] == 'imgrupo')
			{
				$resultsetor[$i++] = $participante['idobjeto'];
			} else if($participante['tipoobjeto'] == 'pessoa')
			{
				$resultpessoa[$j++] = $participante['idobjeto'];
			}
		}
				
		if ($resultsetor) {
			$resultsetor = implode(",", $resultsetor);
		} else {
			$resultsetor = "''";
		}
		
		if ($resultpessoa) {
			$resultpessoa = implode(",", $resultpessoa);
		} else {
			$resultpessoa = "''";
		}
		
		$pessoasAtivasNoEvento = SQL::ini(PessoaQuery::buscarPessoasAtivasPorIdPessoaEGetIdEmpresa(), [
			'idpessoa' => $resultpessoa,
			'getidempresa' => getidempresa('p.idempresa', 'evento')
		])::exec();
	
		//Alterado para chamar a função que está no arquivo [model-evento] - . Busca os grupos Ativos que estão no Evento.
		$gruposAtivosNoEvento = SQL::ini(ImGrupoQuery::buscarGruposAtivosPorIdImGrupoEGetIdEmpresa(), [
			'idimgrupo' => $resultsetor,
			'getidempresa' => getidempresa('i.idempresa','evento')
		])::exec();
		
		/** 
		 * Busca o status do evento no momento
		*
		*/
		$evento = SQL::ini(EventoQuery::buscarEventoPorIdEventoEGetidEmpresa(), [
			'idevento' => $idevento,
			'getidempresa' => getidempresa('idempresa','evento')
		])::exec();

		$statusAtualDoEvento = $evento->data[0]['idfluxostatus'];
		
		$arrDestNotif = [];

		foreach($pessoasAtivasNoEvento->data as $pessoa)
		{
			
			if(self::verificarSePessoaPodeEntrarNoEvento($pessoa["idpessoa"], $ideventotipo, $statusAtualDoEvento))
			{
				EventoController::inserirEmFluxostatusPessoa($idcriador, $pessoa["idempresa"], $idevento, $pessoa["idpessoa"], 'pessoa', 'null', $tokeninicial, 0, 'null', 'null', 'insereParticipantes');
				// $this->inserefluxostatuspessoa($idevento, $pessoa["idempresa"], $pessoa["idpessoa"], 'pessoa', $tokeninicial, $idcriador, 'insereParticipantes');
				if($pessoa["idpessoa"] != $idcriador)
					$arrDestNotif[] = $pessoa["idpessoa"];
			}
		}

		if(count( $arrDestNotif ))
		{
			EventoController::notificarEvento($idevento, $arrDestNotif);
		}

		foreach($gruposAtivosNoEvento->data as $grupo)
		{
		
			if(self::verificarSePessoaPodeEntrarNoEvento($grupo["idimgrupo"], $ideventotipo, $statusAtualDoEvento))
			{
				EventoController::inserirEmFluxostatusPessoa($idcriador, $grupo["idempresa"], $idevento, $grupo["idimgrupo"], 'imgrupo', 'null', $tokeninicial);
				
				self::inserirPessoasDoGrupoEmFluxostatusPessoa($grupo["idimgrupo"], $idevento, $tokeninicial);
			}
		}
		
		//Insere as pessoas que tem que assinar na tabela carrimbo - Lidiane (04/06/2020)
		$pessoasQuePrecisamAssinar = SQL::ini(FluxoQuery::buscarPessoasQuePrecisamAssinarPorIdEventoTipo(), [
			'ideventotipo' => $ideventotipo
		])::exec();

		foreach($pessoasQuePrecisamAssinar->data as $pessoa)
		{
			$idobjeto = $pessoa['idobjeto'];

			$dadosCarimbo = [
				'idempresa' => $_SESSION['SESSAO']['IDEMPRESA'],
				'idpessoa' => $idobjeto,
				'idobjeto' => $idevento, 
				'tipoobjeto' => 'evento', 
				'idobjetoext' => $statusAtualDoEvento, 
				'tipoobjetoext' => 'idfluxostatus',
				'status' => 'PENDENTE', 
				'criadopor' => $_SESSION['SESSAO']['USUARIO'], 
				'criadoem' => 'now()', 
				'alteradopor' => $_SESSION['SESSAO']['USUARIO'], 
				'alteradoem' => 'now()'
			];

			$inserindoRegistroCarimbo = SQL::ini(CarimboQuery::inserir(), $dadosCarimbo)::exec();
			
		}
		
		//Alterado para chamar a função que está no arquivo [model-evento] - . Replace para inserir as pessoas do Grupo no Evento						
		EventoController::inserirPessoasDoGrupoNoEvento($idevento, $tokeninicial);
	}

	public static function verificarSePessoaPodeEntrarNoEvento($idPessoa, $idEventoTipo, $statusAtualDoEvento)
	{
		$permissao = SQL::ini(FluxoQuery::verificarSePessoaPodeEntrarNoEvento(), [
			'ideventotipo' => $idEventoTipo,
			'idpessoa' => $idPessoa
		])::exeC();

		return ($permissao->numRows() && ($permissao->data[0]['inidstatus'] == $statusAtualDoEvento || $permissao->data[0]['inidstatus'] == NULL));
	}

	public static function inserirPessoasDoGrupoEmFluxostatusPessoa($idImGrupo, $idEvento, $idFluxostatus)
	{

		$pessoasDoGrupo = SQL::ini(ImGrupoPessoaQuery::buscarPessoasDeUmGrupoQueNaoEstejamVinculadas(), [
			'idimgrupo' => $idImGrupo,
			'idevento' => $idEvento
		])::exec();

		$arrDestNotif = [];
		foreach($pessoasDoGrupo->data as $pessoa) {
			EventoController::inserirEmFluxostatusPessoa($_SESSION["SESSAO"]["IDPESSOA"], $pessoa["idempresa"], $idEvento, $pessoa["idpessoa"], 'pessoa', '', $idFluxostatus, 0, $idImGrupo, 'imgrupo', 'insereParticipantes');
			
			if($pessoa["idpessoa"] != $_SESSION["SESSAO"]["IDPESSOA"])
			$arrDestNotif[] = $pessoa["idpessoa"];
		}

		if (count( $arrDestNotif )) {
			EventoController::notificarEvento($idEvento, $arrDestNotif);
		}
	}

	public static function buscarStatusDosEventos($toJson = false)
	{
		$arrRetorno = [];
		$status = SQL::ini(FluxoQuery::buscarStatusDosEventos())::exec();

		if($status->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $status->errorMessage());
			return [];
		}

		if($toJson)
		{
			$virgula = '';
			$arrRetorno = "[";

			foreach($status->data as $key => $item)
			{
				$arrRetorno .= $virgula.'{"'.$item['rotulo'].'":"'.$item['rotulo'].'"}';
				$virgula = ',';
			}

			$arrRetorno .= "]";
		} else 
		{
			foreach($status->data as $item)
			{
				$arrRetorno[$item['rotulo']] = $item['rotulo'];
			}
		}

		return $arrRetorno;
	}

	public static function buscarStatusDoFluxo($tabela, $_primary, $idobjeto)
	{
		$statusDoFluxo = SQL::ini(FluxoQuery::buscarStatusDoFluxo(), [
			'tabela' => $tabela,
			'_primary' => $_primary,
			'idobjeto' => $idobjeto
		])::exec();

		if($statusDoFluxo->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $statusDoFluxo->errorMessage());
			return [];
		}

		return $statusDoFluxo->data[0];
	}

	// ----- FUNÇÕES -----

	public static function verificarInicio($_modulo, $_primary, $_idobjeto) {

		$idunidade = getUnidadePadraoModulo($_modulo);
        $moduloFormalizacao = getModuloTipo($_modulo, $idunidade);

		if((validaStatus($_modulo) == 0 && $moduloFormalizacao != 'formalizacao') && !empty($_modulo)) 
			return [
				"resposta" => "10",
				"formato" => "SEM FLUXO",
			];

        if($moduloFormalizacao != 'formalizacao') {
			$sqlAnd = self::getIdEventoTipo($_modulo, $_idobjeto);

            if($_modulo == 'evento') {

				$result = SQL::ini(FluxoStatusHistQuery::buscarQuantidadeFluxoStatusHistPorModulo(), [
					"modulo" => $_modulo,
					"idobjeto" => $_idobjeto
				])::exec();
		
				if($result->error()){
					return [
						"resposta" => "0",
						"formato" => __CLASS__."::".__FUNCTION__.": ".$result->errorMessage(),
					];
				}
				
            } else {
                $tabela = getModuloTab($_modulo);
				$result = SQL::ini(FluxoStatusQuery::buscarFluxoStatusPorTabela(), [
					"tabela" => $tabela,
					"primary" => $_primary,
					"idobjeto" => $_idobjeto
				])::exec();
		
				if($result->error()){
					return [
						"resposta" => "0",
						"formato" => __CLASS__."::".__FUNCTION__.": ".$result->errorMessage(),
					];
				}
            }

            $row = $result->data[0];

            //Validação do count com o Evento pois o Evento tem Inicio e este ao salvar o Evento passa para o próximo fluxo
            if((empty($row['idfluxostatus']) && $_modulo != 'evento') || ($row['contador'] == 0 && $_modulo == 'evento')){

				$status = empty($row['status'])
					? " AND (s.statustipo = 'INICIO' OR s.tipobotao = 'INICIO')"
					: " AND s.statustipo = '".$row['status']."'";

				$resFluxoInicial = SQL::ini(FluxoStatusQuery::buscarFluxoStatusInicialAtivoPorModulo(), [
					"modulo" => $_modulo,
					"status" => $status,
					"sqlAnd" => $sqlAnd['sql'],
					"idobjeto" => $_idobjeto,
				])::exec();
		
				if($resFluxoInicial->error()){
					return [
						"resposta" => "0",
						"formato" => __CLASS__."::".__FUNCTION__.": ".$resFluxoInicial->errorMessage(),
					];
				}

                $rowFluxo = $resFluxoInicial->data[0];
            
                //Atualiza o status da TAbela referente ao modulo atual
                if($_modulo == 'evento') {
					$rek = SQL::ini(EventoTipoQuery::buscarEventoTipoFluxoPorModuloEPessoa(), [
						"idevento" => $_idobjeto,
						"idpessoa" => $_SESSION["SESSAO"]["IDPESSOA"],
					])::exec();
			
					if($rek->error()){
						return [
							"resposta" => "0",
							"formato" => __CLASS__."::".__FUNCTION__.": ".$rek->errorMessage(),
						];
					}

                    $rok = $rek->data[0];

                    if($rok['posicao'] == "INICIO") {
						$eventoclass = new EVENTO();
                        $arrSt = explode(",",$rok['fluxo']);  

						if(!empty($arrSt['0']))
							$eventoclass->atualizaEventoStatus($rok['idfluxostatuspessoa'], $arrSt['0'], 'N', 'false');

                        //Verifica se tem idfluxostatus e tem etapa no Início
                        if($rowFluxo['idfluxostatus']){
                            self::inserirFluxoStatusHist($_modulo, $_idobjeto, $rowFluxo['idfluxostatus'], 'ATIVO');        
                            self::inserirFluxoStatusHist($_modulo, $_idobjeto, $arrSt['0'], 'PENDENTE');
                        } else{
                            self::inserirFluxoStatusHist($_modulo, $_idobjeto,  $rowFluxo['idfluxostatus'], 'PENDENTE');
                        }
                    }
                } else {
                     
                    if(!empty($rowFluxo['statustipo']) && !empty($rowFluxo['idfluxostatus'])){
                        self::inserirFluxoStatusHist($_modulo, $_idobjeto, $rowFluxo['idfluxostatus'], 'PENDENTE');
                        self::atualizaModuloTab($_modulo, $_primary, $_idobjeto, $rowFluxo['statustipo'], $rowFluxo['idfluxostatus']);
						$retorno = [
							"resposta" => $rowFluxo['statustipo'],
							"formato" => "999_i",
						];
                    } else {
						$retorno = [
							"resposta" => "10",
							"formato" => "Status Tipo Não Configurado",
						];
                    }
                }
            } else if($_modulo == 'evento'){
				$rek = SQL::ini(EventoTipoQuery::buscarEventoTipoFluxoPorModuloEPessoa(), [
					"idevento" => $_idobjeto,
					"idpessoa" => $_SESSION["SESSAO"]["IDPESSOA"],
				])::exec();
		
				if($rek->error()){
					return [
						"resposta" => "0",
						"formato" => __CLASS__."::".__FUNCTION__.": ".$rek->errorMessage(),
					];
				}

				$rok = $rek->data[0];

				if($rok['posicao'] == "INICIO") {
					$eventoclass = new EVENTO();
					$arrSt = explode(",",$rok['fluxo']);  

					if(!empty($arrSt['0']))
						$eventoclass->atualizaEventoStatus($rok['idfluxostatuspessoa'], $arrSt['0'], 'N', 'false');
				} 
            }

            self::insereAssinatura($_modulo, $_idobjeto, $rowFluxo['idfluxostatus']);

			return $retorno;
        } else {

			$res = SQL::ini(LoteAtivQuery::buscarFluxoFormalizacaoPorAtividade(), [
				"idobjeto" => $_idobjeto,
			])::exec();
	
			if($res->error()){
				return [
					"resposta" => "0",
					"formato" => __CLASS__."::".__FUNCTION__.": ".$res->errorMessage(),
				];
			}

			$resAberto = SQL::ini(FormalizacaoQuery::buscarStatusTipoFormalizacao(), [
				"idobjeto" => $_idobjeto,
			])::exec();
	
			if($resAberto->error()){
				return [
					"resposta" => "0",
					"formato" => __CLASS__."::".__FUNCTION__.": ".$res->errorMessage(),
				];
			}

			$row = $res->data[0];
			$rowAberto = $resAberto->data[0];

            if(empty($row['idfluxostatus']) && !empty($row['idloteativ'])) {
                self::inserirFluxoStatusHist($_modulo, $_idobjeto, $row['idfluxostatusprativ'], 'PENDENTE');
                if(empty($rowAberto['statustipo'])){
					$resAbertoid = SQL::ini(FormalizacaoQuery::buscarStatusAbertoInicioFormalizacao(), [
						"idformalizacao" => $_idobjeto,
					])::exec();
			
					if($resAbertoid->error()){
						return [
							"resposta" => "0",
							"formato" => __CLASS__."::".__FUNCTION__.": ".$resAbertoid->errorMessage(),
						];
					}

                    $statustipo = $resAbertoid->data[0]['statustipo'];
                    $idfluxostatus = $resAbertoid->data[0]['idfluxostatus'];
                } else {
                    $statustipo = $row['statustipo'];
                    $idfluxostatus = $row['idfluxostatusprativ'];
                }

                self::atualizaModuloTab($_modulo, $_primary, $_idobjeto, $statustipo, $idfluxostatus); 

                self::inserirFluxoStatusHist($_modulo, $_idobjeto, $idfluxostatus, 'ATIVO');

            //Caso seja restaurado insere no hist de acordo com a fórmula selecionada
            } elseif(!empty($row['idfluxostatus']) && ($rowAberto['statustipo'] == "ABERTO" || ($rowAberto['statustipo'] == "TRIAGEM" && $rowAberto['tipobotao'] == 'INICIO')) && $row['status'] != "CONCLUIDO" && !empty($row['idloteativ'])){
                
				$resCount = SQL::ini(FluxoStatusHistQuery::buscarQuantidadeFluxoStatusHistPorModuloEFluxoStatus(), [
					"idmodulo" => $_idobjeto,
					"modulo" => $_modulo,
					"idfluxostatus" => $row['idfluxostatusprativ'],
				])::exec();
		
				if($resCount->error()){
					return [
						"resposta" => "0",
						"formato" => __CLASS__."::".__FUNCTION__.": ".$resCount->errorMessage(),
					];
				}

                $rowCount = $resCount->data[0];   

                if($rowCount['contador'] == 0)
					self::inserirFluxoStatusHist($_modulo, $_idobjeto, $row['idfluxostatusprativ'], 'PENDENTE');                  
            }
        }
    }

	public static function carregarCadastroFluxo ($_modulo, $_primary, $_idobjeto, $_idempresa, $mostraBotao) {
		$status = [];
		$tabela 			= getModuloTab($_modulo);
		$idunidade 			= getUnidadePadraoModulo($_modulo, $_idempresa);
        $moduloFormalizacao = getModuloTipo($_modulo, $idunidade);

		if(empty($_modulo) || (validaStatus($_modulo) == 0 && $moduloFormalizacao != 'formalizacao')) return $status;

		//Validar se o lote possui formalizacao
		if($tabela == 'lote') {
			$resultLoteForm = SQL::ini(LoteQuery::buscarFormalizacaoPorLote(), [
				"idlote" => $_idobjeto,
			])::exec();
			
			if($resultLoteForm->numRows() > 0) {
				$status['esconderRestaurar'] = 'Y';
			}			
		}

		if($moduloFormalizacao == 'formalizacao') {

			$result = SQL::ini(FormalizacaoQuery::buscarFluxoFormalizacao(), [
				"idobjeto" => $_idobjeto,
				"modulo" => $_modulo,
			])::exec();

		} else {

			if($_modulo == 'evento') {
				$resultCampoOculto = SQL::ini(FluxostatuspessoaQuery::buscarCampoOcultoPessoa(), [
					"modulo" => $tabela,
					"idmodulo" => $_idobjeto,
					"idobjeto" => $_SESSION["SESSAO"]["IDPESSOA"],
				])::exec();		
				$ocultoPessoa = $resultCampoOculto->data[0]['oculto'];		
			}

            $sqlAnd = self::getIdEventoTipo($_modulo, $_idobjeto);

			$result = SQL::ini(FluxoQuery::buscarFluxoModulo(), [
				"clausula" => $sqlAnd['sql'],
				"tabela" => $tabela,
				"primary" => $_primary,
				"idobjeto" => $_idobjeto,
				"modulo" => $_modulo,
			])::exec();
        }
		
		//Valida se tem assinatura para acrescentar o ícone
		$historico = self::buscarHistoricoAssinaturaFluxo($_modulo, $_idobjeto);

		if(!$historico) $historico = null;        

		if($result->numRows() > 0) {
			foreach($result->data as $row){
				$ordem = $row['ordem'] + 2;
				
				$status['etapas'][$ordem][$row['ordemfs']]['ordem'] 				= $ordem;
				$status['etapas'][$ordem][$row['ordemfs']]['etapa'] 				= $row['etapa'];
				$status['etapas'][$ordem][$row['ordemfs']]['rotulo'] 				= $row['rotulo'];
				$status['etapas'][$ordem][$row['ordemfs']]['ocultar'] 				= $row['ocultar'];
				$status['etapas'][$ordem][$row['ordemfs']]['usuario'] 				= $_SESSION["SESSAO"]["USUARIO"];
				$status['etapas'][$ordem][$row['ordemfs']]['idstatus'] 				= $row['idstatus'];
				$status['etapas'][$ordem][$row['ordemfs']]['criadoem'] 				= dmahms($row['criadoem']);
				$status['etapas'][$ordem][$row['ordemfs']]['criadopor'] 			= $row['criadopor'];
				$status['etapas'][$ordem][$row['ordemfs']]['tipobotao'] 			= $row['tipobotao'];
				$status['etapas'][$ordem][$row['ordemfs']]['prioridade'] 			= $row['ordemfs'];
				$status['etapas'][$ordem][$row['ordemfs']]['statustipo'] 			= $row['statustipo'];
				$status['etapas'][$ordem][$row['ordemfs']]['botaocriador'] 			= $row['botaocriador'];
				$status['etapas'][$ordem][$row['ordemfs']]['idfluxostatus'] 		= $row['idfluxostatus'];
				$status['etapas'][$ordem][$row['ordemfs']]['statustipotab'] 		= $row["statustipotab"];
				$status['etapas'][$ordem][$row['ordemfs']]['numfluxostatus'] 		= $row['numfluxostatus'];
				$status['etapas'][$ordem][$row['ordemfs']]['statusloteativ'] 		= $row["statusloteativ"];
				$status['etapas'][$ordem][$row['ordemfs']]['idfluxostatustab'] 		= $row["idfluxostatustab"];
				$status['etapas'][$ordem][$row['ordemfs']]['idfluxostatushist'] 	= $row['idfluxostatushist'];
				$status['etapas'][$ordem][$row['ordemfs']]['carrimbo']['historico'] = $historico;
				
				$status['etapas'][$ordem][$row['ordemfs']]['hist'][$row['idfluxostatushist']]['criadoem'] 		= dmahms($row['criadoem']);
				$status['etapas'][$ordem][$row['ordemfs']]['hist'][$row['idfluxostatushist']]['criadopor'] 		= $row['criadopor'];                    
				$status['etapas'][$ordem][$row['ordemfs']]['hist'][$row['idfluxostatushist']]['alteradoem'] 	= dmahms($row['alteradoem']);
				$status['etapas'][$ordem][$row['ordemfs']]['hist'][$row['idfluxostatushist']]['alteradopor'] 	= $row['alteradopor'];                    
				

				$status['etapas'][$ordem][$row['ordemfs']]['idetapa'] 	= $row['idetapa']; 
				$status['etapas'][$ordem][$row['ordemfs']]['status'] 	= ($row['status'] != 'INATIVO') ? $row['status'] : '';
			}
		}

		if(($ocultoPessoa == 0 && $sqlAnd['fluxounico'] == 'Y' && $_modulo == 'evento') || ($sqlAnd['fluxounico'] == 'N' && $_modulo == 'evento') || ($_modulo != 'evento')) {

			if($tabela == 'lote') $mostraBotao = self::verificaLoteOP($_idobjeto);

			$permissaolpfluxo = "";
			$permissaolpfluxoBotao = "";
			$permissaoBotaoArquivo = "N";

			if($mostraBotao && $moduloFormalizacao != 'formalizacao') {
				$resnovofluxo = self::getLayoutBotao($_modulo, $_idobjeto, $_primary, $sqlAnd['sql']);
				$permissaolpfluxo = self::permissaoLpFluxo($tabela, $_primary, $_idobjeto);

				if(count($resnovofluxo) == 0) {
					$status['permissaolp'] = $permissaolpfluxo['permissao']; 
				} else {                 
					$status['permissaolp'] = $permissaolpfluxo['permissao'];

					foreach($resnovofluxo as $k => $rownovofluxo) {
						$status['botao'][$rownovofluxo['ordem']]['botaocriador'] = $rownovofluxo['botaocriador'];
						$status['botao'][$rownovofluxo['ordem']]['criadopor'] = $rownovofluxo['criadopor'];
						$status['botao'][$rownovofluxo['ordem']]['botaoparticipante'] = $rownovofluxo['botaoparticipante'];
						$status['botao'][$rownovofluxo['ordem']]['idfluxostatuspessoa'] = $rownovofluxo['idfluxostatuspessoa'];
						$status['botao'][$rownovofluxo['ordem']]['idfluxostatus'] = $rownovofluxo['idfluxostatus'];
						$status['botao'][$rownovofluxo['ordem']]['idfluxostatushist'] = $rownovofluxo['idfluxostatushist'];
						$status['botao'][$rownovofluxo['ordem']]['idfluxo'] = $rownovofluxo['idfluxo'];
						$status['botao'][$rownovofluxo['ordem']]['idfluxostatusf'] = $rownovofluxo['idfluxostatusf'];
						$status['botao'][$rownovofluxo['ordem']]['botao'] = $rownovofluxo['botao'];
						$status['botao'][$rownovofluxo['ordem']]['statustipo'] = $rownovofluxo['statustipo'];
						$status['botao'][$rownovofluxo['ordem']]['tipobotao'] = $rownovofluxo['tipobotao'];
						$status['botao'][$rownovofluxo['ordem']]['ocultar'] = $rownovofluxo['ocultar'];
						$status['botao'][$rownovofluxo['ordem']]['rotuloresp'] = $rownovofluxo['rotuloresp'];
						$status['botao'][$rownovofluxo['ordem']]['rotulo'] = $rownovofluxo['rotulo'];
						$status['botao'][$rownovofluxo['ordem']]['cor'] = $rownovofluxo['cor'];
						$status['botao'][$rownovofluxo['ordem']]['cortexto'] = $rownovofluxo['cortexto'];
						$status['botao'][$rownovofluxo['ordem']]['prioridade'] = $rownovofluxo['ordem'];
						$status['botao'][$rownovofluxo['ordem']]['permissaolp'] = $status['permissaolp'];
						$status['botao'][$rownovofluxo['ordem']]['idpessoa'] = $_SESSION["SESSAO"]["IDPESSOA"];
						$status['botao'][$rownovofluxo['ordem']]['usuario'] = $_SESSION["SESSAO"]["USUARIO"];
						$status['botao'][$rownovofluxo['ordem']]['permissao'] = $_SESSION["SESSAO"]["USUARIO"];

						$permissaolpfluxoBotao = self::permissaoLpFluxoPorIdFluxoStatus($tabela, $_primary, $_SESSION["SESSAO"]["IDPESSOA"], $rownovofluxo['idfluxostatusf']);
						if($permissaolpfluxoBotao == 'r'){
							$status['permissaoLP'][$rownovofluxo['idfluxostatusf']]['modulo'] = $_modulo;
							$status['permissaoLP'][$rownovofluxo['idfluxostatusf']]['esconderbotaoass'] = 'N';
							$status['permissaoLP'][$rownovofluxo['idfluxostatusf']]['esconderbotao'] = 'Y';
							$status['permissaoLP'][$rownovofluxo['idfluxostatusf']]['mensagem'] = "Sem Permissão";
							$status['permissaoLP'][$rownovofluxo['idfluxostatusf']]['idfluxostatusf'] = $rownovofluxo['idfluxostatusf'];
							$status['permissaoLP'][$rownovofluxo['idfluxostatusf']]['status'] = empty($rownovofluxo['statustipo']) ? $rownovofluxo['botao'] : $rownovofluxo['statustipo'];
							$permissaoBotaoArquivo = 'Y';
						}
					}
				}
			} else if ($moduloFormalizacao == 'formalizacao') {                              
				$status['permissaolp'] = ($_SESSION["SESSAO"]["SUPERUSUARIO"] == true) 
					? 'r' 
					: $permissaolpfluxo; 
			}

			if(file_exists(__DIR__."/../../eventcode/fluxo/fluxo_".$_modulo.".php")) {
				require __DIR__."/../../eventcode/fluxo/fluxo_".$_modulo.".php";
			} else if($permissaolpfluxo['permissao'] == 'r') {
				$status['permissao']['modulo'] = $_modulo;
				$status['permissao']['esconderbotaoass'] = 'N';
				$status['permissao']['esconderbotao'] = 'Y';
				$status['permissao']['status'] = $rownovofluxo['statustipo'];
			} else {
				$status['permissao']['modulo'] = '';
				$status['permissao']['esconderbotaoass'] = 'N';
				$status['permissao']['esconderbotao'] = '';
				$status['permissao']['status'] = '';
			}

			$status['permissaoassinatura'] = self::assinatura($_modulo, $_idobjeto);

			if($_modulo == 'evento'){
				$status['esconderRestaurar'] = ($sqlAnd['fluxounico'] == 'Y') ? 'N' : 'Y' ;
			}			
		}

		return $status;
	}

	public static function carregarFluxoObjeto($_modulo, $_primary, $_idobjeto) {

        $idunidade = getUnidadePadraoModulo($_modulo);
        $moduloFormalizacao = getModuloTipo($_modulo, $idunidade);

        if($moduloFormalizacao == 'formalizacao') {

			$res = SQL::ini(FormalizacaoQuery::buscarFluxoAtividadesFormalizacao(), [
				"modulo" => $_modulo,
				"idmodulo" => $_idobjeto,
				"idformalizacao" => $_idobjeto,
			])::exec();

        } else {
            $tabela = getModuloTab($_modulo);
            $sqlAnd = self::getIdEventoTipo($_modulo, $_idobjeto);

			$where = ($_modulo == 'evento') ? ' WHERE idfluxostatuspessoa IS NULL' : "";

			$res = SQL::ini(FluxoQuery::buscarFluxoStatus(), [
				"modulo" => $_modulo,
				"clausula1" => $sqlAnd['sql'],
				"tabela" => $tabela,
				"primary" => $_primary,
				"idprimary" => $_idobjeto,
				"idmodulo" => $_idobjeto,
				"clausula2" => $where,
			])::exec();

        }

        if($res->error()){
			return [
				"resposta" => "0",
				"formato" => "Erro ao consultar SQL Etapa Carrega Fluxo",
				"info" => []
			];
        }

        $break = "";
        $etapa = "";
        $class = "";
        $status = "";
        $progresso = "";
        $breakmodulo = "";
        $cancelado = false;
		$corFluxo = [];
		
        foreach($res->data as $k => $row) {

            $corFluxo[$row['idetapa']]['idetapa'] = $row['idetapa'];

            if($row['idfluxostatus'] == $row['idfluxostatustab']){     
                if(in_array($row['statustipotab'],['CANCELADO', 'DEVOLVIDO', 'RECUSADO', 'REPROVADO', 'EXTRAVIADO']))  {
                    $corFluxo[$row['idetapa']]['class'] = "cancelado";
                    $corFluxo[$row['idetapa']]['progresso'] = ' cor-vermelho';
                    $cancelado = true;
                } else {
                    $corFluxo[$row['idetapa']]['class'] = "activeout";
                    $corFluxo[$row['idetapa']]['progresso'] = ' cor-verde';
                    if($_modulo == 'evento'){
                        $breakmodulo = 'evento';
                    }                  
                }    
                $etapa = $row['idetapa'];                                           
                $break = 'break';
            } elseif((($break == 'break' || empty($row['status'])) && $cancelado == false && $row['statustipotab'] != 'CONCLUIDO') || $breakmodulo == 'evento'){
                if($moduloFormalizacao == 'formalizacao' && in_array($row['statustipotab'],['ABERTO']) && $row['status'] != 'PENDENTE'){
                    $corFluxo[$row['idetapa']]['class'] = "activeout";
                    $corFluxo[$row['idetapa']]['progresso'] = ' cor-verde';
                } elseif($breakmodulo == 'evento' && $etapa == $row['idetapa']){
                    $corFluxo[$row['idetapa']]['class'] = "activeout";
                } else {
                    if(($_modulo == 'evento' && $etapa == $row['idetapa'] && $class == 'activeout') || ($moduloFormalizacao == 'formalizacao' && $row['status'] == 'CONCLUIDO')){
                        $corFluxo[$row['idetapa']]['class'] = "activeout";
                    } elseif(($row['tipobotao'] == 'FIM' && $row['idfluxostatustab'] == $row['idfluxostatus']) || ($break == 'break' && $etapa == $row['idetapa'])){
                        $corFluxo[$row['idetapa']]['class'] = "activeout";
                    } else {
                        $corFluxo[$row['idetapa']]['class'] = "cinza";
                    }
                    
                    if($progresso == 'verde' && $status != 'PENDENTE' && $_modulo != 'evento'){
                        $corFluxo[$row['idetapa']]['progresso'] = ' cor-verde';
                    } else {
                        $corFluxo[$row['idetapa']]['progresso'] = ' cor-cinza';
                    } 
                }               
            } elseif($cancelado == false && (in_array($row['status'],['ATIVO','PENDENTE']))) {                        
                $corFluxo[$row['idetapa']]['class'] = "activeout";
                
                if($_modulo == 'evento'){
                    $etapa = $row['idetapa'];
                    $class = 'activeout';
                } 

                $corFluxo[$row['idetapa']]['progresso'] = ' cor-verde';
                if($row['status'] == 'PENDENTE'){
                    $status = 'PENDENTE';
                }
            } elseif(($_modulo == 'evento' && $etapa == $row['idetapa'] && $class == 'activeout') || ($moduloFormalizacao == 'formalizacao' && $row['status'] == 'CONCLUIDO')){
                $corFluxo[$row['idetapa']]['class'] = "activeout";
            } elseif(empty($break)) {
                $corFluxo[$row['idetapa']]['class'] = "cinza";
                $corFluxo[$row['idetapa']]['progresso'] = ' cor-cinza';
            } 
        }

        return [
			"resposta" => "1",
			"formato" => "SUCESSO",
			"info" => $corFluxo
		];
    }

	public static function listaRestaurarFluxo($_modulo, $_primary, $_idobjeto) {    
        $idunidade = getUnidadePadraoModulo($_modulo);
        $moduloFormalizacao = getModuloTipo($_modulo, $idunidade);
		$tabela = getModuloTab($_modulo);
		$sqlAnd = self::getIdEventoTipo($_modulo, $_idobjeto);

        if($moduloFormalizacao == 'formalizacao') {

            //Validação para Pegar o fluxo Autorizado, caso seja Vacina e o status da Solfab for Aprovada
			$res = SQL::ini(FormalizacaoQuery::buscarFluxoRestauracao(), [
				"idformalizacao" => $_idobjeto,
			])::exec();

        } elseif($_modulo == 'evento' && $sqlAnd['fluxounico'] == 'Y') {

			$res = SQL::ini(FluxoQuery::buscarFluxoRestauracaoModuloComTipo(), [
				"modulo" => $_modulo,
				"_primary" => $_primary,
				"tabela" => $tabela,
				"_idobjeto" => $_idobjeto,
				"sqlAnd" => $sqlAnd['sql']
			])::exec();

	 	} else {

			$res = SQL::ini(FluxoQuery::buscarFluxoRestauracao(), [
				"modulo" => $_modulo,
				"_primary" => $_primary,
				"tabela" => $tabela,
				"_idobjeto" => $_idobjeto
			])::exec();

        }
  
        if($res->error()){
			return [
				"resposta" => "0",
				"formato" => "Erro ao consultar SQL Etapa Lista Restaurar",
			];
        }

		$fluxo = [];
        foreach($res->data as $k => $row) {
            $fluxo[$row['ordem']]['rotulo'] = $row['rotulo'];
            $fluxo[$row['ordem']]['statustipo'] = $row['statustipo'];
            $fluxo[$row['ordem']]['idfluxostatus'] = $row['idfluxostatus'];
        }
		
		$resm = SQL::ini(FluxostatushistmotivoQuery::buscarMotivosPorModulo(), [
			"modulo" => $_modulo,
		])::exec();
		
        foreach ($resm->data as $k => $motivo) {
            $fluxo['motivo'][$k] = $motivo['motivo'];
        }
		
		return [
			"resposta" => "1",
			"formato" => "SUCESSO",
			"info" => $fluxo
		];
    }

	public static function getIdFluxoStatus($_modulo, $status, $id = NULL, $tipo = NULL) {
		$idunidade = getUnidadePadraoModulo($_modulo);
        $moduloFormalizacao = getModuloTipo($_modulo, $idunidade);

        if(!empty($id) && $moduloFormalizacao != 'formalizacao' && $_modulo != 'documento') {
			
			$res = SQL::ini(UnidadeObjetoQuery::buscarIdFluxoStatusPorUnidade(), [
				"modulotipo" => $_modulo,
				"statustipo" => $status,
				"idunidade" => $id,
			])::exec();
 
        } elseif($moduloFormalizacao == 'formalizacao'){

			$validaTipo = (!empty($tipo)) ? " AND s.tipobotao = 'INICIO'" : "";

			$res = SQL::ini(FluxoQuery::buscarIdFluxoStatusPorPrProc(), [
				"statustipo" => $status,
				"clausula" => $validaTipo,
				"modulo" => $_modulo,
				"idprproc" => $id,
			])::exec();

        } elseif($_modulo == 'documento'){

			$res = SQL::ini(SgdocQuery::buscarIdFluxoStatusPorIdSgDoc(), [
				"statustipo" => $status,
				"idsgdoc" => $id,
			])::exec();

        } else {
			
			$res = SQL::ini(FluxoQuery::buscarIdFluxoStatusPorModulo(), [
				"statustipo" => $status,
				"modulo" => $_modulo,
			])::exec();

        }

        return $res->data[0]['idfluxostatus'];
    }

	public static function getidfluxostatusInativo($_modulo, $_status, $_tipobotao  = NULL) {

		$and = (!empty($_tipobotao)) ? " AND s.tipobotao = 'INICIO'" : "";

		$res = SQL::ini(FluxoQuery::buscarIdFluxoStatusPorStatusTipo(), [
			"statustipo" => $_status,
			"modulo" => $_modulo,
			"clausula" => $and,
		])::exec();

        return $res->data[0];
    }

	public static function atualizarHistoricoRestaurar($idfluxostatushistobs, $motivoobs) {
		
		$res = SQL::ini(FluxostatushistobsQuery::atualizarHistoricoRestaurar(), [
			"idfluxostatushistobs" => $idfluxostatushistobs,
			"motivoobs" => $motivoobs,
		])::exec();

		if($res->error()){
			return false;
		}
		
        return true;
    }

	public static function getHistoricoRestaurar($_modulo, $_idobjeto) {
		
		$res = SQL::ini(FluxostatushistobsQuery::buscarFluxoStatusHistObsPorModulo(), [
			"idmodulo" => $_idobjeto,
			"modulo" => $_modulo,
		])::exec();

		$status = [];
		$permitealterar = getModsUsr("MODULOS")["editarrestaurar"];
        foreach($res->data as $k => $rowHistorico) {
            $status[$k]['idfluxostatushistobs'] = $rowHistorico['idfluxostatushistobs'];
            $status[$k]['rotulo'] = $rowHistorico['rotulo'];
            $status[$k]['motivo'] = $rowHistorico['motivo'];
            $status[$k]['motivoobs'] = $rowHistorico['motivoobs'];
            $status[$k]['criadoem'] = dma($rowHistorico['criadoem']);
            $status[$k]['nome'] = $rowHistorico['nome'];
			if(!empty($permitealterar)){
				$status[$k]['permitealterar'] = true;
			}else{
				$status[$k]['permitealterar'] = false;
			}
        }
		

        return $status;
    }

	public static function getIdFluxoStatusHist($_modulo, $_idobjeto) {
		
        $res = SQL::ini(FluxoStatusHistQuery::buscarFluxoStatusPendentePorModuloeId(), [
			"idmodulo" => $_idobjeto,
			"modulo" => $_modulo,
		])::exec();

        return $res->data[0]['idfluxostatushist'];
    }

	public static function alterarStatus($_modulo, $_primary, $_idobjeto, $idfluxostatushist, $idstatusf, $statustipo = "", $idfluxostatuspessoa, $ocultar, $idfluxostatus, $idfluxo, $prioridade, $tipobotao = null, $idcarrimbo = null) {
        if(!empty($_modulo) 
			AND $_SESSION["SESSAO"]["FULLACCESS"] != "Y" 
			AND (getModsUsr("MODULOS")[$_modulo]["permissao"] != 'i' AND getModsUsr("MODULOS")[$_modulo]["permissao"] != 'w')
			){
			
			$rowRotulo = _moduloController::buscarRotuloMenu($_modulo);
			$rotulo = empty($rowRotulo['rotulomenu']) ? $_GET["_modulo"] : $rowRotulo['rotulomenu'];

			return [
				"resposta" => "0",
				"formato" => "Sem permissão de escrita ao Módulo [ ".$rotulo." ].\nVerificar configurações da LP [".getModsUsr("LPS")."].\nPermissão atual: [".getModsUsr("MODULOS")[$_modulo]["permissao"]."]",
			];
        }

        $tabela = getModuloTab($_modulo);
		$sqlAnd = self::getIdEventoTipo($_modulo, $_idobjeto);

        //Altera as informações do Evento pq possui algumas particularidades
        if($_modulo == 'evento'){
            //Chama a Classe Evento
            $eventoclass = new EVENTO();
            $eventoclass->atualizaEventoStatus($idfluxostatuspessoa, $idstatusf, $ocultar, 'false', NULL, $sqlAnd['fluxounico'], $_idobjeto);
        }

        //Verifica se tem algum item marcado no pedido  quando o status for SOLICITADO
        if(($statustipo == "SOLICITADO" || $statustipo == "PEDIDO" || $statustipo == "PRODUCAO" || $statustipo == "EXPEDICAO" || $statustipo == "FATURAR") && $_modulo == 'pedido') {
            
			$resultContador = SQL::ini(NfItemQuery::buscarNfitemDanfe(), [
				"idnf" => $_idobjeto,
			])::exec();

            if($resultContador->error() OR $resultContador->data[0]['contador'] == 0){
				return [
					"resposta" => "0",
					"formato" => "Selecionar ao menos um item.",
				];
            }
        }

        //Valida o Tipo de Assinatura. Se for todos não irá alterar o status do módulo equanto todos não assinarem. 
        //Se for Parcial, irá apagar as outras assinaturas que estiverem pendente
		if(!empty($idcarrimbo) && $idcarrimbo != 'undefined')
		{
			$resultCarrimbo = SQL::ini(CarimboQuery::buscarPorChavePrimaria(), [
				"pkval" => $idcarrimbo,
			])::exec();

			$tipoassinatura = (!$resultCarrimbo->error() OR $resultCarrimbo->numRows() > 0) ? $resultCarrimbo->data[0]['tipoassinatura'] : "";
		} else {
			$tipoassinatura = "";
		}
		
        if($tipoassinatura == 'TODOS'){

			$resultCarrimbo = SQL::ini(CarimboQuery::buscarAssinaturaPorIdObjetoTipoObjetoStatus(), [
				"idobjeto" => $_idobjeto,
				"tipoobjeto" => $tabela,
				"status" => " = 'PENDENTE'",
			])::exec();
                    
        } elseif($tipoassinatura == 'PARCIAL' || $tipoassinatura == 'INDIVIDUAL'){

			$resultCarrimbo = SQL::ini(CarimboQuery::buscarAssinaturaPorIdObjetoTipoObjetoStatus(), [
				"idobjeto" => $_idobjeto,
				"tipoobjeto" => $tabela,
				"status" => " IN ('ATIVO', 'ASSINADO')",
			])::exec();

        } elseif($_modulo == 'amostratra'){

			$resultCarrimbo = SQL::ini(CarimboQuery::buscarAssinaturaComArquivoFluxoStatus(), [
				"idobjeto" => $_idobjeto,
				"tipoobjeto" => $tabela,
			])::exec();

        } else {
			$resultCarrimbo = null;
		}

		if(!empty($resultCarrimbo)){
			$total = $resultCarrimbo->numRows(); 
			$amostraTra = $resultCarrimbo->data[0];

			if($total == 0 and $tipoassinatura == 'TODOS' && ($_modulo != 'amostratra' && $statustipo != 'ABERTO')){
				$assinatura = 'ERROASSTODOS' ;
			} elseif($total == 0 and ($tipoassinatura == 'INDIVIDUAL' || $tipoassinatura == 'PARCIAL') && ($_modulo == 'amostratra' && $statustipo != 'ABERTO')) {
				$assinatura = 'ERROINDIVIDUAL' ;
			} elseif($total >= 1 && $_modulo == 'amostratra' && $statustipo == 'ASSINAR' && empty($amostraTra['idarquivo'])){
				$assinatura = 'ERROAMOSTRA' ;
			}
		}
        
        if(empty($assinatura)) {          
            //Caso seja cancelado. Irá inserir o próximo com o status Cancelado e o que está no momento continua azul
            //Se for concluido, tanto o status atual como o concluído ficam verdes

            if($tipobotao == 'FIM' OR
				in_array($statustipo, ['CANCELADO', 'CONCLUIDO', 'DEVOLVIDO', 'RECUSADO', 'FIM', 'REPROVADO', 'EXTRAVIADO'])
				){

                if(in_array($statustipo, ['CANCELADO', 'DEVOLVIDO', 'RECUSADO', 'REPROVADO', 'EXTRAVIADO'])) {
                    self::inserirFluxoStatusHist($_modulo, $_idobjeto, $idstatusf, $statustipo);//Insere o "FIM" com Status CANCELADO 
                } else {

                    if($_modulo == 'evento' && $sqlAnd['fluxounico'] == 'N'){
						$resultOrdem = SQL::ini(FluxoStatusQuery::buscarOrdemFluxoStatusPorModuloeId(), [
							"modulo" => $_modulo,
							"clausula" => $sqlAnd['sql'],
							"idfluxostatus" => $idfluxostatus,
						])::exec();
  
                        if($resultOrdem->error()){
							return [
								"resposta" => "0",
								"formato" => "Erro ao consultar SQL Etapa Validação Hist",
							];
                        }

                        if($prioridade < $resultOrdem->data[0]['ordem']) {
							$resultHist = SQL::ini(FluxoStatusHistQuery::buscarFluxoStatusHistPendentePorModuloeUsuario(), [
								"idmodulo" => $_idobjeto,
								"modulo" => $_modulo,
								"criadopor" => $_SESSION["SESSAO"]["USUARIO"],
								"idfluxostatus" => $idfluxostatus,
							])::exec();

                            foreach ($resultHist->data as $key => $qro) {
								self::atualizaFluxoHist($qro['idfluxostatushist']); //Atualiza o anterior para Ativo
							}

                            //Atualiza o fluxo anterior caso seja o penultimo e esteja em algum status fim  
							$resultHist = SQL::ini(FluxoStatusHistQuery::buscarFluxoStatusHistPendentePorModulo(), [
								"idmodulo" => $_idobjeto,
								"modulo" => $_modulo
							])::exec();

                            if($resultHist->numRows() == 0){
                                self::inserirFluxoStatusHist($_modulo, $_idobjeto, $idstatusf, 'ATIVO'); //Insere o "FIM" com Status ATIVO
                            } elseif($statustipo == 'CONCLUIDO'){
                                self::inserirFluxoStatusHist($_modulo, $_idobjeto, $idstatusf, 'ATIVO'); //Insere o "FIM" com Status ATIVO
                            }
                        } else {
							$resultContador = SQL::ini(FluxostatuspessoaQuery::buscarQtdFluxoStatusPessoaEvento(), [
								"idmodulo" => $_idobjeto,
							])::exec();

                            if($resultContador->data[0]['contador'] == 1) {
                                self::inserirFluxoStatusHist($_modulo, $_idobjeto, $idstatusf, 'ATIVO'); //Insere o "FIM" com Status ATIVO                     
                        
                                //Atualiza o fluxo anterior caso seja o penultimo e esteja em algum status fim          
                                $resultHist = SQL::ini(FluxoStatusHistQuery::buscarFluxoStatusHistPendentePorModulo(), [
									"idmodulo" => $_idobjeto,
									"modulo" => $_modulo
								])::exec();

                                foreach($resultHist->data as $k => $rowdIdFluxo){
                                    self::atualizaFluxoHist($rowdIdFluxo['idfluxostatushist']); //Atualiza o anterior para Ativo
                                }

								$resultFluxoE = SQL::ini(FluxoStatusQuery::buscarFluxoStatusPorIdStatusF(), [
									"idstatusf" => $idstatusf,
									"modulo" => $_modulo,
									"clausula1" => $sqlAnd['sql'],
									"clausula2" => $sqlAnd['etapa'],
								])::exec();

                                $rowIdFluxoE = $resultFluxoE->data[0];

                                if($rowIdFluxoE['idfluxostatus']){
                                    $fluxostatushist = self::inserirFluxoStatusHist($_modulo, $_idobjeto, $rowIdFluxoE['idfluxostatus'], 'ATIVO');

                                    if($fluxostatushist['contador'] > 0){

										$resultFluxo = SQL::ini(FluxoStatusHistQuery::buscarFluxoStatusPorModuloeId(), [
											"idmodulo" => $_idobjeto,
											"modulo" => $_modulo,
											"idfluxostatus" => $rowIdFluxoE['idfluxostatus'],
										])::exec();

                                        self::atualizaFluxoHist($resultFluxo->data[0]['idfluxostatushist']); 
                                    }
                                }
                            }
                        }
                    } else {
                        self::inserirFluxoStatusHist($_modulo, $_idobjeto, $idstatusf, 'ATIVO'); //Insere o "FIM" com Status ATIVO                     
                    
                        //Atualiza o fluxo anterior caso seja o penultimo e esteja em algum status fim    
						
						$resultHist = SQL::ini(FluxoStatusHistQuery::buscarFluxoStatusHistPendentePorModulo(), [
							"idmodulo" => $_idobjeto,
							"modulo" => $_modulo
						])::exec();

						foreach($resultHist->data as $k => $rowdIdFluxo){
							self::atualizaFluxoHist($rowdIdFluxo['idfluxostatushist']); //Atualiza o anterior para Ativo
						}

                        $resultFluxoE = SQL::ini(FluxoStatusQuery::buscarFluxoStatusPorIdStatusF(), [
							"idstatusf" => $idstatusf,
							"modulo" => $_modulo,
							"clausula1" => $sqlAnd['sql'],
							"clausula2" => $sqlAnd['etapa'],
						])::exec();

                        $rowIdFluxoE = $resultFluxoE->data[0];

                        if($rowIdFluxoE['idfluxostatus']){
							$fluxostatushist = self::inserirFluxoStatusHist($_modulo, $_idobjeto, $rowIdFluxoE['idfluxostatus'], 'ATIVO');

							if($fluxostatushist['contador'] > 0){

								$resultFluxo = SQL::ini(FluxoStatusHistQuery::buscarFluxoStatusPorModuloeId(), [
									"idmodulo" => $_idobjeto,
									"modulo" => $_modulo,
									"idfluxostatus" => $rowIdFluxoE['idfluxostatus'],
								])::exec();

								self::atualizaFluxoHist($resultFluxo->data[0]['idfluxostatushist']); 
							}
                        }
                    } 
                }

            } else {     
				$resultOrdem = SQL::ini(FluxoStatusQuery::buscarOrdemFluxoStatusPorModuloeId(), [
					"modulo" => $_modulo,
					"clausula" => $sqlAnd['sql'],
					"idfluxostatus" => $idfluxostatus,
				])::exec();

				if($resultOrdem->error()){
					return [
						"resposta" => "0",
						"formato" => "Erro ao consultar SQL Etapa Validação Hist",
					];
				}
				
				$ocultarFluxo = traduzid("fluxostatus", 'idfluxostatus', 'ocultar', $idstatusf);
                if($prioridade >= $resultOrdem->data[0]['ordem'] && (($_modulo == 'evento' && $sqlAnd['fluxounico'] == 'Y' && $ocultarFluxo == 'N') 
					|| ($_modulo == 'evento' && $sqlAnd['fluxounico'] == 'N') || ($_modulo != 'evento'))) {
                    //Valida se tem Hist para atualizar o Fluxo Atual
                    if(!empty($idfluxostatushist) && $idfluxostatushist != 'null')
                        self::atualizaFluxoHist($idfluxostatushist); //Atualiza o anterior para Ativo
						
                    if(!empty($idstatusf))
                        self::inserirFluxoStatusHist($_modulo, $_idobjeto, $idstatusf, 'PENDENTE'); //Insere o próximo como PENDENTE

                } elseif($_modulo != 'evento' || ($_modulo == 'evento' && $sqlAnd['fluxounico'] == 'Y' && $ocultarFluxo == 'N') || ($_modulo == 'evento' && $sqlAnd['fluxounico'] == 'N')) {
                    //Restaura para o Fluxo anterior
                    if(!empty($statustipo) && !empty($idstatusf))
                        self::restaurarFluxo($_modulo, $_primary, $_idobjeto, $statustipo, $idstatusf);
                }
            }

            //Atualiza o status da TAbela referente ao modulo atual
            if($_modulo != 'evento' || ($_modulo == 'evento' && $sqlAnd['fluxounico'] == 'Y' && $ocultarFluxo == 'N')){
				
                if($statustipo == 'ASSINA')
					$statustipo = 'FECHADO';

                if(!empty($statustipo))
                    self::atualizaModuloTab($_modulo, $_primary, $_idobjeto, $statustipo, $idstatusf);
				elseif($_modulo == 'evento' && $sqlAnd['fluxounico'] == 'Y')
					self::atualizaModuloTab($_modulo, $_primary, $_idobjeto, NULL, $idstatusf);

                //Insere Pessoas no Carrimbo de acordo com o status definido.
                self::insereAssinatura($_modulo, $_idobjeto, $idstatusf);
            }        

			return [
				"resposta" => "1",
				"formato" => "html",
			];

        } else {
            $msg = "";
            if($assinatura == 'ERROASSTODOS'){
                $msg = 'Todos precisam assinar para que o status seja alterado.';
            } elseif($assinatura == 'ERROINDIVIDUAL'){
                $msg = 'É necessário pelo menos uma assinatura para que o status seja alterado.';
            } elseif($assinatura == 'ERROAMOSTRA'){
                $msg = 'É necessário assinar o TEA para alterar o status.';
            }
			
			return [
				"resposta" => "0",
				"formato" => $msg,
			];
        }
    }

	public static function restaurarFluxo($_modulo, $_primary, $_idobjeto, $status, $idfluxostatus,$motivorestaturacao = null,$obsrestauracao = null, $inativarConsumo = null) {   

        $tabela = getModuloTab($_modulo);
        $idunidade = getUnidadePadraoModulo($_modulo);
        $moduloFormalizacao = getModuloTipo($_modulo, $idunidade);
		$sqlAnd = self::getIdEventoTipo($_modulo, $_idobjeto);

        if($moduloFormalizacao == 'formalizacao') {
			$resultRestaurar = SQL::ini(FormalizacaoQuery::atualizarStatuseFluxoStatusFormalizacao(), [
				"idfluxostatus" => $idfluxostatus,
				"usuario" => $_SESSION["SESSAO"]["USUARIO"],
				"status" => $status,
				"idformalizacao" => $_idobjeto,
			])::exec();
			
            if($resultRestaurar->error()){
				return [
					"resposta" => "0",
					"formato" => "Erro ao Restaurar formalizacao",
				];
            }

            //Quando Restaurar para Autorizado deixar o statusj Aberto como ATIVO
            if($status == 'AUTORIZADO') {
				$resultStatusForm = SQL::ini(FormalizacaoQuery::buscarStatusAbertoFormalizacao(), [
					"idformalizacao" => $_idobjeto,
				])::exec();

                $limitAberto = " AND idfluxostatus <> '".$resultStatusForm->data[0]['idfluxostatus']."'";
            }
			
			$attFluxo = SQL::ini(FluxoStatusHistQuery::alterarStatusFluxoStatusHistPorModulo(), [
				"status" => 'INATIVO',
				"usuario" => $_SESSION["SESSAO"]["USUARIO"],
				"modulo" => $_modulo,
				"idmodulo" => $_idobjeto,
				"clausula" => $limitAberto,
			])::exec();
			
            if($attFluxo->error()){
				return [
					"resposta" => "0",
					"formato" => "Erro ao Inativar fluxostatushist",
				];
            }
			
			//Busca o Idlote
			$resFormalizacao = SQL::ini(FormalizacaoQuery::buscarPorChavePrimaria(), [
				"pkval" => $_idobjeto,
			])::exec();
            
            if($resFormalizacao->error()){
				return [
					"resposta" => "0",
					"formato" => "Erro ao Buscar Lote",
				];
            }

			$rowLote = $resFormalizacao->data[0];

            //Relacionar Teste com Formalização para Cancela-los quando for restaurado.
			$resAtivTeste = SQL::ini(ObjetoVinculoQuery::buscarResultadoeAmostraVinculadoPorLote(), [
				"idlote" => $rowLote['idlote'],
			])::exec();

            foreach ($resAtivTeste->data as $k => $rowAtivTeste) {
                $idresultado = $rowAtivTeste['idresultado'];
                if($idresultado) {

                    $rowFluxoResultado = self::getDadosResultadoAmostra('resultado', 'idresultado', $idresultado, 'OFFLINE', 'resultado', '', '');	
                    self::inserirFluxoStatusHist($rowFluxoResultado['modulo'], $idresultado, $rowFluxoResultado['idfluxostatus'], 'ATIVO');
					
					SQL::ini(ResultadoQuery::atualizarResultadoParaOffline(), [
						"idfluxostatus" => $rowFluxoResultado['idfluxostatus'],
						"idresultado" => $idresultado,
					])::exec();
					
                }

                $idamostra = $rowAtivTeste['idamostra'];
            }

            $rowFluxo = self::getDadosResultadoAmostra('amostra', 'idamostra', $idamostra, 'CANCELADO', 'amostra', '', '');
            self::inserirFluxoStatusHist($rowFluxo['modulo'], $idamostra, $rowFluxo['idfluxostatus'], 'ATIVO');

			SQL::ini(AmostraQuery::atualizarStatuseFluxoAmostra(), [
				"idfluxostatus" => $rowFluxo['idfluxostatus'],
				"idamostra" => $idamostra,
			])::exec();

			if($inativarConsumo <> 'N') {
				$resAtiv = SQL::ini(LoteconsQuery::atualizarLoteConsRestauracaoPorIdLote(), [
					"idlote" => $rowLote['idlote'],
				])::exec();
	
				if($resAtiv->error()){
					return [
						"resposta" => "0",
						"formato" => "Erro ao Deletar loteativ",
					];
				}
	
				self::excluirLoteconsRestaurarOP($rowLote['idlote']);	
	
				$resAtiv = SQL::ini(LoteAtivQuery::deletarLoteAtivPorLote(), [
					"idlote" => $rowLote['idlote'],
				])::exec();
	
				if($resAtiv->error()){
					return [
						"resposta" => "0",
						"formato" => "Erro ao Deletar loteativ",
					];
				}
			}

			$resObj = SQL::ini(LoteObjQuery::deletarLoteObjPorLote(), [
				"idlote" => $rowLote['idlote'],
			])::exec();
            
            if($resObj->error()){
				return [
					"resposta" => "0",
					"formato" => "Erro ao Deletar loteobj",
				];
            }

			SQL::ini(FormalizacaoQuery::atualizarResponsavel(), [
				"idformalizacao" => $_idobjeto
			])::exec();

            $modulolote = self::getDadosModuloPrincipal($rowLote['idlote']);
            $rowFluxo = self::getFluxoStatusHist($modulolote, 'idlote', $rowLote['idlote'], $status);
            $rowFluxo['ordem'] = $rowFluxo['ordem'] + 1;

            if($status != "CANCELADO"){
				$attFluxo = SQL::ini(FluxoStatusHistQuery::alterarStatusFluxoStatusHistPorModulo(), [
					"status" => 'INATIVO',
					"usuario" => $_SESSION["SESSAO"]["USUARIO"],
					"modulo" => $modulolote,
					"idmodulo" => $rowLote['idlote'],
					"clausula" => "",
				])::exec();

                if($attFluxo->error()){
					return [
						"resposta" => "0",
						"formato" => "Erro ao Inativar fluxostatushist lote",
					];
                }
            }
            
            //Insere o Fluxo Restaurado para Pendente
            self::inserirFluxoStatusHist($_modulo, $_idobjeto,  $idfluxostatus, 'ATIVO');
            
            self::alterarStatus($modulolote, 'idlote', $rowLote['idlote'], null, $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], null, 0, $rowFluxo['idfluxostatus'], $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);
            
			if(!empty($rowFluxo['idfluxostatus'])){

				$reslote = SQL::ini(LoteQuery::atualizarStatuseFluxoStatusPorLote(), [
					"status" => $status,
					"idfluxostatus" => $rowFluxo['idfluxostatus'],
					"idlote" => $rowLote['idlote'],
				])::exec();

                if($reslote->error()){
					return [
						"resposta" => "0",
						"formato" => "Erro ao atualizar status lote Quarentena",
					];
                }
            }            
		} elseif($_modulo == 'evento' && $sqlAnd['fluxounico'] == 'Y') {

			self::atualizaModuloTab($_modulo, $_primary, $_idobjeto, $status, $idfluxostatus);

			SQL::ini(FluxoStatusPessoaQuery::atualizarFluxoStatusPessoaPorModulo(), [
				"idmodulo" => $_idobjeto,
				"modulo" => $_modulo,
				"idfluxostatus" => $idfluxostatus,
				"alteradopor" => $_SESSION["SESSAO"]["USUARIO"],
			])::exec();

			//Pega a Data do Fluxo a ser restaurado para Inativar os posteriores a ele
			$rocxHIst = SQL::ini(FluxoStatusHistQuery::buscarFluxoStatusHistPorModuloEFluxoStatus(), [
				"idmodulo" => $_idobjeto,
				"modulo" => $_modulo,
				"idfluxostatus" => $idfluxostatus,
				"clausula" => "LIMIT 1",
			])::exec();

			$rocxHIst = $rocxHIst->data[0];

			
            //Atualiza os Dados do fluxoHist
			$updateRestaura = SQL::ini(FluxoStatusHistQuery::inativarFluxosHistPorModulo(), [
				"idfluxostatushist" => $rocxHIst['idfluxostatushist'],
				"modulo" => $_modulo,
				"idmodulo" => $_idobjeto,
			])::exec();
			

        } else {

            //Pega a Data do Fluxo a ser restaurado para Inativar os posteriores a ele
			$rocxHIst = SQL::ini(FluxoStatusHistQuery::buscarFluxoStatusHistPorModuloEFluxoStatus(), [
				"idmodulo" => $_idobjeto,
				"modulo" => $_modulo,
				"idfluxostatus" => $idfluxostatus,
				"clausula" => "LIMIT 1",
			])::exec();

			$rocxHIst = $rocxHIst->data[0];

            //Atualiza os Dados do fluxoHist
			$updateRestaura = SQL::ini(FluxoStatusHistQuery::inativarFluxosHistPorModulo(), [
				"idfluxostatushist" => $rocxHIst['idfluxostatushist'],
				"modulo" => $_modulo,
				"idmodulo" => $_idobjeto,
			])::exec();
			
            if($updateRestaura->error()){
				return [
					"resposta" => "0",
					"formato" => "Erro ao Restaurar fluxostatushist",
				];
            }

            //Insere o Fluxo Restaurado para Pendente
            self::inserirFluxoStatusHist($_modulo, $_idobjeto,  $idfluxostatus, 'PENDENTE');

			if($_modulo == 'amostratra'){
				self::voltaResultadosFechadoRestauraAmostra($_idobjeto);
			}

            //Atualiza o fluxo da Tabela
            $updateRestaura = SQL::ini("UPDATE $tabela
                SET idfluxostatus = '$idfluxostatus', 
					status = '$status', 
					alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."', 
					alteradoem = sysdate()
                WHERE $_primary = '$_idobjeto'",[]
			)::exec();

            if($updateRestaura->error()){
				return [
					"resposta" => "0",
					"formato" => "Erro ao Restaurar $tabela",
				];
            }

			$rest = SQL::ini(FluxoStatusHistQuery::alterarStatusFluxoStatusHistPorModulo(), [
				"status" => 'PENDENTE',
				"usuario" => $_SESSION["SESSAO"]["USUARIO"],
				"modulo" => $_modulo,
				"idmodulo" => $_idobjeto,
				"clausula" => "AND idfluxostatushist = '".$rocxHIst['idfluxostatushist']."'",
			])::exec();

            if($rest->error()){
				return [
					"resposta" => "0",
					"formato" => "Erro ao Atualizar status Restaurar da Tabela FluxoHist",
				];
            }       
        }

        //Atualiza o fluxo da Tabela
        if (isset($motivorestaturacao) and isset($obsrestauracao)) {

			$InsObs = SQL::ini(FluxostatushistobsQuery::inserirFluxoStatusHistObs(), [
				'idempresa' => $_SESSION['SESSAO']['IDEMPRESA'],
				'idmodulo' => $_idobjeto,
				'modulo' => $_modulo,
				'motivo' => $motivorestaturacao,
				'motivoobs' => $obsrestauracao,
				'status' => $status,
				'idfluxostatus' => $idfluxostatus,
				'criadopor' => $_SESSION["SESSAO"]["USUARIO"],
				'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
			])::exec();

            if ($InsObs->error()) {
				return [
					"resposta" => "0",
					"formato" => "Erro ao inserir Obs",
				];
            }
        }

		$rest = SQL::ini(FluxoStatusHistQuery::alterarStatusFluxoStatusHistPorModulo(), [
			"status" => 'PENDENTE',
			"usuario" => $_SESSION["SESSAO"]["USUARIO"],
			"modulo" => $_modulo,
			"idmodulo" => $_idobjeto,
			"clausula" => "AND idfluxostatushist = '".$rocxHIst['idfluxostatushist']."'",
		])::exec();

        if($rest->error()){
			return [
				"resposta" => "0",
				"formato" => "Erro ao Atualizar status Restaurar da Tabela FluxoHist",
			];
        }       
    }

	public static function excluirLoteconsRestaurarOP($idLote)
    {
        $result = SQL::ini(LoteConsQuery::excluirLoteconsRestaurarOP(), [
			'idlote' => $idLote, 
			'tipoobjeto' => 'lote'
		])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());

            return [];
        }
    }

	public static function insereAssinatura ($_modulo, $_idobjeto, $idstatusf) {
        if($_modulo == 'evento') return;

		$tabela = getModuloTab($_modulo);

		$resCarrimbo = SQL::ini(CarimboQuery::buscarCarimboPorTabelaEModulo(), [
			"idobjeto" => $_idobjeto,
			"modulo" => $_modulo,
			"tabela" => $tabela,
		])::exec();

		if($resCarrimbo->error()){
			parent::error(__CLASS__, __FUNCTION__, $resCarrimbo->errorMessage());
			return [];
		}

		if($resCarrimbo->numRows() > 0) return;

		$res = SQL::ini(FluxoObjetoQuery::buscarPessoasFluxoParaAssinatura(), [
			"modulo" => $_modulo,
			"idstatusf" => $idstatusf,
		])::exec();

		if($res->error()){
			parent::error(__CLASS__, __FUNCTION__, $res->errorMessage());
			return [];
		}

		foreach($res->data as $k => $row){
			SQL::ini(CarimboQuery::inserirAssinaturaPendenteFluxo(), [
				'idempresa' => cb::idempresa(),
                'idpessoa' => $row['idpessoa'],
                'idobjeto' => $_idobjeto,
                'tipoobjeto' => $_modulo, 
                'idobjetoext' => $idstatusf,
                'tipoassinatura' => $row['assina'],
                'criadopor' => $_SESSION['SESSAO']['USUARIO'],
			])::exec();
		}
    }

	public static function inserirFluxoStatusHist($_modulo, $_idobjeto, $idstatusf, $status, $idempresa = 0, $criadopor = null) {   
        $idempresa = ($idempresa != 0) ? $idempresa : $_SESSION["SESSAO"]["IDEMPRESA"];
		
		$criadopor = ($criadopor != null) ? $criadopor : $_SESSION["SESSAO"]["USUARIO"];

		if(!empty($idstatusf)){
			$result = SQL::ini(FluxoStatusHistQuery::inserirFluxoStatusHist(), [
				"idempresa" => $idempresa,
				"idfluxostatus" => $idstatusf,
				"idmodulo" => $_idobjeto,
				"modulo" => $_modulo,
				"status" => $status,
				"criadopor" => $criadopor,
			])::exec();

			if($result->error()){
				parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
				return null;
			}else{
				return $result->lastInsertId();
			}
		} else {
			return null;
		}
    }

	public static function atualizaModuloTab($_modulo, $_primary, $_idobjeto, $statustipo, $idfluxostatus) {
        $tabela = getModuloTab($_modulo);

		$atualizandoTabela = SQL::ini(FluxoStatusQuery::atualizarFluxoStatusPorTabela(), [
			"tabela" => $tabela,
			"idfluxostatus" => $idfluxostatus,
			"status" => $statustipo,
			"usuario" => $_SESSION["SESSAO"]["USUARIO"],
			"primary" => $_primary,
			"idobjeto" => $_idobjeto,
		])::exec();

		SQL::ini(AuditoriaQuery::inserirAuditoriaFluxo(), [
			"idempresa" => cb::idempresa(),
			"objeto" => $tabela,
			"idobjeto" => $_idobjeto,
			"valor" => $statustipo,
			"criadopor" => $_SESSION["SESSAO"]["USUARIO"],
			"tela" => "fluxo: ".$_SERVER["HTTP_REFERER"],
		])::exec();
    }

	public static function getLayoutBotao ($_modulo, $_idobjeto, $_primary, $sql = null) {
		$sqlAnd = self::getIdEventoTipo($_modulo, $_idobjeto);
        if($_modulo == 'evento') {
			$result = SQL::ini(EventoQuery::buscarBotoesFluxoEvento(), [
				"lps" => getModsUsr("LPS"),
				"idobjeto" => $_idobjeto,
				"idpessoa" => $_SESSION["SESSAO"]["IDPESSOA"],
			])::exec();
        } else {
            $tabela = getModuloTab($_modulo);
			$result = SQL::ini(FluxoStatusQuery::buscarBotoesFluxoPorTabela(), [
				"sql" => $sql,
				"lps" => getModsUsr("LPS"),
				"tabela" => $tabela,
				"primary" => $_primary,
				"modulo" => $_modulo,
				"idobjeto" => $_idobjeto,
			])::exec();
        }
                       
        if($result->error()){
			parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
			return [];
		}else{
			return $result->data;
		}
    }

	public static function permissaoLpFluxo($tabela, $_primary, $_idobjeto) {
		$result = SQL::ini(FluxoStatusLp::buscarPermissaoTabelaPorLp(), [
			"tabela" => $tabela,
			"lps" => getModsUsr("LPS"),
			"primary" => $_primary,
			"idobjeto" => $_idobjeto,
		])::exec();

		if($result->error()){
			parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
			return "";
		}else if($result->numRows() == 0 || empty($result->data[0]['permissaolp'])){
			return "";
		}else{
			return $result->data[0]['permissaolp'];
		}
    }

	public static function permissaoLpFluxoPorIdFluxoStatus($tabela, $_primary, $_idobjeto, $idfluxostatus) {
		$result = SQL::ini(FluxoStatusLp::buscarPermissaoTabelaPorLpPorIdFluxoStatus(), [
			"tabela" => $tabela,
			"lps" => getModsUsr("LPS"),
			"primary" => $_primary,
			"idobjeto" => $_idobjeto,
			"idfluxostatus" => $idfluxostatus
		])::exec();

		if($result->error()){
			parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
			return "";
		}else if($result->numRows() == 0 || empty($result->data[0]['permissaobotao'])){
			return "";
		}else{
			return $result->data[0]['permissaobotao'];
		}
    }

	public static function getDadosModuloPrincipal($_idobjeto) {
		$result = SQL::ini(LoteQuery::buscarModuloLotePorIdLote(), [
			"idobjeto" => $_idobjeto
		])::exec();

		if($result->error()){
			parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
			return "";
		}else if($result->numRows() == 0){
			return "";
		}else{
			return $result->data[0]['modulo'];
		}
    }

	public static function voltaResultadosFechadoRestauraAmostra($idamostra){

		$result = SQL::ini(AmostraQuery::buscarPorChavePrimaria(),[
			'pkval' => $idamostra
		])::exec();
		if($result->error() || $result->numRows() == 0) return false;

		if($result->data[0]['status'] == "CONFERIDO"){
			
			$idfluxostatus = self::getIdFluxoStatus('resultaves','FECHADO');

			$resr = AmostraController::buscarResultadosAssinadosDaAmostra($idamostra);       
			$res = AmostraController::fecharResultadosAssinados($idamostra,$idfluxostatus);
			if(!$res){
				echo("[saveposchange__amostraaves]-Erro ao atualizar resultados assinados para fechado");
			}else{
				$res = AmostraController::deletarResultadoAssinaturaPorIdamostra($idamostra); 
				
				foreach($resr as $k => $rowr){    
					$resaud = AmostraController::inserirRegistroAuditoria(cb::idempresa(),'1','u','resultado',$rowr['idresultado'],'status','FECHADO',$_SESSION["SESSAO"]["USUARIO"],$_SERVER["HTTP_REFERER"]);
				}
			}

		}
	
	}

	public static function getDadosResultadoAmostra($tabela, $_primary, $_idobjeto, $statustipo, $modulotipo = NULL, $_primaryhist = NULL, $status = NULL) {   
        
		$modulotipo = !empty($modulotipo) ? $modulotipo : $tabela;

        if($tabela == 'amostra') {
            $from = $tabela.' r';
			$criadopor = "r.criadopor";
            $unidade = 'r.idunidade';
			$idresultado = '';
        } else {
            $from = $tabela.' r JOIN amostra a ON a.idamostra = r.idamostra';
			$criadopor = "a.criadopor";
            $unidade = 'a.idunidade';
            $idresultado = ', r.idresultado';
        }

        if(!empty($status))
            $_status = "and r.status = '$status'";

        if(!empty($_primaryhist))
            $idfluxostatushist = " (SELECT idfluxostatushist FROM fluxostatushist fh WHERE fh.idmodulo = r.$_primaryhist AND fh.modulo = f.modulo ORDER BY idfluxostatushist DESC LIMIT 1) AS idfluxostatushist,";

		
		$result = SQL::ini(FluxoQuery::buscarDadosResultadoAmostra(), [
			"idfluxostatushist" => $idfluxostatushist,
			"idresultado" => $idresultado,
			"from" => $from,
			"criadopor" => $criadopor,
			"unidade" => $unidade,
			"modulotipo" => $modulotipo,
			"statustipo" => $statustipo,
			"primary" => $_primary,
			"idobjeto" => $_idobjeto,
			"status" => $_status,
		])::exec();

        if($result->error() || $result->numRows() == 0) return false;

		return $result->data[0];
    }

	public static function getFluxoStatusHist($_modulo, $_primary, $_idobjeto, $status, $idprproc = NULL, $tipo = NULL) {
        $tabela = getModuloTab($_modulo);

        if (!empty($idprproc)) {   
            $join = " JOIN prproc p ON t.idprproc = p.idprproc";
            $sql = " AND f.tipoobjeto = 'subtipo' AND f.idobjeto = p.subtipo";
            
            if (!empty($tipo)) {
                $validaTipo = " AND s.tipobotao = 'INICIO'";
                $ordem = " ORDER BY fs.ordem LIMIT 1";
            }
        }

		$result = SQL::ini(FluxoQuery::buscarFluxoStatusHist(), [
			"primary" => $_primary,
			"modulo" => $_modulo,
			"status" => $status,
			"validaTipo" => $validaTipo,
			"tabela" => $tabela,
			"join" => $join,
			"idobjeto" => $_idobjeto,
			"sql" => $sql,
			"ordem" => $ordem,
		])::exec();

        if($result->error() || $result->numRows() == 0) return false;

		return $result->data[0];
    }
	
	public static function InsertFluxoEsDiario($idfluxo, $idfluxostatus, $entrada, $saida, $saldo, $atraso, $criadoem){

			SQL::ini(FluxoStatusHistQuery::InsertFluxoEsDiario(), [
			"idfluxo" => $idfluxo,
			"idfluxostatus" => $idfluxostatus,
			"entrada" => $entrada,
			"saida" => $saida,
			"saldo" => $saldo,
			"atraso" => $atraso,
			"criadoem" => $criadoem
		])::exec();
	}

	public static function atualizaFluxoHist($idfluxostatushist){
		if(empty($idfluxostatushist)) return false;
		
		$sqlfluxostatus = SQL::ini(FluxoStatusHistQuery::buscarPrazoFLuxo(), [
			"idfluxostatushist" => $idfluxostatushist,
		])::exec();

		if ($sqlfluxostatus->error()) {
            parent::error(__CLASS__, __FUNCTION__, $sqlfluxostatus->errorMessage());
            return false;
        }

		$updateatrasodias = "";

		if($sqlfluxostatus->numRows() > 0) {
			//CALCULA DIAS EM ATRASO DO FLUXOSTATUSHIST
			$updateatrasodias = self::calculaDiasAtraso($sqlfluxostatus);
		}

		$atualizandoFluxoHist = SQL::ini(FluxoStatusHistQuery::atualizarStatusFluxostatushist(), [
			"usuario" => $_SESSION["SESSAO"]["USUARIO"],
			"idfluxostatushist" => $idfluxostatushist,
			"atrasodias" => $updateatrasodias
		])::exec();

		if ($atualizandoFluxoHist->error()) {
            parent::error(__CLASS__, __FUNCTION__, $atualizandoFluxoHist->errorMessage());
            return false;
        }
    }

	public static function calculaDiasAtraso($sqlfluxostatus){
		//CALCULAR DIAS EM ATRASO DE ACORDO COM PRAZOD DO FLUXO
		$atraso = 0;

		foreach($sqlfluxostatus->data as $rfluxostatus) {
			$sqlbuscaregistro = "SELECT ".$rfluxostatus["colprazod"]." FROM ".$rfluxostatus["tab"]." WHERE ".$rfluxostatus["col"]." = ".$rfluxostatus["idmodulo"];
			$rregistro = SQL::ini($sqlbuscaregistro)::exec();

			if ($rregistro->error()) {
				parent::error(__CLASS__, __FUNCTION__, $rregistro->errorMessage());
				return false;
			}

			$prazo = $rregistro->data[0][$rfluxostatus["colprazod"]];
			$datasaida = $rfluxostatus["alteradoem"];
			$prazodiasetapa = $rfluxostatus["prazod"];
	
			$datasaidaEsperada = date_add(date_create($prazo), date_interval_create_from_date_string($prazodiasetapa . " days"));
			$datasaidaEsperada->settime(0, 0);
			$datasaida = date_create($datasaida);
			$datasaida->settime(0, 0);

			if($datasaida > $datasaidaEsperada) {
				$diferenca = date_diff($datasaidaEsperada, $datasaida);
				$atraso = $diferenca->days;
			} else {
				$atraso = 0;
			}
		}
		return ", atrasodias = '".$atraso."'";
	}

	public static function alterarStatusFormalizacao($_modulo, $_idobjeto, $idloteativ, $status = NULL, $idfluxostatus = NULL) {
        //Atualiza o Status da Formalização de acordo com a Atividade Concluída
		$resultLoteAtiv = SQL::ini(LoteAtivQuery::buscarFluxoStatusLoteAtiv(), [
			"idloteativ" => $idloteativ,
		])::exec();
		
        if($resultLoteAtiv->numRows() > 0) {
            $status = $resultLoteAtiv->data[0]['statuspai'];
            $idfluxostatus = $resultLoteAtiv->data[0]['idfluxostatus'];
        } else {
            $status = $status;
            $idfluxostatus = $idfluxostatus;
            self::inserirFluxoStatusHist($_modulo, $_idobjeto, $idfluxostatus, 'ATIVO');
        }

        if(!empty($idfluxostatus))
			self::atualizaModuloTab('formalizacao', 'idformalizacao', $_idobjeto, $status, $idfluxostatus);

		$resultQtdAtiv = SQL::ini(LoteAtivQuery::buscarQuantidadeAtividadesCompletas(), [
			"idobjeto" => $_idobjeto,
		])::exec();

		$resultQtdAtiv = $resultQtdAtiv->data[0];

        if($resultQtdAtiv['total'] == $resultQtdAtiv['countcompleto']) {
            //Seleciono quantos estão completos para inserir o próximo fluxo

			$resultFormAtiv = SQL::ini(LoteAtivQuery::buscarFluxoStatusLoteAtivPorFormalizacao(), [
				"idobjeto" => $_idobjeto,
				"idetapa" => $resultQtdAtiv['idetapa'],
			])::exec();
			
			foreach($resultFormAtiv->data as $k => $rowLote){ 
                //Valida se tem algum idfluxostatus dentro da etapa para não repetir
				$resultFluxoAtivQtd = SQL::ini(FluxoStatusHistQuery::buscarQuantidadeFluxoStatusHistPorModuloEAtividade(), [
					"idetapa" => $resultQtdAtiv['idetapa'],
					"idmodulo" => $_idobjeto,
					"modulo" => $_modulo,
					"idfluxostatus" => $rowLote['idfluxostatus'],
				])::exec();
 
                if($resultFluxoAtivQtd->data[0]['contador'] == 0)
                    self::inserirFluxoStatusHist($_modulo, $_idobjeto, $rowLote['idfluxostatus'], 'PENDENTE');
            }
        }

        $idlote = traduzid('formalizacao','idformalizacao','idlote',$_idobjeto);

        $modulolote = self::getDadosModuloPrincipal($idlote);

        $rowFluxo = self::getFluxoStatusHist($modulolote, 'idlote', $idlote, $resultLoteAtiv->data[0]['statuspai']);

		$resultFluxoAtivQtd = SQL::ini(FluxoStatusHistQuery::buscarQuantidadeFluxoStatusHistPorModuloEFluxoStatus(), [
			"idmodulo" => $idlote,
			"modulo" => $modulolote,
			"idfluxostatus" => $rowFluxo['idfluxostatus'],
		])::exec();
        
        if(!$resultFluxoAtivQtd->error() && $resultFluxoAtivQtd->data[0]['contador'] == 0) {
            $rowFluxo['ordem'] = $rowFluxo['ordem'] + 1;
            self::alterarStatus($modulolote, 'idlote', $idlote, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], null, 0, $rowFluxo['idfluxostatus'], $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);
        }
    }

	public static function buscarHistoricoAssinaturaFluxo($_modulo, $_idobjeto){
		$tabela = getModuloTab($_modulo);

		$result = SQL::ini(CarimboQuery::buscarAssinaturaFluxo(), [
				"tabela" => $tabela,
				"idobjeto" => $_idobjeto,
				"modulo" => $_modulo,
			])::exec();

		if($result->error() || $result->numRows() == 0) return false;

		$historico = array();
		$statuscarimbo = array('ATIVO', 'ASSINADO');
		foreach($result->data as $k => $rowCarimbo){
			
			$historico[$rowCarimbo['idcarrimbo']]['criadoem'] 		= date("d/m/Y H:i:s", strtotime($rowCarimbo['criadoem']));
			$historico[$rowCarimbo['idcarrimbo']]['criadopor'] 		= $rowCarimbo['criadopor'];
			$historico[$rowCarimbo['idcarrimbo']]['alteradoem'] 	= date("d/m/Y H:i:s", strtotime($rowCarimbo['alteradoem']));
			$historico[$rowCarimbo['idcarrimbo']]['idobjetoext'] 	= $rowCarimbo['idobjetoext'];
			$historico[$rowCarimbo['idcarrimbo']]['alteradopor'] 	= $rowCarimbo['alteradopor'];
			$historico[$rowCarimbo['idcarrimbo']]['idfluxostatus'] 	= $rowCarimbo['idfluxostatus'];

			$historico[$rowCarimbo['idcarrimbo']]['status'] 
				= (!empty($rowCarimbo['idarquivo']) || in_array($rowCarimbo['status'], $statuscarimbo))
				? 'ASSINADO'
				: $rowCarimbo['status'];
		}

		return $historico;
	}

	public static function getIdEventoTipo($_modulo, $_idobjeto) {
		$sqlAnd = array(
			'sql' => '',
			'ideventotipo' => '',
			'etapa' => ''
		);

		if(empty($_modulo) || empty($_idobjeto)) return $sqlAnd;

        if($_modulo == 'evento'){
            $result = SQL::ini(FluxostatuspessoaQuery::buscarEventoTipoPorModulo(), [
				"idobjeto" => $_idobjeto,
				"modulo" => $_modulo,
			])::exec();

			if($result->error()) return $sqlAnd;

            $ideventotipo = $result->data[0]['ideventotipo'];

            $sqlAnd['sql'] 			= " AND f.idobjeto = '".$ideventotipo."' AND f.tipoobjeto = 'ideventotipo'";
			$sqlAnd['etapa'] 		= " AND e.idobjeto = '".$ideventotipo."' AND e.tipoobjeto = 'ideventotipo'";
			$sqlAnd['fluxounico'] 	= $result->data[0]['fluxounico'];
            $sqlAnd['ideventotipo'] = $ideventotipo;

        } elseif($_modulo == 'documento') {
			$result = SQL::ini(SgdocQuery::buscarPorChavePrimaria(), [
				"pkval" => $_idobjeto
			])::exec();

			if($result->error()) return $sqlAnd;

			$idsgdoctipo = $result->data[0]['idsgdoctipo'];

            $sqlAnd['sql'] 			= " AND f.idobjeto = '".$idsgdoctipo."'";
			$sqlAnd['etapa'] 		= '';
			$sqlAnd['fluxounico'] 	= '';
            $sqlAnd['ideventotipo'] = '';
        }

        return $sqlAnd;
    }

	public static function verificaLoteOP ($idlote) 
	{
        $result = SQL::ini(FormalizacaoQuery::buscarFormalizacaoPorIdLote(), [
			"idlote" => $idlote,
		])::exec();
		
		$mostrarBotao = $result->numRows() > 0 ? FALSE : TRUE;
		return $mostrarBotao;
    }

	public static function assinatura ( $_modulo, $_idobjeto ) {
		// Retorno padrão
		$status = array(
			'modulo' => $_modulo,
			'esconderbotaoass' => 'N',
			'esconderbotao' => 'N',
			'idcarrimbo' => '',
			'criadoem' => '',
			'status' => ''
		);

		if($_modulo == 'evento') return $status;

		$assinatura = '';

		$confAssinatura = SQL::ini(FluxoQuery::buscarConfiguracaoAssinaturaFluxo(), [
			"modulo" => $_modulo
		])::exec();

		if($confAssinatura->error()){
			parent::error(__CLASS__, __FUNCTION__, $confAssinatura->errorMessage());
			return $status;
		}
		
		foreach($confAssinatura as $k => $row){
			$assina = $row['assina'];

			if(!empty($assina)) {
				if($assina=='TODOS'){
					$sqls = CarimboQuery::buscarAssinaturasPendentes();
				}elseif($assina=='PARCIAL'){
					$sqls = CarimboQuery::buscarAssinaturasAtivas();
				}else{
					$sqls = CarimboQuery::buscarQuantidadeAssinaturasPendentesEAtivas();
				}

				$resAssinaturas = SQL::ini($sqls, [
					"modulo" => $_modulo,
					"idobjeto" => $_idobjeto,
				])::exec();

				if($resAssinaturas->error()){
					parent::error(__CLASS__, __FUNCTION__, $resAssinaturas->errorMessage());
					return $status;
				}

				$total = $resAssinaturas->numRows(); 
				$rwx = $resAssinaturas->data[0];

				if($total > 0 and $assina == 'TODOS'){
					$assinatura = 'ERROASSTODOS' ;
					break;
				} elseif($total < 1 and $assina == 'PARCIAL') {
					$assinatura = 'ERROASSPARCIAL' ;
					break;
				} elseif($rwx['countativo'] < $rwx['countpendente'] && $rwx['countativo'] == 0 && $assina == 'INDIVIDUAL') {
					$assinatura = 'ERROASSINDIVIDUAL' ;
					break;
				} 
			}  
		}

		$resultAssinaturas = SQL::ini(CarimboQuery::buscarAssinaturasPendentesPorPessoa(), [
				"modulo" => $_modulo,
				"idobjeto" => $_idobjeto,
				"idpessoa" => $_SESSION["SESSAO"]["IDPESSOA"],
			])::exec();

		if($resultAssinaturas->error()){
			parent::error(__CLASS__, __FUNCTION__, $resultAssinaturas->errorMessage());
			return $status;
		}

		$qtda = $resultAssinaturas->numRows();

		if($qtda > 0){
			$rowa = $resultAssinaturas->data[0];

			$status['modulo'] = $_modulo;
			$status['esconderbotaoass'] = $assinatura;
			$status['idcarrimbo'] = $rowa['idcarrimbo'];
			$status['criadoem'] = dmahms($rowa['criadoem']);
			$status['status'] = '';
		} else {
			$status['modulo'] = '';
			$status['esconderbotaoass'] = '';
			$status['idcarrimbo'] = '';
			$status['criadoem'] = '';
			$status['status'] = '';
		}
        
        return $status;
	}

	public static function buscarStatusPorModulo($modulo)
	{
		$status = SQL::ini(FluxoQuery::buscarStatusPorModulo(), [
			'modulo' => $modulo
		])::exec();

		if($status->error()){
			parent::error(__CLASS__, __FUNCTION__, $status->errorMessage());
			return [];
		}

		return $status->data;
	}

	public static function buscarFillSelectFluxoPorModuloETipoObjeto($modulo, $tipoobjeto, $idobjeto)
	{
		$_listarStatus = SQL::ini(FluxoQuery::buscarFluxoPorModuloETipoObjeto(), [
			"modulo" => $modulo,
			"tipoobjeto" => $tipoobjeto,
			"idobjeto" => $idobjeto
		])::exec();

		if($_listarStatus->error()){
			parent::error(__CLASS__, __FUNCTION__, $_listarStatus->errorMessage());
			return "";
		} else {
			$arrStatus = [];
            foreach($_listarStatus->data as $status)
            {
                $arrStatus[$status['idfluxostatus']] = $status['rotuloresp'];
            }
            return $arrStatus;
		}
	}

	public static function buscarStatusPorIdFluxoStatus($idfluxostatus)
	{
		$status = SQL::ini(FluxoStatusQuery::buscarStatusPorIdFluxoStatus(), [
			'idfluxostatus' => $idfluxostatus
		])::exec();

		if($status->error()){
			parent::error(__CLASS__, __FUNCTION__, $status->errorMessage());
			return [];
		}

		return $status->data;
	}

	public static function buscarStatusParaVinculoPorIdFluxoStatus($idFluxo, $idFluxoStatus, $toFillSelect = false)
	{
		$status = SQL::ini(_StatusQuery::buscarStatusParaVinculoPorIdFluxoStatus(), [
			'idfluxo' => $idFluxo,
			'idfluxostatus' => $idFluxoStatus
		])::exec();

		if ($status->error()) {
			parent::error(__CLASS__, __FUNCTION__, $status->errorMessage());
			return [];
		}

		if ($toFillSelect) {
			$arrRetorno = [];

			foreach ($status->data as $status)
				$arrRetorno[$status['idstatus']] = $status['rotuloresp'];

			return $arrRetorno;
		}

		return $status->data;
	}

	public static function buscarFluxoStatusHistPorModuloEFluxoStatus($_modulo, $_idobjeto, $idfluxostatus)
	{
		$status = SQL::ini(FluxoStatusHistQuery::buscarFluxoStatusHistPorModuloEFluxoStatus(), [
			"idmodulo" => $_idobjeto,
			"modulo" => $_modulo,
			"idfluxostatus" => $idfluxostatus,
			"clausula" => "LIMIT 1",
		])::exec();

		if($status->error()){
			parent::error(__CLASS__, __FUNCTION__, $status->errorMessage());
			return false;
		} 

		return $status->data;
	}

	public static function buscarUltimoStatusHist($idLote) {
		$result = SQL::ini(FluxoStatusHistQuery::buscarUltimoStatusHist(), [
			"idlote" => $idLote
		])::exec();

		if($result->error() || $result->numRows() == 0) return false;

		return $result->data[0];
	}
	
	public static function CountUnidadeResultado($idresultado){
		$arrResult = InclusaoResultadoController::buscarTipoUnidadeAmostra($idresultado);
        $i = 0;
        foreach ($arrResult as $_arrResult) {
            $lista[$i] = $_arrResult['idresultado'];
            $i++;
        }
        return $lista;
	}

	public static function buscarIdfluxostatusInicioPorModulo($modulo)  {
		$result = SQL::ini(FluxoQuery::buscarIdfluxostatusInicioPorModulo(), [
			'modulo' => $modulo
		])::exec();

		if($result->error()){
			parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
			return false;
		} 

		return $result->data[0]['idfluxostatus'];
	}
}
?>