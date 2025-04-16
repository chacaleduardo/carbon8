<?
require_once(__DIR__."/_controller.php");
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/rheventofolha_query.php");
require_once(__DIR__."/../querys/contaitem_query.php");
require_once(__DIR__."/../querys/tipoprodserv_query.php");
require_once(__DIR__."/../querys/rhtipoevento_query.php");
require_once(__DIR__."/../querys/rheventofolhaitem_query.php");

class RheventofolhaController extends Controller{

    public static function buscarRheventofolhaItem($idrheventofolha)
	{
        $results = SQL::ini(RheventofolhaQuery::buscarRheventofolhaItem(), [
            "idrheventofolha" => $idrheventofolha
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarRhtipoeventoConf()
	{
        $results = SQL::ini(RhtipoeventoQuery::buscarRhtipoeventoConf(), [])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
           

            foreach($results->data as $_val)
            {	
                $lista[$_val['idrhtipoevento']] = $_val['evento'];                
            }
            return $lista;
        }
    }

    public static $ArrayVazio = array(''=>'');

    public static function buscarContaItemTipoProdservTipoProdServ($idcontaitem)
	{
		$results = SQL::ini(TipoProdServQuery::buscarContaItemTipoProdservTipoProdServ(),["idcontaitem"=>$idcontaitem])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            foreach($results->data as $_valor)
            {	
                $lista[$_valor['idtipoprodserv']] = $_valor['tipoprodserv'];                
            }
            return $lista;
        }
	}

    public static function buscarContaItemTipoProdserv($idrheventofolhaitem, $campo)
	{
        $results = SQL::ini(RheventoFolhaItemQuery::buscarContaItemRhEventoFolhaPessoa(), [
            "campoColuna" => $campo,
            "campores" => $idrheventofolhaitem
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
    }

    //----- AUTOCOMPLETE -----
    public static function buscarContaItemRhEventoFolhaPessoaFillSelect($idpessoa, $campo, $dtemissao = NULL)
	{
        if(!empty($dtemissao))
        {
            $dtemissao = " - ".dma($dtemissao);
        }

        $arrContaItemPessoa = [];
		$results = SQL::ini(RheventoFolhaItemQuery::buscarContaItemRhEventoFolhaPessoa(), [
            "campoColuna" => $campo,
            "campores" => $idpessoa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            foreach($results->data as $_contaItemPessoa)
			{	
				$arrContaItemPessoa[$_contaItemPessoa['idrheventofolhaitem']] = $_contaItemPessoa['descricao'].$dtemissao;
			}
        }

        return $arrContaItemPessoa;
	}
    //----- AUTOCOMPLETE -----
}
?>