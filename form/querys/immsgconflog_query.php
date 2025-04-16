<?

class ImMsgConfLogQuery
{
    public static function inserirLogDeEnviando()
    {
        return "INSERT INTO immsgconflog
                (idempresa,idimmsgconf,idpk,modulo,status,criadopor,criadoem,alteradopor,alteradoem)
                VALUES
                (?idempresa?,?idimmsgconf?,?idpk?,'?modulo?','ENVIANDO','?prefu?immsgconf',now(),'immsgconf',now())";
    }
    public static function atualizarLogParaSucesso()
    {
        return "UPDATE immsgconflog set status='SUCESSO', idimmsgbody = ?idevento? where idimmsgconflog= ?idimmsgconflog?";
    }

    
}

?>