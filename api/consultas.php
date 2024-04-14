<?php
session_start([
  'cookie_lifetime' => 50400,
]);
date_default_timezone_set('America/Caracas');
// header('Access-Control-Allow-Origin: ');
require_once "../libs/seguridad.php";
evaluarLog();
erroresON();
function erroresON() {
error_reporting(E_ALL);
ini_set('display_errors', '1');
}
require_once "../libs/db_fc.php";
require_once "../funciones/funciones.php";
$id = $_SESSION['id'];
$car = $_SESSION['cargo'];
extract($_GET);
switch($s) {
	case 'cargos':
		$data = cargos($db,$id);
		break;
	case 'perfil':
		$data = perfil($db,$id);
		break;
	case 'editarPerfil':
		$data = editarPerfil($db,$id);
		break;
	case 'secciones':
		$data = secciones($db,$id,$car);
		break;
	case 'sesion':
		$data = sesion($db,$id);
		break;
	case 'buscarRepresentante':
		$data = buscarRepresentante($db,$id);
		break;
	case 'agregarAlum':
		$data = agregarAlum($db,$id);
		break;
	case 'editarAlum':
		$data = editarAlum($db,$id);
		break;
	case 'motivos':
		$data = motivos($db,$id);
		break;
	case 'crearObservacion':
		$data = crearObservacion($db,$id);
		break;
	case 'editarObservacion':
		$data = editarObservacion($db,$id);
		break;
	case 'eliminarObservacion':
		$data = eliminarObservacion($db,$id);
		break;
	case 'menciones':
		$data = menciones($db,$id);
		break;
	case 'burcarEstudiante':
		$data = burcarEstudiante($db,$id);
		break;
	case 'inasistencias':
		$data = inasistencias($db,$id);
		break;
	case 'observaciones':
		$data = observaciones($db,$id);
		break;
	case 'maestros':
		$data = maestros($db,$id);
		break;
	case 'estudiantes':
		$data = estudiantes($db,$id);
		break;
	case 'registrarInasistencias':
		$data = registrarInasistencias($db,$id);
		break;
	case 'materiasCrear':
		$data = materiasCrear($db,$id);
		break;
	case 'registrarPases':
		$data = registrarPases($db,$id);
		break;
	case 'informacionGuardar':
		$data = informacionGuardar($db,$id);
		break;
	case 'informacion':
		$data = informacion($db,$id);
		break;
	case 'horarios':
		$data = horarios($db,$id);
		break;
	case 'horarioCrear':
		$data = horarioCrear($db,$id);
		break;
	case 'editarHorario':
		$data = editarHorario($db,$id);
		break;
	case 'horarioEliminar':
		$data = horarioEliminar($db,$id);
		break;
	case 'materias':
		$data = materias($db,$id);
		break;
	case 'materiasCrear':
		$data = materiasCrear($db,$id);
		break;
	case 'materiaEditar':
		$data = materiaEditar($db,$id);
		break;
	case 'materiaEliminar':
		$data = materiaEliminar($db,$id);
		break;
	case 'mencionCrear':
		$data = mencionCrear($db,$id);
		break;
	case 'mencionEditar':
		$data = mencionEditar($db,$id);
		break;
	case 'seccionEliminar':
		$data = seccionEliminar($db,$id);
		break;
	case 'anoEliminar':
		$data = anoEliminar($db,$id);
		break;
	case 'mencionEliminar':
		$data = mencionEliminar($db,$id);
		break;
	case 'jornadaCrear':
		$data = jornadaCrear($db,$id);
		break;
	case 'jornadaEliminar':
		$data = jornadaEliminar($db,$id);
		break;
	case 'jornadaEditar':
		$data = jornadaEditar($db,$id);
		break;
	case 'rolCambiar':
		$data = rolCambiar($db,$id);
		break;
	case "salir":
		session_destroy();
		setcookie("sid", "", time() - 3600,parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH),parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST),false,true);
		setcookie("nid", "", time() - 3600,parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH),parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST),false,true);
		$data = array("r"=>"true");
		break;
	default:
		$data = array("Seleccion"=>'No existe');
}
$db->close();
echo json_encode($data);
?>