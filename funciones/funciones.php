<?php
  function perfil($db, $id) {
    $sql = "SELECT id_person, nom_per, ape_per, ced_per, ema_per, dir_per, tel_per, nom_car ".
    "FROM personas, personal, cargos WHERE id_per_person=id_per AND id_car_person=id_car AND ".
    "id_person='$id'";
    $res = $db->query($sql);
    $data = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $data[] = $r;
    }
    return $data;
  }

  function editarPerfil($db,$id) {
    extract($_POST);
    $r = true;
    $e="Faltan datos";

    $sql = "SELECT id_per FROM personas, personal WHERE id_per_person=id_per AND id_person='$id'";
    $res = $db->query($sql);
    $rp = $res->fetch_object();

    $sql = "UPDATE `personas` SET ".
    "`fec_mod_per`=NOW() ".
    ($nom ? ",`nom_per`='".$nom."' " : "").
    ($ape ? ",`ape_per`='".$ape."' " : "").
    ($ced ? ",`ced_per`='".$ced."' " : "").
    ($ema ? ",`ema_per`='".$ema."' " : "").
    ($tel ? ",`tel_per`='".$tel."' " : "").
    ($dir ? ",`dir_per`='".$dir."' " : "").
    "WHERE id_per = '".$rp->id_per."'";
    $res = $db->query($sql);
    if ($res) {
      $e = "Persona modificada.";
      $r = true;
    } else {
      $r = false;
      $e = "Ocurrió un error guardando el cambio: ".$db->error;
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }

  function secciones($db,$id) {
    $date_now = date('Y-m-d');
    $sql = "SELECT id_ano, id_men, nom_men, nom_ano, num_ano, sec_ano, COUNT(id_estd) as num_est, pnom_alum, pape_alum ".
    "FROM anos ".
    "JOIN mencion ON id_men_ano=id_men ".
    "LEFT JOIN estudiantes ON id_ano_estd=id_ano ".
    "LEFT JOIN alumnos ON id_alum_estd=id_alum  ".
    "LEFT JOIN semanero ON (id_estd_sem=id_estd AND '$date_now' BETWEEN inicio_sem AND cierre_sem) ".
    "WHERE eli_ano='1' ".
    "GROUP BY id_ano";
    $res = $db->query($sql);
    $data = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $data[] = $r;
    }
    return $data;
  }

  function buscarRepresentante($db,$id) {
    extract($_POST);
    $sql = "SELECT * FROM representantes WHERE ced_rep='$ced'";
    $res = $db->query($sql);
    $data = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $data[] = $r;
    }
    return $data;
  }

  function agregarAlum($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    if (empty($idRe)) {
      if($nomRe && $apeRe && $cedRe && $telRe && $dirRe) {
        $sql = "INSERT INTO `representantes` (`nom_rep`, `ape_rep`, `ced_rep`, `tel_rep`, `dir_rep`, ".
        "`tel_re_rep`, `fec_cre_rep`, `fec_mod_rep`, `eli_rep`) VALUES ('$nomRe', '$apeRe', '$cedRe', ".
        "'$telRe', '$dirRe', '$sTelRe', NOW(), NOW(), '1')";
        $res = $db->query($sql);
        if ($res) {
          $idRe = $db->insert_id;
        } else {
          $r = false;
          $e = "Ocurrió un error registrando el representante: ".$db->error;
        }
      }
    }

    if ($idRe && $pnom && $pape && $fec_nac && $idRe) {
      $sql = "INSERT INTO `alumnos` (`pnom_alum`, `snom_alum`, `pape_alum`, `sape_alum`, `fec_nac_alum`, ".
      "`ced_alum`, `id_rep_alum`, `paren_alum`, `act_alum`, `obs_alum`, `fec_cre_alum`, `fec_mod_alum`, `eli_alum`) ".
      "VALUES ('$pnom', '$snom', '$pape', '$sape', '$fec_nac', '$ced', '$idRe', '$paren', '1', '$obs', NOW(), NOW(), '1')";
      $res = $db->query($sql);

      if ($res) {
        $idAlum = $db->insert_id;
        $sql = "INSERT INTO `estudiantes` (`id_alum_estd`, `id_ano_estd`, `eli_estd`, `fec_cre_estd`, ".
        "`fec_mod_estd`) VALUES ('$idAlum', '$idAno', '1', NOW(), NOW())";
        $res = $db->query($sql);
        if ($res) {
          $e = "Estudiante registrado correctamente";
          $r = true;
          cuentaSemanero($db,$id,$idAno);
        } else {
          $r = false;
          $e = "Ocurrió un error  registrando el estudiante: ".$db->error;
        }
      } else {
        $r = false;
        $e = "Ocurrió un error registrando el alumno: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }

  function cuentaSemanero ($db,$id,$ano) {
    $r = false;
    $e = 'Ocurrió un error';

    $sql = "SELECT id_estd FROM estudiantes, alumnos WHERE id_alum_estd=id_alum AND id_ano_estd='$ano' AND eli_estd='1' ORDER BY ced_alum ASC";
    $res = $db->query($sql);
    $date_now = date('Y-m-d');
    $sqlm = '';
    if ($db->query($sql)) {
      while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
        $sqlm.= "DELETE FROM semanero WHERE id_estd_sem='".$r['id_estd']."';";
        $date_future = strtotime('+7 day', strtotime($date_now));
        $date_future = date('Y-m-d', $date_future);
        $sqlm.= "INSERT INTO `semanero` (`id_estd_sem`, `inicio_sem`, `cierre_sem`, `eli_sem`) VALUES ('".$r['id_estd']."', '$date_now', '$date_future', '1');";
        $date_now = strtotime('+7 day', strtotime($date_now));
        $date_now = date('Y-m-d', $date_now);
      }
      if($db->multi_query($sqlm)) {
        $r = true;
        $e = "Semaneros guardados.";
      } else {
        $r = false;
        $e = "Error guardando los semaneros.".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }

  function sesion($db,$id) {
    extract($_POST);
    $date_now = date('Y-m-d');

    $datos_estd = '';
    if (empty($estd)) {
      $datos_estd = "AND '$date_now' BETWEEN inicio_sem AND cierre_sem ";
    } else {
      $datos_estd = "AND id_estd='$estd'";
    }

    $sql = "SELECT id_estd, pnom_alum, snom_alum, pape_alum, sape_alum, ced_alum, fec_nac_alum, paren_alum, nom_rep, ".
    "ape_rep, ced_rep, tel_rep, tel_re_rep, dir_rep, obs_alum, inicio_sem, cierre_sem, nom_men, nom_ano, abre_men, num_ano, sec_ano ".
    "FROM estudiantes, alumnos, representantes, semanero, mencion, anos ".
    "WHERE id_alum_estd=id_alum AND id_rep_alum=id_rep AND id_ano_estd='$ano' AND id_estd_sem=id_estd ".
    "AND id_ano_estd=id_ano AND id_men_ano=id_men AND act_alum='1' AND eli_estd='1' ".
    $datos_estd.
    "ORDER BY ced_alum ASC";
    $res = $db->query($sql);
    $alum = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $alum[] = $r;
    }

    $sql = "SELECT id_estd, pnom_alum, snom_alum, pape_alum, sape_alum, ced_alum FROM estudiantes, alumnos ".
    "WHERE id_alum_estd=id_alum AND id_ano_estd='$ano' AND act_alum='1' AND eli_estd='1' ".
    "ORDER BY ced_alum ASC";
    $res = $db->query($sql);
    $estd = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $estd[] = $r;
    }

    return array(
      "alum"=>$alum,
      "estd"=>$estd
    );
  }
?>