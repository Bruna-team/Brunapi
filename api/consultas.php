<?php
session_start([
  'cookie_lifetime' => 50400,
]);
// header('Access-Control-Allow-Origin: ');
require_once "../libs/seguridad.php";
evaluarLog();
// erroresON();
function erroresON() {
error_reporting(E_ALL);
ini_set('display_errors', '1');
}
require_once "../libs/db_fc.php";
require_once "../funciones/funciones.php";
$id = $_SESSION['id'];
extract($_GET);
switch($s) {
	case 'perfil':
		$data = perfil($db,$id);
		break;
	case "salir":
		session_destroy();
		setcookie("sid", "", time() - 3600,parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH),parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST),false,true);
		setcookie("nid", "", time() - 3600,parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH),parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST),false,true);
	break;
		session_destroy();
	break;
	default:
		$data = array("Seleccion"=>'No existe');
}
$db->close();
echo json_encode($data);
?>