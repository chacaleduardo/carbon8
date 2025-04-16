<?

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/log_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class LogController extends Controller
{
    public static function inserir($dados)
    {
        $dados['info'] = addslashes($dados['info']);
        $dados['log'] = addslashes($dados['log']);

        $inserindoLog = SQL::ini(LogQuery::inserirLog(), $dados)::exec();

        if($inserindoLog->error())
        {
            $dadosLogErro = [
                'idempresa' => $_SESSION["SESSAO"]["IDEMPRESA"],
                'sessao' => session_id(),
                'tipoobjeto' => $dados['tipoobjeto'] ?? 'DESCONHECIDO',
                'idobjeto' => $dados['idobjeto'] ?? 'DESCONHECIDO',
                'tipolog' => 'status',
                'log' => 'ERRO',
                'status' => 'ERRO',
                'info' => '',
                'criadoem' => "NOW()",
                'data' => "NOW()"
            ];
    
            $inserirLog = SQL::ini(LogQuery::inserirLog(), $dadosLogErro)::exec();
        }

        return $inserindoLog;
    }
}

?>