<?
class ConfcontapagarQuery{
    public static function buscarConfcontapagarInpostoServico(){
        return "SELECT 
                        *
                    FROM
                        confcontapagar
                    WHERE
                        status = 'ATIVO'
                            AND idconfcontapagar = ?idconfcontapagar?
                            AND tipo IN ('CSRF','PIS','COFINS','IRRF','INSS','ISS','GNRE')";
    }

    public static function buscarConfcontapagaritemPorIdconfcontapagar(){
        return "SELECT 
                        * 
                    FROM confcontapagaritem c 
                    WHERE c.idconfcontapagar=?idconfcontapagar?";
    }

    public static function buscarFormaPagamentoPorIdEmpresaETipo(){
        return "SELECT * FROM confcontapagar WHERE status = 'ATIVO' AND idempresa = ?idempresa? AND tipo = '?tipo?'";
    }
}
?>

