<?
//tel itau 0300 100 7575 218875001 
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
include ('../inc/CnabPHP/vendor/autoload.php');
//print_r($_GET);
if($_POST){
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "remessa";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idremessa" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from remessa where idremessa = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");


$data1=$_GET["data1"];
$data2=$_GET["data2"];
$emissao1=$_GET["emissao1"];
$emissao2=$_GET["emissao2"];
$nnfe1=$_GET["nnfe1"];
$nnfe2=$_GET["nnfe2"];
$cliente=$_GET["cliente"];
$formapagto=$_GET["formapagto"];



if(!empty($_1_u_remessa_idagencia)){
$sqle="select 
            e.nomefantasia,e.razaosocial,e.cnpj,e.xlgr,e.nro,e.xbairro,
            e.xmun,e.uf,e.cep,nagencia,nconta,
           a.instrucao
        from 
             agencia a join  empresa e on(a.idempresa=e.idempresa)
        where a.idagencia=".$_1_u_remessa_idagencia;
$rese =  d::b()->query($sqle) or die("Falha ao pesquisar informações da empresa: " . mysqli_error() . "<p>SQL: $sqle");
$qtdeinf=mysqli_num_rows($rese);
if($qtdeinf==0){die('Configurar agencia no cadastro da empresa.');}
$rowe=mysqli_fetch_assoc($rese);
$nconta = explode("-", $rowe['nconta']);

// CABEÇALHO DO ARQUIVO DO BANCO
$codigo_banco = Cnab\Banco::ITAU;
$arquivo = new Cnab\Remessa\Cnab400\Arquivo($codigo_banco);
$arquivo->configure(array(
    'data_geracao'  => new DateTime(),
    'data_gravacao' => new DateTime(), 
    'nome_fantasia' => $rowe['nomefantasia'], // seu nome de empresa
    'razao_social'  => $rowe['razaosocial'],  // sua razão social
    'cnpj'          => $rowe['cnpj'], // seu cnpj completo
    'banco'         => $codigo_banco, //código do banco
    'logradouro'    => $rowe['xlgr'],
    'numero'        => $rowe['nro'],
    'bairro'        => $rowe['xbairro'], 
    'cidade'        => $rowe['xmun'],
    'uf'            => $rowe['uf'],
    'cep'           => $rowe['cep'],
    'agencia'       => $rowe['nagencia'], 
    'conta'         => $nconta[0], // número da conta
    'conta_dac'     => $nconta[1], // digito da conta
));
}
?>
<script>
<?/*
if($_1_u_remessa_status=="CONCLUIDO" ){?>
        //$("#cbModuloForm:input").not('[name*="nf_idnf"],[name*="statusant"],[id*="cbTextoPesquisa"]').prop( "disabled", true );
        $("#cbModuloForm").find('input').not('[name*="remessa_idremessa"]').prop( "disabled", true );
        $("#cbModuloForm").find("select" ).prop( "disabled", true );
        $("#cbModuloForm").find("textarea").prop( "disabled", true );
        $("#cbModuloForm").find("button").prop( "disabled", true );
       
    <?} 
    
    */?>
</script>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">		
            <table>
                <tr>
                    <td><strong>Remessa:</strong></td>
                    <td>
                    <?if(!empty($_1_u_remessa_idregistro)){?>
                        <label class="alert-warning"><?=$_1_u_remessa_idregistro?></label>
                    <?}elseif(!empty($_1_u_remessa_idremessa)){?>
                        <label class="alert-warning"><?=$_1_u_remessa_idremessa?></label>
                    <?}?>
                        <input id="idremessa" name="_1_<?=$_acao?>_remessa_idremessa" type="hidden"	value="<?=$_1_u_remessa_idremessa?>">
                    </td>
                    <td  align="right">Data:</td> 
                    <td>
                        <input name="_1_<?=$_acao?>_remessa_dataenvio" class="calendario" size="10"	value="<?=$_1_u_remessa_dataenvio?>" vnulo>
                    </td>
                    <td  align="right">Agência:</td> 
                    <td>
                        <?
                        if(!empty($_1_u_remessa_idagencia)){
                            $readonlyag="readonly='readonly'";
                            $_agencia= traduzid('agencia', 'idagencia', 'agencia', $_1_u_remessa_idagencia);                         
                        ?>
                            <label class="alert-warning"><?=$_agencia?></label>
                            <input id="agencia"name="_1_<?=$_acao?>_remessa_idagencia" type="hidden"	value="<?=$_1_u_remessa_idagencia?>">
                        <?

                        }else{
                            $readonlyag='';
                        
                        ?>
                        <select name="_1_<?=$_acao?>_remessa_idagencia" id="agencia" vnulo>
                            <?fillselect("select idagencia,agencia 
                                            from agencia 
                                            where status='ATIVO'
                                            ".getidempresa("idempresa",$_GET['_modulo'])."
                                            and boleto is not null 
                                            and remessa is not null 
                                            order by agencia",$_1_u_remessa_idagencia);?>		
                        </select>
                        <?}?>
                    </td>
		          
                    <td>
                    <?if(!empty($_1_u_remessa_texto) and($_1_u_remessa_status!='PENDENTE') ){
                        $sqla="select 
                                    a.remessa
                                from 
                                    agencia a 
                                where a.idagencia=".$_1_u_remessa_idagencia;
                        $resa=d::b()->query($sqla) or die("Erro ao buscar remessa da agencia sql=".$sqla);
                        $rowa=mysqli_fetch_assoc($resa);
                    ?>
                        <a class="fa fa-download" onclick="janelamodal('report/<?=$rowa['remessa']?>.php?idremessa=<?=$_1_u_remessa_idremessa?>&idagencia=<?=$_1_u_remessa_idagencia?>')"></a>
                    <?}?>
                    </td>
                    <td  align="right">Status:</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_remessa_status" id="nfstatus">
                            <?fillselect("select 'PENDENTE','Pendente' 
                                    union select 'GERADO','Gerado' 
                                    union select 'ENVIADO','Enviado' 
                                    union select 'CONCLUIDO','Concluido'",$_1_u_remessa_status);?>		
                        </select>
                    </td>
                    
                    <td><a class="fa fa-refresh cinzaclaro hoverazul btn-lg pointer" title="Atualizar e limpar" onclick="limpar();" ></a></td>
                </tr>
            </table>
        </div>
    </div>
    </div>
</div>
<?
$i=99;
if(!empty($_1_u_remessa_idremessa)){
    if($_1_u_remessa_status =='PENDENTE' OR $_1_u_remessa_status =='GERADO'){
?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" data-toggle="collapse" href="#localInfo">Pesquisar Contas a Receber</div>
        <div class="panel-body collapse" id="localInfo">
            <div class="row">      
                <div class="col-md-3">Emissão entre:</div>
                <div class="col-md-4"> <input name="emissao1" class="calendario" size="10"	value="<?=$emissao1?>"></div>
                <div class="col-md-1">e:</div>
                <div class="col-md-4"> <input name="emissao2" class="calendario" size="10"	value="<?=$emissao2?>"></div>
            </div>
            <div class="row">      
                <div class="col-md-3">Vencimento entre:</div>
                <div class="col-md-4"> <input name="data1" class="calendario" size="10"	value="<?=$data1?>"></div>
                <div class="col-md-1">e:</div>
                <div class="col-md-4"> <input name="data2" class="calendario" size="10"	value="<?=$data2?>"></div>
            </div>
            <div class="row">      
                <div class="col-md-3">N<u>o</u> NF:</div>
                <div class="col-md-4"> <input name="nnfe1"  size="10"	value="<?=$nnfe1?>"></div>
                <div class="col-md-1">e:</div>
                <div class="col-md-4"> <input name="nnfe2"  size="10"	value="<?=$nnfe2?>"></div>
            </div>
             <div class="row">      
                <div class="col-md-3">Cliente:</div>
                <div class="col-md-9"> <input name="cliente"  size="10"	value="<?=$cliente?>"></div>
            </div>
            <div class="row">
                <div class="col-md-3">Form. Pagto:</div>
                <div class="col-md-9">
                    <select  name="formapagto">
                        <?fillselect("select idformapagamento,descricao 
					from formapagamento 
					where status='ATIVO'  ".getidempresa('idempresa','formapagamento')."
                    and idagencia=".$_1_u_remessa_idagencia."
                     and geraremessa='Y' order by ord,descricao",$formapagto);?>		
                    </select> 
                </div>
            </div>           
             <div class="row"> 
                 <div class="col-md-9"></div>
                  <div class="col-md-3">
                    <button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar(this)">
                        <span class="fa fa-search"></span>
                    </button> 
                  </div>
            </div>           
            
        </div>
    </div>
    </div>
</div>
<?

    if (!empty($data1) or !empty($data2)){
	    $dataini = validadate($data1);
	    $datafim = validadate($data2);

	    if ($dataini and $datafim){
		    $clausulad .= " and ( c.datapagto  BETWEEN '" . $dataini ."' and '" .$datafim ."')";
	    }else{
		    die ("Datas n&atilde;o V&aacute;lidas!");
	    }
    }

    if (!empty($emissao1) or !empty($emissao2)){
	    $dataini = validadate($emissao1);
	    $datafim = validadate($emissao2);

	    if ($dataini and $datafim){
		    $sclausula .= " and ( n.emissao  BETWEEN '" . $dataini ."' and '" .$datafim ."')";
		    $pclausula .= " and ( n.dtemissao  BETWEEN '" . $dataini ."' and '" .$datafim ."')";
	    }else{
		    die ("Datas n&atilde;o V&aacute;lidas!");
	    }
    }

    if(!empty($nnfe1) and !empty($nnfe2)){
	$clausulad .= " and (n.nnfe  BETWEEN '".$nnfe1."' and '".$nnfe2."')";
    }
    if(!empty($cliente)){
	$clausulad .= " and (p.nome like   '%".$cliente."%')";
    }

    if(!empty($formapagto)){
	$clausulad .= " and (c.idformapagamento =   '".$formapagto."')";
    }

 

    if($_GET and !empty($clausulad) and !empty($_1_u_remessa_idagencia)){
 
    $sql="select * from (
        select 
        c.idcontapagar,c.tipoobjeto,n.idnf,n.nnfe,n.status as nfstatus,n.dtemissao as emissao,f.idpessoa,f.nome,p.idpessoa as idpessoacli,p.nome as nomecli,c.datareceb,c.datapagto,c.valor,c.status,c.parcelas,c.parcela,c.tipointervalo,c.intervalo,ff.formapagamento as formapagto,r.idremessaitem,r.idremessa
        from nf n join pessoa p join pessoa f join contapagar c force index (objid)
	left join remessaitem r on(r.idcontapagar = c.idcontapagar and r.status not in('A','E'))
        left join formapagamento ff on(c.idformapagamento=ff.idformapagamento)
        where c.tipo ='C'
        and c.status!='INATIVO'
        ".$clausulad."
        and c.tipoobjeto = 'nf'
        and c.parcela is not null
        and c.idagencia = ".$_1_u_remessa_idagencia."
        ".$pclausula."
        and n.idnf = c.idobjeto
        ".getidempresa('n.idempresa','pedido')."
        and n.tiponf='V'
        and n.nnfe is not null
        and n.status in ('CONCLUIDO','ENVIADO','ENVIAR')
        and n.idpessoafat = f.idpessoa
        and n.idpessoa = p.idpessoa
        -- and n.controle is not null
        and not exists (select 1 from remessaitem ri where  ri.idcontapagar=c.idcontapagar and ri.status not in('A','E'))
        union all 
        select 
        c.idcontapagar,c.tipoobjeto,n.idnotafiscal as idnf,n.nnfe,n.status as nfstatus,n.emissao,p.idpessoa,p.nome,p.idpessoa as idpessoacli,p.nome as nomecli,c.datareceb,c.datapagto,c.valor,c.status,c.parcelas,c.parcela,c.tipointervalo,c.intervalo,ff.formapagamento as formapagto,r.idremessaitem,r.idremessa
        from notafiscal n join pessoa p join contapagar c force index (objid)
	left join remessaitem r on(r.idcontapagar = c.idcontapagar and r.status not in('A','E'))
        left join formapagamento ff on(c.idformapagamento=ff.idformapagamento)
        where c.tipo ='C'
        and c.status!='INATIVO'
        and c.parcela is not null
        ".$clausulad."
        and c.tipoobjeto = 'notafiscal'
        and c.idagencia = ".$_1_u_remessa_idagencia."
        ".$sclausula."
        and n.idnotafiscal = c.idobjeto
        ".getidempresa('n.idempresa','nfs')."
        and n.status in ('FATURADO','CONCLUIDO')
        -- and n.controle is not null
        and n.idpessoa = p.idpessoa
        and not exists (select 1 from remessaitem ri where ri.idcontapagar=c.idcontapagar and ri.status not in('A','E'))
     ) as u 
	order by u.nnfe,u.parcela";
    $res=d::b()->query($sql) or die("Erro ao buscar parcelas sql=".$sql);
    $qtdrows=mysqli_num_rows($res);


?>  <!-- <?=$sql?>-->
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Resultado da pequisa</div>
        <div class="panel-body">
<?
	if($qtdrows>0){
?>
            <table class="table table-striped planilha" id="inftable">
            <thead>
            <tr>
                
                <th>N<u>o</u> NF</th>  
                <th >Parcela</th>                
                <th >Cliente</th>
                <th >Faturar para</th>
                <th >Emissão</th>
                <th >Vencimento</th>               
                <th >Valor</th>
                <th >Pagamento</th>               
                <th >Form. Pagto</th>
                <th >Arquivo</th>
            </tr>
            </thead>
            <tbody>
<?
      
        while($row=mysqli_fetch_assoc($res)){
            $i=$i+1;
            if($row['tipoobjeto']=='nf'){
                $modulo='pedido';
		$idmodulo='idnf';
            }else{
                $modulo='nfs';
		$idmodulo='idnotafiscal';
            }
                    			
     
?>
            <tr> 
                <td>
		    <a  class="pointer hoverazul" title="Nota" onclick="janelamodal('?_modulo=<?=$modulo?>&_acao=u&<?=$idmodulo?>=<?=$row['idnf']?>')">
                        <?=$row["nnfe"]?>
                    </a>
                </td>   
                <td >
		     <a  class="pointer hoverazul" title="Parcela" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$row['idcontapagar']?>')">
		    <?=$row["parcela"]?>/<?=$row["parcelas"]?>
		    </a>
		</td> 
                <td >
		    <a  class="pointer hoverazul" title="Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$row['idpessoacli']?>')">
		    <?=$row["nomecli"]?>
		    </a>
		</td>
                <td >
		    <a  class="pointer hoverazul" title="Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$row['idpessoa']?>')">
		    <?=$row["nome"]?>
		    </a>
		</td>
                <td ><?=dma($row["emissao"])?></td>
                <td ><?=dma($row["datapagto"])?></td>
                <td ><?=aplicaMascara('MOEDA', $row["valor"])?></td>
                <td ><?=$row["status"]?></td>               
                <td ><?=$row["formapagto"]?></td>
                <td>
                   
                    <input name="_<?=$i?>_i_remessaitem_idremessa" type="hidden" value="<?=$_1_u_remessa_idremessa?>">
                    <input name="_<?=$i?>_i_remessaitem_idcontapagar" type="hidden" value="<?=$row["idcontapagar"]?>">
			        <input  name="chk[<?=$i?>]" value="<?=$row["idcontapagar"]?>" type="checkbox" >       
                </td>		
            </tr>
<?
        }// while($row=mysql_fetch_assoc($res)){ 
?>
            

            </tbody>
            </table>
            <table width="100%">
                <tr>
                    <td align="right">
                <button class="btn btn-success btn-xs" onclick="adicionar(this);">
                    <i class="fa fa-circle"></i> Adicionar
                </button>	
                    </td>
                </tr>
            </table>
<?
    }else{//if($qtdrows>0){

      echo("Não foram encontradas parcelas nestas condições.");

    }//if($qtdrows>0){
  ?>
        </div>
    </div>
    </div>
</div>
<?
	}
    }
    $sql="select * from (
            select 
            CASE
                WHEN n.controle > 0 THEN concat(n.controle,c.parcela)    
                ELSE c.idcontapagar
            END as nossonumero,c.idcontapagar,c.tipoobjeto,n.idnf,n.nnfe,n.status as nfstatus,r.status as remessa,n.dtemissao as emissao,f.idpessoa,f.cpfcnpj,f.nome,f.razaosocial,p.idpessoa  as idpessoacli, p.nome as nomecli,c.datareceb,c.datapagto,c.valor,c.status,c.parcelas,c.parcela,c.tipointervalo,c.intervalo,ff.formapagamento as formapagto,r.idremessaitem,r.idremessa,
            e.endereco,e.numero,e.bairro,replace(replace(e.cep,'-',''),'.','') as cep,cc.cidade,e.uf,r.tipo 
            from nf n join pessoa p join pessoa f join contapagar c join remessaitem r join endereco e join nfscidadesiaf cc
            left join formapagamento ff on(c.idformapagamento=ff.idformapagamento)
            where c.tipo ='C'
            and c.status!='INATIVO'
            and r.idcontapagar = c.idcontapagar
            and r.idremessa = ".$_1_u_remessa_idremessa."
            and c.tipoobjeto = 'nf'
            and n.idnf = c.idobjeto
            -- and n.status='CONCLUIDO'
            and e.idpessoa=f.idpessoa
            and e.idtipoendereco = 2 
            and e.status='ATIVO'
            and cc.codcidade = e.codcidade
            and n.idpessoafat = f.idpessoa
            and n.idpessoa = p.idpessoa
            union all 
            select 
            CASE
                WHEN n.controle > 0 THEN concat(n.controle,c.parcela)    
                ELSE c.idcontapagar
            END as nossonumero,c.idcontapagar,c.tipoobjeto,n.idnotafiscal as idnf,n.nnfe,n.status as nfstatus,r.status as remessa,n.emissao,p.idpessoa,p.cpfcnpj,p.nome,p.razaosocial,p.idpessoa as idpessoacli ,p.nome as nomecli,c.datareceb,c.datapagto,c.valor,c.status,c.parcelas,c.parcela,c.tipointervalo,c.intervalo,ff.formapagamento as formapagto,r.idremessaitem,r.idremessa,
            e.endereco,e.numero,e.bairro,replace(replace(e.cep,'-',''),'.','') as cep,cc.cidade,e.uf,r.tipo 
            from notafiscal n join pessoa p join contapagar c join remessaitem r join endereco e join nfscidadesiaf cc
            left join formapagamento ff on(c.idformapagamento=ff.idformapagamento)
            where c.tipo ='C'
            and r.idcontapagar = c.idcontapagar
            and c.status!='INATIVO'
            and r.idremessa = ".$_1_u_remessa_idremessa."
            and c.tipoobjeto = 'notafiscal'
            and n.idnotafiscal = c.idobjeto
            -- and n.status='FATURADO'
            and e.idpessoa=p.idpessoa
            and e.idtipoendereco = 2 
            and e.status='ATIVO'
            and cc.codcidade = e.codcidade
            and n.idpessoa = p.idpessoa
        ) as u 
	order by u.nnfe,u.parcela";
    $res=d::b()->query($sql) or die("Erro ao buscar remessas sql=".$sql);
    $qtdrows=mysqli_num_rows($res);
    if($qtdrows>0){
?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Itens da Remessa - (<?=$qtdrows?>)</div>
        <div class="panel-body">
             <table class="table table-striped planilha">
            <thead>
            <tr>
                
                <th>N<u>o</u> NF</th>  
                <th >Parcela</th>                
                <th >Cliente</th>
                <th >Faturado para</th>
                <th >Emissão</th>
                <th >Vencimento</th>               
                <th >Valor</th>
                <th >Pagamento</th>               
                <th >Form. Pagto</th>
		<th >Operação</th>
		<th >Remessa</th>
		<?if($_1_u_remessa_status!='CONCLUIDO'){?>
                <th >Arquivo</th>
		<?}else{?>
		<th>Boleto</th>
		<?}?>
            </tr>
            </thead>
            <tbody>
    <?	    
    if(!empty($_1_u_remessa_idagencia)){
    
        $sqlt="select * from agencia where idagencia=".$_1_u_remessa_idagencia;
        $rest=d::b()->query($sqlt) or die("Erro ao buscar informacoes da agencia sql=".$sqlt);
        $rowt=mysqli_fetch_assoc($rest);
        if(empty($rowt['juros'])){
            $txjuro=0.00;
        }else{
            $txjuro=$rowt['juros']/100;
        }
        
        if(empty($rowt['multa'])){
            $txmulta=0.00;
        }else{
            $txmulta=$rowt['multa']/100;
        }
        
    }else{
        $txjuro=0.00;
        $txmulta=0.00;
    }

    
    $valor=0;
        while($row=mysqli_fetch_assoc($res)){
	    $i=$i+1;
            if($row['tipoobjeto']=='nf'){
                $modulo='pedido';
		$idmodulo='idnf';
            }else{
                $modulo='nfs';
		$idmodulo='idnotafiscal';
            }
	    $valor=$valor+$row["valor"];
    ?>
            <tr> 
                <td>
                    <a  class="pointer hoverazul" title="Nota" onclick="janelamodal('?_modulo=<?=$modulo?>&_acao=u&<?=$idmodulo?>=<?=$row['idnf']?>')">
                        <?=$row["nnfe"]?>
                    </a>
                </td>   
                <td>
		    <a  class="pointer hoverazul" title="Parcela" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$row['idcontapagar']?>')">
		    <?=$row["parcela"]?>/<?=$row["parcelas"]?>
		    </a>
		</td> 
                 <td>
		    <a  class="pointer hoverazul" title="Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$row['idpessoacli']?>')">
			<?=$row["nomecli"]?>
		    </a>
		</td>
                <td>
		    <a  class="pointer hoverazul" title="Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$row['idpessoa']?>')">
			<?=$row["nome"]?>
		    </a>
		</td>
                <td><?=dma($row["emissao"])?></td>
                <td><?=dma($row["datapagto"])?></td>
                <td><?= aplicaMascara('MOEDA', $row["valor"])?></td>
                <td><?=$row["status"]?></td>               
                <td><?=$row["formapagto"]?></td>
		<td>
		     <select name="_<?=$i?>_u_remessaitem_tipo"  >
		    <?
		    fillselect("select '1','Entrada' 
				union select '6','Alteração'
				union select '2','Baixa'",$row['tipo']);
		    ?>
		    </select>	
		</td>
		<td>
		    <input  name="_<?=$i?>_u_remessaitem_idremessaitem" type="hidden" value="<?=$row['idremessaitem']?>">
		    <select name="_<?=$i?>_u_remessaitem_status"  >
		    <?
		    fillselect("select 'P','Pendente' 
				union select 'E','Erro'
				union select 'C','Concluido' 
				union select 'A','Alterado'",$row['remessa']);
		    ?>
		    </select>		   
		</td>
		<?if($_1_u_remessa_status =='ENVIADO' OR $_1_u_remessa_status =='GERADO' OR $_1_u_remessa_status =='CONCLUIDO'){
             $sqla="select 
                        a.boleto
                    from 
                        agencia a 
                    where a.idagencia=".$_1_u_remessa_idagencia;
            $resa=d::b()->query($sqla) or die("Erro ao buscar remessa da agencia sql=".$sqla);
            $rowa=mysqli_fetch_assoc($resa);
            ?>
		<td>
		    <a class="fa fa-wpforms pointer hoverazul btn-lg pointer" title="Boleto" onclick="janelamodal('inc/boletophp/<?=$rowa['boleto']?>.php?idcontapagar=<?=$row['idcontapagar']?>')"></a>
		</td>              
		<?}else{?>
		<td>
                    <i class="fa fa-trash cinzaclaro hoververmelho btn-lg pointer" idremessaitem="<?=$row['idremessaitem']?>" idremessa="<?=$_1_u_remessa_idremessa?>" idcontapagar="<?=$row['idcontapagar']?>" onclick="altcheck(this,'D')"title="Retirar da remessa"></i>
                </td>

		<?}?>
            </tr>
    <?
    if(strlen($str)==11){
        $sacado_tipo='cpf';        
    }else{
        $sacado_tipo='cnpj';        
    }
    $cpfcnpj=formatarCPF_CNPJ($row['cpfcnpj'],true); 
    //die($cpfcnpj);

   
    $juro=round(($row["valor"]*$txjuro)/30,2) ;
    $vjuro=number_format($juro, 2, '.','');
    
    $multa=round(($row["valor"]*$txmulta),2) ;
    $vmulta=number_format($multa, 2, '.','');

    if($rowe['instrucao']=='Y'){
        $instrucao='01';
       
    }else{
        $instrucao='00';
     
    }

   
    // você pode adicionar vários remessas em uma remessa
$arquivo->insertDetalhe(array(
    'codigo_ocorrencia' => $row['tipo'], // 1 = Entrada de tà­tulo, futuramente poderemos ter uma constante #hermesp# [1 entrada,6 alteração,2 baixa]
    'nosso_numero'      => $row["nossonumero"],
    'numero_documento'  => $row["nnfe"],
    'carteira'          => '109',
    'especie'           => Cnab\Especie::ITAU_DUPLICATA_DE_SERVICO, // Vocàª pode consultar as especies Cnab\Especie
    'valor'             =>$row["valor"], // Valor do remessa
    'instrucao1'        => $instrucao, // 1 = Protestar com (Prazo) dias, 2 = Devolver apà³s (Prazo) dias, futuramente poderemos ter uma constante
    'instrucao2'        => '00', // preenchido com zeros
    'sacado_nome'       => $row["razaosocial"], // O Sacado é o cliente, preste atenção nos campos abaixo
    'sacado_razao_social'=> $row["razaosocial"], // O Sacado é o cliente, preste atenção nos campos abaixo
    'sacado_tipo'       => $sacado_tipo, //campo fixo, escreva 'cpf' (sim as letras cpf) se for pessoa fisica, cnpj se for pessoa juridica
    'sacado_cpf'        => $cpfcnpj,
    'sacado_cnpj'   => $cpfcnpj,
    'sacado_logradouro' => $row['endereco'].' '.$row['numero'],
    'sacado_bairro'     => $row['bairro'],
    'sacado_cep'        => $row['cep'], // sem hà­fem
    'sacado_cidade'     => $row['cidade'],
    'sacado_uf'         => $row['uf'],
    'data_vencimento'   => new DateTime($row['datapagto']),
    'data_cadastro'     => new DateTime(date("Y-m-d")),
    'juros_de_um_dia'     => $vjuro, // Valor do juros de 1 dia'
    'data_desconto'       => new DateTime($row['datapagto']),
    'valor_desconto'      => 00.0, // Valor do desconto
    'prazo'               => 0, // prazo de dias para o cliente pagar apà³s o vencimento
    'taxa_de_permanencia' => '0', //00 = Acata Comissão por Dia (recomendável), 51 Acata Condiçàµes de Cadastramento na CAIXA
    'mensagem'            => ' ',
    'data_multa'          => new DateTime($row['datapagto']), // data da multa
    'valor_multa'         => $vmulta, // valor da multa
));


        }
?>
            <tr>
		<td colspan="6"></td>
		<td><b><?=aplicaMascara('MOEDA', $valor)?></b></td>
	    </tr>

            </tbody>
            </table>
       </div>
    </div>
    </div>
</div>
<?   
    }
    if(!empty($_1_u_remessa_idremessa)){
?>
<!--div class="row ">
<div class="container-fluid">
    <div class="panel panel-default">		
        <div class="panel-body">
                <div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:50%;height:100%;">
                        <i class="fa fa-cloud-upload fonte18"></i>
                </div>
        </div>    
    </div>
</div>
</div-->
<?
    }
?>
<?
if(!empty($_1_u_remessa_idremessa)){
    $sql = "select p.idpessoa
                ,p.nome 
                ,CASE
                    WHEN c.status ='ATIVO' THEN dma(c.alteradoem)
                    ELSE ''
                END as dataassinatura 
                ,CASE
                    WHEN c.status ='ATIVO' THEN 'ASSINADO'
                    ELSE 'PENDENTE'
                END as status
            from carrimbo c ,pessoa p 
            where c.idpessoa = p.idpessoa
            and c.status IN ('ATIVO','PENDENTE')
            and c.tipoobjeto in('remessa')
            and c.idobjeto =".$_1_u_remessa_idremessa."  order by nome";

    $res = d::b()->query($sql) or die("A Consulta de assinaturas falhou :".mysqli_error(d::b())."<br>Sql:".$sql); 
    $existe = mysqli_num_rows($res);
    if($existe > 0){
?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Assinaturas</div>
        <div class="panel-body">
            <table class="planilha grade compacto">
               <tr>
                    <th >Funcionários</th>
                    <th >Data Assinatura</th>
                    <th >Status</th>	
                </tr>			
<?			
        while($row = mysqli_fetch_assoc($res)){			
?>	
                <tr class="res">
                    <td nowrap><?=$row["nome"]?></td>
                    <td nowrap><?=$row["dataassinatura"]?></td>
                    <td nowrap><?=$row["status"]?></td>
                </tr>				
<?							
        }
?>	
            </table>
        </div>
    </div>
    </div>
</div>
<?}}?>


<?
if(!empty($_1_u_remessa_idremessa)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_remessa_idremessa; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "remessa"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>

<?

// para salvar

//$arquivo->save('../tmp/remessa/remessa_'.$_1_u_remessa_idremessa.'.txt');
 
$texto=$arquivo->getText();
}//if($_1_u_remessa_idremessa){
?>
<div>
    <input value="<?=$texto?>" name="_1_<?=$_acao?>_remessa_texto" type="hidden">
</div>
<script>
	
<?
if(!empty($_1_u_remessa_idremessa)){
    $sqla="select * from carrimbo 
	    where status='PENDENTE' 
	    and idobjeto = ".$_1_u_remessa_idremessa." 
	    and tipoobjeto in ('remessa')
	    and idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
    $resa=d::b()->query($sqla) or die("Erro ao buscar se modulo esta assinado: Erro: ".mysqli_error(d::b())."\n".$sqla);
    $qtda= mysqli_num_rows($resa);
    if($qtda > 0){
	 $rowa=mysqli_fetch_assoc($resa);

?>    
	    botaoAssinar(<?=$rowa['idcarrimbo']?>);  
<?	    

    }// if($qtda>0){
}//if(!empty($_1_u_sgdoc_idsgdoc)){
?>
	
function botaoAssinar(inidcarrimbo){
    $bteditar = $("#btAssina");
    if($bteditar.length==0){
	CB.novoBotaoUsuario({
	    id:"btAssina"
	    ,rotulo:"Assinar"
	    ,class:"verde"
	    ,icone:"fa fa-pencil"
	    ,onclick:function(){
                CB.post({
		    objetos: "_x_u_carrimbo_idcarrimbo="+inidcarrimbo+"&_x_u_carrimbo_status=ATIVO"
		    ,parcial:true  
                    ,posPost: function(data, textStatus, jqXHR){
                            escondebotao();  
                    }
		});
	    }
            
	});
    }
}

function escondebotao(){
    $('#btAssina').hide();
   // document.location.reload(); 
}
    
function pesquisar(vthis){

    $(vthis).html('<span class="fa fa-spinner fa-pulse"></span>');
    var data1 = $("[name=data1]").val();
    var data2 = $("[name=data2]").val();
    var nnfe1 = $("[name=nnfe1]").val();
    var nnfe2 = $("[name=nnfe2]").val();
    var emissao1 = $("[name=emissao1]").val();
    var emissao2 = $("[name=emissao2]").val();
    var cliente = $("[name=cliente]").val();
    var formapagto = $("[name=formapagto]").val();
    var remessa = $("[name=remessa]").val();
    var idremessa =$('#idremessa').val();
    var idagencia =$('#idagencia').val();
    var str="idremessa="+idremessa+"&idagencia="+idagencia+"&emissao1="+emissao1+"&emissao2="+emissao2+"&data1="+data1+"&data2="+data2+"&nnfe1="+nnfe1+"&nnfe2="+nnfe2+"&cliente="+cliente+"&formapagto="+formapagto+"&remessa="+remessa;
  
        CB.go(str);
}

function limpar(){
    var idremessa =$('#idremessa').val();
    CB.go("idremessa="+idremessa);
}
 
 function altcheck(vthis,vop){ 
     
     
     if(vop=='D'){
         var str ="_x_d_remessaitem_idremessaitem="+$(vthis).attr('idremessaitem');
     }else{
     
        var str ="_x_i_remessaitem_idcontapagar="+$(vthis).attr('idcontapagar')+"&_x_i_remessaitem_idremessa="+$('#idremessa').val();
     }
     
    CB.post({
        objetos: str 
       
    }); 
}

function adicionar(vthis){
    //pega todos os inputs checkados 		
    var inputprenchido= $("#inftable").children().find("input:checkbox:checked");

    //pega todos os input e os inputs checkados e transforma em string para enviar para submit 		
    var vsubmit= $(inputprenchido).parent().parent().find("input:text, input:hidden").serialize();
   // alert(vsubmit);
 
    //insere no banco de dados via submitajax
    //CB.post(vsubmit);
   
    CB.post({
	objetos: vsubmit		
	,parcial:true
    })
    
}

if( $("[name=_1_u_remessa_idremessa]").val() ){
	$(".cbupload").dropzone({
		idObjeto: $("[name=_1_u_remessa_idremessa]").val()
		,tipoObjeto: 'remessa'
		,idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>'
	});
}
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
