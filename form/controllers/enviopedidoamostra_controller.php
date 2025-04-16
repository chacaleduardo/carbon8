<?
require_once(__DIR__ . "/_controller.php");
require_once(__DIR__."/../querys/enviopedidoamostra_query.php");

class EnvioPedidoAmostraController extends Controller
{
	public static $materialEnviadoCheck = [
		[
			'label' => 'Tampa Roxa',
			'value' => 'Tampa Roxa'
		],
		[
			'label' => 'Frasco Formol',
			'value' => 'Frasco Formol'
		],
		[
			'label' => 'Tampa Vermelha',
			'value' => 'Tampa Vermelha'
		],
		[
			'label' => 'Tampa Cinza',
			'value' => 'Tampa Cinza'
		],
		[
			'label' => 'Tampa Verde',
			'value' => 'Tampa Verde'
		],
		[
			'label' => 'Urina',
			'value' => 'Urina'
		],
		[
			'label' => 'Laminas',
			'value' => 'Laminas'
		],
		[
			'label' => 'Parasitológico',
			'value' => 'Parasitológico'
		],
		[
			'label' => 'Swab',
			'value' => 'Swab'
		],
	];

	public static $descricaoMaterialEnviado = [
		[
			'label' => 'Medula Ossea',
			'value' => 'Medula Ossea'
		],
		[
			'label' => 'Swab Pele',
			'value' => 'Swab Pele'
		],
		[
			'label' => 'Sangue Total',
			'value' => 'Sangue Total'
		],
		[
			'label' => 'Líquido Cavitário',
			'value' => 'Líquido Cavitário'
		],
		[
			'label' => 'Soro',
			'value' => 'Soro'
		],
		[
			'label' => 'Líquor',
			'value' => 'Líquor'
		],
		[
			'label' => 'Sangue Edta',
			'value' => 'Sangue Edta'
		],
		[
			'label' => 'Urina',
			'value' => 'Urina'
		],
		[
			'label' => 'Swab Nasal',
			'value' => 'Swab Nasal'
		],
		[
			'label' => 'Fezes',
			'value' => 'Fezes'
		],
		[
			'label' => 'Swab Orofaríngeo',
			'value' => 'Swab Orofaríngeo'
		],
		[
			'label' => 'Líquido Ascitico',
			'value' => 'Líquido Ascitico'
		],
		[
			'label' => 'Swab Ocular',
			'value' => 'Swab Ocular'
		],
		[
			'label' => 'Outro',
			'value' => 'Outro'
		],
		[
			'label' => 'Swab Ouvido',
			'value' => 'Swab Ouvido'
		]
	];

	public static function buscarInformacoesPedidoAmostra($protocolo)
	{
		$results = SQL::ini(EnvioPedidoAmostra::buscarInformacoesPedidoAmostra(), [
			'protocolo' => $protocolo
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		} else {
			return $results->data[0];
		}
	}
	
	public static function gerarAmostraPeloPedido($dados, $dadosEnvio, $amostraEnviada)
	{
		$idregistro = EnvioPedidoAmostra::gerarRegistroAmostraPeloPedido(1);
		$observacao = $dados['clinica_observacao'].'\n';

		// Isolamento
		$amostra = SQL::ini(EnvioPedidoAmostra::gerarAmostraPeloPedido(), [
			'idpessoa'              => !empty($dados['razao_social'])?$dados['razao_social']:$_SESSION['SESSAO']['IDPESSOA'],
			'paciente'              => $dados['paciente'],
			'tutor'                 => $dados['tutor'],
			'idade'                 => $dados['idade'],
			'tipoidade'             => $dados['periodo'],
			'idespeciefinalidade'   => $dados['especie'],
			'sexo'                  => $dados['sexagem'],
			'responsavel'           => $dados['veterinario'],
			'responsavelcolcrmv'    => $dados['crmv'],
			'responsavelcolcont'    => $dados['responsavelcolcont'],
			'uf'                    => $dados['uf'],
			'email'                 => $dados['email'],
			'clienteterceiro'       => $dados['clienteterceiro'],
			'observacaointerna'     => $observacao,
			'status'                => $dados['status'],
			'meiotransp'            => $dadosEnvio['modoEnvio'],
			'datacoleta'            => $amostraEnviada['dataColeta'],
			'nroamostra'            => 1,
			'idunidade'             => 1,
			'idempresa'             => 1,
			'exercicio'             => date('Y').'PROVISORIO',
			'idnucleo'              => 0,
			'idsubtipoamostra'      => $amostraEnviada['idsubtipoamostra'],
			'idregistro'            => $idregistro,
			'dataamostra'           => date('Y-m-d'),
			'criadoem'              => date('Y-m-d H:i:s'),
			'criadopor'             => $_SESSION['SESSAO']['USUARIO']
		])::exec();
		
		if ($amostra->error()) {
			parent::error(__CLASS__, __FUNCTION__, $amostra->errorMessage());
			return false;
		}else{
			return mysql_insert_id($amostra);
		}
	}

	public static function gerarEnvioPedido($protocolo, $json)
	{
		// Isolamento
		$enviopedido = SQL::ini(EnvioPedidoAmostra::gerarEnvioPedido(), [
			'jsonpedido' => json_encode($json, JSON_UNESCAPED_UNICODE),
			'protocolo' => $protocolo,
			'criadopor' => $_SESSION['SESSAO']['USUARIO'],
			'criadoem' => date('Y-m-d H:i:s')
		])::exec();
		
		if ($enviopedido->error()) {
			parent::error(__CLASS__, __FUNCTION__, $enviopedido->errorMessage());
			return false;
		}else{
			return mysql_insert_id($enviopedido);
		}
	}
}
