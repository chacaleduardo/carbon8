<?
 $_idtipoteste=$_SESSION['arrpostbuffer']['x']['i']['desconto']['idtipoteste'];
 $_idcontrato=$_SESSION['arrpostbuffer']['x']['i']['desconto']['idcontrato'];
 
// inserir as formulas e comissões ao inserir o produto
 if(!empty($_idtipoteste) and  !empty($_idcontrato) and !empty($_SESSION["_pkid"]) ){
    // echo('qa');
    $tipocontrato=traduzid("contrato","idcontrato","tipo", $_idcontrato);
    $idplantel=traduzid("contrato","idcontrato","idplantel", $_idcontrato);
    
    $sqld="select * from desconto where idcontrato= ".$_idcontrato." order by iddesconto desc limit 1";
    $resd = d::b()->query($sqld) or die("Erro ao buscar ulitmo desconto criado: ".mysqli_error()." ".$sqld);
    $rowd=mysqli_fetch_assoc($resd);
    $iddesconto=$rowd['iddesconto'];

    if($tipocontrato=='P'){
        $tipocontrato='PRODUTO';
    }else{
        $tipocontrato='SERVICO';
    }

   
  
    //somente produtos
    //if($tipocontrato=='P'){
/*
        $sql="select idprodservformula,vlrvenda from prodservformula f where f.idprodserv = ".$_idtipoteste." and status='ATIVO'";
        $res = d::b()->query($sql) or die("Erro ao buscar formulas do produto: ".mysqli_error());
        while($row=mysqli_fetch_assoc($res)){
            if(empty($row['vlrvenda'])){
                $row['vlrvenda']='0.00';
            }
                $inscont = new Insert();
                $inscont->setTable("contratoprodservformula");
                $inscont->iddesconto=$_SESSION["_pkid"];
                $inscont->idprodservformula=$row['idprodservformula'];     
                $inscont->valor=$row['vlrvenda'];     
                $idcontratoprodservformula=$inscont->save();

        }//while($row=mysqli_fetch_assoc($res)){
*/

        if($tipocontrato=='PRODUTO'){
            $sql1="select p.idpessoa,ifnull(p.nomecurto,p.nome) as nome 
            from contratopessoa cp join pessoacontato c on (c.idpessoa = cp.idpessoa)
            join pessoa p on(p.idpessoa = c.idcontato and p.idtipopessoa in (12,1) and p.status  ='ATIVO')
            join  plantelobjeto po on( po.idobjeto =p.idpessoa and po.tipoobjeto='pessoa' and po.idplantel=".$idplantel.")
            where cp.idcontrato =".$_idcontrato." 
            ".getidempresa('p.idempresa','pessoa')."     
            group by idpessoa           
            union
            select f.idpessoa,ifnull(f.nomecurto,f.nome) as nome 
            from contratopessoa cp 
            join  plantelobjeto po on( po.idobjeto =cp.idpessoa and po.tipoobjeto='pessoa' and po.idplantel=".$idplantel.")
            join divisaoplantel dp on(dp.idplantel=po.idplantel)
            join divisao d on (dp.iddivisao =d.iddivisao and d.tipo='".$tipocontrato."' and d.status='ATIVO')   
            join pessoa f on(f.idpessoa = d.idpessoa)                
            where cp.idcontrato =".$_idcontrato." 
            ".getidempresa('f.idempresa','pessoa')."                 
            group by idpessoa order by nome";

        }else{
            $sql1="select p.idpessoa,ifnull(p.nomecurto,p.nome) as nome 
            from contratopessoa cp join pessoacontato c on (c.idpessoa = cp.idpessoa)
            join pessoa p on(p.idpessoa = c.idcontato and p.idtipopessoa in (12,1) and p.status  ='ATIVO')
            where cp.idcontrato =".$_idcontrato." 
            ".getidempresa('p.idempresa','pessoa')."     
            group by idpessoa           
            union
            select f.idpessoa,ifnull(f.nomecurto,f.nome) as nome 
            from contratopessoa cp 
            join  plantelobjeto po on( po.idobjeto =cp.idpessoa and po.tipoobjeto='pessoa')
            join divisaoplantel dp on(dp.idplantel=po.idplantel)
            join divisao d on (dp.iddivisao =d.iddivisao and d.tipo='".$tipocontrato."' and d.status='ATIVO')   
            join pessoa f on(f.idpessoa = d.idpessoa)                
            where cp.idcontrato =".$_idcontrato." 
            ".getidempresa('f.idempresa','pessoa')."                 
            group by idpessoa order by nome";

        }
 
      
//die($sql1);
        $res1 = d::b()->query($sql1) or die("Erro ao buscar responsaveis do produto: ".mysqli_error()." ".$sql1);
        while($row1=mysqli_fetch_assoc($res1)){
            $inscontc = new Insert();
            $inscontc->setTable("contratocomissao");
            $inscontc->iddesconto= $iddesconto;
            $inscontc->idpessoa=$row1['idpessoa'];            
            $idcontratoc=$inscontc->save();
        }//while($row1=mysqli_fetch_assoc($res1)){   
    
    //}// if($tipocontrato=='P'){
 }//f(!empty($_idtipoteste) and  !empty($_idcontrato) and !empty($_SESSION["_pkid"]) ){

 $_idpessoa=$_SESSION['arrpostbuffer']['x']['u']['contrato']['idpessoa'];
 $_idcontrato=$_SESSION['arrpostbuffer']['x']['u']['contrato']['idcontrato'];
//inserir comissão ao inserir um novo cliente 
 if(!empty($_idpessoa) and !empty( $_idcontrato)){
    $tipocontrato=traduzid("contrato","idcontrato","tipo", $_idcontrato);
    //somente produtos
  //  if($tipocontrato=='P'){
    if($tipocontrato=='P'){
        $tipocontrato='PRODUTO';
    }else{
        $tipocontrato='SERVICO';
    }

    if($tipocontrato=='PRODUTO'){
        $sql="select p.idpessoa,ifnull(p.nomecurto,p.nome) as nome
        from pessoacontato c 
        join pessoa p on(p.idpessoa = c.idcontato and p.idtipopessoa in (12,1) and p.status  ='ATIVO')
        join  plantelobjeto po on( po.idobjeto =p.idpessoa and po.tipoobjeto='pessoa' and po.idplantel=".$idplantel.")
        where c.idpessoa=".$_idpessoa."
        group by idpessoa 
        union	
    select f.idpessoa,ifnull(f.nomecurto,f.nome) as nome
        from  plantelobjeto po 
        join divisaoplantel dp on(dp.idplantel=po.idplantel)
        join divisao d on (dp.iddivisao =d.iddivisao and d.tipo='".$tipocontrato."' and d.status='ATIVO' )   
        join pessoa f on(f.idpessoa = d.idpessoa)
        where  po.idobjeto=".$_idpessoa." 
        and po.tipoobjeto='pessoa' 
        and po.idplantel=".$idplantel."
        group by idpessoa";

    }else{
        $sql="select p.idpessoa,ifnull(p.nomecurto,p.nome) as nome
        from pessoacontato c 
        join pessoa p on(p.idpessoa = c.idcontato and p.idtipopessoa in (12,1) and p.status  ='ATIVO')
       
        where c.idpessoa=".$_idpessoa."
        group by idpessoa 
        union	
            select f.idpessoa,ifnull(f.nomecurto,f.nome) as nome
                from  plantelobjeto po 
                join divisaoplantel dp on(dp.idplantel=po.idplantel)
                join divisao d on (dp.iddivisao =d.iddivisao and d.tipo='".$tipocontrato."' and d.status='ATIVO' )   
                join pessoa f on(f.idpessoa = d.idpessoa)
                where  po.idobjeto=".$_idpessoa." 
                and po.tipoobjeto='pessoa' 
                group by idpessoa";
    }


        $res = d::b()->query($sql) or die("Erro ao buscar responsaveis do cliente: ".mysqli_error());
        while($row=mysqli_fetch_assoc($res)){
            $sql1=" select * from desconto d 
            where d.idcontrato=". $_idcontrato."
            and not exists(select 1 from contratocomissao c where c.iddesconto = d.iddesconto and c.idpessoa = ".$row['idpessoa']." )";
            $res1 = d::b()->query($sql1) or die("Erro ao buscar se ja exite comissao para o produto: ".mysqli_error());
            while($row1=mysqli_fetch_assoc($res1)){
                $inscontc = new Insert();
                $inscontc->setTable("contratocomissao");
                $inscontc->iddesconto=$row1['iddesconto'];
                $inscontc->idpessoa=$row['idpessoa'];               
                $idcontratoc=$inscontc->save();
            }//while($row1=mysqli_fetch_assoc($res1)){
        }//while($row=mysqli_fetch_assoc($res)){
    
   // }//if($tipocontrato=='P'){
 }// if(!empty($_idpessoa) and !empty( $_idcontrato)){

?>