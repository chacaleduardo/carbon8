<?
    class RheventofolhaQuery
    {

        public static function buscarRheventofolhaItem(){
            return "SELECT
                            r.evento,e.*
                    FROM rheventofolhaitem e left join rhtipoevento r on(r.idrhtipoevento=e.idrhtipoevento)
                        
                    WHERE e.status='ATIVO' 
                    AND  e.idrheventofolha=?idrheventofolha? order by r.evento";
        }
    
    }
?>