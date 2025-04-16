<?
if(preg_match("/SVG/i", $_FILES[$_fileElementName]["type"]) == 0) die("[PreUpload] Erro: arquivo possui formato inválido");

$qr = "SELECT 
        COUNT(*) AS numarq
    FROM
        arquivo a
    WHERE
        a.tipoobjeto = '".$_tipoobjeto."'
            AND a.idobjeto = ".$_idobjeto."
            AND tipoarquivo = '".$_tipoarquivo."'
    ORDER BY idarquivo ASC";
$rs = d::b()->query($qr);
$numarq = mysqli_fetch_assoc($rs)['numarq'];

if($numarq > 0) die("[PreUpload] Erro: módulo já possui um arquivo anexo");
?>