<?
require_once("../inc/php/functions.php");

class crh{

    public static $idempresa;

    static public function valorpagamento($inidpessoa,$indata,$tipofolha,$tipo='comum'){

        if($tipofolha=='FOLHA'){
            $strtipo=" (t.flgfolha='Y'  or t.flgfixo='Y'  ) ";
        }elseif($tipofolha=='FOLHA FERIAS'){
            $strtipo=" t.flgferias ='Y' ";
        }else{
             $strtipo=" t.flgdecimoterc ='Y' ";
        }	
        $sl="select * from rhfolha f 
        where tipofolha='".$tipofolha."'
        and idempresa = ".self::$idempresa." 
        and datafim < '".$indata."'         
        and status!='FECHADA' order by datafim desc limit 1";

        $resl=d::b()->query($sl);
        //die($sl);
        $qtdl=mysqli_num_rows($resl);
        if($qtdl>0){
            $rl=mysqli_fetch_assoc($resl);
            $strdatafim="  and e.dataevento > '".$rl['datafim']."'";

        }else{
            $strdatafim='';
        }

        $sql="select (sum(soma)-sum(reduz)) as valor from (
            select sum(e.valor) soma,0 as reduz 
            from  rhevento e 
			join rhtipoevento t on( t.status ='ATIVO' 
								and ".$strtipo." 
								and t.tipo='C'
								and t.formato='D'
                                and t.idrhtipoevento not in (473,39) 
								and e.idrhtipoevento=t.idrhtipoevento)
            where e.idpessoa=".$inidpessoa."
            and e.status ='PENDENTE' 
            ".$strdatafim."
            and e.dataevento <= '".$indata."'
            and e.situacao='A'
            union
            select 0 as soma,sum(e.valor) reduz 
            from  rhevento e 
			join rhtipoevento t on( t.status ='ATIVO' 
								and ".$strtipo." 
								and t.tipo='D'
								and t.formato='D' 
								and e.idrhtipoevento=t.idrhtipoevento)
            where e.idpessoa=".$inidpessoa." 
            and e.status ='PENDENTE'
            ".$strdatafim." 
            and e.dataevento <= '".$indata."' 
            and e.situacao='A') as u";

        $res= d::b()->query($sql);
        $row=mysqli_fetch_assoc($res);
        return abs($row['valor']);
    }

    static public function eventospagamento($inidpessoa,$indata,$tipofolha,$tipo='comum'){
        
        if($tipofolha=='FOLHA'){
            $strtipo=" (t.flgfolha='Y'  or t.flgfixo='Y'  ) ";
            $stritem="REMUNERAÇÃO";

        }elseif($tipofolha=='FOLHA FERIAS'){
            $strtipo=" t.flgferias ='Y' ";
            $stritem="FÉRIAS";
        }elseif($tipofolha=='DECIMO TERCEIRO'){
            $strtipo=" t.flgdecimoterc ='Y' ";
            $stritem="DÉCIMO TERCEIRO";
        }else{
            $strtipo=" t.flgdecimoterc2 ='Y' ";
            $stritem="DECIMO TERCEIRO - 2P";
        }	
        $sl="select * from rhfolha f 
        where tipofolha='".$tipofolha."'
        and idempresa = ".self::$idempresa." 
        and datafim < '".$indata."'         
        and status!='FECHADA' order by datafim desc limit 1";

        $resl=d::b()->query($sl);
        //die($sl);
        $qtdl=mysqli_num_rows($resl);
        if($qtdl>0){
            $rl=mysqli_fetch_assoc($resl);
            $strdatafim="  and e.dataevento > '".$rl['datafim']."'";

        }else{
            $strdatafim='';
        }

        $sql="select sum(soma) as soma,reduz,u.idrhtipoevento,evento,r.idcontaitem,r.idtipoprodserv 
        from (
            select 
                sum(e.valor) as soma,0 as reduz,t.idrhtipoevento,p.idpessoa,concat(ifnull(p.nomecurto,p.nome),' - ".$stritem."') as evento
            from  rhevento e 
                join rhtipoevento t on( t.status ='ATIVO' 
                                and ".$strtipo."
                                and t.tipo='C'
                                and t.formato='D'
                                and t.idrhtipoevento !=38 
                                and e.idrhtipoevento=t.idrhtipoevento)
                join pessoa p on(p.idpessoa=e.idpessoa)
            where e.idpessoa=".$inidpessoa."
            and e.status ='PENDENTE'  
            ".$strdatafim."          
            and e.dataevento <=  '".$indata."'
            and e.situacao='A'
            union
            select - sum(e.valor) as soma,0 as reduz,'' as idrhtipoevento,p.idpessoa,concat(ifnull(p.nomecurto,p.nome),' - ".$stritem."') as evento
            from  rhevento e 
                join rhtipoevento t on( t.status ='ATIVO' 
                                and  ".$strtipo."
                                and t.tipo='D'
                                and t.formato='D' 
                                and e.idrhtipoevento=t.idrhtipoevento  -- and t.idrhtipoevento = 22
                                )
                join pessoa p on(p.idpessoa=e.idpessoa)
            where e.idpessoa=".$inidpessoa."
            and e.status ='PENDENTE'
            ".$strdatafim."
            and e.dataevento <=  '".$indata."'
            and e.situacao='A' 
            ) as u   
                left join pessoa f on(f.idpessoa=u.idpessoa)
                left join rheventofolhaitem r on(r.idrheventofolha=f.idrheventofolha and r.idrhtipoevento=u.idrhtipoevento and r.status='ATIVO')";

        $res= d::b()->query($sql);

       // die($sql);
      
        return $res;
        
    }

 
    static public function eventospagamentoPJ($inidpessoa,$indata,$tipofolha,$tipo='comum'){
        
        if($tipofolha=='FOLHA'){
            $strtipo=" (t.flgfolha='Y'  or t.flgfixo='Y'  ) ";
            $stritem="REMUNERAÇÃO";

        }elseif($tipofolha=='FOLHA FERIAS'){
            $strtipo=" t.flgferias ='Y' ";
            $stritem="FÉRIAS";
        }elseif($tipofolha=='DECIMO TERCEIRO'){
            $strtipo=" t.flgdecimoterc ='Y' ";
            $stritem="DÉCIMO TERCEIRO";
        }else{
            $strtipo=" t.flgdecimoterc2 ='Y' ";
            $stritem="DECIMO TERCEIRO - 2P";
        }	
        $sl="select * from rhfolha f 
        where tipofolha='".$tipofolha."'
        and idempresa = ".self::$idempresa." 
        and datafim < '".$indata."'         
        and status!='FECHADA' order by datafim desc limit 1";

        $resl=d::b()->query($sl);
        //die($sl);
        $qtdl=mysqli_num_rows($resl);
        if($qtdl>0){
            $rl=mysqli_fetch_assoc($resl);
            $strdatafim="  and e.dataevento > '".$rl['datafim']."'";

        }else{
            $strdatafim='';
        }

        $sql="
            select sum(soma) as soma,reduz,u.idrhtipoevento,evento,r.idcontaitem,r.idtipoprodserv 
            from (
                select 
                    sum(e.valor) as soma,0 as reduz,t.idrhtipoevento,p.idpessoa,concat(ifnull(p.nomecurto,p.nome),' - ".$stritem."') as evento
                from  rhevento e 
                    join rhtipoevento t on( t.status ='ATIVO' 
                                    and ".$strtipo." 
                                    and t.tipo='C'
                                    and t.formato='D'
                                    and t.idrhtipoevento !=38 
                                    and e.idrhtipoevento=t.idrhtipoevento)
                    join pessoa p on(p.idpessoa=e.idpessoa)
                where e.idpessoa=".$inidpessoa."
                and e.status ='PENDENTE' 
                ".$strdatafim."
                and e.dataevento <= '".$indata."'
                and e.situacao='A'
                union
                select - sum(e.valor) as soma,0 as reduz,t.idrhtipoevento,p.idpessoa,concat(ifnull(p.nomecurto,p.nome),' - ".$stritem."') as evento
                from  rhevento e 
                    join rhtipoevento t on( t.status ='ATIVO' 
                                    and ".$strtipo." 
                                    and t.tipo='D'
                                    and t.formato='D' 
                                    and e.idrhtipoevento=t.idrhtipoevento  and t.idrhtipoevento = 22)
                    join pessoa p on(p.idpessoa=e.idpessoa)
                where e.idpessoa=".$inidpessoa." 
                and e.status ='PENDENTE'
                ".$strdatafim." 
                and e.dataevento <= '".$indata."' 
                and e.situacao='A' 
                ) as u   
                    left join pessoa f on(f.idpessoa=u.idpessoa)
                    left join rheventofolhaitem r on(r.idrheventofolha=f.idrheventofolha and r.idrhtipoevento=u.idrhtipoevento and r.status='ATIVO')       
            union
            select 0 as soma,sum(e.valor) as reduz,e.idrhtipoevento,concat(ifnull(p.nomecurto,p.nome),' - ',t.evento) as evento,r.idcontaitem,r.idtipoprodserv 
            from  rhevento e 
			    join rhtipoevento t on( t.status ='ATIVO' 
								and ".$strtipo." 
								and t.tipo='D'
								and t.formato='D' 
								and e.idrhtipoevento=t.idrhtipoevento  and t.idrhtipoevento != 22)
                join pessoa p on(p.idpessoa=e.idpessoa)
                left join rheventofolhaitem r on(r.idrheventofolha=p.idrheventofolha and r.idrhtipoevento=t.idrhtipoevento and r.status='ATIVO')
            where e.idpessoa=".$inidpessoa." 
            and e.status ='PENDENTE'
            ".$strdatafim." 
            and e.dataevento <= '".$indata."' 
            and e.situacao='A'  group by e.idrhtipoevento";

        $res= d::b()->query($sql);

       // die($sql);
      
        return $res;
        
    }


    static public function valorevento($inidpessoa,$indata,$idrheventotipo,$tipofolha){

        $sl="select * from rhfolha f 
        where tipofolha='".$tipofolha."'
        and idempresa = ".self::$idempresa." 
        and datafim < '".$indata."'         
        and status!='FECHADA' order by datafim desc limit 1";

        $resl=d::b()->query($sl);
        //die($sl);
        $qtdl=mysqli_num_rows($resl);
        if($qtdl>0){
            $rl=mysqli_fetch_assoc($resl);
            $strdatafim="  and dataevento > '".$rl['datafim']."'";

        }else{
            $strdatafim='';
        }
        
        $sql="select sum(valor) as valor
            from  rhevento 
            where idrhtipoevento=".$idrheventotipo." 
            and idpessoa=".$inidpessoa." 
            and status ='PENDENTE' 
            ".$strdatafim."
            and dataevento <= '".$indata."'
            and situacao='A'";
        $res= d::b()->query($sql);
        $row=mysqli_fetch_assoc($res);    
        return $row['valor'];
    }

    static public function dnfitemrhfolha($idnf){
        $sql="select i.* from  nf n join nfitem i on(i.idnf=n.idnf)
        where n.idnf=".$idnf."
        and n.tipoobjetosolipor='rhfolha'
        and not exists(select 1 from rhfolhaitem f where f.idpessoa=i.idpessoa and f.idrhfolha =n.idobjetosolipor)";
        $res= d::b()->query($sql);
        while($row=mysqli_fetch_assoc($res)){
            $sqd=" delete  from nfitem where idnfitem = ".$row['idnfitem'];
            $resd= d::b()->query($sqd);
        }
    }

    static public function dtodosnfitemrhfolha($idnf){
        $sql="select i.* from  nf n join nfitem i on(i.idnf=n.idnf)
        where n.idnf=".$idnf."
        and n.tipoobjetosolipor='rhfolha'
        and exists(select 1 from rhfolhaitem f where f.idpessoa=i.idpessoa and f.idrhfolha =n.idobjetosolipor)";
        $res= d::b()->query($sql);
        while($row=mysqli_fetch_assoc($res)){
            $sqd=" delete  from nfitem where idnfitem = ".$row['idnfitem'];
            $resd= d::b()->query($sqd);
        }
    }

    static public function buscarRheventoFolhaIdcontatipoIdprodserv($idpessoa,$idrhtipoevento){
        $sql="SELECT 
                i.idcontaitem, i.idtipoprodserv
            FROM
                pessoa p
                    JOIN
                rheventofolhaitem i ON (i.idrheventofolha = p.idrheventofolha and i.status='ATIVO'
                    AND i.idrhtipoevento = ".$idrhtipoevento.")
            WHERE
                p.idpessoa =".$idpessoa;
         $res= d::b()->query($sql);
         $row=mysqli_fetch_assoc($res);   
         return $row;

    }
}