<?

require_once(__DIR__ . "/../../api/notifitem/notif.php");

// CONTROLLERS
require_once(__DIR__ . "/_controller.php");
require_once(__DIR__ . "/log_controller.php");

// QUERYS
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/evento_query.php");
require_once(__DIR__ . "/../querys/_modulo_query.php");
require_once(__DIR__ . "/../querys/sgdoctipodocumento_query.php");
require_once(__DIR__ . "/../querys/eventoobj_query.php");
require_once(__DIR__ . "/../querys/fluxostatuspessoa_query.php");
require_once(__DIR__ . "/../querys/eventotipocampos_query.php");
require_once(__DIR__ . "/../querys/fluxostatushist_query.php");
require_once(__DIR__ . "/../querys/modulocom_query.php");
require_once(__DIR__ . "/../querys/eventoadd_query.php");
require_once(__DIR__ . "/../querys/eventotipoadd_query.php");
require_once(__DIR__ . "/../querys/carimbo_query.php");
require_once(__DIR__ . "/../querys/arquivo_query.php");
require_once(__DIR__ . "/../querys/eventochecklist_query.php");
require_once(__DIR__ . "/../querys/eventochecklistitem_query.php");
require_once(__DIR__ . "/../querys/log_query.php");
require_once(__DIR__ . "/../querys/vwevento_query.php");
require_once(__DIR__ . "/../querys/pessoaobjeto_query.php");
require_once(__DIR__ . "/../querys/eventorelacionamento_query.php");

class EventoController extends Controller
{
	public static $formasAtendmento = [
		'TELEFONE' => 'Telefone',
		'EMAIL' => 'Email',
		'PESSOAL' => 'Pessoal',
		'CORRESPONDENCIA' => 'Correspondência',
		'SITESAC' => 'Site/Sac'
	];

	public static $peridiocidade = [
		'DIARIO' => 'Diario',
		'SEMANAL' => 'Semanal',
		'MENSAL' => 'Mensal',
		'BIMESTRAL' => 'Bimestral',
		'TRIMESTRAL' => 'Trimestral',
		'SEMESTRAL' => 'Semestral',
		'ANUAL' => 'Anual',
		'BIANUAL' => 'Bianual',
		'TRIANUAL' => 'Trianual'
	];

	public static $fimDeSemana = [
		'Y' => 'Sim',
		'N' => 'Não'
	];

	public static function buscarPorChavePrimaria($idEvento)
	{
		$evento = SQL::ini(EventoQuery::buscarPorChavePrimaria(), [
			'pkval' => $idEvento
		])::exec();

		if ($evento->error()) {
			parent::error(__CLASS__, __FUNCTION__, $evento->errorMessage());
			return false;
		}

		return $evento->data[0];
	}

	public static function inserirComentarioEvento($idevento, $comentario, $usuario)
	{
		$results = SQL::ini(EventoQuery::inserirComentarioEvento(), [
			"idempresa" => cb::idempresa(),
			"idevento" => $idevento,
			"descricao" => $comentario,
			"usuario" => $usuario,
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return false;
		} else {
			return true;
		}
	}

	public static function buscarCamposTituloEventoComCodePorIdEventoTipo($idEventoTipo)
	{
		$arrRetorno = [];
		$campos = SQL::ini(EventoTipoQuery::buscarCamposTituloEventoComCodePorIdEventoTipo(), [
			'ideventotipo' => $idEventoTipo
		])::exec();

		if ($campos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $campos->errorMessage());
			return false;
		}

		foreach ($campos->data as $campo) {
			$arrRetorno[$campo['ideventotipo']] = $campo;
		}

		return $arrRetorno;
	}

	public static function inserirEvento($idEvento, $evento, $dataInicioEvento, $dataFimEvento, $tokeninicial)
	{
		// VERIFICA SE JÁ EXISTE UM EVENTO FILHO COM A DATA ESPECIFICADA
		$eventos = SQL::ini(EventoQuery::buscarEventosFilhosPorDataInicio(), [
			'datainicio' => $dataInicioEvento,
			'idevento' => $idEvento
		])::exec();

		$criar = true;
		$ideventofilho = "";

		foreach ($eventos->data as $item) {
			$criar = false;
			$ideventofilho .= ' ' . $item['idevento'];
		}

		// CASO NEGATIVO, CRIA O EVENTO FILHO PARA A DATA ESPECIFICADA
		if ($criar) {
			$dadosEvento = [
				'ideventotipo' => $evento['ideventotipo'],
				'idempresa' => $evento['idempresa'],
				'ideventopai' => $evento['idevento'],
				'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"],
				'idequipamento' => $evento['idequipamento'] ?? 0,
				'idpessoaev' => $evento['idpessoaev'] ?? 0,
				'modulo' => '',
				'idmodulo' => 'null',
				'idsgsetor' => 'null',
				'idsgdepartamento' => 'null',
				'jsonhistorico' => '{}',
				'evento' => addslashes($evento['evento']),
				'status' => '',
				'idfluxostatus' => $tokeninicial,
				'prazo' => "'$dataInicioEvento'",
				'jsonconfig' => '{}',
				'descricao' => addslashes($evento['descricao']),
				'inicio' => "'$dataInicioEvento'",
				'iniciohms' => "'{$evento['iniciohms']}'",
				'fim' => "'$dataFimEvento'",
				'fimhms' => "'{$evento['fimhms']}'",
				'duracaohms' => "''",
				'periodicidade' => '',
				'repetirevento' => '',
				'repetirate' => 'null',
				'fimsemana' =>  'N',
				'resultado' => $evento['resultado'],
				'versao' => $evento['versao'],
				'jsonresultado' => '{}',
				'cor' => '',
				'servico' => '',
				'nomecompleto' => '',
				'complemento' =>  '',
				'formaatendimento' => '',
				'classificacao' => '',
				'textocurto1' => '',
				'textocurto2' => '',
				'textocurto3' => '',
				'textocurto4' =>  '',
				'textocurto5' => '',
				'textocurto6' => '',
				'textocurto7' => '',
				'textocurto8' => '',
				'textocurto9' => '',
				'textocurto10' => '',
				'textocurto11' => '',
				'textocurto12' => '',
				'textocurto13' => '',
				'textocurto14' => '',
				'textocurto15' => '',
				'prioridade' => '',
				'diainteiro' => '',
				'datainicio' => "null",
				'datafim' => "null",
				'horainicio' =>  "null",
				'horafim' => "null",
				'criadopor' => $evento['criadopor'],
				'criadoem' => "'{$evento['criadoem']}'",
				'alteradopor' => $evento['alteradopor'],
				'alteradoem' => "'{$evento['alteradoem']}'",
				'motivo' => '',
				'idsgdoc' => $evento['idsgdoc']
			];

			$inserindoEvento = SQL::ini(EventoQuery::inserir(), $dadosEvento)::exec();

			$idEventoInserido = $inserindoEvento->lastInsertId();

			//Select o titulo do Evento Pai (LTM - 10/07/2020)
			$eventosAdd = SQL::ini(EventoAddQuery::buscarObjetosPorIdEvento(), [
				'idevento' => $idEvento
			])::exec();

			foreach ($eventosAdd->data as $eventoAdd) {
				$dadosEventoAdd = [
					'idempresa' => $eventoAdd['idempresa'],
					'idobjeto' => $eventoAdd['idobjeto'],
					'objeto' => $eventoAdd['objeto'],
					'idevento' => $idEventoInserido,
					'titulo' => $eventoAdd['titulo'],
					'observacao' => '',
					'ord' => 'null',
					'tipoobjeto' => $eventoAdd['tipoobjeto'],
					'criadopor' => $_SESSION["SESSAO"]["USUARIO"],
					'criadoem' => 'now()',
					'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
					'alteradoem' => 'now()',
				];

				$inserindoEventoAdd = SQL::ini(EventoAddQuery::inserir(), $dadosEventoAdd)::exec();
			}

			$eventoObj = SQL::ini(EventoObjQuery::buscarEventoObjPorIdEvento(), [
				'idevento' => $idEvento
			])::exec();

			foreach ($eventoObj->data as $item) {
				self::inserirEventoObj($item, $idEventoInserido);
			}

			$fluxostatusPessoa = SQL::ini(FluxostatusPessoaQuery::buscarFluxoStatuspessoaPorIdEvento(), [
				'idevento' => $idEvento
			])::exec();

			foreach ($fluxostatusPessoa->data as $pessoa) {
				EventoController::inserirEmFluxostatusPessoa(
					$pessoa["idpessoa"],
					$pessoa["idempresa"],
					$idEventoInserido,
					$pessoa['idobjeto'],
					$pessoa['tipoobjeto'],
					$pessoa['status'],
					$pessoa['idfluxostatus'],
					$pessoa['oculto'],
					$pessoa['idobjetoext'],
					$pessoa['tipoobjetoext'],
					'null',
					$pessoa['inseridomanualmente'],
					$pessoa['visualizado'],
					$pessoa['assinar'],
					$pessoa['editar']
				);
			}
		} else {
			// CASO POSITIVO, ATUALIZA OS DADOS DO EVENTO FILHO
			// @TODO: AJUSTAR PARA ATUALIZAR SOMENTE OS EVENTOS FILHOS NÃO FINALIZADOS
			$ideventofilho = str_replace(' ', ',', trim($ideventofilho));

			$dadosEventosFilhos = [
				'evento' => $evento['evento'],
				'inicio' => $dataInicioEvento,
				'prazo' => $dataInicioEvento,
				'iniciohms' => $evento['iniciohms'],
				'fim' => $dataFimEvento,
				'fimhms' => $evento['fimhms'],
				'ideventofilho' => $ideventofilho
			];

			$atualizandoEventosFilhos = SQL::ini(EventoQuery::atualizarEventosFilhos(), $dadosEventosFilhos)::exec();

			//atualizar eventoobj nos filhos

			$eventosAdicionais = SQL::ini(EventoQuery::buscarEventosAdicionais(), [
				'idevento' => $ideventofilho
			])::exec();


			foreach ($eventosAdicionais->data as $item) {
				self::inserirEventoObj($item, $ideventofilho);
			}

			$pessoasResponsaveis = SQL::ini(EventoQuery::buscarPessoasResponsaveis(), [
				'idevento' => $ideventofilho
			])::exec();

			foreach ($pessoasResponsaveis->data as $pessoa) {
				$inserindoPessoaResponsavel = self::inserirEmFluxostatusPessoa(
					$pessoa["idpessoa"],
					$pessoa["idempresa"],
					$ideventofilho,
					$pessoa['idobjeto'],
					$pessoa['tipoobjeto'],
					$pessoa['status'],
					$pessoa['idfluxostatus'],
					$pessoa['oculto'],
					$pessoa['idobjetoext'] ? $pessoa['idobjetoext'] : 'null',
					$pessoa['tipoobjetoext'],
					'null',
					$pessoa['inseridomanualmente'],
					$pessoa['visualizado'],
					$pessoa['assinar'],
					$pessoa['editar']
				);
			}
		}
	}

	public static function inserirEventoObj($evento, $idEventoInserido)
	{
		// Busca o Id do eventoadd correspondente ao novo evento criado (LTM - 10-07-2020)
		$eventoAdd = SQL::ini(EventoAddQuery::buscarEventoAddPorIdEvento(), [
			'idevento' => $idEventoInserido
		])::exec();

		$dadosEventoObj = [
			'idevento' => $idEventoInserido,
			'ideventoadd' => $eventoAdd->data[0]['ideventoadd'],
			'idobjeto' => $evento['idobjeto'],
			'objeto' => $evento['objeto'],
			'idempresa' => $evento['idempresa'],
			'minimo' => $evento['minimo'],
			'maximo' => $evento['maximo'],
			'atual' => $evento['atual'],
			'resultado' => $evento['resultado'],
			'obs' => $evento['obs'],
			'conclusao' => $evento['conclusao'],
			'status' => $evento['status'],
			'criadopor' => $evento['criadopor'],
			'criadoem' => $evento['criadoem'],
			'datainicio' => $evento['datainicio'] ? "'{$evento['datainicio']}'" : 'null',
			'alteradopor' => $evento['alteradopor'],
			'alteradoem' => $evento['alteradoem'],
			'datafim' => $evento['datafim'] ? "'{$evento['datafim']}'" : 'null',
			'horainicio' => $evento['horainicio'] ? "'{$evento['horainicio']}'" : 'null',
			'horafim' => $evento['horafim'] ? "'{$evento['horafim']}'" : 'null',
			'ord' => $evento['ord'] ? $evento['ord'] : 'null'
		];

		$inserindoEventoObj = SQL::ini(EventoObjQuery::inserir(), $dadosEventoObj)::exec();
	}

	public static function buscarPorChavePrimariaPadrao($idEvento)
	{
		$evento = SQL::ini(EventoQuery::buscarPorChavePrimariaPadrao(), [
			'pkval' => $idEvento
		])::exec();

		if ($evento->error()) {
			parent::error(__CLASS__, __FUNCTION__, $evento->errorMessage());
			return false;
		}

		return $evento->data[0];
	}

	public static function arredondarMinutoParaCima(\DateTime $dateTime, $minuteInterval = 30)
	{
		return $dateTime->setTime(
			$dateTime->format('H'),
			ceil($dateTime->format('i') / $minuteInterval) * $minuteInterval,
			0
		);
	}

	/*
    * Centralizar a consulta de Módulo
    * Evitar falhas em relação à  Módulos Vinculados
    * Complementar com as colunas necessárias diretamente na consulta
    */
	public static function retornaChaveModuloEvento($inModulo, $inbypass = false)
	{
		if (empty($inModulo)) {
			parent::error(__CLASS__, __FUNCTION__, "Parâmetro inModulo não informado");

			die("retArrModuloConf: Parâmetro inModulo não informado");
		}

		//Permite reaproveitamento sem verificação de segurança. Ex: Tela de _modulo necessita recuperar informaçàµes do módulo mesmo que não estejam devidamente atribuà­das em alguma LP
		if ($inbypass !== true) {
			$joinLp = ($_SESSION["SESSAO"]["LOGADO"]) ? "left join " . _DBCARBON . "._lpmodulo l on (l.modulo=m.modulo and l.idlp='" . $_SESSION["SESSAO"]["IDLP"] . "')" : "";
			$whereMod = ($_SESSION["SESSAO"]["LOGADO"]) ? "and m.modulo in (" . getModsUsr("SQLWHEREMOD") . ")" : "";
			$ifrestaurar = (getModsUsr("SQLWHEREMOD")) ? ",IF(1=(('restaurar' in  (" . getModsUsr("SQLWHEREMOD") . "))),'Y','N') as oprestaurar" : "";
		}

		$modulo = SQL::ini(_ModuloQuery::buscarModuloComChavePrimariaPorModulo(), [
			'modulo' => $inModulo
		])::exec();

		if ($modulo->error()) {
			parent::error(__CLASS__, __FUNCTION__, $modulo->errorMessage());
			return false;
		}
		return ($modulo->data[0]['chavefts']);
	}

	public static function buscarVariaveisDoEvento()
	{
		return EventoQuery::buscarVariaveisDoEvento();
	}

	public static function buscarTokenInicialDoEvento($idevento)
	{
		$evento = SQL::ini(EventoQuery::buscarTokenInicialDoEventoPorIdEvento(), [
			'idevento' => $idevento
		])::exec();

		if ($evento->error()) {
			parent::error(__CLASS__, __FUNCTION__, $evento->errorMessage());
			return false;
		}

		return $evento->data[0]['idfluxostatus'];
	}

	public static function atualizarStatusParaLidoPorIdEventoEIdPessoa($idevento, $idPessoa)
	{
		$atualizandoStatusDoEvento = SQL::ini(EventoQuery::atualizarStatusParaLidoPorIdEventoEIdPessoa(), [
			'idevento' => $idevento,
			'idpessoa' => $idPessoa
		])::exec();

		if ($atualizandoStatusDoEvento->error()) {
			parent::error(__CLASS__, __FUNCTION__, $atualizandoStatusDoEvento->errorMessage());
			return false;
		}
	}

	public static function buscarDuracao()
	{
		/*
         * Alterado para aparecer até 6 horas. Caso precise de mais, tem a opção de marcar o dia todo.
         * Até 1 hora de 15 em 15 minutos, até as 2 de 30 em 30 e acima de 2, de 1 em 1
         * 30-01-2020 - Lidiane
         */
		$i = 0;

		$arrDuracao = array();
		$minutoPadrao = 15;

		for ($h = 0; $h < 12; $h++) {
			$hora = str_pad($h, 2, "0", STR_PAD_LEFT);

			for ($i = 1; $i <= 4; $i++) {
				$minuto = str_pad($minutoPadrao * $i, 2, "0", STR_PAD_LEFT);
				$label = "$hora h $minuto min";

				if ($i == 4) {
					$hora = str_pad($h + 1, 2, "0", STR_PAD_LEFT);
					$minuto = "00";

					$label = "$hora h";
				}

				if (($h < 1) && ($i != 4))
					$label = "$minuto min";

				$arrDuracao["$hora:$minuto"] = $label;
			}
		}

		return $arrDuracao;
	}

	public static function buscarHorarioComercial()
	{
		$hora = 0;

		$minutoEmSegundos = 45 * 3600;

		$arrDuracao = [];

		for ($h = 0; $h <= 91; $h++) {
			$minutoEmSegundos += (15 * 3600);

			$minuto = $minutoEmSegundos / 3600;

			if ($minuto >= 60) {
				$hora++;
				$minutoEmSegundos = 0;
				$minuto = 0;
			};

			$horaFormatada = str_pad($hora, 2, '0', STR_PAD_LEFT) . ":" . str_pad($minuto, 2, '0');

			$arrDuracao[$horaFormatada] = $horaFormatada;
		}

		return $arrDuracao;
	}

	public static function buscarMotivos($autocomplete = false)
	{
		$motivos = SQL::ini(SgdoctipodocumentoQuery::buscarMotivos())::exec();

		if ($motivos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $motivos->errorMessage());
			return false;
		}

		if ($autocomplete) {
			$arrRetorno = [];

			foreach ($motivos->data as $key => $motivo) {
				$arrRetorno[$key]['label'] = $motivo['tipodocumento'];
				$arrRetorno[$key]['value'] = $motivo['idsgdoctipodocumento'];
			}

			return $arrRetorno;
		}

		return $motivos->data;
	}

	public static function buscarPermissoesPorIdEvento($idEvento)
	{
		$permissoes = SQL::ini(FluxostatuspessoaQuery::buscarPermissoesPorIdEventoEIdPessoa(), [
			'idevento' => $idEvento,
			'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"]
		])::exec();

		if ($permissoes->error()) {
			parent::error(__CLASS__, __FUNCTION__, $permissoes->errorMessage());
			return false;
		}

		return $permissoes->data;
	}
	public static function buscarPermissoesAbertoPorIdEvento($idEvento)
	{
		$permissoes = SQL::ini(FluxostatuspessoaQuery::buscarPermissoesAbertoPorIdEvento(), [
			'idevento' => $idEvento,
			'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"]
		])::exec();

		if ($permissoes->error()) {
			parent::error(__CLASS__, __FUNCTION__, $permissoes->errorMessage());
			return false;
		}

		return $permissoes->data;
	}

	public static function buscarCamposVisiveisPorIdEventoTipo($idEventoTipo)
	{
		$camposVisiveis = SQL::ini(EventoTipoCamposQuery::buscarCamposVisiveisPorIdEventoTipo(), [
			'ideventotipo' => $idEventoTipo
		])::exec();

		if ($camposVisiveis->error()) {
			parent::error(__CLASS__, __FUNCTION__, $camposVisiveis->errorMessage());
			return false;
		}

		return $camposVisiveis->data;
	}

	public static function buscarCamposVisiveisEventoTipoAdd($idEventoTipo)
	{
		$camposVisiveis = SQL::ini(EventoTipoCamposQuery::buscarCamposVisiveisEventoTipoAdd(), [
			'ideventotipo' => $idEventoTipo
		])::exec();

		if ($camposVisiveis->error()) {
			parent::error(__CLASS__, __FUNCTION__, $camposVisiveis->errorMessage());
			return false;
		}

		return $camposVisiveis->data;
	}

	public static function buscarCamposVisiveisPorIdEventoTipoAdd($idEventoTipo)
	{
		$camposVisiveis = SQL::ini(EventoTipoCamposQuery::buscarCamposVisiveisPorIdEventoTipoAdd(), [
			'ideventotipoadd' => $idEventoTipo
		])::exec();

		if ($camposVisiveis->error()) {
			parent::error(__CLASS__, __FUNCTION__, $camposVisiveis->errorMessage());
			return false;
		}

		return $camposVisiveis->data;
	}

	public static function buscarFluxoStatusHistPorIdEvento($idModulo)
	{
		$fluxoStatusHist = SQL::ini(FluxoStatusHistQuery::buscarFluxoStatusHistPorIdEvento(), [
			'idmodulo' => $idModulo
		])::exec();

		if ($fluxoStatusHist->error()) {
			parent::error(__CLASS__, __FUNCTION__, $fluxoStatusHist->errorMessage());
			return false;
		}

		return $fluxoStatusHist->data;
	}

	public static function buscarEventoFilhoPorIdModulo($idModulo)
	{
		$eventoFilho = SQL::ini(EventoObjQuery::buscarEventoFilhoPorIdModulo(), [
			'idmodulo' => $idModulo
		])::exec();

		if ($eventoFilho->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventoFilho->errorMessage());
			return false;
		}

		return $eventoFilho->data[0];
	}

	public static function buscarListaDeComentariosDoEvento($idEvento)
	{
		$comentarios = SQL::ini(ModulocomQuery::buscarListaDeComentariosDoEvento(), [
			'idevento' => $idEvento
		])::exec();

		if ($comentarios->error()) {
			parent::error(__CLASS__, __FUNCTION__, $comentarios->errorMessage());
			return false;
		}

		return $comentarios->data;
	}

	public static function buscarEventoAddPorIdEvento($idEvento)
	{
		$eventosAdd = SQL::ini(EventoAddQuery::buscarEventoAddPorIdEvento(), [
			'idevento' => $idEvento
		])::exec();

		if ($eventosAdd->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventosAdd->errorMessage());
			return false;
		}

		return $eventosAdd->data;
	}

	public static function buscarEventoTipoAddPorChavePrimaria($idEventoTipoAdd)
	{
		$eventoTipoAdd = SQL::ini(EventoTipoAddQuery::buscarPorChavePrimaria(), [
			'pkval' => $idEventoTipoAdd
		])::exec();

		if ($eventoTipoAdd->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventoTipoAdd->errorMessage());
			return false;
		}

		return $eventoTipoAdd->data[0];
	}

	public static function buscarCorPorIdEventoEIdEventoObj($idEvento, $idEventoObj)
	{
		$evento = SQL::ini(EventoObjQuery::buscarCorPorIdEventoEIdEventoObj(), [
			'idevento' => $idEvento,
			'ideventoobj' => $idEventoObj
		])::exec();

		if ($evento->error()) {
			parent::error(__CLASS__, __FUNCTION__, $evento->errorMessage());
			return false;
		}

		return $evento->data;
	}

	public static function buscarCamposObjPorIdEventoEIdEventoAdd($idEvento, $idEventoAdd, $objeto)
	{
		$eventosAdd = SQL::ini(EventoObjQuery::buscarCamposObjPorIdEventoEIdEventoAdd(), [
			'idevento' => $idEvento,
			'ideventoadd' => $idEventoAdd,
			'objeto' => $objeto
		])::exec();

		if ($eventosAdd->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventosAdd->errorMessage());
			return false;
		}

		return $eventosAdd->data;
	}

	public static function atualizarJsonConfigCampos($nomeCampo, $valor, $ideventoobj)
	{
		$atualizaCampos = SQL::ini(EventoAddQuery::atualizarJsonConfigCampos(), [
			'nomeCampo' => $nomeCampo,
			'valor' => $valor,
			'ideventoobj' => $ideventoobj
		])::exec();

		if ($atualizaCampos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $atualizaCampos->errorMessage());
			return false;
		}

		return $atualizaCampos->data;
	}

	public static function inserirJsonConfigCampos($jsonpreferencias, $ideventoobj)
	{
		$campos = SQL::ini(EventoAddQuery::inserirJsonConfigCampos(), [
			'jsonpreferencias' => $jsonpreferencias,
			'ideventoobj' => $ideventoobj
		])::exec();

		if ($campos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $campos->errorMessage());
			return false;
		}

		return $campos->data;
	}

	public static function buscarStatusDoUsuarioNoEventoPorIdPessoaEIdModulo($idPessoa, $idModulo)
	{
		$evento = SQL::ini(EventoObjQuery::buscarStatusDoUsuarioNoEventoPorIdPessoaEIdModulo(), [
			'idpessoa' => $idPessoa,
			'idmodulo' => $idModulo
		])::exec();

		if ($evento->error()) {
			parent::error(__CLASS__, __FUNCTION__, $evento->errorMessage());
			return false;
		}

		return $evento->data;
	}

	public static function buscarEventoTipoBlocoPorIdEventoTipo($idEventoTipo)
	{
		$eventoTipoBloco = SQL::ini(EventoTipoAddQuery::buscarEventoTipoBlocoPorIdEventoTipo(), [
			'ideventotipo' => $idEventoTipo
		])::exec();

		if ($eventoTipoBloco->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventoTipoBloco->errorMessage());
			return false;
		}

		return $eventoTipoBloco->data;
	}

	public static function buscarEventosFilhosPorIdEvento($idEvento)
	{
		$eventos = SQL::ini(EventoQuery::buscarEventosFilhosPorIdEvento(), [
			'idevento' => $idEvento
		])::exec();

		if ($eventos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventos->errorMessage());
			return false;
		}

		return $eventos->data;
	}

	public static function buscarEquipamentosPorIdEvento($idEvento)
	{
		$eventos = SQL::ini(EventoQuery::buscarEquipamentosPorIdEvento(), [
			'idevento' => $idEvento
		])::exec();

		if ($eventos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventos->errorMessage());
			return false;
		}

		return $eventos->data;
	}

	public static function buscarEventoObjPorChavePrimaria($chavePrimaria)
	{
		$eventosObj = SQL::ini(EventoObjQuery::buscarPorChavePrimaria(), [
			'pkval' => $chavePrimaria
		])::exec();

		if ($eventosObj->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventosObj->errorMessage());
			return false;
		}

		return $eventosObj->data;
	}

	public static function excluirAssinaturaPorIdFluxostatusPessoa($idFluxostatusPessoa)
	{
		$eventos = SQL::ini(FluxostatusPessoaQuery::buscarEventosPorIdFluxostatusPessoa(), [
			'idfluxostatuspessoa' => $idFluxostatusPessoa
		])::exec();

		if ($eventos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventos->errorMessage());
			return false;
		}

		foreach ($eventos->data as $evento) {
			$idobjeto = $evento['idobjeto'];
			$tipoobjeto = $evento['tipoobjeto'];
			$idevento = $evento['idevento'];

			if ($evento["modulo"] and $evento["idmodulo"]) {
				$excluindoAssinatura = SQL::ini(CarimboQuery::deletarPorIdObjetoTipoObjetoEIdPessoa(), [
					'idobjeto' => $evento["idmodulo"],
					'tipoobjeto' => $evento["modulo"],
					'idpessoa' => $evento["idobjeto"]
				])::exec();
			}
		}

		if ($tipoobjeto == 'imgrupo') {
			$fluxoStatusPessoa = SQL::ini(FluxostatuspessoaQuery::buscarFluxoStatuspessoaPorIdObjetoExtEIdEvento(), [
				'idobjetoext' => $idobjeto,
				'idevento' => $idevento
			])::exec();

			$c = 10;

			foreach ($fluxoStatusPessoa->data as $item) {
				$c++;

				$_SESSION['arrpostbuffer'][$c]['d']['fluxostatuspessoa']['idfluxostatuspessoa'] = $item['idfluxostatuspessoa'];
			}
		}
	}

	public static function deleterArquivoPorIdArquivo($idArquivo, $tipoObjeto)
	{
		$arquivo = SQL::ini(ArquivoQuery::buscarArquivoPorIdArquivoETipoObjeto(), [
			'idarquivo' => $idArquivo,
			'tipoobjeto' => $tipoObjeto
		])::exec();

		if ($arquivo->numRows()) {
			$removendoModuloCom = SQL::ini(ModulocomQuery::deletarPorIdEventoEDescricao(), [
				'idevento' => $arquivo->data[0]["idevento"],
				'descricao' => $arquivo->data[0]["nome"]
			])::exec();

			unlink($arquivo->data[0]["caminho"]);
		}
	}

	public static function inserirCamposEspecificosDeTiPorIdEvento($idEvento)
	{
		$eventosObj = SQL::ini(EventoObjQuery::buscarEventoObjPorIdEvento(), [
			'idevento' => $idEvento
		])::exec();

		if ($eventosObj->numRows() == 0) {
			$arrDados = [
				'idevento' => '',
				'idevento' => $idEvento,
				'ideventoadd' => 13,
				'idobjeto' => 98505,
				'objeto' => 'pessoa',
				'idempresa' => 'null',
				'minimo' => '',
				'maximo' => 'null',
				'atual' => '0.1 ESTUDO DE VIABILIDADE',
				'resultado' => 'null',
				'obs' => 'null',
				'conclusao' => 'null',
				'status' => '',
				'criadopor' => $_SESSION["SESSAO"]["USUARIO"],
				'criadoem' => 'NOW()',
				'datainicio' => '43901',
				'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
				'alteradoem' => 'NOW()',
				'datafim' => '43921',
				'horainicio' => 'null',
				'horafim' => 'null',
				'ord' => 'null'
			];

			$inserindoDados1 = SQL::ini(EventoObjQuery::inserir(), $arrDados)::exec();

			$arrDados['ideventoadd'] = 52;
			$arrDados['atual'] = '1.1 ANÁLISE DO PROJETO';
			$arrDados['datainicio'] = 'null';
			$arrDados['datafim'] = 'null';

			$inserindoDados2 = SQL::ini(EventoObjQuery::inserir(), $arrDados)::exec();

			$arrDados['atual'] = '1.2 ELABORAÇÃO DAS ATIVIDADES';

			$inserindoDados3 = SQL::ini(EventoObjQuery::inserir(), $arrDados)::exec();

			$arrDados['atual'] = '1.3 CRIAÇÃO DE DOCUMENTAÇÃO';

			$inserindoDados4 = SQL::ini(EventoObjQuery::inserir(), $arrDados)::exec();

			$arrDados['atual'] = '1.4 ESTIMATIVA';

			$inserindoDados5 = SQL::ini(EventoObjQuery::inserir(), $arrDados)::exec();

			$arrDados['ideventoadd'] = 54;
			$arrDados['atual'] = '2.1 ESTUDO';

			$inserindoDados6 = SQL::ini(EventoObjQuery::inserir(), $arrDados)::exec();

			$arrDados['atual'] = '2.2 CODIFICAÇÃO';

			$inserindoDados7 = SQL::ini(EventoObjQuery::inserir(), $arrDados)::exec();

			$arrDados['atual'] = '2.3 TESTES';

			$inserindoDados8 = SQL::ini(EventoObjQuery::inserir(), $arrDados)::exec();

			$arrDados['ideventoadd'] = 60;
			$arrDados['atual'] = '3.1 IMPLANTAÇÃO EM HOMOLOGAÇÃO';

			$inserindoDados9 = SQL::ini(EventoObjQuery::inserir(), $arrDados)::exec();

			$arrDados['atual'] = '3.2 TESTES';

			$inserindoDados10 = SQL::ini(EventoObjQuery::inserir(), $arrDados)::exec();

			$arrDados['ideventoadd'] = 61;
			$arrDados['atual'] = '4.1 IMPLANTAÇÃO EM PRODUÇÃO';

			$inserindoDados11 = SQL::ini(EventoObjQuery::inserir(), $arrDados)::exec();

			$arrDados['ideventoadd'] = 61;
			$arrDados['atual'] = '4.2 TESTES';

			$inserindoDados11 = SQL::ini(EventoObjQuery::inserir(), $arrDados)::exec();
		}
	}

	//VERIFICA SE FOI INSERIDO O CRIADOR DO EVENTO NA LISTA DE PARTICIPANTES.. 
	//SE NEGATIVO, INSERE O MESMO.
	public static function verificarSeCriadorFoiInseridoNoEvento($idEvento, $idFluxostatus)
	{
		$pessoaInseridaNoEvento = SQL::ini(EventoQuery::buscarPessoasVinculadasNoEventoPorIdEvento(), [
			'idevento' => $idEvento
		])::exec();

		if ($pessoaInseridaNoEvento->error()) {
			parent::error(__CLASS__, __FUNCTION__, $pessoaInseridaNoEvento->errorMessage());
			return false;
		}

		if (!$pessoaInseridaNoEvento->numRows() || !$pessoaInseridaNoEvento->data[0]['idfluxostatuspessoa']) {
			self::inserirEmFluxostatusPessoa($pessoaInseridaNoEvento->data[0]['idpessoa'], $pessoaInseridaNoEvento->data[0]['idempresa'], $idEvento, $pessoaInseridaNoEvento->data[0]['idpessoa'], 'pessoa', 'null', $idFluxostatus);
		}

		return $pessoaInseridaNoEvento->data[0];
	}

	public static function inserirEmFluxostatusPessoa($idcriador, $idempresa, $idevento, $idobjeto, $tipoobjeto, $status = 'null', $idfluxostatus, $oculto = 0, $idobjetoext = 'null', $tipodobjetoext = 'null', $rec = 'null', $inseridoManualmente = 'N', $visualizado = 0, $assinar = 'N', $editar = 'null')
	{
		$objeto = self::buscarObjetoNoEvento($idevento, 'evento', $idobjeto, $tipoobjeto);

		if ($objeto) {
			$idfluxostatuspessoa = $objeto['idfluxostatuspessoa'];
		} else {
			$campos = 'idobjetoext, tipoobjetoext';
			$valores = ($idobjetoext ? $idobjetoext : 'null') . "," . ($tipodobjetoext ? "'$tipodobjetoext'" : 'null');

			$dados = [
				'idpessoa' => $idcriador,
				'idempresa' => $idempresa,
				'idmodulo' => $idevento,
				'modulo' => 'evento',
				'idobjeto' => $idobjeto,
				'tipoobjeto' => $tipoobjeto,
				'status' => $status,
				'idfluxostatus' => $idfluxostatus,
				'oculto' => $oculto,
				'inseridomanualmente' => $inseridoManualmente,
				'visualizado' => $visualizado,
				'assinar' => $assinar,
				'editar' => $editar,
				'campos' => $campos,
				'valores' => $valores,
				'criadopor' => $_SESSION["SESSAO"]["USUARIO"],
				'criadoem' => 'now()',
				'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
				'alteradoem' => 'now()'
			];
			$inserindoPessoas = SQL::ini(FluxostatusPessoaQuery::inserir(), $dados)::exec();

			if ($inserindoPessoas->error()) {
				$dadosLog = [
					'idempresa' => $_SESSION["SESSAO"]["IDEMPRESA"],
					'sessao' => session_id(),
					'tipoobjeto' => 'evento',
					'idobjeto' => $idevento,
					'tipolog' => 'saveposchange__evento',
					'log' => 'Erro:' . $inserindoPessoas->sql(),
					'status' => '',
					'info' => $inserindoPessoas->errorMessage(),
					'criadoem' => "NOW()",
					'data' => "NOW()"
				];

				$inserindoLog = LogController::inserir($dadosLog);
			}

			$idfluxostatuspessoa = $inserindoPessoas->lastInsertId();
		}

		if ($rec == 'insereParticipantes') {
			self::inserirEmFluxostatusHist($idfluxostatuspessoa, $idfluxostatus, $idevento, $_SESSION["SESSAO"]["IDEMPRESA"]);
		}
	}

	public static function inserirEmFluxostatusHist($idfluxostatuspessoa, $idfluxostatus, $idevento, $idempresa)
	{
		$dados = [
			'idempresa' => $idempresa,
			'idfluxostatus' => $idfluxostatus,
			'idfluxostatuspessoa' => $idfluxostatuspessoa,
			'idmodulo' => $idevento,
			'modulo' => 'evento',
			'criadopor' => $_SESSION["SESSAO"]["USUARIO"],
			'criadoem' => 'now()',
			'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
			'alteradoem' => 'now()',
		];

		$inserindoFluxoStatusHist = SQL::ini(FluxostatusHistQuery::inserir(), $dados)::exec();
	}

	public static function notificarEvento($idevento, $arrDestinatarios = [])
	{
		$eventoTipo =  EventoTipoController::buscarEventoTipoPorIdEvento($idevento); // $this->getEventoTipoByIdEvento($idevento);
		$evento = self::buscarPorChavePrimariaPadrao($idevento); // $this->getEventoTituloByIdEvento($idevento);

		if (!count($eventoTipo) || !count($evento)) {
			return false;
		}

		$notif = Notif::ini()
			->canal("browser")
			->conf([
				"mod" => "evento",
				"modpk" => "idevento",
				"idmodpk" => $idevento,
				"title" => "Você foi adicionado em um evento de {$eventoTipo['eventotipo']}",
				"corpo" => $evento['evento'],
				"localizacao" => "dashboardsnippet",
				"url" => "https://sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=" . $idevento
			]);

		foreach ($arrDestinatarios as $idpessoa) {
			$notif->addDest($idpessoa);
		}
		$notif->send();
	}

	public static function inserirPessoasDoGrupoNoEvento($idevento, $tokeninicial, $union = true)
	{
		if ($union) {
			$eventos = SQL::ini(EventoQuery::buscarPessoasDeGruposInseridasOuNaoManualmentePorIdEvento(), [
				'idevento' => $idevento
			])::exec();
		} else {
			$eventos = SQL::ini(EventoQuery::buscarPessoasDeGruposPorIdEvento(), [
				'idevento' => $idevento
			])::exec();
		}

		$arrInsertFluxoStatusPessoa = array();
		$arrDestinatarios = array();
		$i = 0;

		foreach ($eventos->data as $evento) {
			$pessoaEstaNoEvento = SQL::ini(FluxostatuspessoaQuery::buscarObjetoNoEvento(), [
				'tipoobjeto' => 'pessoa',
				'idobjeto' => $evento["idobjeto"],
				'idmodulo' => $idevento,
				'modulo' => 'evento'
			])::exec();

			if (!$pessoaEstaNoEvento->numRows()) {
				$eventoTipoDescr = $evento["eventotipo"];
				$eventoTitulo = $evento["evento"];

				$arrInsertFluxoStatusPessoa["_evento" . $i . "_i_fluxostatuspessoa_oculto"] 				= 0;
				$arrInsertFluxoStatusPessoa["_evento" . $i . "_i_fluxostatuspessoa_visualizado"] 			= 0;
				$arrInsertFluxoStatusPessoa["_evento" . $i . "_i_fluxostatuspessoa_assinar"] 				= 'N';
				$arrInsertFluxoStatusPessoa["_evento" . $i . "_i_fluxostatuspessoa_modulo"] 				= 'evento';
				$arrInsertFluxoStatusPessoa["_evento" . $i . "_i_fluxostatuspessoa_tipoobjeto"] 			= 'pessoa';
				$arrInsertFluxoStatusPessoa["_evento" . $i . "_i_fluxostatuspessoa_idmodulo"] 				= $idevento;
				$arrInsertFluxoStatusPessoa["_evento" . $i . "_i_fluxostatuspessoa_idfluxostatus"] 			= $tokeninicial;
				$arrInsertFluxoStatusPessoa["_evento" . $i . "_i_fluxostatuspessoa_idobjeto"] 				= $evento["idobjeto"];
				$arrInsertFluxoStatusPessoa["_evento" . $i . "_i_fluxostatuspessoa_idempresa"] 				= $evento["idempresa"];
				$arrInsertFluxoStatusPessoa["_evento" . $i . "_i_fluxostatuspessoa_idobjetoext"] 			= $evento["idobjetoext"];
				$arrInsertFluxoStatusPessoa["_evento" . $i . "_i_fluxostatuspessoa_tipoobjetoext"] 			= $evento["tipoobjetoext"];
				$arrInsertFluxoStatusPessoa["_evento" . $i . "_i_fluxostatuspessoa_inseridomanualmente"] 	= $evento["inseridomanualmente"];

				if ($evento["idpessoa"] != $evento["idobjeto"])
					$arrDestinatarios[] = $evento["idobjeto"];

				$i++;
			}
		}

		if ($i > 0) {
			$_CMD = new cmd();
			$_CMD->disablePrePosChange = true;
			$res = $_CMD->save($arrInsertFluxoStatusPessoa);
			if (!$res) {
				die($_CMD->erro);
			} else if (count($arrDestinatarios) > 0) {
				$notif = Notif::ini()
					->canal("browser")
					->conf([
						"mod" => "evento",
						"idmod" => 155, // id do modulo - necessário por conta das restrições do usuario
						"modpk" => "idevento", // 
						"idmodpk" => $idevento,
						"title" => "Você foi adicionado em um evento de " . $eventoTipoDescr,
						"corpo" => $eventoTitulo ?? '',
						"localizacao" => "dashboardsnippet",
						"url" => "https://sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=" . $idevento
					]);

				foreach ($arrDestinatarios as $key => $idpessoa) {
					$notif->addDest($idpessoa);
				}

				$notif->send();
			}
			$_CMD = null;
		}
	}

	//evento - atualiza os participantes definidos no eventotipo
	public static function atualizarParticipantesDoEvento($idevento, $tokeninicial, $tipo = NULL, $idobjeto = NULL)
	{
		if ($tipo == 'imgrupo')
			FluxoController::inserirPessoasDoGrupoEmFluxostatusPessoa($idobjeto, $idevento, $tokeninicial);

		//Alterado para chamar a função que está no arquivo [model-evento] - . . Replace para inserir as pessoas do Grupo no Evento
		self::inserirPessoasDoGrupoNoEvento($idevento, $tokeninicial, false);

		//Retorna o tipo de Módulo e o Id deste.
		$pessoasDoEvento = SQL::ini(FluxostatusPessoaQuery::buscarPessoasDeUmEvento(), [
			'idevento' => $idevento
		])::exec();

		foreach ($pessoasDoEvento->data as $pessoa) {
			if ($pessoa['assinar'] == 'Y')
				criaAssinatura($pessoa["idobjeto"], $pessoa["modulo"], $pessoa["idmodulo"]);
		}
	}

	//Funçao para verificar o dia da semana recebe ANO MES E DIA 2011-07-11 junto
	public static function verificarSeEFimDeSemana($data)
	{
		$data = SQL::ini(EventoQuery::verificarDiaDaSemana(), [
			'data' => $data
		])::exec();

		//se o retorno for diferente de 7 sabado ou 1 domingo mostra no dia
		if ($data->data[0]["dia"] == 1 or $data->data[0]["dia"] == 7) {
			return true;
		}

		return false;
	}

	// DELETA TODOS OS EVENTOS FILHOS QUE ESTÃO FORA DO RANGE DO EVENTO PAI (REPETIR ATE)
	public static function deletarTodosEventosFilhosForaDoRangeDoEventoPai($idEventoPai, $datainicioEvento, $dataRepetirAte, $comparacaoMaior = false)
	{
		if ($comparacaoMaior) {
			$deletandoEventosForaDeUmRange = SQL::ini(EventoObjQuery::deletarEventosForaDoRangeDeDataEIdEventoPai(), [
				'ideventopai' => $idEventoPai,
				'inicio' => $datainicioEvento,
				'fim' => $dataRepetirAte
			])::exec();

			$removendoEventosForaDeUmRange = SQL::ini(FluxostatusPessoaQuery::deletarEventosForaDoRangeDeDataEIdEventoPai(), [
				'ideventopai' => $idEventoPai,
				'inicio' => $datainicioEvento,
				'fim' => $dataRepetirAte
			])::exec();

			$removendoEventosForaDoRange = SQL::ini(EventoQuery::deletarEventosForaDoRangeDeDataEIdEventoPai(), [
				'ideventopai' => $idEventoPai,
				'inicio' => $datainicioEvento,
				'fim' => $dataRepetirAte
			])::exec();

			return true;
		}

		$deletandoEventosForaDeUmRange = SQL::ini(EventoObjQuery::deletarEventosPorRangeDeDataEIdEventoPai(), [
			'ideventopai' => $idEventoPai,
			'inicio' => $datainicioEvento,
			'fim' => $dataRepetirAte
		])::exec();

		$removendoEventosForaDeUmRange = SQL::ini(FluxostatusPessoaQuery::deletarEventosPorRangeDeDataEIdEventoPai(), [
			'ideventopai' => $idEventoPai,
			'inicio' => $datainicioEvento,
			'fim' => $dataRepetirAte
		])::exec();

		//Deleta os eventos que estão na tabela eventoadd (LTM - 10/07/2020)
		$removendoEventosForaDoRange = SQL::ini(EventoAddQuery::deletarEventosPorRangeDeDataEIdEventoPai(), [
			'ideventopai' => $idEventoPai,
			'inicio' => $datainicioEvento,
			'fim' => $dataRepetirAte
		])::exec();

		$removendoEventosForaDoRange = SQL::ini(EventoQuery::deletarEventosPorRangeDeDataEIdEventoPai(), [
			'ideventopai' => $idEventoPai,
			'inicio' => $datainicioEvento,
			'fim' => $dataRepetirAte
		])::exec();
	}

	public static function buscarTodasPessoasDoEventoPorIdEvento($idEvento)
	{
		$eventos = SQL::ini(FluxostatusPessoaQuery::buscarPessoasParaListarNoEventoPorIdEvento(), [
			'idevento' => $idEvento
		])::exec();

		if ($eventos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventos->errorMessage());
			return false;
		}

		return $eventos->data;
	}

	public static function buscarEventosPorIdEventoTipoIdPessoaEData($idEventoTipo, $idPessoa, $dataInicio, $dataFim)
	{
		$eventos = SQL::ini(EventoQuery::buscarEventosPorIdEventoTipoIdPessoaEData(), [
			'ideventotipo' => $idEventoTipo,
			'idpessoa' => $idPessoa,
			'dataInicio' => $dataInicio,
			'dataFim' => $dataFim
		])::exec();

		if ($eventos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventos->errorMessage());
			return false;
		}

		return $eventos->data;
	}

	public static function buscarChaveDoModulo($inModulo, $inbypass = false)
	{
		if (!$inModulo) return [];
		// die("retArrModuloConf: Parâmetro inModulo não informado");

		//Permite reaproveitamento sem verificação de segurança. Ex: Tela de _modulo necessita recuperar informações do módulo mesmo que não estejam devidamente atribuídas em alguma LP
		if ($inbypass !== true) {
			$joinLp = ($_SESSION["SESSAO"]["LOGADO"]) ? "left join " . _DBCARBON . "._lpmodulo l on (l.modulo=m.modulo and l.idlp='" . $_SESSION["SESSAO"]["IDLP"] . "')" : "";
			$whereMod = ($_SESSION["SESSAO"]["LOGADO"]) ? "and m.modulo in (" . getModsUsr("SQLWHEREMOD") . ")" : "";
			$ifrestaurar = (getModsUsr("SQLWHEREMOD")) ? ",IF(1=(select ('restaurar' in  (" . getModsUsr("SQLWHEREMOD") . "))),'Y','N') as oprestaurar" : "";
		}

		$chaveDoModulo = SQL::ini(_ModuloQuery::buscarChaveDoModulo(), [
			'joinlp' => $joinLp,
			'where' => $whereMod,
			'modulo' => $inModulo
		])::exec();

		if ($chaveDoModulo->error()) {
			parent::error(__CLASS__, __FUNCTION__, $chaveDoModulo->errorMessage());
			return [];
		}

		// if (!$rmod) die("retArrModuloConf: Erro ao recuperar Módulo ".  mysql_error(d::b()));

		// $rows = mysql_fetch_assoc($rmod);
		// return ($rows['chavefts']);
		return $chaveDoModulo->data[0]['chavefts'];
	}

	public static function buscarListaDeEventos($ocultosFilter, $filterEventoTipo, $ord, $filtroMiniEvento = NULL, $tarefasFilter = NULL, $asyncLoad = NULL)
	{
		$ordem = "";

		if ($ord) {
			$ordem = 'ORDER BY ' . $ord;
		}

		$listaDeEventos = SQL::ini(EventoQuery::buscarListaDeEventos(), [
			'filtrominievento' => $filtroMiniEvento,
			'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"],
			'filtrodetarefa' => $tarefasFilter,
			'filtrodeocultos' => $ocultosFilter,
			'filtrodeeventotipo' => $filterEventoTipo,
			'ordem' => $ordem,
			'carregamentoassincrono' => $asyncLoad
		])::exec();

		if ($listaDeEventos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $listaDeEventos->errorMessage());
			return [];
		}

		return $listaDeEventos->data;
	}

	public static function buscarBotoes($idEvento, $tipo = NULL)
	{
		if ($tipo == 'botao_menu_lateral') {
			$colunas = ", etsf.ocultar";
			$where = "r.idmodulo = " . $idEvento . " AND r.tipoobjeto ='pessoa' ";
		} else {
			$where = "r.idmodulo = " . $idEvento . " 
                    AND r.idobjeto = " . $_SESSION["SESSAO"]["IDPESSOA"] . " 
                    AND r.tipoobjeto ='pessoa'";
		}

		$botoes = SQL::ini(EventoQuery::buscarBotoes(), [
			'colunas' => $colunas,
			'where' => $where
		])::exec();

		if ($botoes->error()) {
			parent::error(__CLASS__, __FUNCTION__, $botoes->errorMessage());
			return [];
		}

		return $botoes->data;
	}

	public static function buscarFluxoStatuspessoaPorIdEventoEIdPessoa($idEvento, $idPessoa)
	{
		$pessoa = SQL::ini(FluxostatuspessoaQuery::buscarFluxoStatuspessoaPorIdEventoEIdPessoa(), [
			'idevento' => $idEvento,
			'idpessoa' => $idPessoa
		])::exec();

		if ($pessoa->error()) {
			parent::error(__CLASS__, __FUNCTION__, $pessoa->errorMessage());
			return [];
		}

		return $pessoa->data[0];
	}

	public static function buscarEventosFilhosSemStatusPorIdEvento($idEvento)
	{
		$eventos = SQL::ini(EventoQuery::buscarEventosFilhosSemStatusPorIdEvento(), [
			'idevento' => $idEvento
		])::exec();

		if ($eventos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventos->errorMessage());
			return [];
		}

		return $eventos->data;
	}

	public static function buscarEDeletarResponsaveisPorEventosFilhos($idEventoFilho)
	{
		$eventos = SQL::ini(EventoQuery::buscarResponsaveisPelosEventosFilhos(), [
			'ideventofilho' => $idEventoFilho
		])::exec();

		if ($eventos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventos->errorMessage());
			return false;
		}

		$return = true;

		foreach ($eventos->data as $evento) {
			$deletandoFluxostatus = SQL::ini(FluxostatusPessoaQuery::deletarPorIdFluxostatusPessoa(), [
				'idfluxostatuspessoa' => $evento['idfluxostatuspessoa']
			])::exec();
		}

		return $return;
	}

	public static function ocultarEventos()
	{
		$eventos = SQL::ini(EventoQuery::buscarEventosQueDevemSerOcultados())::exec();

		if ($eventos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventos->errorMessage());
			return false;
		}

		foreach ($eventos->data as $evento) {
			$atualizandoEvento = SQL::ini($evento['update'])::exec();
			echo $atualizandoEvento->sql();
			echo '<br />';
		}
	}

	public static function buscarObjetoNoEvento($idEvento, $modulo, $idObjeto, $tipoObjeto)
	{
		$pessoa = SQL::ini(FluxostatusPessoaQuery::buscarObjetoNoEvento(), [
			'idmodulo' => $idEvento,
			'modulo' => $modulo,
			'idobjeto' => $idObjeto,
			'tipoobjeto' => $tipoObjeto
		])::exec();

		if ($pessoa->error()) {
			parent::error(__CLASS__, __FUNCTION__, $pessoa->errorMessage());
			return [];
		}

		return $pessoa->data[0];
	}

	public static function removerEventoFilhosSemStatus($idEvento)
	{
		$removendoEventosFilhos = SQL::ini(EventoQuery::removerEventoFilhosSemStatus(), [
			'idevento' => $idEvento
		])::exec();

		if ($removendoEventosFilhos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $removendoEventosFilhos->errorMessage());
			return false;
		}

		return true;
	}

	public static function removerEventoObjVinculadoEvendoAdd($ideventoadd)
	{
		$removendoEventosFilhos = SQL::ini(EventoObjQuery::removerEventoObjVinculadoEvendoAdd(), [
			'ideventoadd' => $ideventoadd
		])::exec();

		if ($removendoEventosFilhos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $removendoEventosFilhos->errorMessage());
			return false;
		}
	}

	public static function transformarSelectEmArray($select)
	{
		$arrRetorno = [];
		$valores = explode('UNION', $select);

		foreach ($valores as $item) {
			[$chave, $valor] = explode(',', str_replace('"', '', $item));

			$chave = str_replace(['SELECT', '"', ' '], '', $chave);
			$arrRetorno[$chave] = $valor;
		}

		return $arrRetorno;
	}

	public static function inserirEventoChecklist(array $dados): void
	{
		$inserindoEventoChecklist = SQL::ini(EventoChecklistQuery::inserirEventoChecklist(), [
			'titulo' => $dados['titulo'] ?? '',
			'idevento' => $dados['idevento'],
			'idempresa' => $dados['idempresa'] ?? cb::idempresa(),
			'criadopor' => isset($dados['criadopor']) ? $dados['criadopor'] : $_SESSION["SESSAO"]["USUARIO"],
			'criadoem' => 'NOW()',
			'alteradopor' => isset($dados['alteradopor']) ? $dados['alteradopor'] : $_SESSION["SESSAO"]["USUARIO"],
			'alteradoem' => 'NOW()'
		])::exec();
	}

	public static function inserirEventoChecklistItem(array $dados): array
	{
		$inserindoEventoChecklistItem = SQL::ini(EventoChecklistItemQuery::inserirEventoChecklistItemQuery(), [
			'ideventochecklist' => $dados['ideventochecklist'],
			'idempresa' => $dados['idempresa'],
			'titulo' => $dados['titulo'] ?? '',
			'checked' => $dados['checked'] ?? 'N',
			'criadopor' => $dados['criadopor'] ?? $_SESSION["SESSAO"]["USUARIO"],
			'criadoem' => 'NOW()',
			'alteradopor' => $dados['alteradopor'] ?? $_SESSION["SESSAO"]["USUARIO"],
			'alteradoem' => 'NOW()'
		])::exec();

		return self::buscarEventoChecklistItemPorChavePrimaria($inserindoEventoChecklistItem->lastInsertId());
	}

	public static function buscarEventoChecklistItemPorChavePrimaria($id)
	{
		$checklistItem = SQL::ini(EventoChecklistItemQuery::buscarPorChavePrimaria(), [
			'pkval' => $id
		])::exec();

		if ($checklistItem->error()) {
			parent::error(__CLASS__, __FUNCTION__, $checklistItem->errorMessage());
			return [];
		}

		return $checklistItem->data[0];
	}

	public static function removerEventoChecklistItemPorChavePrimaria(int $id): bool
	{
		$removendoChecklistItem = SQL::ini(EventoChecklistItemQuery::removerEventoChecklistItemPorChavePrimaria(), [
			'ideventochecklistitem' => $id
		])::exec();

		if ($removendoChecklistItem->error()) {
			parent::error(__CLASS__, __FUNCTION__, $removendoChecklistItem->errorMessage());
			return false;
		}

		return true;
	}

	public static function buscarChecklistPorIdEvento($idEvento)
	{
		$checklist = SQL::ini(EventoChecklistQuery::buscarChecklistPorIdEvento(), [
			'idevento' => $idEvento
		])::exec();


		if ($checklist->error()) {
			parent::error(__CLASS__, __FUNCTION__, $checklist->errorMessage());
			return [];
		}

		return $checklist->data;
	}

	public static function buscarCheckListItemPorIdEventoCheckList($idEventoChecklist)
	{
		$checkListItens = SQL::ini(EventoChecklistItemQuery::buscarCheckListItemPorIdEventoCheckList(), [
			'ideventochecklist' => $idEventoChecklist
		])::exec();

		if ($checkListItens->error()) {
			parent::error(__CLASS__, __FUNCTION__, $checkListItens->errorMessage());
			return [];
		}

		return $checkListItens->data;
	}

	public static function atualizarTituloEventoChecklistItem(array $dados)
	{
		$atualizandoChecklistItem = SQL::ini(EventoChecklistItemQuery::atualizarTituloEventoChecklistItem(), [
			'ideventochecklistitem' => $dados['ideventochecklistitem'],
			'titulo' => $dados['titulo']
		])::exec();

		if ($atualizandoChecklistItem->error()) {
			parent::error(__CLASS__, __FUNCTION__, $atualizandoChecklistItem->errorMessage());
			return false;
		}

		return true;
	}

	public static function atualizarCheckedEventoChecklistItem(array $dados)
	{
		$atualizandoChecklistItem = SQL::ini(EventoChecklistItemQuery::atualizarCheckedEventoChecklistItem(), [
			'ideventochecklistitem' => $dados['ideventochecklistitem'],
			'checked' => $dados['checked']
		])::exec();

		if ($atualizandoChecklistItem->error()) {
			parent::error(__CLASS__, __FUNCTION__, $atualizandoChecklistItem->errorMessage());
			return false;
		}

		return true;
	}

	public static function buscarSolicitantesDeEventos($toSelectPicker = false)
	{
		$solicitantes = SQL::ini(VwEventoQuery::buscarSolicitantesDeEventos())::exec();

		if ($solicitantes->error()) {
			parent::error(__CLASS__, __FUNCTION__, $solicitantes->errorMessage());
			return [];
		}

		if ($toSelectPicker) {
			$arrRetorno = [];

			foreach ($solicitantes->data as $key => $solicitante) {
				$arrRetorno[$key][$solicitante['criadopor']] = $solicitante['nome'];
			}

			return $arrRetorno;
		}

		return $solicitantes->data;
	}

	public static function verificarLogEventoNoJira($idobjeto, $tipoobjeto, $tipolog)
	{
		$results = SQL::ini(LogQuery::buscarlog(), [
			"idobjeto" => $idobjeto,
			"tipoobjeto" => $tipoobjeto,
			"tipolog" => $tipolog,
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return 1;
		}

		return $results->numRows();
	}

	public static function inserirLog($idobjeto, $tipoobjeto, $tipolog)
	{
		$dadosLog = array(
			'idempresa' => $_SESSION["SESSAO"]["IDEMPRESA"],
			'sessao' => session_id(),
			'log' => 'SUCESSO',
			'status' => 'SUCESSO',
			'info' => '',
			'criadoem' => sysdate(),
			'data' => sysdate(),
			"idobjeto" => $idobjeto,
			"tipoobjeto" => $tipoobjeto,
			"tipolog" => $tipolog
		);
		$results = LogController::inserir($dadosLog);

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return 1;
		}

		return $results->numRows();
	}

	/**
	 * @param idFluxostatusPessoa Int
	 */
	public static function atualizarVinculoParticipanteEvento(Int $idFluxostatusPessoa): Bool
	{
		$participante = SQL::ini(FluxostatuspessoaQuery::buscarParticipanteComGrupoDesatualizadoNoEvento(), [
			'idfluxostatuspessoa' => $idFluxostatusPessoa
		])::exec();

		if (!$participante->data)
			return false;

		$atualizandoVinculoParticipante = SQL::ini(FluxostatuspessoaQuery::atualizarVinculoParticipanteEvento(), [
			'idobjetoext' => $participante->data[0]['idgrupo'],
			'idfluxostatuspessoa' => $idFluxostatusPessoa
		])::exec();

		if ($atualizandoVinculoParticipante->error()) {
			parent::error(__CLASS__, __FUNCTION__, $atualizandoVinculoParticipante->errorMessage());

			return false;
		}

		return true;
	}

	public static function buscarDocsDisponiveisParaVinculo($idEvento, $idEmpresa, $toAutocomplete = false)
	{
		$docs = SQL::ini(ObjetoVinculoQuery::buscarDocsDisponiveisParaVinculo(), [
			'idevento' => $idEvento,
			'idempresa' => $idEmpresa
		])::exec();

		if ($docs->error()) {
			parent::error(__CLASS__, __FUNCTION__, $docs->errorMessage());
			return [];
		}

		if ($toAutocomplete)
			return array_map(function ($item) {
				return [
					'label' => $item['titulo'],
					'value' => $item['idsgdoc']
				];
			}, $docs->data);

		return $docs->data;
	}

	public static function buscarDocumentosVinculadosPorIdEvento($idEvento)
	{
		$documentos = SQL::ini(ObjetoVinculoQuery::buscarDocumentosVinculadosPorIdEvento(), [
			'idevento' => $idEvento
		])::exec();

		if ($documentos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $documentos->errorMessage());
			return [];
		}

		return $documentos->data;
	}

	public static function buscarLinksVinculados($idEvento)
	{
		$links = SQL::ini(EventoQuery::buscarLinksVinculados(), [
			'idevento' => $idEvento
		])::exec();

		if ($links->error()) {
			parent::error(__CLASS__, __FUNCTION__, $links->errorMessage());
			return [];
		}

		return $links->data;
	}

	public static function buscarEventoApontamento($idEvento)
	{
		$arr = SQL::ini(EventoQuery::buscarEventoApontamento(), [
			'idevento' => $idEvento
		])::exec();

		if ($arr->error()) {
			parent::error(__CLASS__, __FUNCTION__, $arr->errorMessage());
			return false;
		}

		return $arr->data;
	}

	public static function buscarEventosDestaque($idpessoa)
	{
		$eventos = SQL::ini(EventoQuery::buscarEventosDestaque(), [
			'idpessoa' => $idpessoa
		])::exec();

		if ($eventos->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventos->errorMessage());
			return [];
		}

		return $eventos->data;
	}

	public static function buscarSolmatVinculada($idEvento, $idEventoAdd)
	{
		$eventosAdd = SQL::ini(EventoObjQuery::buscarSolmatVinculada(), [
			'idevento' => $idEvento,
			'ideventoadd' => $idEventoAdd,
		])::exec();

		if ($eventosAdd->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventosAdd->errorMessage());
			return false;
		}

		return $eventosAdd->data;
	}

	public static function inserirHorasExec($horasexec, $idevento)
	{
		$horas = SQL::ini(EventoQuery::inserirHorasExec(), [
				'horasexec' => $horasexec,
				'idevento' => $idevento
		])::exec();

		if ($horas->error()) {
			parent::error(__CLASS__, __FUNCTION__, $horas->errorMessage());
			return false;
		}

		return $horas->data;
	}

	public static function buscarValorEventoApontamento($idevento)
	{
		$horas = SQL::ini(EventoQuery::buscarValorEventoApontamento(), [
				
				'idevento' => $idevento
		])::exec();

		if ($horas->error()) {
			parent::error(__CLASS__, __FUNCTION__, $horas->errorMessage());
			return false;
		}

		return $horas->data[0]['valor'];

	}

	public static function buscarValorDecimal($idevento)
	{
		$horas = SQL::ini(EventoQuery::buscarValorDecimal(), [
				
				'idevento' => $idevento
		])::exec();

		if ($horas->error()) {
			parent::error(__CLASS__, __FUNCTION__, $horas->errorMessage());
			return false;
		}

		return $horas->data[0]['valordecimal'];

	}

	public static function buscarEventosPorIdEventoTipoParaKanban($filtros)
	{
		$eventoTipo = SQL::ini(EventoQuery::buscarEventosPorIdEventoParaKaban(), [
			'filtros' => $filtros,
			'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"]
		])::exec();

		if ($eventoTipo->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventoTipo->errorMessage());
			return [];
		}

		$data = [];
		foreach ($eventoTipo->data as $key => $evento) {
			$data[$evento['idfluxostatus']][] = $evento;
		}

		return $data;
	}

	public static function updateEventoOrder($idevento,$order)
	{
		$eventoTipo = SQL::ini(EventoQuery::UpdateEventoOrder(), [
			'idevento' => $idevento,
			'ordem' => $order
		])::exec();

		if ($eventoTipo->error()) {
			parent::error(__CLASS__, __FUNCTION__, $eventoTipo->errorMessage());
			return false;
		}

		return true;
	}

	public static function buscarPessoaDepartamento($idpessoa)
	{
		$results = SQL::ini(PessoaobjetoQuery::buscarPessoaDepartamento(), [
				'idpessoa' => $idpessoa
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return false;
		} else {
			return $results->data[0]['idpessoa'];
		}
	}

	public static function buscarRelacionamento($ideventotipo)
	{
		$results = SQL::ini(EventoRelacionamentoQuery::buscarRelacionamento(), [
				'ideventotipo' => $ideventotipo
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return false;
		} else {
			return $results->data;
		}
	}

}
