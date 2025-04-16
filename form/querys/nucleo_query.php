<?
require_once(__DIR__."/_iquery.php");

class NucleoQuery implements DefaultQuery
{
    public static $table = 'nucleo';
    public static $pk = 'idnucleo';

    public static function buscarPorChavePrimaria(){
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL,[
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarNucleosComparativo(){
        return "SELECT b.idnucleo
        ,b.estudo as nucleo
        ,n.lote
        ,YEAR(b.nascimento) as ano
        ,n.situacao
        ,b.idespeciefinalidade
        ,b.idunidade
        ,CAST(a.idade as UNSIGNED) as idade
        ,r.idservicoensaio
        ,ps.idprodserv
        ,ps.tipoespecial
        ,ps.descr
        ,r.idresultado
        ,CASE 
            WHEN modelo = 'UPLOAD' THEN (select re.titer from resultadoelisa re where re.idresultado=r.idresultado and re.nome = 'GMN' and re.status='A')
            ELSE r.gmt
        END as gmt
    from amostra a -- FORCE INDEX(pessoa_nucleo)
        join nucleo n FORCE INDEX(PRIMARY) on (n.idnucleo = a.idnucleo)
        join resultado r FORCE INDEX(idamostra) on (r.idamostra = a.idamostra)
        join bioensaio b on (n.idnucleo = b.idnucleo)
        -- join lote l on (b.idlotepd = l.idlote)
        join prodserv ps FORCE INDEX(PRIMARY) on (
            ps.idprodserv = r.idtipoteste 
            AND ps.tipo in ('SERVICO','PRODUTO') 
            AND ps.comparativodelotes='Y'
        )
    where 1 ?getidempresa?
         and b.idlotepd = ?idlote?
        and CAST(a.idade as UNSIGNED) > ''
    order by ano desc,idade asc,n.nucleo;";
    }

    public static function inserirNucleo(){
        return "INSERT INTO nucleo 
        (idobjeto,idempresa,idpessoa,idunidade,idespeciefinalidade,objeto,nucleo,lote
        ) values (?idobjeto?,?idempresa?,?idpessoa?,?idunidade?,?idespeciefinalidade?,'?objeto?','?nucleo?',?lote?)";
    }

    public static function atualizarNucleoCompleto(){
        return "UPDATE nucleo SET 
                    idobjeto=?idlote?,
                    nucleo='?nucleo?',
                    idpessoa=?idpessoa?,
                    idespeciefinalidade=?idespeciefinalidade?,
                    lote = '?lote?',
                    alteradopor = '?usuario?',
                    alteradoem = sysdate()
                WHERE idnucleo=?idnucleo?";
    }

    public static function atualizarNucleoParcial(){
        return "UPDATE nucleo SET 
                nucleo='?nucleo?',
                idpessoa=?idpessoa?,
                idespeciefinalidade=?idespeciefinalidade?,
                alteradopor = '?usuario?',
                alteradoem = NOW()
                WHERE idnucleo=?idnucleo?";
    }
    
}?>