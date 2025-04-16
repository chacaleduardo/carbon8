<?
class ComunicacaoExtQuery{

    public static function buscarComunicacaoExtResultado(){

        return  "SELECT c.*
                    FROM comunicacaoextitem i
                        join comunicacaoext c on (c.idcomunicacaoext = i.idcomunicacaoext)
                    WHERE
                        i.tipoobjeto = 'resultado'
                        and c.status != 'ERRO'
                        and i.idobjeto = ?idresultado?";
    }

    public static function buscarComunicacaoExtResultadoSucesso(){

        return  "SELECT c.*
                    FROM comunicacaoextitem i
                        join comunicacaoext c on (c.idcomunicacaoext = i.idcomunicacaoext)
                    WHERE
                        i.tipoobjeto = 'resultado'
                        and c.status = 'SUCESSO'
                        and i.idobjeto = ?idresultado?";
    }

    public static function atualizarComunicacaoExtParaReenvio(){

        return  "UPDATE comunicacaoext
                    SET status = 'SUCESSO/NOVAVERSAO'
                    WHERE
                        idcomunicacaoext = ?idcomunicacaoext?";
    }

}?>