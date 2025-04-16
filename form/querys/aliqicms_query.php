<?
class AliqicmsQuery{
    public static function buscarAliqicmsUF(){
        return "SELECT 
                        i.idaliqicms, i.aliq, ii.aliq AS aliqicmsint
                    FROM
                        aliqicmsuf a,
                        aliqicms i,
                        aliqicms ii
                    WHERE
                        ii.idaliqicms = a.idaliqicmsint
                            AND i.idaliqicms = a.idaliqicms
                            AND a.uf = '?uf?'";
    }

    public static function buscarAliqicmsPorId()
    {
        return "SELECT 
                    i.idaliqicms, i.aliq
                FROM
                    aliqicms i
                WHERE
                    i.idaliqicms = ?idaliqicms?";        

    }
}