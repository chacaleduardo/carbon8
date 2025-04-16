<?
//print_r($_SESSION["arrpostbuffer"]["x"]["u"]["nf"]["idnf"]);die;
$iu = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'] ? 'u' : 'i';

$_idnf = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];
$natop = $_SESSION['arrpostbuffer']['1']['u']['nf']['natop'];
$status=$_SESSION['arrpostbuffer']['1']['u']['nf']['status'];

$_idnfajax = $_SESSION["arrpostbuffer"]["x"]["u"]["nf"]["idnf"];

$_arrtabdef = retarraytabdef('nfitem');
/*
 * INSERIR ITENS NA TELA DE PEDIDO CLICANDO EM +
 */
$arrInsProd=array();
foreach($_POST as $k=>$v) {
	if(preg_match("/_(\d*)#(.*)/", $k, $res)){
		$arrInsProd[$res[1]][$res[2]]=$v;
	}
}

if(!empty($arrInsProd)){
   $i=99977;
   // LOOP NOS ITENS DO + DA TELA
   foreach($arrInsProd as $k=>$v){
      // print_r($v);die();
       $i=$i+1;

        $idprodserv=$v['idprodserv'];
        $idnf= $_SESSION["arrpostbuffer"]["1"]["u"]["nf"]["idnf"];
        

        if(empty($idprodserv)){die("[saveprechange_nf]-Não foi possivel identificar o produto!!!");}
        if(empty($idnf)){die("[saveprechange_nf]-Não foi possivel identificar o ID do Pedido!!!");}
	
        //busca endereco do cliente ou fornecedor tipoendereco = sacado		
        $sqlp = "select e.uf,p.inscrest,indiedest,p.idpessoa 
            from nf f,pessoa p, endereco e 
        where p.idpessoa = f.idpessoafat
        and e.idendereco = f.idenderecofat and f.idnf = ".$idnf;
        $resp = d::b()->query($sqlp) or die("Erro ao buscar endereco: ".mysqli_error());
        $qtdrowsp= mysqli_num_rows($resp);
        $rowuf=mysqli_fetch_assoc($resp);
        $uf=$rowuf["uf"];
        if($qtdrowsp == 0){	
            //$aliqicms=18;
           // $uf="MG";
            die("Não foi encontrado a UF do cliente!!!");
        }			

        
	if(!empty($uf)){
	
            $sqlaliq="select i.idaliqicms,i.aliq,ii.aliq as aliqicmsint
                    from aliqicmsuf a,aliqicms i,aliqicms ii
                    where ii.idaliqicms=a.idaliqicmsint
                    and i.idaliqicms = a.idaliqicms
                    and a.uf='".$uf."'";
            $resaliq=d::b()->query($sqlaliq);
            $rowaliq=mysqli_fetch_assoc($resaliq);

            //se não tiver IE pega produto com valor de aliquota de 18
            if($rowuf['indiedest'] == 9 and $uf !="MG"){
                $sqlaliq18="select i.idaliqicms,i.aliq
                                from aliqicms i
                                where i.idaliqicms = 4";
                $resaliq18=d::b()->query($sqlaliq18);
                $rowaliq18=mysqli_fetch_assoc($resaliq18);
                $aliqitem=$rowaliq18['idaliqicms'];
            }else{
                $aliqitem=$rowaliq['idaliqicms'];
            }
		
	}else{
            die("Não foi possivel identificar o estado UF do cliente!!!");
	}


            $_sql = "select ps.*,p.valor,
                                    (select sum(l.qtddisp) from lote l
                                            where l.status = 'APROVADO'
                                            and l.idprodserv = ps.idprodserv) as qtddisp 
                    from prodserv ps left join prodvalor  p on(p.idprodserv = ps.idprodserv and p.idaliqicms = ".$aliqitem." and p.status='ATIVO')
                    where ps.idprodserv =".$idprodserv."  order by ps.descr";



            //echo $_sql;	
            $res = d::b()->query($_sql) or die($_sql."Erro ao retornar produto: ".mysqli_error());
            $qtdrows1= mysqli_num_rows($res);
            $ufemp=traduzid("empresa","idempresa","uf",$_SESSION["SESSAO"]["IDEMPRESA"]);
	
		
            if($qtdrows1 > 0){	
              
                $row = mysqli_fetch_assoc($res);
                  

                if($ufemp==$uf /*or $rowuf['indiedest']==1*/){
                        if($row['cst']!=60 and $row['cst']!=41 and $row['cst']!=00){// se for 60 e devolução e deve permanecer o 60
                                $row['cst']=40;
                        }
                }

                $sqlvlr="select d.desconto from contratopessoa cp,contrato c,desconto d
                                        where d.idaliqicms =  ".$aliqitem." 
                                        and d.idtipoteste = ".$row['idprodserv']."
                                        and d.idcontrato = c.idcontrato
                                        and c.tipo = 'P'
                                        and c.status = 'ATIVO'
                                        and c.idcontrato = cp.idcontrato
                                        and  cp.idpessoa = ".$rowuf['idpessoa'];
                $resvlr=d::b()->query($sqlvlr) or die("Erro ao buscar valor de contrato do produto sql=".$sqlvlr);
                $qtdvlr=mysqli_num_rows($resvlr);
                if($qtdvlr>0){
                        $rowvlr=mysqli_fetch_assoc($resvlr);
                        $valor=$rowvlr['desconto'];

                }else{
                        $valor=$row["valor"];
                }
                
               
                // montar o item para insert
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['qtd']=$v["quantidade"];
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"];
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['idprodserv']=$idprodserv;
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['idnf']=$idnf;
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['tiponf']='O';
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['ncm']=$row["ncm"];
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['aliqbasecal']=$row["reducaobc"];                
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['aliqipi']=$row["ipi"];                
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['aliqicms']=$rowaliq["aliq"]; 
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['aliqicmsint']=$rowaliq["aliqicmsint"]; 
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['cst']= (string)$row["cst"];
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['piscst']= (string)$row["piscst"];
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['confinscst']= (string)$row["confinscst"];
                $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['vlritem']=$valor;
		$_SESSION['arrpostbuffer'][$i]['i']['nfitem']['criadopor']=$_SESSION["SESSAO"]["USUARIO"];
		$_SESSION['arrpostbuffer'][$i]['i']['nfitem']['criadoem']=dmahms(sysdate());
		$_SESSION['arrpostbuffer'][$i]['i']['nfitem']['alteradopor']=$_SESSION["SESSAO"]["USUARIO"];
		$_SESSION['arrpostbuffer'][$i]['i']['nfitem']['alteradoem']=dmahms(sysdate());
                if($rowuf['indiedest'] == 9 and $uf !="MG"){
                   $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['indiedest']=$rowuf["indiedest"];   
                   $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['cfop']='6107';   
                }
                 
            }else{
                die("Não encontrada configuração do produto!!!!");
            }
      
   } //foreach($arrInsProd as $k=>$v){
  
}//if(!empty($arrInsProd)){

if(!empty($_idnfajax) and empty($_idnf)){
    $_idnf=$_idnfajax;
    $iu="u";
}

if(empty($natop) and $iu=="u" and !empty($_idnf) and $status!="ORCAMENTO"){
    	// preencher natureza da operação e fimnfe na notafiscal
	$sqlcfop="SELECT i.cfop,c.finnfe,natop
				FROM nfitem i,cfop c
				where c.cfop = i.cfop
				and  i.idnf = ".$_idnf."
				and (i.cfop is not null and i.cfop !=' ') limit 1";
	$rescfop=mysql_query($sqlcfop) or die("Erro ao buscar CFOP");	
	$qtdcfop=mysql_num_rows($rescfop);
	if($qtdcfop>0){		
		$rowcfop=mysql_fetch_assoc($rescfop);	
		$finnfe=$_SESSION['arrpostbuffer']['1']['u']['nf']['finnfe'];
		if(!empty($rowcfop['finnfe']) ){		
		$_SESSION['arrpostbuffer']['1']['u']['nf']['finnfe']=$rowcfop['finnfe'];
		}
		
		if(!empty($rowcfop['natop']) ){
		$_SESSION['arrpostbuffer']['1']['u']['nf']['natop']=$rowcfop['natop'];
		}
	}
}


if($iu=="u" and !empty($_idnf) and $_POST['collapse']=='Y' ){
    $sqlu="update nfitem set collapse = 'collapse in' where idnf=".$_idnf;
    $res=d::b()->query($sqlu) or die("Erro ao atualizar collapse dos itens sql=".$sqlu);
}elseif($iu=="u" and !empty($_idnf) and $_POST['collapse']=='N' ){
    $sqlu="update nfitem set collapse = 'collapse' where idnf=".$_idnf;
    $res=d::b()->query($sqlu) or die("Erro ao atualizar collapse dos itens  sql=".$sqlu);
}

if($iu=="i" and !empty($_SESSION['arrpostbuffer']['1']['i']['nf']['idpessoa'])){
    $_SESSION['arrpostbuffer']['1']['i']['nf']['idpessoafat']=$_SESSION['arrpostbuffer']['1']['i']['nf']['idpessoa'];
    $_SESSION['arrpostbuffer']['1']['i']['nf']['idenderecofat']=$_SESSION['arrpostbuffer']['1']['i']['nf']['idendereco'];
}