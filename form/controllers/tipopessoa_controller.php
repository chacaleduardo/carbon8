<?
require_once(__DIR__."/_controller.php");
require_once(__DIR__ . "/../querys/tipopessoa_query.php");


class TipoPessoaController extends Controller {
    public static function buscarPorChavePrimaria($id) {
        $tipoPessoa = SQL::ini(TipoPessoaQuery::buscarPorChavePrimaria(), [
            'pkval' => $id
        ])::exec();

        if($tipoPessoa->error()) {
			parent::error(__CLASS__, __FUNCTION__, $tipoPessoa->errorMessage());
			return [];
		}

		return $tipoPessoa->data[0];
    }
}

?>