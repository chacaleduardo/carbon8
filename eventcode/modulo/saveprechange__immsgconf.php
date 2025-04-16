<?

$objeto=$_SESSION['arrpostbuffer']['x']['i']['immsgconfdest']['objeto'];
$idobjeto=$_SESSION['arrpostbuffer']['x']['i']['immsgconfdest']['idobjeto'];
$idimmsgconf=$_SESSION['arrpostbuffer']['x']['i']['immsgconfdest']['idimmsgconf'];

$Didimmsgconfdest=$_SESSION['arrpostbuffer']['x']['d']['immsgconfdest']['idimmsgconfdest'];



if(!empty($idimmsgconf) and !empty($idobjeto) and $objeto=='imgrupo'){
     
    /*
        $s="select 
                ps.idpessoa
            from pessoaobjeto ps,pessoa p 
            where p.idpessoa=ps.idpessoa 
            and p.status='ATIVO' 
            and ps.idobjeto = ".$idobjeto."
			and ps.tipoobjeto = 'sgsetor'
            and not exists (select 1 from immsgconfdest d where d.idimmsgconf=".$idimmsgconf." and d.idobjeto=p.idpessoa and d.objeto='pessoa' )
            union
            select 
                p.idpessoa 
            from pessoa p,sgsetor s 
            where s.idtipopessoa=p.idtipopessoa 
            and p.status='ATIVO' 
            and not exists (select 1 from immsgconfdest d where d.idimmsgconf=".$idimmsgconf." and d.idobjeto=p.idpessoa and d.objeto='pessoa' )
            and s.idsgsetor=".$idobjeto;
            */
        $s="select ps.idpessoa 
                    from imgrupopessoa ps join pessoa p on(p.status='ATIVO' and p.idpessoa = ps.idpessoa)
                     where idimgrupo=".$idobjeto."
                     and not exists (select 1 from immsgconfdest d where d.idimmsgconf=".$idimmsgconf." and d.idobjeto=p.idpessoa and d.objeto='pessoa')";

        $r=d::b()->query($s) or die("Erro ao buscar imgrupopessoa para: ".mysqli_error(d::b()).$s);
        $i=99;
        while($rw=mysqli_fetch_assoc($r)){
            $i++;
            $_SESSION['arrpostbuffer'][$i]['i']['immsgconfdest']['idimmsgconf']=$idimmsgconf;
            $_SESSION['arrpostbuffer'][$i]['i']['immsgconfdest']['idobjeto']=$rw['idpessoa'];
            $_SESSION['arrpostbuffer'][$i]['i']['immsgconfdest']['objeto']='pessoa';
			$_SESSION['arrpostbuffer'][$i]['i']['immsgconfdest']['idobjetoext']=$idobjeto;
			$_SESSION['arrpostbuffer'][$i]['i']['immsgconfdest']['objetoext']='imgrupo';
        }//while($rw=mysqli_fetch_assoc($r)){        

    
}elseif(!empty($Didimmsgconfdest)){
   /* 
    $s="select 
		d2.idimmsgconfdest
        from pessoaobjeto ps,immsgconfdest d,immsgconfdest d2
        where d2.objeto ='pessoa'
        and d2.idobjeto=ps.idpessoa
        and ps.idobjeto = d.idobjeto
		and ps.tipoobjeto = 'sgsetor'
        and d.idimmsgconf = d2.idimmsgconf
        and d.objeto='sgsetor'
        and d.idimmsgconfdest=".$Didimmsgconfdest."  
        union
        select 
            d2.idimmsgconfdest
        from pessoa p,sgsetor s,immsgconfdest d,immsgconfdest d2 
        where d2.objeto ='pessoa'
        and d2.idobjeto=p.idpessoa 
        and s.idtipopessoa=p.idtipopessoa 
        and p.status='ATIVO' 
        and s.idsgsetor=d.idobjeto
        and d.idimmsgconf = d2.idimmsgconf
        and d.objeto='sgsetor'
        and d.idimmsgconfdest=".$Didimmsgconfdest;
    */
    
    $s="select 
    d2.idimmsgconfdest
    from imgrupopessoa ps,immsgconfdest d,immsgconfdest d2
    where d2.objeto ='pessoa'
    and d2.idobjeto=ps.idpessoa
    and ps.idimgrupo = d.idobjeto
    and d.idimmsgconf = d2.idimmsgconf
    and d.objeto='imgrupo'
    and d.idimmsgconfdest=".$Didimmsgconfdest;

    $r=d::b()->query($s) or die("Erro ao buscar imgrupopessoa para: ".mysqli_error(d::b()).$s);
    $i=99;
    while($rw=mysqli_fetch_assoc($r)){
        $i=$i+1;
        $_SESSION['arrpostbuffer'][$i]['d']['immsgconfdest']['idimmsgconfdest']=$rw["idimmsgconfdest"];
    }
    
    
}

//print_r($_SESSION['arrpostbuffer']); die;