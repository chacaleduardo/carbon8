<?
    class RhtipoeventoQuery
    {

        public static function buscarRhtipoeventoConf(){
            return "SELECT 
                            idrhtipoevento, evento
                        FROM
                            rhtipoevento
                        WHERE
                            status = 'ATIVO' 
                            AND formato = 'D'
                                AND (flgfolha = 'Y' 
                                OR flgfixo = 'Y'
                                OR flgferias = 'Y'
                                OR flgdecimoterc = 'Y'
                                OR flgdecimoterc2 = 'Y')
                        ORDER BY evento";
        }
        
        public static function buscarHistoricoDominio(){
            return "SELECT historicodominio FROM rhtipoevento WHERE historicodominio = '?historicodominio?'";
        }

        public static function buscarEventosRh(){
            return "SELECT idrhtipoevento, evento FROM rhtipoevento WHERE status = 'ATIVO' ORDER BY evento;";
        }
    }
?>

