<?
class PlpositivoQuery{

    public static function inserirPlPositivo(){
        return "INSERT into plpositivo (idempresa,idresultado)
		(
		select r.idempresa,r.idresultado 
		from prodserv p,resultado r,amostra a
		where not exists (select 1 from plpositivo pos where pos.idresultado = r.idresultado)
		and p.relatoriopositivo = 'Y'
		and r.idtipoteste =p.idprodserv
		and r.alerta ='Y'
		and r.status = 'ASSINADO'
		and r.idsecretaria is not null
		and r.idamostra = a.idamostra
		?clausula?)";
    }
}