<?

$idTag = $_POST;

$sql = "INSERT INTO mapaequipamento VALUES(null, $idTag, '{}', 'ademi', '2022-05-13', 'ademi', '2022-05-13')";

$result = d::b()->query($sql) or die(mysql_error(d::b()));