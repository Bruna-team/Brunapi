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

function evaluarLog() {
  if (!isset($_SESSION['id'])) {
    if($_GET['s'] == "auth") {
      require_once('../libs/db_fc.php');
      $r = false;
      $e = 'Rellena los datos';
      $usuario = strip_tags($_POST['usuario']);
      $con = strip_tags($_POST['clave']);
      $rec= strip_tags($_POST['rec']);

      if ($usuario && $con) {
        $sql = "SELECT id_person, nom_car, nom_per, ape_per, cla_log FROM personal, personas, login, cargos ".
        "WHERE id_per=id_per_person AND ".
        "id_person_log=id_per AND ".
        "id_car_person=id_car AND ".
        "ced_per='$usuario' AND ".
        "eli_person='1'";
        $res = $db->query($sql);
        if ($res->num_rows > 0) {
          $row = $res->fetch_assoc();
          $con2 = $row['cla_log'];
          if ($con == $con2) {
            $_SESSION['id'] = $row['id_person'];
            $_SESSION['cargo'] = $row['nom_cargo'];
            $_SESSION["nombre"] = $row['nom_per'];
            $_SESSION["apellido"] = $row['ape_per'];

            if($rec) {
              $hora = sha1(date('d-m-Y h:m:s').md5($usuario).'1').md5($usuario.$usuario);
              $nid=sha1(md5($_SERVER['HTTP_USER_AGENT']).$row['id_person']);
              $domain = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
              $path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
              $exp = time() + (86400 * 5);
              $sql = "INSERT INTO `hashes` (`id_person_hash`,`has_hash`,`exp_hash`) ".
              "VALUES ('".$_SESSION['id']."','$hora','$exp') ";
              $res = $db->query($sql);
              setcookie("sid", $hora, $exp, $path, $domain, false, true);
              setcookie("nid", $nid, $exp, $path, $domain, false, true);
            }
            $e = "Sesion iniciada";
            $r = true;
          } else {
            $r = false;
            $e = "Verifique sus datos.";
          }
        } else {
          $r = false;
          $e = "Verifique sus datos";
        }
      }
      $json = array("r"=>$r,"e"=>$e);
      echo json_encode($json);
      exit;
    } else if (isset($_COOKIE['sid']) && isset($_COOKIE['nid'])) {
      require_once('../libs/db_fc.php');
			$sql = "SELECT id_person, nom_per, ape_per, nom_car FROM hashes, peronal, cagos, personas WHERE ".
			"has_hash='".$_COOKIE['sid']."' AND ".
			"id_person=id_person_hash AND ".
			"id_per_person=id_per AND ".
			"id_car_person=id_car AND ".
			"exp_hash>UNIX_TIMESTAMP()";
      $res = $db->query($sql);

      if ($res->num_rows > 0){
        $row = $res->fetch_assoc();
        $id = $row["id_person"];
        session_start();
        if($_COOKIE['nid'] == sha1(md5($_SERVER['HTTP_USER_AGENT']).$id) ) {
          $_SESSION['id'] = $row['id_person'];
          $_SESSION['cargo'] = $row['nom_cargo'];
          $_SESSION["nombre"] = $row['nom_per'];
          $_SESSION["apellido"] = $row['ape_per'];
          return;
        }
      }
    } else if ($_GET['s'] == "register") {
      require_once('../libs/db_fc.php');
      $r = false;
      $e = 'Código inválido';
      $user = strip_tags($_POST['ced']);
      $nom = strip_tags($_POST['nom']);
      $ape = strip_tags($_POST['ape']);
      $ema = strip_tags($_POST['ema']);
      $tel = strip_tags($_POST['tel']);
      $dir = strip_tags($_POST['dir']);
      $car = strip_tags($_POST['car']);
      $cod = strip_tags($_POST['cod']);
      $con = strip_tags($_POST['clave']);

      if ($cod) {
        $sql = "SELECT id_person FROM personal, login ".
        "WHERE id_person_log=id_per AND ".
        "cla_log='$cod'";
        $res = $db->query($sql);
        if ($res->num_rows > 0) {
          $sql = "INSERT INTO `personas` (`nom_per`, `ape_per`, `ced_per`, `ema_per`, `tel_per`, ".
          "`dir_per`, `eli_per`, `fec_cre_per`, `fec_mod_per`) VALUES ('$nom', '$ape', '$user', '$ema', ".
          "'$tel', '$dir', '1', NOW(), NOW()) ";
          $db->query($sql);
          $per=$db->insert_id;
          if ($per) {
            $sql = "INSERT INTO `personal` (`id_car_person`, `id_per_person`, `eli_person`, ".
            "`fec_cre_person`) VALUES ('$car', '$per', '1', NOW());".
            "INSERT INTO `login` (`id_person_log`, `cla_log`, `fec_con_log`, `eli_log`) ".
            "VALUES ('$per', '$con', NOW(), '1');";
            if($db->multi_query($sql)){
              $r=true;
              $e="Agregado con éxito";
            } else {
              $e="Error guardando personal";
            }
          }
        }
      }
      $json = array("r"=>$r,"e"=>$e);
      echo json_encode($json);
      exit;
    }
    $r= false;
    $e= "Invalido";
    $json = array("r"=>$r,"e"=>$e);
    echo json_encode($json);
    exit;
  }
}
?>
