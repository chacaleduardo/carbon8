<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/tiporelatorio_query.php");
require_once(__DIR__."/../querys/_rep_query.php");
require_once(__DIR__."/../querys/_repcol_query.php");

class _RepController extends Controller
{
	// ----- FUNÇÕES -----
	public static function listarTipoRelatorioPorIdProdserv($idprodserv)
	{
		$results = SQL::ini(TipoRelatorioQuery::listarTipoRelatorioPorIdProdserv(), [
            'idprodserv' => $idprodserv
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

    public static function buscarColunasDoRelatorioPorIdRep($idRep, $toFillSelect = false)
    {
        $colunas = SQl::ini(_RepQuery::buscarColunasDoRelatorioPorIdRep(), [
            'idrep' => $idRep
        ])::exec();

        if($colunas->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $colunas->errorMessage());
            return [];
        }

        if($toFillSelect)
        {
            $arrRetorno = [];

            foreach($colunas->data as  $coluna)
                $arrRetorno[$coluna['col']] = $coluna['col'];

            return $arrRetorno;
        }
        
        return $colunas->data;
    }

    public static function buscarRepColPorIdRepTabEColuna($idRep, $tab, $coluna)
    {
        $coluna = SQL::ini(_RepColQuery::buscarRepColPorIdRepTabEColuna(), [
            'idrep' => $idRep,
            'tab' => $tab,
            'coluna' => $coluna
        ])::exec();

        if($coluna->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $coluna->errorMessage());
            return [];
        }

        return $coluna->data[0];
    }

    public static function buscarInfoRelatorioPorIdRep($idrep, $modulos) {
        $rep = SQL::ini(_RepQuery::buscarInfoRelatorioPorIdRep(), [
            'idrep' => $idrep,
            'modulos' => implode("','", array_keys($modulos))
            //,'idempresa' => cb::idempresa()
        ])::exec();

        if($rep->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $rep->errorMessage());
            return [];
        }

        return $rep->data[0];
    }
}
?>