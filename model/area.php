<? 
require_once("builder.php");

class Area
{
    const TABLE = 'sgarea';

    public function getDisponiveis($id = null, $toString = false)
    {
        $queryBuilder = new Builder();

        $query =  $queryBuilder
                    ->select("idsgarea, area")
                    ->from(self::TABLE)
                    ->where("idempresa = ".cb::idempresa())
                    ->where(self::TABLE.".status = 'ATIVO'");
        
        $arr = [];
        $i = 0;

        if($toString)
        {
            return $query->toString()->get();
        }

        $result = $query->get();

        while($item = mysql_fetch_assoc($result))
        {
            $arr[$i]['idsgarea'] = $item['idsgarea'];
            $arr[$i]['area']     = $item['area'];
        }
   
        return $arr;
    }

    public function getConselhosDisponiveis($idsgconselho = null)
    {
        $queryBuilder = new Builder();

        $query = $queryBuilder
                    ->select("idsgconselho, conselho")
                    ->from("sgconselho")
                    ->where("idempresa = ".cb::idempresa())
                    ->where("status = 'ATIVO'");

        if($idsgconselho)
        {
            $query->where("idsgconselho != $idsgconselho");
        }
        
        $arr = [];
        $i = 0;

        $result = $query->get();

        while($item = mysql_fetch_assoc($result))
        {
            $arr[$i]['idsgconselho'] = $item['idsgconselho'];
            $arr[$i]['conselho']     = $item['conselho'];

            $i++;
        }
    
        return $arr;
    }

    public function getObjetoVinculo($idObjeto = null, $idObjetoVinc)
    {
        $queryBuilder = new Builder();

        if(!$idObjeto)
        {
            return 0;
        }

        $query = $queryBuilder
                    ->select("ov.idobjetovinculo")
                    ->from("objetovinculo ov")
                    ->where("ov.idobjetovinc = $idObjetoVinc")
                    ->where("ov.tipoobjetovinc = 'sgarea'")
                    ->where("ov.idobjeto = $idObjeto")
                    ->where("ov.tipoobjeto = 'sgconselho'");

        $arr = [];
        $i = 0;

        $result = $query->get();

        while($item = mysql_fetch_assoc($result))
        {
            $arr['idobjetovinculo'] = $item['idobjetovinculo'];

            $i++;
        }

        return $arr['idobjetovinculo'];
    }

    public function getById($id)
    {
        $queryBuilder = new Builder();

        $query = $queryBuilder
                    ->select('idsgarea, area')
                    ->from(self::TABLE)
                    ->where("idsgarea = $id");

        $result = $query->get();

        $arr = [];

        while($item = mysql_fetch_assoc($result))
        {
            $arr['idsgarea'] = $item['idsgarea'];
            $arr['area']        = $item['area'];
        }

        if(!count($arr))
        {
            $arr['error'] = 'Nenhum registro econtrado!';
        }

        return $arr;
    }
}