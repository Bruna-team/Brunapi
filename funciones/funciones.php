<?php
  function iniciarSesion($db) {
    extract($_POST);
    $r = false;
    $e = 'Faltan datos';
    $con = base64_decode($clave);

    if ($usuario && $clave) {
      $sql = "SELECT id_person, nom_car, nom_per, ape_per, cla_log FROM personal, personas, login, cargos ".
      "WHERE id_per=id_per_person AND ".
      "id_person_log=id_person AND ".
      "id_car_person=id_car AND ".
      "ced_per='$usuario' AND ".
      "eli_person='1'";
      $res = $db->query($sql);
      if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $con2 = base64_decode($row['cla_log']);
        if ($con == $con2) {
          $_SESSION['id'] = $row['id_person'];
          $_SESSION['cargo'] = $row['nom_cargo'];
          $_SESSION["nombre"] = $row['nom_per'];
          $_SESSION["apellido"] = $row['ape_per'];
          $e = "Sesion iniciada";
          $r = true;
        } else {
          $r = false;
          $e = "Verifique sus datos.";
        }
      } else {
        $r = false;
        $e = "Verifique sus datos.";
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );

  }
?>