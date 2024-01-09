<?php
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
function limpiarCadena($valor){
    $valor = str_ireplace("SELECT","",$valor);
    $valor = str_ireplace("INSERT","",$valor);
    $valor = str_ireplace("COPY","",$valor);
    $valor = str_ireplace("DELETE","",$valor);
    $valor = str_ireplace("DROP","",$valor);
    $valor = str_ireplace("DUMP","",$valor);
    $valor = str_ireplace(" OR ","",$valor);
    $valor = str_ireplace("'%","",$valor);
    $valor = str_ireplace("%'","",$valor);
    $valor = str_ireplace("LIKE","",$valor);
    $valor = str_ireplace("--","",$valor);
    $valor = str_ireplace("^","",$valor);
    $valor = str_ireplace("[","",$valor);
    $valor = str_ireplace("]","",$valor);
    $valor = str_ireplace("\\","",$valor);
    $valor = str_ireplace("!","",$valor);
    $valor = str_ireplace("¡","",$valor);
    $valor = str_ireplace("<?","",$valor);
    $valor = str_ireplace("?>","",$valor);
    $valor = str_ireplace("=","",$valor);
    $valor = str_ireplace("&&","",$valor);
    $valor = str_ireplace("'","",$valor);
    $valor = str_ireplace("\"","",$valor);
    $valor = str_ireplace("<","",$valor);
    $valor = str_ireplace(">","",$valor);

    return $valor;
}

$input = array();
foreach ($_GET as $key => $input) {
	$_GET[$key] = limpiarCadena($input);
}
$input = array();
foreach ($_POST as $key => $input) {
    $_POST[$key] = limpiarCadena($input);
}
?>
