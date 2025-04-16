<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/_snippet_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class _SnippetController extends Controller
{
    public static function buscarSnippetsPorLpIdEmpresaEModulos($lps, $idEmpresa, $modulos, $moduloPar = false, $mostrarMenu = false)
    {
        if($moduloPar)
        {
            $moduloPar = "WHERE u.modulopar in($moduloPar)";
        } else {
            $moduloPar = '';
        }

        $snippets = SQL::ini(_SnippetQuery::buscarSnippetsPorLpIdEmpresaEModulos(), [
            'lps' => $lps,
            'idempresa' => $idEmpresa,
            'modulos' => $modulos,
            'modulopar' => $moduloPar,
            'mostrarMenu' => $mostrarMenu
        ])::exec();
        
        if($snippets->error()){
            parent::error(__CLASS__, __FUNCTION__, $snippets->errorMessage());
            return [];
        }

        return $snippets->data;
    }

    public static function buscarSnippetPorNotificacaoEEmpresa()
	{
		$results = SQL::ini(_SnippetQuery::buscarSnippetPorNotificacaoEEmpresa(), [
            "getidempresa" => getidempresa('idempresa', '_snippet')
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0]['idsnippet'];
        }
	}
}
?>