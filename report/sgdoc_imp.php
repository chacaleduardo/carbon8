<?

require_once("../inc/php/validaacesso.php");

$pagvaltabela = "sgdoc";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idsgdoc" => "pk"
);
$pagsql = "select * from sgdoc where idsgdoc = '#pkid'";
require_once("../inc/php/controlevariaveisgetpost.php");

if($_1_u_sgdoc_idsgdoc){

    function listaPessoaEvento()
    {
        global $_1_u_sgdoc_idsgdoc,$_1_u_sgdoc_versao,$_1_u_sgdoc_idsgdoctipo,$disabledp;
        $s = "SELECT r.idfluxostatuspessoa, 
        IF(s.nomecurto is null, s.nome, s.nomecurto) AS nomecurto, 
        s.idpessoa,
        im.setor,
        r.idpessoa as vinculadopor,
        r.inseridomanualmente,
        r.editar,
        r.criadopor,
        r.criadoem,
        r.status,
        s.idtipopessoa,
        rg.idfluxostatuspessoa AS idfluxostatuspessoagrupo,
        r.assinar,
        rg.idobjeto,
        c.idcarrimbo,
        c.alteradoem,
        c.status as statuscar,
        c.versao as versao,
        e.versao as versaoDoc,
        case when r.idobjetoext != '' and r.idobjetoext is not null  then 1 
        else 2 end as ordemsetor,
        case when c.status ='ASSINADO' and c.versao=e.versao then 1 
        when c.status ='ASSINADO' and c.versao<e.versao then 2
        when c.status ='PENDENTE' and c.versao=e.versao then 3
        when c.status ='PENDENTE' and c.versao<e.versao then 4
        else 5 end as ordemass,
        rg.tipoobjeto as tipolocal,
        case when rg.tipoobjeto = 'sgarea' then sa.area
        when rg.tipoobjeto = 'sgdepartamento' then sd.departamento
        when rg.tipoobjeto = 'sgsetor' then ss.setor
        else '' end as local,
        case when rg.tipoobjeto = 'sgarea' then 1
        when rg.tipoobjeto = 'sgdepartamento' then 2
        when rg.tipoobjeto = 'sgsetor' then 3
        else 4 end as localord
    FROM fluxostatuspessoa r
    JOIN sgdoc e ON r.idmodulo = e.idsgdoc AND r.modulo = 'documento'
    JOIN pessoa s ON s.idpessoa = r.idobjeto  AND r.tipoobjeto ='pessoa'
    left JOIN carrimbo c on e.idsgdoc = c.idobjeto  and c.idpessoa = s.idpessoa and c.tipoobjeto = 'documento' and c.versao = e.versao
    left join pessoaobjeto gp on (s.idpessoa = gp.idpessoa and gp.tipoobjeto='sgsetor')
    left join sgsetor im on (im.idsgsetor = gp.idobjeto)
    LEFT JOIN sgsetor ss ON ss.idsgsetor = r.idobjetoext and r.tipoobjetoext='sgsetor'  AND ss.status = 'ATIVO' 
    LEFT JOIN sgdepartamento sd ON sd.idsgdepartamento = r.idobjetoext and r.tipoobjetoext='sgdepartamento'  AND sd.status = 'ATIVO' 
    LEFT JOIN sgarea sa ON sa.idsgarea = r.idobjetoext and r.tipoobjetoext='sgarea'  AND sa.status = 'ATIVO' 
    LEFT JOIN fluxostatuspessoa rg ON rg.idobjeto = r.idobjetoext AND rg.idmodulo = r.idmodulo AND rg.modulo = 'documento' and rg.tipoobjeto !='pessoa'
    WHERE r.idmodulo =  ".$_1_u_sgdoc_idsgdoc."
    GROUP BY s.nome
    ORDER BY  localord asc, local asc, ordemass asc,  nomecurto asc";
    
        $rts = d::b()->query($s) or die("[model-evento] - listaPessoaEvento: ". mysqli_error(d::b()));
    
        echo "<div id='listapessoaevento'>";
        //echo "<!--$s-->";
        $local = "";
    
        while ($r = mysqli_fetch_assoc($rts)) 
        {
            $cassinar='Y';
                //Retorna a Versão do Documento
                
            $versao=$_1_u_sgdoc_versao;
                
            //Retorna a Assinatura
            $sqlx = "SELECT c.idcarrimbo,
                            c.status,
                            c.versao as versao
                    FROM sgdoc s
                    JOIN carrimbo c on s.idsgdoc = c.idobjeto 
                    WHERE c.status      in ('PENDENTE','ASSINADO')
                    AND c.idpessoa    = ".$r['idpessoa']."
                    AND c.idobjeto    = ".$_1_u_sgdoc_idsgdoc."
                    order by c.versao desc                                  
                    LIMIT 1";
            $resx = d::b()->query($sqlx) or die("Erro versao assinada do documento para assinatura: ".mysqli_error(d::b()));
            $rowx=mysqli_fetch_assoc($resx);
            //var_dump($rowx);
            if($rowx['status']=='PENDENTE'){
                $clbt="primary";
                $cassinar='N';
                $title =  "title='Assinatura Pendente na versão ".$rowx['versao']."'";
            }elseif($rowx['status']=='ASSINADO' AND $rowx['versao'] == $versao){
                $clbt="success";
    
            }elseif($rowx['status']=='ASSINADO' AND $rowx['versao'] < $versao){
                $clbt="warning";
                $title =  "title='Solicitar Assinatura em nova versão do Documento'";
            }else {
                $clbt='warning';
                $title =  "title='Solicitar Assinatura'";
            }
    
            $sql1="select c.idcarrimbo,c.versao,c.status,c.alteradoem 
                from carrimbo c 
                where c.idpessoa=".$r['idpessoa']." and c.idobjeto=".$_1_u_sgdoc_idsgdoc." and c.tipoobjeto='documento'  order by versao desc limit 1";
            $rs1 = d::b()->query($sql1) or die("listaDocAssinatura: obsoleta". mysqli_error(d::b()));
            $n1 = mysqli_num_rows($rs1);
            $r1 = mysqli_fetch_assoc($rs1);
            
            if ($r['editar'] == 'Y') {
                $cheked = 'btn btn-xs btn-success';
            }else {
                $cheked = 'btn btn-xs btn-default';
            }
        
            
            if ($r1['versao']) {
                $versaoass="<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'> Versão:<b>".$rowx['versao']."</b></div>
                    <div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'>".dma($r['alteradoem'])."</div>";
            }else {
                $versaoass="<div class='col-md-2 hideprint'></div>";
            }
            if($r['idcarrimbo']){
                $idcarrimbo = $r['idcarrimbo'];
            } else {
                $idcarrimbo = 0;
            }		
    
            if ($clbt=='success' AND $r1['versao'] == $versao or !empty($disabledp)) {
                $disableass = 'disabled';
            }else {
                $disableass = '';
            }
            $inbtstatus="<button ".$disableass." onclick=\"criaassinatura(".$r['idpessoa'].",'documento',".$_1_u_sgdoc_idsgdoc.",".$versao.",'".$cassinar."',".$r['idfluxostatuspessoa'].",".$idcarrimbo.")\" type='button' class=' btn btn-xs hideprint btn-".$clbt." hovercinza pointer floatright ' ".$title." style='margin-right: 8px; border-radius: 4px;font-size:9px'><i class='fa fa-check'></i>&nbsp;Assinatura</button>";
        
            $pad = '';
            
            if ( $r['oculto'] == '1'){
                $op = 'opacity:0.5;';
            }else{
                $op ='';
            }
        
            if ($local != $r["local"]){
                if ($local != ''){
                    echo "</fieldset>";
                    echo '</div>';
                }
                $local = $r["local"];
                $bars = "<a align='RIGHT' class='fa fa-bars pointer hoverazul' onclick=\"janelamodal('?_modulo=".$r['tipolocal']."&_acao=u&id".$r['tipolocal']."=".$r["idobjeto"]."')\"></a>";
                $excluir="<i class=\"fa fa-trash hideprint fa-1x cinzaclaro hovercinza btn-lg pointer ui-droppable\" onClick=\"retiragrupo(".$r['idobjeto'].",".$_1_u_sgdoc_idsgdoc.",".$r['idfluxostatuspessoagrupo'].",'".$r['tipolocal']."')\" title='Excluir!'></i>";
                if (empty($local)) {
                    $excluir = '';
                    $bars = '';
                }
                if ($local != ''){
                    echo "<div class='filtrarAssinaturaPorNome' style='padding:2px 10px; margin-top: 30px; margin: 8px; background: #eee;'><legend class='scheduler-border hideprint' style='font-size:11px' ><b style='text-transform:uppercase;'>".($local)."</b> $excluir $bars</legend>";
                    echo "<fieldset style='margin-top: -20px;' class='scheduler-border'>";
                }
            }	
    
            if (!empty($r["local"])){
                $pad = '';
            }
    
            if($r['idtipopessoa']==1){
                $mod='funcionario';
            }else{
                $mod='pessoa';
            }
            unset($botao);
            if(empty($r['idobjeto']) ){
                if (!empty($disabledp) or $r1['versao']) {
                    $botao = "<i class='fa fa-ban fa-1x hideprint cinzaclaro ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' ></i>";
                }else {
                    $botao="<i class='fa fa-trash hideprint fa-1x cinzaclaro hoververmelho  pointer ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' onclick='retirapessoa(".$r["idfluxostatuspessoa"].")'></i>";
                }
            }else{
                $botao = "<i class='fa fa-ban fa-1x hideprint hideprint cinzaclaro ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' ></i>";
            }
            $title="Vinculado por: ".$r1["criadopor"]." - ".dmahms($r1["criadoem"],true);
            if ($r["setor"]){
                $cl = "&nbsp<span class='hideprint' style='display: inline; background: rgb(102, 102, 102);font-size: 10px;color: #fff;padding: 0px 6px;border-radius: 3px;'>".$r["setor"]."</span>";
            }else{
                $cl = '';
            }
    
            if ($r['editar'] == 'Y' and $r['idpessoa'] == $_SESSION['SESSAO']['IDPESSOA']) {
                $inputpermissao = '<input type="hidden" id="_permissaoeditardoctemp_" value="Y">';
            }
    
            if((empty($rowx['versao']) or $rowx['versao'] < $versao) and ($r['localord']=="4") ){
                continue;
            } else {
                if($r['localord']!="4"){
                    echo "<div id='".$r["idfluxostatuspessoa"]."' class='col-md-12 filtrarAssinaturaPorNome' style='".$pad."".$op."''>
                            <div class='col-md-6' style='line-height: 16px; padding: 8px; font-size: 10px;'>
                                ".$r["nomecurto"].$cl."
                            </div>
                            ".$versaoass."
                            <div align='right' class='col-md-2'>
                                <button ".$disabledp." style='margin-right: 8px; border-radius: 4px;font-size:9px' class='".$cheked." hideprint' value='".$r['editar']."' idfluxo=".$r['idfluxostatuspessoa']." type='button' class='compacto' title='Editar' onclick='editardoc(this)'>
                                    <i class='fa fa-pencil'></i>&nbsp;Editar
                                </button>
                            </div>
                            <div class='col-md-1'>".$inbtstatus."</div>
                            <div class='col-md-1'>".$botao."</div> 
                        </div>
                        <div id='collapse-".$r['idpessoa']."'>".$inputpermissao."</div>";
                }
            }
        }
        if ($local != ''){
            echo "</fieldset>";
            echo '</div>';
        }
    
        echo "</div>"; // Fecha div id=listaPessoaEvento
}
function listaPessoaEventoInseridosManualmente()
{
	global $_1_u_sgdoc_idsgdoc,$_1_u_sgdoc_versao,$_1_u_sgdoc_idsgdoctipo,$disabledp;
	$s = "SELECT r.idfluxostatuspessoa, 
	IF(s.nomecurto is null, s.nome, s.nomecurto) AS nomecurto, 
	s.idpessoa,
	im.setor,
	r.idpessoa as vinculadopor,
	r.inseridomanualmente,
	r.editar,
	r.criadopor,
	r.criadoem,
	r.status,
	s.idtipopessoa,
	rg.idfluxostatuspessoa AS idfluxostatuspessoagrupo,
	r.assinar,
	rg.idobjeto,
	c.idcarrimbo,
	c.alteradoem,
	c.status as statuscar,
	c.versao as versao,
	e.versao as versaoDoc,
    case when r.idobjetoext != '' and r.idobjetoext is not null  then 1 
    else 2 end as ordemsetor,
    case when c.status ='ASSINADO' and c.versao=e.versao then 1 
    when c.status ='ASSINADO' and c.versao<e.versao then 2
    when c.status ='PENDENTE' and c.versao=e.versao then 3
    when c.status ='PENDENTE' and c.versao<e.versao then 4
    else 5 end as ordemass,
	rg.tipoobjeto as tipolocal,
	case when rg.tipoobjeto = 'sgarea' then sa.area
	when rg.tipoobjeto = 'sgdepartamento' then sd.departamento
	when rg.tipoobjeto = 'sgsetor' then ss.setor
	else '' end as local,
	case when rg.tipoobjeto = 'sgarea' then 1
	when rg.tipoobjeto = 'sgdepartamento' then 2
	when rg.tipoobjeto = 'sgsetor' then 3
	else 4 end as localord
FROM fluxostatuspessoa r
JOIN sgdoc e ON r.idmodulo = e.idsgdoc AND r.modulo = 'documento'
JOIN pessoa s ON s.idpessoa = r.idobjeto  AND r.tipoobjeto ='pessoa'
left JOIN carrimbo c on e.idsgdoc = c.idobjeto  and c.idpessoa = s.idpessoa and c.tipoobjeto = 'documento' and c.versao = e.versao
left join pessoaobjeto gp on (s.idpessoa = gp.idpessoa and gp.tipoobjeto='sgsetor')
left join sgsetor im on (im.idsgsetor = gp.idobjeto)
LEFT JOIN sgsetor ss ON ss.idsgsetor = r.idobjetoext and r.tipoobjetoext='sgsetor'  AND ss.status = 'ATIVO' 
LEFT JOIN sgdepartamento sd ON sd.idsgdepartamento = r.idobjetoext and r.tipoobjetoext='sgdepartamento'  AND sd.status = 'ATIVO' 
LEFT JOIN sgarea sa ON sa.idsgarea = r.idobjetoext and r.tipoobjetoext='sgarea'  AND sa.status = 'ATIVO' 
LEFT JOIN fluxostatuspessoa rg ON rg.idobjeto = r.idobjetoext AND rg.idmodulo = r.idmodulo AND rg.modulo = 'documento' and rg.tipoobjeto !='pessoa'
WHERE r.idmodulo =  ".$_1_u_sgdoc_idsgdoc."
GROUP BY s.nome
ORDER BY  localord asc, local asc, ordemass asc,  nomecurto asc";

	$rts = d::b()->query($s) or die("[model-evento] - listaPessoaEventoInseridosManualmente: ". mysqli_error(d::b()));

	echo "<div id='listapessoaeventoinseridosmanualmente'>";
	echo "<div class='filtrarAssinaturaPorNome' style='padding:2px 10px; margin-top: 30px; margin: 8px; background: #eee;'>";

	echo "<legend class='scheduler-border hideprint' style='font-size:11px;padding: 8px 10px 8px 0px;' ><b style='text-transform:uppercase;'>Inseridos Manualmente</b></legend>";
	echo "<fieldset style='margin-top: -20px;' class='scheduler-border'>";
	//echo "<!--$s-->";
	$local = "";

	while ($r = mysqli_fetch_assoc($rts)) 
	{
		$cassinar='Y';
			//Retorna a Versão do Documento
			
		$versao=$_1_u_sgdoc_versao;
			
		//Retorna a Assinatura
		$sqlx = "SELECT c.idcarrimbo,
						c.status,
						c.versao as versao
				FROM sgdoc s
				JOIN carrimbo c on s.idsgdoc = c.idobjeto 
				WHERE c.status      in ('PENDENTE','ASSINADO')
				AND c.idpessoa    = ".$r['idpessoa']."
				AND c.idobjeto    = ".$_1_u_sgdoc_idsgdoc."
				order by c.versao desc                                  
				LIMIT 1";
		$resx = d::b()->query($sqlx) or die("Erro versao assinada do documento para assinatura: ".mysqli_error(d::b()));
		$rowx=mysqli_fetch_assoc($resx);
		//var_dump($rowx);
		if($rowx['status']=='PENDENTE'){
			$clbt="primary";
			$cassinar='N';
			$title =  "title='Assinatura Pendente na versão ".$rowx['versao']."'";
		}elseif($rowx['status']=='ASSINADO' AND $rowx['versao'] == $versao){
			$clbt="success";

		}elseif($rowx['status']=='ASSINADO' AND $rowx['versao'] < $versao){
			$clbt="warning";
			$title =  "title='Solicitar Assinatura em nova versão do Documento'";
		}else {
			$clbt='warning';
			$title =  "title='Solicitar Assinatura'";
		}

		$sql1="select c.idcarrimbo,c.versao,c.status,c.alteradoem 
			from carrimbo c 
			where c.idpessoa=".$r['idpessoa']." and c.idobjeto=".$_1_u_sgdoc_idsgdoc." and c.tipoobjeto='documento'  order by versao desc limit 1";
		$rs1 = d::b()->query($sql1) or die("listaDocAssinatura: obsoleta". mysqli_error(d::b()));
		$n1 = mysqli_num_rows($rs1);
		$r1 = mysqli_fetch_assoc($rs1);
		
		if ($r['editar'] == 'Y') {
			$cheked = 'btn btn-xs btn-success';
		}else {
			$cheked = 'btn btn-xs btn-default';
		}
	
		
		if ($r1['versao']) {
			$versaoass="<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'> Versão:<b>".$rowx['versao']."</b></div>
				<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'>".dma($r['alteradoem'])."</div>";
		}else {
			$versaoass="<div class='col-md-2 hideprint'></div>";
		}
		if($r['idcarrimbo']){
			$idcarrimbo = $r['idcarrimbo'];
		} else {
			$idcarrimbo = 0;
		}		

		if ($clbt=='success' AND $r1['versao'] == $versao or !empty($disabledp)) {
			$disableass = 'disabled';
		}else {
			$disableass = '';
		}
		$inbtstatus="<button ".$disableass." onclick=\"criaassinatura(".$r['idpessoa'].",'documento',".$_1_u_sgdoc_idsgdoc.",".$versao.",'".$cassinar."',".$r['idfluxostatuspessoa'].",".$idcarrimbo.")\" type='button' class=' btn btn-xs hideprint btn-".$clbt." hovercinza pointer floatright ' ".$title." style='margin-right: 8px; border-radius: 4px;font-size:9px'><i class='fa fa-check'></i>&nbsp;Assinatura</button>";
	
		$pad = '';
		
		if ( $r['oculto'] == '1'){
			$op = 'opacity:0.5;';
		}else{
			$op ='';
		}
	
	
		if (!empty($r["local"])){
			$pad = '';
		}

		if($r['idtipopessoa']==1){
			$mod='funcionario';
		}else{
			$mod='pessoa';
		}
		unset($botao);
		if(empty($r['idobjeto']) ){
			if (!empty($disabledp) or $r1['versao']) {
				$botao = "<i class='fa fa-ban fa-1x hideprint cinzaclaro ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' ></i>";
			}else {
				$botao="<i class='fa fa-trash hideprint fa-1x cinzaclaro hoververmelho  pointer ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' onclick='retirapessoa(".$r["idfluxostatuspessoa"].")'></i>";
			}
		}else{
			$botao = "<i class='fa fa-ban fa-1x hideprint cinzaclaro ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' ></i>";
		}
		$title="Vinculado por: ".$r1["criadopor"]." - ".dmahms($r1["criadoem"],true);
		if ($r["setor"]){
			$cl = "&nbsp<span class='hideprint' style='display: inline; background: rgb(102, 102, 102);font-size: 10px;color: #fff;padding: 0px 6px;border-radius: 3px;'>".$r["setor"]."</span>";
		}else{
			$cl = '';
		}

		if ($r['editar'] == 'Y' and $r['idpessoa'] == $_SESSION['SESSAO']['IDPESSOA']) {
			$inputpermissao = '<input type="hidden" id="_permissaoeditardoctemp_" value="Y">';
		}

		if(($r['localord']=="4") and ($rowx['versao'] == $versao) or ($r['localord']=="4" and empty($rowx['versao']))){

			echo "
				<div class='col-md-12'>
					<div class='col-md-6' style='line-height: 16px; padding: 8px; font-size: 10px;'>
						".$r["nomecurto"].$cl."
					</div>
					".$versaoass."
					<div  align='right' class='col-md-2'>
						<button ".$disabledp." style='margin-right: 8px; border-radius: 4px;font-size:9px' class='".$cheked." hideprint' value='".$r['editar']."' idfluxo=".$r['idfluxostatuspessoa']." type='button' class='compacto' title='Editar' onclick='editardoc(this)'>
							<i class='fa fa-pencil'></i>&nbsp;Editar
						</button>
					</div>
					<div class='col-md-1'>".$inbtstatus."</div>
					<div class='col-md-1'>".$botao."</div> 
				</div>
				<div id='collapse-".$r['idpessoa']."'>
					".$inputpermissao."
				</div>";	
		}
		
	}

	echo "</fieldset>";
	echo "</div>"; // Fecha div class="filtrarAssinaturaPorNome"
	echo "</div>"; // Fecha div id=listaPessoaEventoInseridosManualmente
}
function listaPessoaEventoSemSetorESemAssinatura()
{
	global $_1_u_sgdoc_idsgdoc,$_1_u_sgdoc_versao,$_1_u_sgdoc_idsgdoctipo,$disabledp;
	$s = "SELECT r.idfluxostatuspessoa, 
	IF(s.nomecurto is null, s.nome, s.nomecurto) AS nomecurto, 
	s.idpessoa,
	im.setor,
	r.idpessoa as vinculadopor,
	r.inseridomanualmente,
	r.editar,
	r.criadopor,
	r.criadoem,
	r.status,
	s.idtipopessoa,
	rg.idfluxostatuspessoa AS idfluxostatuspessoagrupo,
	r.assinar,
	rg.idobjeto,
	c.idcarrimbo,
	c.alteradoem,
	c.status as statuscar,
	c.versao as versao,
    case when r.idobjetoext != '' and r.idobjetoext is not null  then 1 
    else 2 end as ordemsetor,
    case when c.status ='ASSINADO' and c.versao=e.versao then 1 
    when c.status ='ASSINADO' and c.versao<e.versao then 2
    when c.status ='PENDENTE' and c.versao=e.versao then 3
    when c.status ='PENDENTE' and c.versao<e.versao then 4
    else 5 end as ordemass,
	rg.tipoobjeto as tipolocal,
	case when rg.tipoobjeto = 'sgarea' then sa.area
	when rg.tipoobjeto = 'sgdepartamento' then sd.departamento
	when rg.tipoobjeto = 'sgsetor' then ss.setor
	else '' end as local,
	case when rg.tipoobjeto = 'sgarea' then 1
	when rg.tipoobjeto = 'sgdepartamento' then 2
	when rg.tipoobjeto = 'sgsetor' then 3
	else 4 end as localord
    

FROM fluxostatuspessoa r
JOIN sgdoc e ON r.idmodulo = e.idsgdoc AND r.modulo = 'documento'
JOIN pessoa s ON s.idpessoa = r.idobjeto  AND r.tipoobjeto ='pessoa'
left JOIN carrimbo c on e.idsgdoc = c.idobjeto  and c.idpessoa = s.idpessoa and c.tipoobjeto = 'documento'
left join pessoaobjeto gp on (s.idpessoa = gp.idpessoa and gp.tipoobjeto='sgsetor')
left join sgsetor im on (im.idsgsetor = gp.idobjeto)
LEFT JOIN sgsetor ss ON ss.idsgsetor = r.idobjetoext and r.tipoobjetoext='sgsetor' -- AND ss.status = 'ATIVO' 
LEFT JOIN sgdepartamento sd ON sd.idsgdepartamento = r.idobjetoext and r.tipoobjetoext='sgdepartamento' -- AND sd.status = 'ATIVO' 
LEFT JOIN sgarea sa ON sa.idsgarea = r.idobjetoext and r.tipoobjetoext='sgarea' -- AND sa.status = 'ATIVO' 
LEFT JOIN fluxostatuspessoa rg ON rg.idobjeto = r.idobjetoext AND rg.idmodulo = r.idmodulo AND rg.modulo = 'documento' and rg.tipoobjeto !='pessoa'
WHERE r.idmodulo =  ".$_1_u_sgdoc_idsgdoc."
GROUP BY s.nome
ORDER BY  localord asc, local asc, im.setor is null, ordemass asc,  nomecurto asc";

	$rts = d::b()->query($s) or die("[model-evento] - listaPessoaEventoSemSetorESemAssinatura: ". mysqli_error(d::b()));

	//echo "<!--$s-->";
	$local = "";
	$controleCabecalho = true;
	while ($r = mysqli_fetch_assoc($rts)) 
	{
		$cassinar='Y';
			//Retorna a Versão do Documento
			
		$versao=$_1_u_sgdoc_versao;
			
		//Retorna a Assinatura
		$sqlx = "SELECT c.idcarrimbo,
						c.status,
						c.versao as versao
				FROM sgdoc s
				JOIN carrimbo c on s.idsgdoc = c.idobjeto 
				WHERE c.status      in ('PENDENTE','ASSINADO')
				AND c.idpessoa    = ".$r['idpessoa']."
				AND c.idobjeto    = ".$_1_u_sgdoc_idsgdoc."
				order by c.versao desc                                  
				LIMIT 1";
		$resx = d::b()->query($sqlx) or die("Erro versao assinada do documento para assinatura: ".mysqli_error(d::b()));
		$rowx=mysqli_fetch_assoc($resx);
		//var_dump($rowx);
		if($rowx['status']=='ASSINADO' AND $rowx['versao'] < $versao){
			$clbt="light";
			$title =  "title='Solicitar Assinatura em nova versão do Documento'";
		}else {
			$clbt='light';
			$title =  "title='Solicitar Assinatura'";
		}

		$sql1="select c.idcarrimbo,c.versao,c.status,c.alteradoem 
			from carrimbo c 
			where c.idpessoa=".$r['idpessoa']." and c.idobjeto=".$_1_u_sgdoc_idsgdoc." and c.tipoobjeto='documento'  order by versao desc limit 1";
		$rs1 = d::b()->query($sql1) or die("listaDocAssinatura: obsoleta". mysqli_error(d::b()));
		$n1 = mysqli_num_rows($rs1);
		$r1 = mysqli_fetch_assoc($rs1);
		
		if ($r['editar'] == 'Y') {
			$cheked = 'btn btn-xs btn-success';
		}else {
			$cheked = 'btn btn-xs btn-default';
		}
	
		
		if ($r1['versao']) {
			$versaoass="<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'> Versão:<b>".$rowx['versao']."</b></div>
				<div class='col-md-1 hideprint' style='line-height: 16px; padding: 8px; font-size: 10px;'>".dma($r['alteradoem'])."</div>";
		}else {
			$versaoass="<div class='col-md-2 hideprint'></div>";
		}
		if($r['idcarrimbo']){
			$idcarrimbo = $r['idcarrimbo'];
		} else {
			$idcarrimbo = 0;
		}		

		if ($clbt=='success' AND $r1['versao'] == $versao or !empty($disabledp)) {
			$disableass = 'disabled';
		}else {
			$disableass = '';
		}
		$inbtstatus="<button ".$disableass." onclick=\"criaassinatura(".$r['idpessoa'].",'documento',".$_1_u_sgdoc_idsgdoc.",".$versao.",'".$cassinar."',".$r['idfluxostatuspessoa'].",".$idcarrimbo.")\" type='button' class=' btn btn-xs hideprint hovercinza pointer floatright ' ".$title." style='margin-right: 8px;background: silver; border-radius: 4px;font-size:9px'><i class='fa fa-check'></i>&nbsp;Assinatura</button>";
	
		$pad = '';
		
		if ( $r['oculto'] == '1'){
			$op = 'opacity:0.5;';
		}else{
			$op ='';
		}
	


		if (!empty($r["local"])){
			$pad = '';
		}

		if($r['idtipopessoa']==1){
			$mod='funcionario';
		}else{
			$mod='pessoa';
		}
		unset($botao);
		if(empty($r['idobjeto']) ){
			if (!empty($disabledp) or $r1['versao']) {
				$botao = "<i class='fa fa-ban fa-1x hideprint cinzaclaro ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' ></i>";
			}else {
				$botao="<i class='fa fa-trash hideprint fa-1x cinzaclaro hoververmelho  pointer ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' onclick='retirapessoa(".$r["idfluxostatuspessoa"].")'></i>";
			}
		}else{
			$botao = "<i class='fa fa-ban fa-1x hideprint cinzaclaro ui-droppable' status='".$r["status"]."' idfluxostatuspessoa='".$r["idfluxostatuspessoa"]."' ></i>";
		}
		$title="Vinculado por: ".$r1["criadopor"]." - ".dmahms($r1["criadoem"],true);
		if ($r["setor"]){
			$cl = "&nbsp<span class='hideprint' style='display: inline; background: rgb(102, 102, 102);font-size: 10px;color: #fff;padding: 0px 6px;border-radius: 3px;'>".$r["setor"]."</span>";
		}else{
			$cl = '';
		}

		if ($r['editar'] == 'Y' and $r['idpessoa'] == $_SESSION['SESSAO']['IDPESSOA']) {
			$inputpermissao = '<input type="hidden" id="_permissaoeditardoctemp_" value="Y">';
		}

		if(((!empty($rowx['versao'])) && ($rowx['versao'] < $versao) && $r['localord']=="4")){

			if($controleCabecalho){
				$controleCabecalho = false;
				echo "<div id='listapessoaeventosemsetoresemassinatura'>";
				echo "<div class='filtrarAssinaturaPorNome' style='padding:2px 10px; margin-top: 30px; margin: 8px; background: #eee;'>";

				echo "<legend class='scheduler-border hideprint' style='font-size:11px;padding: 8px 10px 8px 0px;' ><b style='text-transform:uppercase;'>
						Inseridos Manualmente - Obsoletos</b>
						<i class='fa fa-arrows-v fa-2x cinzaclaro pointer' title='Detalhar' data-toggle='collapse' href='#semsetoresemassinatura' aria-expanded='true' style='float: right;'></i>
					</legend>";

				echo "<fieldset style='margin-top: -20px;' class='scheduler-border'>";
					
				echo '<div id="semsetoresemassinatura">';
			}

			echo "<div id='".$r["idfluxostatuspessoa"]."' class='col-md-12 filtrarAssinaturaPorNome' style='".$pad."".$op."'>
					<div class='col-md-6' style='line-height: 16px; padding: 8px; font-size: 10px;'>
						".$r["nomecurto"].$cl."
					</div>
					".$versaoass."
					<div  align='right' class='col-md-2'>
						<button ".$disabledp." style='margin-right: 8px; border-radius: 4px;font-size:9px' class='".$cheked." hideprint' value='".$r['editar']."' idfluxo=".$r['idfluxostatuspessoa']." type='button' class='compacto' title='Editar' onclick='editardoc(this)'>
							<i class='fa fa-pencil'></i>&nbsp;Editar
						</button>
					</div>
					<div class='col-md-1'>".$inbtstatus."</div>
					<div class='col-md-1'>".$botao."</div> 
				</div>
				<div id='collapse-".$r['idpessoa']."'>".$inputpermissao."</div>";

		} 
	}

	if(!$controleCabecalho){
		echo "</div>"; // Fecha div id=semsetoresemassinatura
		echo "</div>"; // Fecha div class="collapse"
		echo "</fieldset>";
		echo "</div>"; // Fecha div class=filtrarAssinaturaPorNome
		echo "</div>"; // Fecha div id=listapessoaeventosemsetoresemassinatura
	}
}

$tdoc=getObjeto("sgdoctipo",$_1_u_sgdoc_idsgdoctipo)["rotulo"];
$stdoc=getObjeto("sgdoctipodocumento",$_1_u_sgdoc_idsgdoctipodocumento)["tipodocumento"];
$dts=empty($stdoc)? $tdoc : $tdoc . " / " .$stdoc;
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="..\inc\css\carbon.css" rel="stylesheet">
<link href="..\inc\css\bootstrap\css\bootstrap.css" rel="stylesheet">
<link href="..\inc\css\fontawesome\font-awesome.min.css" rel="stylesheet">
<title><?=$_1_u_sgdoc_titulo?></title>
<style>
    /*Copiado de mtorep.css*/
    @media print { 
  * {
    -webkit-transition: none !important;
    transition: none !important;
  }
}
* {
	text-shadow: none !important;
	filter:none !important;
	-ms-filter:none !important;
	font-family: Helvetica, Arial;
	font-size: 10px;
	-webkit-box-sizing: border-box; 
	-moz-box-sizing: border-box;    
	box-sizing: border-box; 
}
html{
	background-color: silver;
}
body {
	line-height: 1.4em;
	background-color: white;
}
.right{
	text-align:right;
}
legend{
	text-transform:uppercase;
	font-size:12px;
	font-weight:bold;
}
@media screen{
	body {
		margin: auto;
		margin-top: 0.2cm;
		margin-bottom: 1cm;
		padding: 3mm 10mm;
		width: 21cm;
	}
	.quebrapagina{
		page-break-before:always;
		border: 2px solid #c0c0c0;
		width: 120%;
		margin: 1.5cm -1.5cm;
	}
	.rot{
		color: gray;
	}
}

@media print{
	html{
		background-color: transparent;
	}
	body {
		margin: 0cm;
	}
	.quebrapagina{
		page-break-before:always;
	}
	.rot{
		color: #777777;
	}
}

.ordContainer{
	display: flex;
	flex-direction: column;
}
.ord1{order: 1;}
.ord2{order: 2;}
.ord3{order: 3;}
.ord4{order: 4;}
.ord5{order: 5;}
.ord6{order: 6;}
.ord7{order: 7;}
.ord8{order: 8;}
.ord9{order: 9;}
.ord10{order: 10;}
.ord11{order: 11;}
.ord12{order: 12;}
.ord13{order: 13;}
.ord14{order: 14;}
.ord15{order: 15;}
.ord16{order: 16;}
.ord17{order: 17;}
.ord18{order: 18;}
.ord19{order: 19;}
.ord20{order: 20;}
.ord21{order: 21;}
.ord22{order: 22;}
.ord23{order: 23;}
.ord24{order: 24;}
.ord25{order: 25;}
.ord26{order: 26;}
.ord27{order: 27;}
.ord28{order: 28;}
.ord29{order: 29;}
.ord30{order: 30;}
.ord31{order: 31;}
.ord32{order: 32;}
.ord33{order: 33;}
.ord34{order: 34;}
.ord35{order: 35;}
.ord36{order: 36;}
.ord37{order: 37;}
.ord38{order: 38;}
.ord39{order: 39;}
.ord40{order: 40;}
.ord41{order: 41;}
.ord42{order: 42;}
.ord43{order: 43;}
.ord44{order: 44;}
.ord45{order: 45;}
.ord46{order: 46;}
.ord47{order: 47;}
.ord48{order: 48;}
.ord49{order: 49;}
.ord50{order: 50;}
.ord51{order: 51;}
.ord52{order: 52;}
.ord53{order: 53;}
.ord54{order: 54;}
.ord55{order: 55;}
.ord56{order: 56;}
.ord57{order: 57;}
.ord58{order: 58;}
.ord59{order: 59;}
.ord60{order: 60;}
.ord61{order: 61;}
.ord62{order: 62;}
.ord63{order: 63;}
.ord64{order: 64;}
.ord65{order: 65;}
.ord66{order: 66;}
.ord67{order: 67;}
.ord68{order: 68;}
.ord69{order: 69;}
.ord70{order: 70;}
.ord71{order: 71;}
.ord72{order: 72;}
.ord73{order: 73;}
.ord74{order: 74;}
.ord75{order: 75;}
.ord76{order: 76;}
.ord77{order: 77;}
.ord78{order: 78;}
.ord79{order: 79;}
.ord80{order: 80;}
.ord81{order: 81;}
.ord82{order: 82;}
.ord83{order: 83;}
.ord84{order: 84;}
.ord85{order: 85;}
.ord86{order: 86;}
.ord87{order: 87;}
.ord88{order: 88;}
.ord89{order: 89;}
.ord90{order: 90;}
.ord91{order: 91;}
.ord92{order: 92;}
.ord93{order: 93;}
.ord94{order: 94;}
.ord95{order: 95;}
.ord96{order: 96;}
.ord97{order: 97;}
.ord98{order: 98;}
.ord99{order: 99;}
.ord100{order: 100;}


[class*='5']{width: 5%;}
[class*='10']{width: 9%;}
[class*='15']{width: 15%;}
[class*='20']{width: 20%;}
[class*='25']{width: 25%;}
[class*='30']{width: 30%;}
[class*='35']{width: 35%;}
[class*='40']{width: 39.99%;}
[class*='45']{width: 45%;}
[class*='50']{width: 50%;}
[class*='55']{width: 55%;}
[class*='60']{width: 60%;}
[class*='65']{width: 65%;}
[class*='70']{width: 70%;}
[class*='75']{width: 75%;}
[class*='80']{width: 80%;}
[class*='85']{width: 85%;}
[class*='90']{width: 90%;}
[class*='95']{width: 95%;}
[class*='100']{width: 100%;}
header{
	 background-color: white;
	 top: 0;
	 height: 1cm;
	 line-height: 1cm;
	 display: table;

}
.header{
	 text-transform: uppercase;
	 font-weight: bold;
}
hr{
	margin: 0;
}
.logosup{
	height: inherit;
	line-height: inherit;
	display: table-cell;
}
.logosup img{
	height: 0.5cm;
	vertical-align: middle;
}
.titulodoc{
	height: inherit;
	line-height: inherit;
	display: table-cell;
	text-align: center;
	font-size: 0.5cm;
	font-weight: bold;
}
.row{
	display: table;
	table-layout: fixed;
	width: 99%;
	margin: 0mm 0mm;
}
.linhainferior{
	border-bottom: 1px dashed gray;
}
.col{
	display: table-cell;
	white-space: nowrap;
	padding: 1.5mm 1mm;
}
.row.grid .col{
	border: 1px solid silver;
	
}
.row.grid .col:first-child{
	border-top: 1px solid silver;
}
.col.grupo {}
.col.grupo .titulogrupo{
	margin: 0px;
	border-bottom: 1px solid silver;
	color: #777777;
	font-weight: bold;
	Xmargin-bottom: 2mm;
}
.rot{
	overflow: hidden;
	font-size: 10px;
}
.quebralinha{
	white-space: normal;
}
[class*='margem0.0']{
	margin: 0 0;
}
.hidden{
	display: none;
}
.sublinhado{
	border-bottom: 1px dashed gray;
}
.fonte8{
	font-size: 8px;
}
.resultadodescritivo{
	margin: 0 0;
}
.resultadodescritivo p{
	margin: 0 0;	
}

</style>
<style>
.rotulo{
font-weight: bold;
font-size: 9px;
}
.texto{
font-size: 9px;
}
.textoitem{
font-size: 9px;
}
.textoitem8{
font-size: 8px;
}

.box {
    display: table-cell;
    text-align: center;
    vertical-align: middle;
    width: 550px;
}
.box * {
    vertical-align: middle;
}
.breakw {
	word-break: break-word;
}

@media print{
	#rodapengeraarquivo{
		position: fixed;
		bottom: 0;
	}
	.breakw {
	word-break: break-word;
	position:absolute; 
	top: 3cm;
    }
    table { page-break-inside:avoid }
    tr    { page-break-inside:avoid; page-break-after:auto }
    td    { page-break-inside:avoid; }
    thead { display:table-header-group }
    tfoot { display:table-footer-group }
}
@media screen {
    .screen{
        display: none;
    }
}
</style>
<style>
@media print {
	#cbSteps{
		display: none;
	}

	.hideprint{
		display: none !important;
	}

	.pagebreakprint{
		page-break-after: always;
	}

	}

body.somenteleitura #editor1 {
    display: none;
}
body.somenteleitura  #cabHistdoc {
    text-align:center;
    color: black;
    border: 1px solid silver;
    position:fixed;
    /**adjust location**/
    right: 0px;
    bottom: 0px;
    padding: 0 10px 0 10px;
    width: 100%;
    /* Netscape 4, IE 4.x-5.0/Win and other lesser browsers will use this */
    _position: absolute;
}

/* GVT - 04/02/2020 - removido essa propriedade, pois fazia com que o texto quebrasse a div no modo somente leitura */

	#editor1Container{
		min-height: 90vh;
	}
	#editor1{
		height: 90vh;
		width: 100%;
		overflow-y: scroll;
		background-color: white;
	}
	
	.transparente{
		opacity: 0;
		transition: opacity .25s ease-in-out;
		-moz-transition: opacity .25s ease-in-out;
		-webkit-transition: opacity .25s ease-in-out;
	}
	.opaco{
		opacity: 1;
		transition: opacity .25s ease-in-out;
		-moz-transition: opacity .25s ease-in-out;
		-webkit-transition: opacity .25s ease-in-out;
	}
.copiancontrolada{
	border: none;
	position:fixed;
	top: 35%;
	left:35%;
	z-index:-100;
}

div.dz-filename > span{
	word-break: break-word;
}

div.dz-preview > i.fa{
	margin: 10px;
}
</style>
<body style="margin: auto;width: min-content;">
<div >
<div class="print">
    <?if($_1_u_sgdoc_cpctr == 'N'){?>
        <img class="copiancontrolada screen" id="imagencopia" border="0" src="../inc/img/copiancontrolada.gif"/>  
    <?}else{?>
        <img class="copiacontrolada screen" id="imagencopia" border="0" src="../inc/img/copiacontrolada.gif"/>  
    <?}?>
</div>
<table class="tbImpressao print"  >
    <thead >
        <tr>
            <td colspan="999">
                <table style="width: 100%;" class="titulo">
                    <tr style="border-bottom: 1px black solid;">
                        <th style="width: 3%;">
                            <?
                            $sqlfig="select logosis from empresa where idempresa =".cb::idempresa();
                            $resfig = mysql_query($sqlfig) or die("Erro ao retornar figura para cabeçalho do relatà³rio: ".mysql_error());
                            $figrel=mysql_fetch_assoc($resfig);
                            $figrel["logosis"] = str_replace("../", "", $figrel["logosis"]);
                            ?>
                            <img class="logoesquerda" src="/<?=$figrel["logosis"]?>">
                        </th>

                        <th style="text-align: center;white-space: inherit;width: 97%;">
                            <div><?=$dts?></div>
                            <div><?=$_1_u_sgdoc_titulo?></div>
                        </th>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th colspan="999" style="border-bottom: 1px black solid;">
                <table>
                    <tr><td>Cód.:</td><td style="width: 100%"><?=$_1_u_sgdoc_idregistro?></td></tr>
                    <tr><td>Rev.:</td><td><?=$_1_u_sgdoc_versao.".".$_1_u_sgdoc_revisao?></td></tr>
                    <tr>
                        <td>Status:</td>
                        <td><?= getObjeto("sgdocstatus", $_1_u_sgdoc_status)["des"]?></td>
                    </tr>
                    <?if(!empty($_1_u_sgdoc_responsavel)){
                        if($_1_u_sgdoc_idsgdoctipo=='auditoria'){
                            $strexe="Auditor Lider";
                        }else{
                            $strexe="Executor";
                        }?>
                        <tr>
                            <td class="nowrap"><?=$strexe?>:</td>
                            <td><?=$_1_u_sgdoc_responsavel?></td>
                        </tr>
                    <?}

                    if(!empty($_1_u_sgdoc_responsavelsec)){?>
                        <tr>
                            <td class="nowrap">Auditor Participante:</td>
                            <td><?=$_1_u_sgdoc_responsavelsec?></td>
                        </tr>			
                    <?}

                    if(!empty($_1_u_sgdoc_grau)){?>
                        <tr>
                            <td class="nowrap">Grau:</td>
                            <td><?=$_1_u_sgdoc_grau?></td>
                        </tr>			
                    <?}

                    if(!empty($_1_u_sgdoc_impacto)){?>
                        <tr>
                            <td class="nowrap">Impacto:</td>
                            <td><?=$_1_u_sgdoc_impacto?></td>
                        </tr>	
                    <?}

                    if(!empty($_1_u_sgdoc_nota)){?>
                        <tr>
                            <td class="nowrap">Nota:</td>
                            <td><?=$_1_u_sgdoc_nota?></td>
                        </tr>			
                    <?}

                    if(!empty($_1_u_sgdoc_resultado)){?>
                        <tr>
                            <td class="nowrap">Resultado:</td>
                            <td>				
                                <?=$_1_u_sgdoc_resultado?>	
                            </td>
                        </tr>			
                    <?}?>
                </table>
            </th>
        </tr>
        <tr><td colspan="999"></td></tr>
        <tr><td colspan="999"></td></tr>
    </thead>
    <tbody>
        <?
        if( $arrtipodoc["flquestionario"]=="Y"){?>
            <tr>
                <th>Qst.</th>
                <?
                $sqlp="SELECT c.col, tc.rotcurto,tc.dropsql as code,tc.datatype
                                        from sgdoctipodocumentocampos c 
                                                join carbonnovo._mtotabcol tc on (tc.tab = c.tabela and tc.col=c.col) 
                                        where c.idsgdoctipodocumento=".$_1_u_sgdoc_idsgdoctipodocumento." and c.visivel = 'Y' order by c.ord";
                $resp=d::b()->query($sqlp) or die("Erro ao buscar questões sql".$sqlp);
                $qtd=mysqli_num_rows($resp);
                $col = array();
                $rotcurto = array();
                $code = array();
                $datatype = array();
                while ($rowp =mysql_fetch_assoc($resp)){
                    array_push($col, $rowp["col"]);
                    array_push($rotcurto, $rowp["rotcurto"]);
                    array_push($code, $rowp["code"]);
                    array_push($datatype, $rowp["datatype"]);
                    ?>
                    <th><?=$rowp["rotcurto"]?></th>
                <?}?>
            </tr>  
            <?
            $sqlp="select * from sgdocpag where idsgdoc=".$_1_u_sgdoc_idsgdoc." order by pagina asc";
            $rest=d::b()->query($sqlp) or die("Erro ao buscar questões sql".$sqlp);
            $qtdpag=mysqli_num_rows($rest);
            $vqtdpag=$qtdpag+1;
            $li=99;
            if($qtdpag > 0){
                while($rowp =mysql_fetch_assoc($rest)){
                    $li++;
                    $i = 0;
                    ?>
                    <tr>
                        <td>
                            <?=$rowp["pagina"]?>
                        </td>
                        <?
                        while($i < $qtd){?>
                            <td>
                                <?=$rowp[$col[$i]]?>                                                            
                            </td>
                            <?
                            $i++;
                        }
                        ?>
                        <?if(!($_1_u_sgdoc_status == 'APROVADO')){?>
                            <td><a class="fa fa-trash hideprint vermelho fade hoververmelho" title="Excluir" idunidadeobjeto="" onclick="excluirpagina(<?=$rowp["idsgdocpag"]?>)"></a></td>
                        <?}?>
                    </tr>
                <?}
            } 
        
        }else{?>
            <tr style="page-break-after: always">
                <td ><?=$_1_u_sgdoc_conteudo?></td>
            </tr>
        <?}?>        
    </tbody>
</table>

<? if($_acao == "u"){	
    if($_pkid != null or $_pkid == ""){
        
        ?>
        <div class="row screen">

        <?
        if($_GET['_modulo'] == "evento"){
            $and = "and idevento != '".$_pkid."'";
        }

            $sqle="select * From (SELECT e.idevento, e.evento, e.criadopor, e.prazo, s.rotulo AS status, eventotipo
                        FROM evento e join eventotipo t on t.ideventotipo = e.ideventotipo 
                        JOIN fluxostatus f ON f.idfluxostatus = e.idfluxostatus
                        JOIN "._DBCARBON."._status s ON s.idstatus = f.idstatus
                    WHERE e.modulo = '".$_GET['_modulo']."' and e.idmodulo ='".$_pkid."'".$and." 
                    union all
                    select e.idevento, e.evento, e.criadopor, e.prazo, s.rotulo AS status, eventotipo
                        from eventoobj o join evento e on (e.idevento = o.idevento)
                        join eventotipo t on t.ideventotipo = e.ideventotipo 
                        JOIN fluxostatus f ON (f.idfluxostatus = e.idfluxostatus)
                        JOIN "._DBCARBON."._status s ON s.idstatus = f.idstatus
                        where o.objeto in ('".$_GET['_modulo']."')
                        and o.idobjeto= '".$_pkid."'
                    union all
                    SELECT e.idevento, e.evento, e.criadopor, e.prazo, s.rotulo AS status, eventotipo  
                        FROM evento e join eventotipo t on t.ideventotipo = e.ideventotipo 
                        JOIN fluxostatus f ON f.idfluxostatus = e.idfluxostatus
                        JOIN carbonnovo._status s ON s.idstatus = f.idstatus
                    WHERE e.idequipamento = '".$_pkid."') e order by prazo desc";

    /*	
            $sqle="SELECT e.idevento, e.evento, e.criadopor, e.prazo, s.rotulo AS status 
                FROM evento e JOIN fluxostatus f ON f.idfluxostatus = e.idfluxostatus
                JOIN "._DBCARBON."._status s ON s.idstatus = f.idstatus
            WHERE e.modulo = '".$_GET['_modulo']."' and e.idmodulo ='".$_pkid."' $and";
        */

        $rese = d::b()->query($sqle) or die("A Consulta dos eventos falhou :".mysql_error()."<br>Sql:".$sqle);
    
    
        if($qtde=mysqli_num_rows($rese) > 0){
            $colmd = 6;
            ?>
        
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading"  data-toggle="collapse" href="#gpEventos">Evento(s)</div>
                    <div class="panel-body collapse" id="gpEventos" style="padding-top: 8px !important;">
                        <table  class="table table-striped planilha"> 
                            <tr>
                                <td>ID</td>
                                <td>Evento</td>
                                <td>Tipo</td>
                                <td>Prazo</td>
                                <td>Status</td>
                            </tr>
                            <?while($rowe=mysqli_fetch_assoc($rese)){?>
                                <tr>
                                    <td>
                                        <a class="background-color: #FFEFD1; pointer hoverazul" title="Evento" onclick="janelamodal('?_modulo=evento&_acao=u&idevento=<?=$rowe["idevento"]?>')"><?=$rowe["idevento"]?></a>
                                    </td>
                                    <td><?=$rowe["evento"]?></td>
                                    <td><?=$rowe["eventotipo"]?></td>
                                    <td><?=dma($rowe["prazo"])?></td>
                                    <td><?=$rowe["status"]?></td>
                                </tr>
                            <?}?>
                        </table>
                    </div>            
                </div>   
            </div>

        <?}
    }
}?>


</div>

<?

    $sql = 'select p.nome, DATE_FORMAT(sg.alteradoem,"%d/%m/%Y %T") as alteradoem from sgdocupd sg join pessoa p on(p.usuario = sg.alteradopor) where sg.idsgdoc ='.$_1_u_sgdoc_idsgdoc.' and sg.versao='.$_1_u_sgdoc_versao.' and sg.status = "APROVADO" LIMIT 1';
    $rest=d::b()->query($sql) or die("Erro ao buscar aprovador: sql".$sql);
    if($nrest = mysqli_num_rows($rest) > 0){
        $row = mysqli_fetch_assoc($rest);
        $aprovador = $row['nome'];
        $alteradoem = $row['alteradoem'];
    }

    $elaborador=traduzid("pessoa","usuario","nome",$_1_u_sgdoc_criadopor,false);
    ?>

    <TABLE class="tbImpressao pagebreakprint">
        <thead>
        <tr>		
            <TD nowrap>Elaborador:<br><?=$elaborador?><br><?=$_1_u_sgdoc_criadoem?></TD>
            <TD style="width:100%;"></TD>
            <TD nowrap>Aprovador:<br><?=$aprovador?><br><?=$alteradoem?></TD>			
        </tr>
        </thead>
    </TABLE>

    <style>
    .listaitens{
        border: none;
        margin: 5px;
        padding: 0px;
    }
    .listaitens{
        font-size: 11px;
        list-style: none outside none;
    }
    .listaitens .cab{/* cabecalho para liste de itens*/
        color: gray;
        font-size:9px;
        list-style: none outside none;
    }

    </style>

    <table class="print">
        <tr>
            <?
            $sqlv="SELECT s.titulo,s.idsgdoc,v.idsgdocvinc
                FROM `sgdocvinc` v,sgdoc s  
                where s.idsgdoc=v.iddocvinc 
                    and v.idsgdoc = ".$_1_u_sgdoc_idsgdoc." order by titulo";
            $resv = d::b()->query($sqlv) or die("A Consulta dos documentos vinculados falhou :".mysqli_error(d::b())."<br>Sql:".$sqlv);
            $qtdrows1= mysqli_num_rows($resv);

            if($qtdrows1>0 ){?>
                <td colspan="4">
                    <ul class="listaitens">
                        <li class="cab">Documentos vinculados:</li>
                        <?while($rdvinc = mysqli_fetch_array($resv)){?>
                            <li><a target="_blank" href="sgdocprint.php?acao=u&idsgdoc=<?=$rdvinc["idsgdoc"]?>"><?=$rdvinc["idsgdoc"]?> - <?=$rdvinc["titulo"]?></a></li>

                        <?}?>
                    </ul>
                </td>
            <?}?>
        </tr>
        <tr>
            <?
            $sqlarq = "select a.*, dmahms(criadoem) as datacriacao 
                        from arquivo a 
                        where 
                            a.tipoobjeto = 'sgdoc' 
                            and a.idobjeto = ".$_1_u_sgdoc_idsgdoc." 
                            and tipoarquivo = 'ANEXO' 
                        order by idarquivo asc";
            
            $res = d::b()->query($sqlarq) or die("Erro ao pesquisar arquivos:".mysqli_error(d::b()));
            $numarq= mysqli_num_rows($res);

            if($numarq>0  ){?>

                <td colspan="4">
                    <ul class="listaitens">
                        <li class="cab">Arquivos Anexos (<?=$numarq?>)</li>
                        <?while ($row = mysqli_fetch_array($res)) {?>
                            <li><a title="Abrir arquivo" target="_blank"  href="../upload/<?=$row["nome"]?>"><?=$row["nome"]?></a></li>
                        <?}?>
                    </ul>
                </td>
            <?}?>	
        </tr>	
    </TABLE>

    <?
    $sqlalt="select dmahms(a.alteradoem) as dmadata,a.* from sgdocupd a
        where a.idsgdoc = ".$_1_u_sgdoc_idsgdoc." order by a.idsgdocupd desc";
    $resalt = d::b()->query($sqlalt) or die("A Consulta do relatário de versões falhou :".mysql_error(d::b())."<br>Sql:".$sqlalt);
    $qtdrowa2= mysqli_num_rows($resalt);
                
    if($_1_u_sgdoc_status == 'APROVADO' OR $_1_u_sgdoc_status == 'OBSOLETO'){
        $_1_u_sgdoc_acompversao = "";
    }	
?>
    <table style="width:100%;" class="tbImpressao print">
        <thead>
            <tr> 
                <th colspan="3">
                Histórico do Documento
                </th>		
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>Versão</th>
                <th >Data</th>
                <th>Descrição</th>				
            </tr>
            <?while($rowalt = mysqli_fetch_array($resalt)){?>
                <tr class="respreto">
                    <td><?=$rowalt["versao"]?>.<?=$rowalt["revisao"]?></td>
                    <td nowrap><?=$rowalt["dmadata"]?></td> 
                    <td><?=$rowalt["acompversao"]?></td>
                </tr>
            <?}?>
            <tr class="screen">
                <td colspan="3">
                    <div class="panel-heading" >Participantes</div>
                    <div class="panel-body"> 					
                        <div >
                            <?= listaPessoaEvento()?>

                            <?= listaPessoaEventoInseridosManualmente() ?>

                            <?= listaPessoaEventoSemSetorESemAssinatura()?>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</body>
<?}?>