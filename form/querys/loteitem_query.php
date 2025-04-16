<? 
class LoteItemQuery {
    public static function buscarInsumosLote() {
        return "SELECT sum(valortotal) as insumo, idlote FROM loteitem WHERE idlote in (?idlote?) and fabricado ='N' GROUP by idlote";
    }
}