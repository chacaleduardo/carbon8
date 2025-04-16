<?
require_once(__DIR__."/../../inc/php/laudo.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/eventotipo_query.php");
require_once(__DIR__."/../querys/tag_query.php");
require_once(__DIR__."/../querys/formalizacaosubtipo_query.php");

// CONTROLLERS
require_once(__DIR__."/_controller.php");

class CalendarioController extends Controller
{
    public static function buscarEventoTiposPorIdPessoaClausulaEGetIdEmpresa($idPessoa, $clausula, $getIdEmpresa)
    {
        $eventoTipos = SQL::ini(EventoTipoQuery::buscarEventoTipoPorIdPessoaClausulaEGetIdEmpresa(), [
            'idpessoa' => $idPessoa,
            'clausula' => $clausula,
            'getidempresa' => $getIdEmpresa
        ])::exec();

        if($eventoTipos->error()){
            parent::error(__CLASS__, __FUNCTION__, $eventoTipos->errorMessage());
            return [];
        }

        return $eventoTipos->data;
    }

    public static function buscarTagClassPorIdClassGetIdEmpresaEShare($idTagClass, $getIdEmpresa, $share)
    {
        $tags = SQL::ini(TagQuery::buscarTagClass1PorGetIdEmpresaEShare(), [
            'idtagclass' => $idTagClass,
            'getidempresa' => $getIdEmpresa,
            'share' => $share
        ])::exec();

        if($tags->error()){
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        return $tags->data;
    }

    public static function buscarTagClassPorIdClassGetIdEmpresaECalendario($idTagClass, $getIdEmpresa, $calendario)
    {
        $tags = SQL::ini(TagQuery::buscarTagClassPorIdClassGetIdEmpresaECalendario(), [
            'idtagclass' => $idTagClass,
            'getidempresa' => $getIdEmpresa,
            'calendario' => $calendario
        ])::exec();

        if($tags->error()){
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        return $tags->data;
    }

    public static function buscarFormalizacaoSubTiposPorShare($share)
    {
        $formalizacaoSubTipos = SQL::ini(FormalizacaoSubTipoQuery::buscarFormalizacaoSubTipoPorShare(), [
            'share' => $share
        ])::exec();

        if($formalizacaoSubTipos->error()){
            parent::error(__CLASS__, __FUNCTION__, $formalizacaoSubTipos->errorMessage());
            return [];
        }

        return $formalizacaoSubTipos->data;
    }
}

?>
