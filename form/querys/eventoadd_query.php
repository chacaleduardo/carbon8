<?
require_once(__DIR__."/_iquery.php");

class EventoAddQuery implements defaultQuery
{
    public static $table = 'eventoadd';
    public static $pk = 'ideventoadd';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function inserir()
    {
        return "INSERT INTO eventoadd (
                    idempresa, idobjeto, objeto, idevento,  titulo,  observacao, ord,
                    tipoobjeto, criadopor, criadoem, alteradopor, alteradoem
                ) VALUES (
                    ?idempresa?, ?idobjeto?, '?objeto?', ?idevento?, '?titulo?', '?observacao?', ?ord?,
                    '?tipoobjeto?', '?criadopor?', ?criadoem?, '?alteradopor?', ?alteradoem?
                );";
    }

    //Insere os campos do EventotipoAdd na tabela EventoAdd - LTM (07/07/2020)
    public static function inserirCamposPorIdEventoEIdEventoTipo()
    {
        return "INSERT INTO eventoadd (
                    idobjeto, idempresa, objeto, idevento, titulo, tipoobjeto, alteradoem, alteradopor, criadoem,
                    criadopor
                ) SELECT
                    ideventotipoadd, 
                    idempresa, 
                    'ideventotipoadd',
                    ?idevento?, 
                    titulo,
                    CASE WHEN tag = 'Y' THEN 'tag'
                        WHEN sgdoc = 'Y' THEN 'sgdoc'
                        WHEN pessoa = 'Y' THEN 'pessoa'
                        WHEN prodserv = 'Y' THEN 'prodserv'
                        WHEN minievento = 'Y' THEN 'minievento'
                        WHEN tipocampos = 'Y' THEN 'tipocampos'
                        WHEN criasolmat = 'Y' THEN 'criasolmat'
                    END AS 'tipoobjeto',
                    now(),
                    '?usuario?',
                    now(),
                    '?usuario?'
                FROM eventotipoadd 
                WHERE ideventotipo = ?ideventotipo?
                AND status='ATIVO'
                ORDER BY ideventotipoadd";
    }

    public static function buscarEventoAddPorIdEvento()
    {
        return "SELECT * 
                FROM eventoadd 
                WHERE idevento = ?idevento?
                ORDER BY ord, ideventoadd";
    }

    public static function buscarObjetosPorIdEvento()
    {
        return "SELECT 
                    idempresa,
                    if(idobjeto IS NULL, ideventoadd, idobjeto) AS idobjeto,
                     titulo, 
                     objeto, 
                     tipoobjeto 
                FROM eventoadd 
                WHERE idevento = ?idevento?";
    }

    public static function atualizarTituloDosCamposPorIdObjetoETipoObjeto()
    {
        return "UPDATE eventoadd SET titulo = '?titulo?', alteradopor = '?usuario?', alteradoem = sysdate()
                WHERE idobjeto = ?idobjeto? AND objeto = '?tipoobjeto?'";
    }

    public static function deletarEventosPorRangeDeDataEIdEventoPai()
    {
        return "DELETE ea.* 
                FROM eventoadd ea 
                JOIN evento e ON ea.idevento = e.idevento 
                WHERE e.status is null  
                AND e.ideventopai = ?ideventopai?
                AND (e.inicio < '?inicio?' OR e.fim > '?fim?')";
    }

    public static function atualizarJsonConfigCampos(){
        return "UPDATE eventoobj SET jsonconfigcampos = JSON_SET(jsonconfigcampos, '$.?nomeCampo?','?valor?') WHERE ideventoobj = '?ideventoobj?'";
    }

    public static function inserirJsonConfigCampos(){
        return "UPDATE eventoobj SET jsonconfigcampos = JSON_INSERT(jsonconfigcampos, '$.?nomeCampo?','?valor?') WHERE ideventoobj = '?ideventoobj?'";
    }
}

?>