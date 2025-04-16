<?
// Controllers
require_once(__DIR__."/_controller.php");
require_once(__DIR__."/formulaprocesso_controller.php");

// Querys
require_once(__DIR__."/../querys/formularotulo_query.php");
require_once(__DIR__."/../querys/lote_query.php");
require_once(__DIR__."/../querys/rotulolote_query.php");
require_once(__DIR__."/../querys/objetojson_query.php");

class FormulaRotuloController extends Controller
{
    protected static $status = [
        'INICIO' => '',
        'REVISAO' => 'REVISÃO INICIADA',
        'AGUARDANDO_REVISAO' => 'APROVAÇÃO SOLICITADA',
        'APROVADO' => 'APROVADO '
    ];

    protected static $statusButton = [
        'INICIO' => [
            'label'=> 'Revisar',
            'colorClass'=> 'primary',
            'icon'=> 'fa fa-refresh',
        ],
        'REVISAO'  => [
            'label'=> 'Solicitar aprovação',
            'colorClass'=> 'primary',
            'icon' => 'fa fa-check'
        ],
        'AGUARDANDO_REVISAO' => [
            'label' => 'Aprovar',
            'colorClass'=> 'success',
            'icon'=> 'fa fa-refresh',
        ],
        'APROVADO' => [
            'label' => 'Revisar',
            'icon' => 'fa fa-refresh',
            'colorClass'=> 'primary',
        ]
    ];

    public static function buscarFormulaRotuloPorIdProdservFormula($idprodservformula)
	{   
        $results = SQL::ini(FormulaRotuloQuery::buscarFormulaRotuloPorIdProdservFormula(), [          
			"idprodservformula" => $idprodservformula
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];            
        }
    }

    public static function buscarProdServEFormulaRotuloPorIdProdServFormula($idProdServFormula)
    {
        $rotulo = SQL::ini(FormulaRotuloQuery::buscarProdServEFormulaRotuloPorIdProdServFormula(), [
            'idprodservformula' => $idProdServFormula
        ])::exec();

        if($rotulo->error()){
            parent::error(__CLASS__, __FUNCTION__, $rotulo->errorMessage());
            return [];
        }

        return $rotulo->data[0];
    }

    public static function getLabelStatus($statusParam)
    {
        return self::$status[$statusParam] ?? '';
    }

    public static function getLabelStatusButton($statusParam) 
    {
        return self::$statusButton[$statusParam] ?? [
            'label'=> '',
            'colorClass'=> '',
            'icon'=> '',
        ];
    }

    public static function versionar($formulaRotulo)
    {
        $versaoAtual = FormulaRotuloController::buscarVersaoAtual($formulaRotulo['idformularotulo']) ?? 1;

        $arrayObjetoJson = [
			"idempresa" => $_SESSION['SESSAO']['IDEMPRESA'],
			"idobjeto" => $formulaRotulo['idformularotulo'],
			"tipoobjeto" => 'formularotulo',
			"jobjeto" => base64_encode(serialize($formulaRotulo)),
			"versaoobjeto" => $versaoAtual + 1,
			"criadopor" => $_SESSION['SESSAO']['USUARIO'],
			"alteradopor" => $_SESSION['SESSAO']['USUARIO'],
		];

		FormulaProcessoController::inserirObjetoJson($arrayObjetoJson);
    }

    public static function buscarFormulaRotuloPorId(int $idFormulaRotulo) 
    {
        $formulaRotulo = SQL::ini(FormulaRotuloQuery::buscarPorChavePrimaria(), [
            'pkval' => $idFormulaRotulo
        ])::exec();

        if($formulaRotulo->error()){
            parent::error(__CLASS__, __FUNCTION__, $formulaRotulo->errorMessage());
            return [];
        }

        return $formulaRotulo->data[0];
    }

    public static function buscarVersaoAtual(int $idFormulaRotulo)
    {
        $versao = SQL::ini(ObjetoJsonQuery::buscarVersaoAtualPorTipoObjeto(), [
            'idobjeto' => $idFormulaRotulo,
            'tipoobjeto'=> 'formularotulo'
        ])::exec();

        if($versao->error()){
            parent::error(__CLASS__, __FUNCTION__, $versao->errorMessage());
            return [];
        }

        return $versao->data[0]['versaoobjeto'];
    }

    public static function atualizarRotuloLote(int $idProdServFormula)
    {
        $lote = SQL::ini(LoteQuery::buscarLotePorIdProdServFormular(), [
            'idprodservformula' => $idProdServFormula
        ])::exec();

        if($lote->error()){
            parent::error(__CLASS__, __FUNCTION__, $lote->errorMessage());
            return [];
        }

        $rotuloLoteQuery = SQL::ini(RotuloLoteQuery::buscarRotuloLotePorIdLote(), [
            'idlote' => $lote->data[0]['idlote']
        ])::exec();

        $rotuloLote = $rotuloLoteQuery->data[0];

        $ultimoRotuloLoteAprovado = SQL::ini(ObjetoJsonQuery::buscarObjetoPorTipoObjeto(), [
            'idobjeto' => $rotuloLote['idformularotulo'],
            'tipoobjeto' => 'formularotulo'
        ])::exec();
    
        $ultimoRotuloLoteAprovado = unserialize(base64_decode($ultimoRotuloLoteAprovado->data[0]['jobjeto'])) ?? [];

        $arrCamposModificados['indicacao'] = strcmp(preg_replace('/\r|\n/', '', $rotuloLote['loterotulaindicacao']), preg_replace('/\r|\n/', '', $ultimoRotuloLoteAprovado['indicacao']));
        $arrCamposModificados['formula'] = strcmp(preg_replace('/\r|\n/', '', $rotuloLote['loterotulaformula']), preg_replace('/\r|\n/', '', $ultimoRotuloLoteAprovado['formula']));
        $arrCamposModificados['cepas'] = strcmp(preg_replace('/\r|\n/', '', str_replace(' ', '', $rotuloLote['loterotulacepas'])), preg_replace('/\r|\n/', '', str_replace(' ', '', $ultimoRotuloLoteAprovado['cepas'])));

        $updateSet = 'SET ';
        $formulaUpdate = '';
        $cepasUpdate = '';
        $indicacaoUpdate = '';

        if($arrCamposModificados['formula'] === 0) {
            $formulaUpdate = "$updateSet formula = '{$rotuloLote['formula']}'";
            $updateSet = ',';
        }

        if($arrCamposModificados['cepas'] === 0) {
            $cepasUpdate = "$updateSet cepas = '{$rotuloLote['cepas']}'";
            $updateSet = ',';
        }

        if($arrCamposModificados['indicacao'] === 0)
            $indicacaoUpdate = "$updateSet indicacao = '{$rotuloLote['indicacao']}'";

        $atualizandoRotuloLote = SQL::ini(RotuloLoteQuery::sincronizarDado(), [
            'updateformula' => $formulaUpdate,
            'updatecepas' => $cepasUpdate,
            'updateindicacao' => $indicacaoUpdate,
            'idloterotulo' => $rotuloLote['idloterotulo']
        ])::exec();

        if($atualizandoRotuloLote->error()){
            parent::error(__CLASS__, __FUNCTION__, $atualizandoRotuloLote->errorMessage());
            return [];
        }
    }
}

?>