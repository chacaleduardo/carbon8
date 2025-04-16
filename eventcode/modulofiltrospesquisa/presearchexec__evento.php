<?

$idusuario= $_SESSION["SESSAO"]["IDPESSOA"];//$perfilpag_idpessoa;

$sql="select * from "._DBCARBON."._lpmodulo where modulo ='eventomaster' and idlp in(".getModsUsr("LPS").")";
$res=d::b()->query($sql) or die("erro ao buscar supervisor sql=".$sql);
$qtdsup= mysqli_num_rows($res);

if(!$qtdsup){
  
    $_SESSION["SEARCH"]["WHERE"]["idpessoa"] ="
    (idpessoa = ".$idusuario."
        OR EXISTS( SELECT 
            1
        FROM
            fluxostatuspessoa mp
        WHERE
            idmodulo = idevento
                AND modulo = 'evento'
                AND mp.idobjeto = ".$idusuario."
                AND mp.tipoobjeto = 'pessoa')
        OR EXISTS( SELECT 
            1 
             FROM
                fluxoobjeto o 
            WHERE
                 o.idfluxo =idfluxo
                    AND o.tipoobjeto = 'pessoa' 
                    AND  o.idobjeto = ".$idusuario."
                    AND o.tipo='ABERTO')
    )";
    
}

//print_r( $SESSION["SEARCH"]["WHERE"]); die;