<?
class AssinaturaQuery{
    public static function buscarAssinatura(){
        return "SELECT 
                    *
                FROM
                    assinatura
                WHERE
                    idobjeto = ?idobjeto?
                        AND tipoobjeto = '?tipoobjeto?'
                        AND status = 'ASSINADO'
                        AND tipoassinatura = '?tipo?'
                        AND assinatura IS NOT NULL";
    }

}
?>