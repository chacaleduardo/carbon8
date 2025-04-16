<? 
require_once("../inc/php/functions.php");
require_once("builder.php");

class Conselho
{
    const TABLE = 'sgconselho';

    public function getDisponiveis($id, $toString = false)
    {
        $queryBuilder = new Builder();

        $query =  $queryBuilder
                    ->select("idsgconselho, conselho")
                    ->from(self::TABLE)
                    ->where("idempresa = ".cb::idempresa())
                    ->where(self::TABLE.".status = 'ATIVO'");
        
        $arr = [];
        $i = 0;
        $result = $query->get();

        if($toString)
        {
            return $query->toString()->get();
        }

        while($item = mysql_fetch_assoc($result))
        {
            $arr[$i]['idsgconselho'] = $item['idsgconselho'];
            $arr[$i]['conselho']     = $item['conselho'];
        }

        
        return $arr;
    }

    public function getAreasDisponiveis($id)
    {
        $queryBuilder = new Builder();
        $queryBuilder2 = new Builder();

        $queryNotIn = $queryBuilder2
                        ->select("idobjetovinc")
                        ->from("objetovinculo")
                        ->where("idobjeto = $id")
                        ->where("tipoobjeto = 'sgconselho'")
                        ->where("tipoobjetovinc = 'sgarea'")
                        ->getQuery();

        $query = $queryBuilder
                    ->select('idsgarea, area')
                    ->from('sgarea')
                    ->where("status = 'ATIVO'")
                    ->where("idempresa = ". cb::idempresa())
                    ->whereNotIn("idsgarea", $queryNotIn);
                
        $result = $query->get();

        $arr = [];
        $i = 0;

        while($item = mysql_fetch_assoc($result))
        {
            $arr[$i]['idsgarea'] = $item['idsgarea'];
            $arr[$i]['area']     = $item['area'];

            $i++;
        }

        if(!count($arr))
        {
            $arr[$i]['error'] = 'Nenhum resultado encontrado!';
        }
        
        return $arr;
    }

    public function getAreasVinculadas($id)
    {
        $queryBuilder = new Builder();
        
        $query = $queryBuilder
                    ->select("a.area, a.idsgarea, ov.idobjetovinculo")
                    ->from("sgarea a")
                    ->join("objetovinculo ov", "ov.idobjetovinc = a.idsgarea AND ov.tipoobjetovinc = 'sgarea' AND ov.tipoobjeto = 'sgconselho'")
                    ->where("ov.idobjeto = $id")
                    ->orderBy("a.area");

        $result = $query->get();

        $arr = [];
        $i = 0;

        while($item = mysql_fetch_assoc($result))
        {
            $arr[$i]['idobjetovinculo'] = $item['idobjetovinculo'];
            $arr[$i]['idsgarea'] = $item['idsgarea'];
            $arr[$i]['area']     = $item['area'];

            $i++;
        }

        if(!count($arr))
        {
            $arr['error'] = 'Nenhum resultado encontrado!';
        }
        
        return $arr;
    }
}