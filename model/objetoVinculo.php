<?
require_once("../inc/php/functions.php");
require_once('builder.php');

class ObjetoVinculo
{
    const TABLE = 'objetovinculo';

    public function getById($id)
    {
        $queryBuilder = new Builder();

        $query = $queryBuilder
                    ->select('idobjetovinculo, idobjeto, idobjetovinc')
                    ->from(self::TABLE)
                    ->where("idobjetovinculo = $id");

        $result = $query->get();

        $i = 0;
        $arr = [];

        while($item = mysql_fetch_assoc($result))
        {
            $arr['idobjetovinculo'] = $item['idobjetovinculo'];
            $arr['idobjeto']        = $item['idobjeto'];
            $arr['idobjetovinc']    = $item['idobjetovinc'];
        }

        if(!count($arr))
        {
            $arr['error'] = 'Nenhum registro econtrado!';
        }

        return $arr;
    }
}

?>