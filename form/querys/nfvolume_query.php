<?
class NfVolumeQuery{
    public static function buscarNfVolumePendente(){
        return "SELECT * FROM nfvolume WHERE status = 'P' and idnf = ?idnf?";
    }

    public static function atualizarStatusEnviadoNfVolume(){
        return "UPDATE nfvolume SET status = 'E' WHERE idnfvolume in (?idnfvolumes?)";
    }
}
?>