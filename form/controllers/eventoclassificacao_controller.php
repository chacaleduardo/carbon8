<?
// CONTROLLERS
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/eventoclassificacao_query.php");

class EventoClassificacaoController extends Controller
{
    public static function buscarClassificacaoPorId($classificacao)
    {
        $classificacao = SQL::ini(EventoClassificacaoQuery::buscarClassificacaoPorId(), [
            'id' => $classificacao
        ])::exec();

        
		if($classificacao->error()){
			parent::error(__CLASS__, __FUNCTION__, $classificacao->errorMessage());
			return false;
		}

        return $classificacao->data;
    }

    public static function buscarClassificacoes($toFillSelect = false)
    {
        $classificacao = SQL::ini(EventoClassificacaoQuery::buscarClassificacoes())::exec();
        
		if($classificacao->error()){
			parent::error(__CLASS__, __FUNCTION__, $classificacao->errorMessage());
			return false;
		}

        if($toFillSelect)
        {
            $arrRetorno = [];

            foreach($classificacao->data as $classificacao)
            {
                $arrRetorno[$classificacao['id']] = $classificacao['classificacao'];
            }

            return $arrRetorno;
        }

        return $classificacao->data;
    }
}

?>