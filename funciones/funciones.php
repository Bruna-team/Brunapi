<?php
  function cargos($db, $id) {
    $sql = "SELECT * FROM `cargos` WHERE nom_car!='Root'";
    $res = $db->query($sql);
    $data = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $data[] = $r;
    }
    return $data;
  }
  function perfil($db, $id) {
    $sql = "SELECT id_person, nom_per, ape_per, ced_per, ema_per, dir_per, tel_per, nom_car, estatus ".
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
  function secciones($db,$id,$car) {
    $date_now = date('Y-m-d');
    $sql = "SELECT id_person FROM personal WHERE id_person = '$id' AND estatus = 1";
    $res = $db->query($sql);
    if ($res->num_rows == 0) {
      $sql = "SELECT id_ano, id_men, nom_men, nom_ano, num_ano, sec_ano, COUNT(id_estd) as num_est, pnom_alum, pape_alum ".
      "FROM anos ".
      "JOIN mencion ON id_men_ano=id_men ".
      "LEFT JOIN estudiantes ON id_ano_estd=id_ano ".
      "LEFT JOIN alumnos ON id_alum_estd=id_alum  ".
      "LEFT JOIN semanero ON (id_estd_sem=id_estd AND '$date_now' BETWEEN inicio_sem AND cierre_sem) ";
      if ($car > 2) {
        $sql.= "LEFT JOIN jornadas ON id_ano_jor=id_ano ".
        "LEFT JOIN materias ON id_mat_jor=id_mat ".
        "LEFT JOIN prof_guia ON id_ano_guia=id_ano ".
        "LEFT JOIN personal ON (id_per_jor=id_person OR id_person_guia=id_person) ";
      }
      $sql.="WHERE eli_ano='1' ";
      if ($car > 2) {$sql.="AND id_person='$id' ";}
      $sql.="GROUP BY id_ano";
      $res = $db->query($sql);
      if ($res->num_rows) {
        $data = array();
        while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
          $data[] = $r;
        }
        return $data;
      } else {
        return array(
          "r"=>true,
          "e"=>"Parece que aun no tienes una sección asignada"
        );
      }
    } else {
      return array(
        "r"=>true,
        "e"=>"Parece que estas bloqueado como profesor"
      );
    }
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
      $sql = "INSERT INTO `alumnos` (`pnom_alum`, `snom_alum`, `pape_alum`, `sape_alum`, `fec_nac_alum`, `sex_alum`, ".
      "`ced_alum`, `id_rep_alum`, `paren_alum`, `act_alum`, `obs_alum`, `fec_cre_alum`, `fec_mod_alum`, `eli_alum`) ".
      "VALUES ('$pnom', '$snom', '$pape', '$sape', '$fec_nac', '$sex', '$ced', '$idRe', '$paren', '1', '$obs', NOW(), NOW(), '1')";
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
  function editarAlum($db,$id) {
    extract($_POST);
    $r = true;
    $e="Faltan datos";

    $sql = "SELECT id_rep, nom_rep, ape_rep, tel_rep, tel_re_rep, dir_rep FROM representantes WHERE ced_rep='$cedRe'";
    $res = $db->query($sql);
    if ($res->num_rows == 0) {
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
    } else {
      $row = $res->fetch_assoc();
      if (
          $row['tel_rep'] != $telRe
          || $row['tel_re_rep'] != $sTelRe
          || $row['dir_rep'] != $dirRe
          || $row['nom_rep'] != $nomRe
          || $row['ape_rep'] != $apeRe
        ) {
        $sql = "UPDATE `representantes` SET ".
        "`fec_mod_rep`=NOW() ".
        ($nomRe ? ",`nom_rep`='".$nomRe."' " : "").
        ($apeRe ? ",`ape_rep`='".$apeRe."' " : "").
        ($telRe ? ",`tel_rep`='".$telRe."' " : "").
        ($sTelRe ? ",`tel_re_rep`='".$sTelRe."' " : "").
        ($dirRe ? ",`dir_rep`='".$dirRe."' " : "").
        "WHERE id_rep = '".$idRe."'";
        $res = $db->query($sql);
        if ($res) {
          $e = "Representante modificada.";
          $r = true;
        } else {
          $r = false;
          $e = "Ocurrió un error guardando el cambio: ".$db->error;
        }
      }
    }

    $sql = "UPDATE `alumnos` SET ".
    "`fec_mod_alum`=NOW() ".
    ($pnom ? ",`pnom_alum`='".$pnom."' " : "").
    ($snom ? ",`snom_alum`='".$snom."' " : "").
    ($pape ? ",`pape_alum`='".$pape."' " : "").
    ($sape ? ",`sape_alum`='".$sape."' " : "").
    ($ced ? ",`ced_alum`='".$ced."' " : "").
    ($fec_nac ? ",`fec_nac_alum`='".$fec_nac."' " : "").
    ($paren ? ",`paren_alum`='".$paren."' " : "").
    ($idRe ? ",`id_rep_alum`='".$idRe."' " : "").
    ($obs ? ",`obs_alum`='".$obs."' " : "").
    ($sex_alum ? ",`sex_alum`='".$sex_alum."' " : "").
    "WHERE id_alum = '".$id."'";
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
  function cuentaSemanero ($db,$id,$ano) {
    $r = false;
    $e = 'Ingrese la información del periodo para realizar el cálculo de semaneros';

    $sql= "SELECT * from informacion ORDER BY id_inf DESC LIMIT 1";
    $res = $db->query($sql);
    if ($res->num_rows != 0) {
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

    $sql = "SELECT id_estd, id_ano, id_rep, id_alum, ced_rep, pnom_alum, snom_alum, pape_alum, sape_alum, ced_alum, fec_nac_alum, paren_alum, nom_rep, ".
    "ape_rep, ced_rep, tel_rep, tel_re_rep, dir_rep, obs_alum, inicio_sem, cierre_sem, nom_men, nom_ano, abre_men, num_ano, sec_ano, sex_alum, ".
    "SUM(CASE WHEN id_mo = '5' THEN 1 ELSE 0 END) AS entrada, SUM(CASE WHEN id_mo = '6' THEN 1 ELSE 0 END) AS salida  ".
    "FROM estudiantes ".
    "JOIN alumnos ON id_alum_estd=id_alum ".
    "JOIN representantes ON id_rep_alum=id_rep ".
    "JOIN semanero ON id_estd_sem=id_estd ".
    "JOIN anos ON id_ano_estd=id_ano ".
    "JOIN mencion ON id_men_ano=id_men ".
    "LEFT JOIN observaciones ON id_estd_obs=id_estd ".
    "LEFT JOIN motivos_obs ON id_mo_obs=id_mo ".
    "WHERE id_ano_estd='$ano' AND act_alum='1' AND eli_estd='1' ".
    $datos_estd.
    "ORDER BY ced_alum ASC";
    $res = $db->query($sql);
    $alum = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $id = $r['id_estd'];
      if (empty($id)) {
        $sql = "SELECT id_estd, id_ano, id_rep, id_alum, ced_rep, pnom_alum, snom_alum, pape_alum, sape_alum, ced_alum, fec_nac_alum, paren_alum, nom_rep, ".
        "ape_rep, ced_rep, tel_rep, tel_re_rep, dir_rep, obs_alum, inicio_sem, cierre_sem, nom_men, nom_ano, abre_men, num_ano, sec_ano, sex_alum, ".
        "SUM(CASE WHEN id_mo = '5' THEN 1 ELSE 0 END) AS entrada, SUM(CASE WHEN id_mo = '6' THEN 1 ELSE 0 END) AS salida  ".
        "FROM estudiantes ".
        "JOIN alumnos ON id_alum_estd=id_alum ".
        "JOIN representantes ON id_rep_alum=id_rep ".
        "JOIN semanero ON id_estd_sem=id_estd ".
        "JOIN anos ON id_ano_estd=id_ano ".
        "JOIN mencion ON id_men_ano=id_men ".
        "LEFT JOIN observaciones ON id_estd_obs=id_estd ".
        "LEFT JOIN motivos_obs ON id_mo_obs=id_mo ".
        "WHERE id_ano_estd='$ano' AND act_alum='1' AND eli_estd='1' ".
        "ORDER BY ced_alum ASC LIMIT 1";
        $res = $db->query($sql);
        while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
          $alum[] = $r;
          $id = $r['id_estd'];
        }
      } else {
        $alum[] = $r;
      }
    }

    $sql = "SELECT id_obs, fec_obs, hor_obs, fec_fin_obs, nom_obs, tipo_mo, nota_obs, id_mo_obs FROM observaciones, motivos_obs ".
    "WHERE id_mo_obs=id_mo AND id_estd_obs='$id'";
    $res = $db->query($sql);
    $cal = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $cal[] = $r;
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
      "cal"=>$cal,
      "estd"=>$estd
    );
  }
  function motivos($db,$id) {
    $sql = "SELECT * FROM motivos_obs";
    $res = $db->query($sql);
    $data = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $data[] = $r;
    }
    return $data;
  }
  function crearObservacion($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    if ($estd && $mot) {
      $sql = "INSERT INTO `observaciones` (`id_estd_obs`,`id_mo_obs`, `fec_obs`, `hor_obs`, `fec_fin_obs`, `nom_obs`, `nota_obs`, `eli_obs`) ".
      "VALUES ('$estd', '$mot', '$fec', '$hor', '$fecFin', '$nom', '$obs', '1')";
      $res = $db->query($sql);
      if ($res) {
        $e = "Observación registrada correctamente";
        $r = true;
      } else {
        $r = false;
        $e = "Ocurrió un error  registrando el observación: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function editarObservacion($db,$id) {
    extract($_POST);
    $r = true;
    $e="Faltan datos";

    if (!empty($id)) {
      $sql = "UPDATE `observaciones` SET ".
      ($mot ? "`id_mo_obs`='".$mot."' " : "").
      ($fec ? ",`fec_obs`='".$fec."' " : "").
      ($hor ? ",`hor_obs`='".$hor."' " : "").
      ($fecFin ? ",`fec_fin_obs`='".$fecFin."' " : "").
      ($nom ? ",`nom_obs`='".$nom."' " : "").
      ($obs ? ",`nota_obs`='".$obs."' " : "").
      "WHERE id_obs = '".$id."'";
      $res = $db->query($sql);
      if ($res) {
        $e = "Observación modificada.";
        $r = true;
      } else {
        $r = false;
        $e = "Ocurrió un error guardando el cambio: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function eliminarObservacion($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    if ($obs) {
      $sql = "DELETE FROM `observaciones` WHERE `id_obs` = $obs";
      $res = $db->query($sql);
      if ($res) {
        $e = "Observación eliminada correctamente";
        $r = true;
      } else {
        $r = false;
        $e = "Ocurrió un error  eliminando la observación: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function menciones($db,$id) {
    $sql = "SELECT id_ano, id_men, nom_men, nom_ano, num_ano, sec_ano ".
    "FROM anos ".
    "JOIN mencion ON id_men_ano=id_men ".
    "WHERE eli_ano='1' ".
    "GROUP BY id_ano ".
    "ORDER BY nom_men, num_ano";
    $res = $db->query($sql);
    $data = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $data[] = $r;
    }
    return $data;
  }
  function observaciones($db,$id) {
    extract($_POST);
    if (empty($fecha)) {
      $fecha_as = " '".date('Y-m-d')."' ";
    } else {
      $fecha = explode(",", $fecha);
      $fecha_as = "";
      $coma = "";
      foreach ($fecha as $key => $value) {
        $fecha_as .= "$coma'$value'";
        $coma = ",";
      }
    }
    $sql = "SELECT id_obs, fec_obs, hor_obs, nota_obs, pnom_alum, snom_alum, pape_alum, sape_alum, DATE(fec_fin_obs) AS fec_fin_obs ".
    "FROM observaciones, motivos_obs, estudiantes, alumnos, anos, mencion ".
    "WHERE id_mo_obs=id_mo AND id_estd_obs=id_estd AND id_alum_estd=id_alum AND id_ano_estd=id_ano AND id_men_ano=id_men ".
    "AND id_mo IN ('2','3') AND (fec_obs >= '$desde' OR DATE(fec_fin_obs) >= '$desde') AND (fec_obs <= '$hasta' OR DATE(fec_fin_obs) <= '$hasta')";
    if (!empty($sec)) {
      $sql.= " AND id_ano='$sec'";
    } else {
      if (!empty($men)) {
        $sql.= " AND id_men='$men'";
      }
      if (!empty($ano)) {
        $sql.= " AND nom_ano='$ano'";
      }
    }
    $sql.= " ORDER BY fec_obs";
    $res = $db->query($sql);
    $data = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $data[] = $r;
    }
    return $data;
  }
  function inasistencias($db,$id) {
    extract($_POST);
    if (empty($fecha)) {
      $fecha_as = " '".date('Y-m-d')."' ";
    } else {
      $fecha = explode(",", $fecha);
      $fecha_as = "";
      $coma = "";
      foreach ($fecha as $key => $value) {
        $fecha_as .= "$coma'$value'";
        $coma = ",";
      }
    }
    $sql = "SELECT id_obs, fec_obs, pnom_alum, snom_alum, pape_alum, sape_alum, id_estd, ".
    "SUM(CASE WHEN id_mo='1' THEN 1 ELSE 0 END) AS justificada, SUM(CASE WHEN id_mo='4' THEN 1 ELSE 0 END) AS inasistencia,".
    "SUM(CASE WHEN id_mo IN ('5','6') THEN 1 ELSE 0 END) AS pases, SUM(CASE WHEN id_mo IN ('1','4') THEN 1 ELSE 0 END) AS total ".
    "FROM observaciones, motivos_obs, estudiantes, alumnos, anos, mencion ".
    "WHERE id_mo_obs=id_mo AND id_mo IN ('1','4','5','6') AND id_estd_obs=id_estd AND id_alum_estd=id_alum ".
    "AND id_ano_estd=id_ano AND id_men_ano=id_men AND id_mo_obs=id_mo ".
    "AND (fec_obs >= '$desde' OR DATE(fec_fin_obs) >= '$desde') AND (fec_obs <= '$hasta' OR DATE(fec_fin_obs) <= '$hasta') AND id_mo IN ('1','4')";
    if (!empty($sec)) {
      $sql.= " AND id_ano='$sec'";
    } else {
      if (!empty($men)) {
        $sql.= " AND id_men='$men'";
      }
      if (!empty($ano)) {
        $sql.= " AND nom_ano='$ano'";
      }
    }
    $sql.= " ORDER BY fec_obs";
    $res = $db->query($sql);
    $data = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $data[] = $r;
    }
    return $data;
  }
  function totalEstudiantes($db, $id) {
    $sql = "SELECT COUNT(*) AS total_estudiantes FROM alumnos";
    $res = $db->query($sql);

    if ($res->num_rows > 0) {
      $row = $res->fetch_assoc();
      return $row["total_estudiantes"];
    } else {
      return "No se encontraron estudiantes.";
    }
  }
  function burcarEstudiante($db,$id) {
    extract($_POST);
    $day = date("l");
    $d = "";
    switch ($day) {
      case "Monday":
        $d = "Lunes";
      break;
      case "Tuesday":
        $d = "Martes";
      break;
      case "Wednesday":
        $d = "Miércoles";
      break;
      case "Thursday":
        $d = "Jueves";
      break;
      case "Friday":
        $d = "Viernes";
      break;
    }
    $sql = "SELECT id_estd, CONCAT(pnom_alum, ' ',pape_alum) as nombre, nom_men, nom_ano, sec_ano, ced_alum, modulo_hor, ".
    "CONCAT(nom_rep, ' ',ape_rep) as representantes, nom_mat, CONCAT(nom_per, ' ',ape_per) as profesor ".
    "FROM estudiantes ".
    "JOIN alumnos ON id_alum_estd=id_alum ".
    "JOIN anos ON id_ano=id_ano_estd ".
    "JOIN mencion ON id_men=id_men_ano ".
    "JOIN representantes ON id_rep=id_rep_alum ".
    "LEFT JOIN jornadas ON (id_ano_jor=id_ano AND dia_jor='$d') ".
    "LEFT JOIN horarios ON (id_hor_jor=id_hor AND '$hor' BETWEEN inicio_hor AND fin_hor) ".
    "LEFT JOIN materias ON id_mat_jor=id_mat ".
    "LEFT JOIN personal ON id_person=id_per_jor ".
    "LEFT JOIN personas ON id_per=id_per_person ".
    "WHERE (pnom_alum LIKE '%$nom%' OR pape_alum LIKE '%$nom%') ";
    if (!empty($ano)) {
      $sql.= " AND id_ano_estd='$ano'";
    }
    $res = $db->query($sql);
    $data = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $data[] = $r;
    }
    return $data;
  }
  function estatusMaestro($db,$id) {
    $sql_car = "SELECT id_person, id_car_person,nom_car AS respuesta
      FROM personal
      WHERE id_person='$id'";
    $res_car = $db->query($sql_car);

    $sql_est = "SELECT id_person, estatus,
      CASE
        WHEN estatus = 1 THEN 'bloqueado'
        ELSE 'activo'
      END AS respuesta
      FROM personal
      WHERE id_person='$id'";
    $res_est = $db->query($sql_est);
    if ($res_car->num_rows > 0) {
      while ($row_car = $res_car->fetch_assoc()) {
        if (
          ($_POST['vista'] == 'maestros' || $_POST['vista'] == 'configuracion' || $_POST['vista'] == 'pases')
          && ($row_car['respuesta'] !== 'Coordinador' || $row_car['respuesta'] !== 'Root')) {
          return array(
            "r"=>false,
            "l"=>'/acceso-denegado',
            "e"=>"noAccess",
          );
        }
        if ($_POST['vista'] == 'seccion' && ($row_car['respuesta'] !== 'Coordinador' || $row_car['respuesta'] !== 'Root')) {
          $sec = $_POST['sec'];
          $sql = "SELECT id_ano_jor, id_per_jor FROM jornadas WHERE id_ano_jor='$sec' AND id_per_jor='$id'";
          $res = $db->query($sql);
          if ($res->num_rows > 0) {
            return array(
              "r"=>true,
            );
          } else {
            return array(
              "r"=>false,
              "l"=>'/acceso-denegado',
              "e"=>"sectionNoAssigned",
            );
          }
        }
        else if ($res_est->num_rows > 0) {
          while ($row_est = $res_est->fetch_assoc()) {
            if($row_est['respuesta'] !== 'activo') {
              return array(
                "r"=>false,
                "l"=>'/acceso-denegado',
                "e"=>"locked",
              );
            }
          }
        }
      }
    }
    return array(
      "r"=>true,
    );
  }
  function modificarEstatusMaestro($db,$id) {
    extract($_POST);
    $sql = "UPDATE personal SET estatus = '$est' WHERE id_person='$id_person'";
    $res = $db->query($sql);
    if($res) {
      $r = true;
      $e = "Estatus del maestro actualizado.";
    } else {
      $r = false;
      $e = "Error actualizando el estatus del maestro.".$db->error;
    }
    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function maestros($db,$id) {
    extract($_POST);
    $where_nom = '';
    if (!empty($nom)) {
      $where_nom = "AND (nom_per LIKE '%$nom%' OR ape_per LIKE '%$nom%') ";
    }
    $where_mat = '';
    if (!empty($mat)) {
      $mat = explode(",", $mat);
      $mat_as = "";
      $coma = "";
      foreach ($mat as $key => $value) {
        $mat_as.= "$coma'$value'";
        $coma = ",";
      }
      $where_mat.= " AND id_mat IN ($mat_as) ";
    }
    $sql = "SELECT id_person, CONCAT(nom_per, ' ',ape_per) as profesor, id_jor, dia_jor, nom_mat, modulo_hor, inicio_hor, estatus,".
    "fin_hor, num_ano, nom_men, sec_ano, nom_men, nom_ano, sec_ano, id_ano, id_mat, id_hor, nom_car, id_car, id_ano_guia ".
    "FROM personal ".
    "JOIN personas ON id_per_person=id_per ".
    "JOIN cargos ON id_car=id_car_person ".
    "LEFT JOIN prof_guia ON id_person_guia=id_person ".
    "LEFT JOIN jornadas ON id_per_jor=id_person ".
    "LEFT JOIN materias ON id_mat=id_mat_jor ".
    "LEFT JOIN horarios ON id_hor_jor=id_hor ".
    "LEFT JOIN anos ON id_ano_jor=id_ano ".
    "LEFT JOIN mencion ON id_men_ano=id_men ".
    "WHERE eli_person='1' ".
    "AND id_car!='1' ".
    $where_nom.
    $where_mat.
    "ORDER BY nom_per, ape_per";
    $res = $db->query($sql);
    $data = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $data[] = $r;
    }
    return $data;
  }
  function estudiantes($db, $id) {
    extract($_POST);
    $sql = "SELECT id_estd, pnom_alum, snom_alum, pape_alum, sape_alum, ced_alum, id_ano_estd FROM estudiantes, alumnos ".
    "WHERE id_alum_estd=id_alum AND act_alum='1' AND eli_estd='1' ";
    if (!empty($ano)) {
      $sql.= "AND id_ano_estd='$ano' ";
    }
    $sql.= "ORDER BY ced_alum ASC";
    $res = $db->query($sql);
    $estd = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $estd[] = $r;
    }
    return $estd;
  }
  function registrarInasistencias($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    if (!empty($alum)) {
      $alum = explode(",", $alum);
      $sql = '';
      foreach ($alum as $key => $value) {
        $sql.= "INSERT INTO `observaciones` (`id_estd_obs`,`id_mo_obs`, `fec_obs`, `hor_obs`, `fec_fin_obs`, `nom_obs`, `nota_obs`, `eli_obs`) ".
        "VALUES ('$value', '4', '$fec', '$hor', '$fec', 'Inasistencia clase', '', '1');";
      }
      if($db->multi_query($sql)) {
        $r = true;
        $e = "Inasistencias registradas.";
      } else {
        $r = false;
        $e = "Error registrando los inasistentes.".$db->error;
      }
    }
    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function registrarPases($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    if (!empty($alum)) {
      $sql = "INSERT INTO `observaciones` (`id_estd_obs`,`id_mo_obs`, `fec_obs`, `hor_obs`, `fec_fin_obs`, `nom_obs`, `nota_obs`, `eli_obs`) ".
      "VALUES ('$alum', '$pase', '$fec', '$hor', '$fec', 'Pase coordinación', '$mot', '1')";
      $res = $db->query($sql);
      if($res) {
        $r = true;
        $e = "Pase registrado.";
      } else {
        $r = false;
        $e = "Error registrando el pase.".$db->error;
      }
    }
    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function informacion($db,$id) {
    $sql= "SELECT * from informacion ORDER BY id_inf DESC LIMIT 1";
    $res = $db->query($sql);
    $inf = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $inf[] = $r;
    }
    return $inf;
  }
  function informacionGuardar($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    $sql = "INSERT INTO informacion (`ano_oct_inf`, `ano_dic_inf`, `ano_ene_inf`, `ano_jul_inf`) VALUES ('$oct', '$dic','$ene','$jul')";
    $res = $db->query($sql);
    if ($res) {
      $r = true;
      $e = "Año escolar registrado";
    } else {
      $r = false;
      $e = "Ocurrió un error registrando el año escolar: ".$db->error;
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function horarios($db,$id) {
    $sql= "SELECT * from horarios WHERE eli_hor='1' ORDER BY modulo_hor ASC";
    $res = $db->query($sql);
    $hor = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $hor[] = $r;
    }
    return $hor;
  }
  function horarioCrear($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    $sql = "INSERT INTO horarios (`modulo_hor`, `inicio_hor`, `fin_hor`) VALUES ('$mod', '$ini','$fin')";
    $res = $db->query($sql);
    if ($res) {
      $r = true;
      $e = "Modulo registrado";
    } else {
      $r = false;
      $e = "Ocurrió un error registrando el modulo: ".$db->error;
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function editarHorario($db,$id) {
    extract($_POST);
    $r = true;
    $e="Faltan datos";

    if (!empty($id)) {
      $sql = "UPDATE `horarios` SET ".
      ($mod ? "`modulo_hor`='".$mod."' " : "").
      ($ini ? ",`inicio_hor`='".$ini."' " : "").
      ($fin ? ",`fin_hor`='".$fin."' " : "").
      "WHERE id_hor = '".$id."'";
      $res = $db->query($sql);
      if ($res) {
        $e = "Modulo modificado.";
        $r = true;
      } else {
        $r = false;
        $e = "Ocurrió un error guardando el cambio: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function horarioEliminar($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    if (!empty($id)) {
      $sql = "DELETE FROM `horarios` WHERE `id_hor` = $id";
      $res = $db->query($sql);
      if ($res) {
        $e = "Modulo eliminado correctamente";
        $r = true;
      } else {
        $r = false;
        $e = "Ocurrió un error eliminando el modulo: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function materias($db,$id) {
    $sql= "SELECT * from materias WHERE eli_mat='1' ORDER BY nom_mat ASC";
    $res = $db->query($sql);
    $mat = array();
    while ($r = $res->fetch_array(MYSQLI_ASSOC)) {
      $mat[] = $r;
    }
    return $mat;
  }
  function materiasCrear($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    if (!empty($mat)) {
      $sql = "INSERT INTO materias (`nom_mat`) VALUES ('$mat')";
      $res = $db->query($sql);
      if ($res) {
        $r = true;
        $e = "Materia registrada";
      } else {
        $r = false;
        $e = "Ocurrió un error registrando la materia: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function materiaEditar($db,$id) {
    extract($_POST);
    $r = true;
    $e="Faltan datos";

    if (!empty($id)) {
      $sql = "UPDATE `materias` SET ".
      ($mat ? "`nom_mat`='".$mat."' " : "").
      "WHERE id_mat = '".$id."'";
      $res = $db->query($sql);
      if ($res) {
        $e = "Materia modificada.";
        $r = true;
      } else {
        $r = false;
        $e = "Ocurrió un error guardando el cambio: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function materiaEliminar($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    if (!empty($id)) {
      $sql = "DELETE FROM `materias` WHERE `id_mat` = $id";
      $res = $db->query($sql);
      if ($res) {
        $e = "Materia eliminada correctamente";
        $r = true;
      } else {
        $r = false;
        $e = "Ocurrió un error eliminando la materia: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function mencionCrear($db,$id) {
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    $r = false;
    $e="Faltan datos";
    $id_men = '';
    if (isset($data[0]->id_men)) $id_men = $data[0]->id_men;

    if (!empty($data[0]->nom_men)) {
      $sql = "INSERT INTO mencion (`nom_men`, `abre_men`) VALUES ('".$data[0]->nom_men."', '".$data[0]->abre_men."')";
      $res = $db->query($sql);
      if ($res) {
        $r = true;
        $e = "Mención registrada";
        $id_men = mysqli_insert_id($db);
      } else {
        $r = false;
        $e = "Ocurrió un error registrando la mención: ".$db->error;
      }
    }
    return anoCrear($db, $id, $r, $e, $id_men);
  }
  function anoCrear($db,$id,$r,$e,$id_men) {
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    $sqlm = '';
    if (empty($r)) $r = false;
    if (empty($e)) $e="Faltan datos";

    if (!empty($id_men)) {
      foreach ($data[0]->ano as $key => $ano) {
        foreach ($ano->sec as $key => $sec) {
          $sqlm.= "INSERT INTO anos (`nom_ano`, `num_ano`, `sec_ano`, `id_men_ano`) VALUES ('$ano->nom_ano', '$ano->num_ano', '$sec->sec_nom', '$id_men');";
        }
      }
      if ($db->multi_query($sqlm)) {
        $r = true;
        $e = "Año registrado";
      } else {
        $r = false;
        $e = "Ocurrió un error registrando el año: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function mencionEditar($db,$id) {
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    $r = false;
    $e="Faltan datos";

    if (!empty($data[0]->id_men)) {
      $sql = "UPDATE `mencion` SET nom_men='".$data[0]->nom_men."', abre_men='".$data[0]->abre_men."' ".
      "WHERE id_men='".$data[0]->id_men."'";
      $res = $db->query($sql);
      if ($res) {
        $r = true;
        $e = "Mención modificada";
      } else {
        $r = false;
        $e = "Ocurrió un error modificando la mención: ".$db->error;
      }
    }
    return anoEditar($db, $id, $r, $e);
  }
  function anoEditar($db,$id,$r,$e) {
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    $sqlm = '';
    if (empty($r)) $r = false;
    if (empty($e)) $e="Faltan datos";

    if (!empty($data[0]->id_men)) {
      foreach ($data as $key => $ano) {
        $sqlm.= "UPDATE `anos` SET ".
        "nom_ano='$ano->nom_ano', ".
        "num_ano='$ano->num_ano', ".
        "sec_ano='$ano->sec_ano' ".
        "WHERE id_ano='$ano->id_ano';";
      }
      if ($db->multi_query($sqlm)) {
        $r = true;
        $e = "Año modificado";
      } else {
        $r = false;
        $e = "Ocurrió un error modificando el año: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function seccionEliminar($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    if (!empty($id)) {
      $sql = "DELETE FROM `anos` WHERE `id_ano` = $id";
      $res = $db->query($sql);
      if ($res) {
        $e = "Sección eliminada correctamente";
        $r = true;
      } else {
        $r = false;
        $e = "Ocurrió un error eliminando la sección: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function anoEliminar($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    if (!empty($id)) {
      $sql = "DELETE FROM `anos` WHERE `nom_ano` = '$id' AND `id_men_ano`='$men'";
      $res = $db->query($sql);
      if ($res) {
        $e = "Año eliminado correctamente";
        $r = true;
      } else {
        $r = false;
        $e = "Ocurrió un error eliminando el año: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function mencionEliminar($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    if (!empty($id)) {
      $sql = "DELETE FROM `mencion` WHERE `id_men`='$id';";
      $sql.= "DELETE FROM `anos` WHERE `id_men_ano`='$id'";
      if ($db->multi_query($sql)) {
        $e = "Mención eliminada correctamente";
        $r = true;
      } else {
        $r = false;
        $e = "Ocurrió un error eliminando la mención: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function jornadaCrear($db,$id) {
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    $sqlm = '';
    $r = false;
    $e="Faltan datos";

    foreach ($data as $key => $j) {
      $sqlm.="INSERT INTO `jornadas` (`dia_jor`, `id_hor_jor`, `id_mat_jor`, `id_ano_jor`, `id_per_jor`) ".
      "VALUES ('$j->dia', '$j->hor', '$j->mat', '$j->ano', '$j->id');";
    }
    if ($db->multi_query($sqlm)) {
      $r = true;
      $e = "Jornada registrads";
    } else {
      $r = false;
      $e = "Ocurrió un error registrando la jornada: ".$db->error;
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function jornadaEliminar($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    if (!empty($id)) {
      $sql = "DELETE FROM `jornadas` WHERE `id_jor`='$id';";
      if ($db->multi_query($sql)) {
        $e = "Jornada eliminada correctamente";
        $r = true;
      } else {
        $r = false;
        $e = "Ocurrió un error eliminando la jornada: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function jornadaEditar($db,$id) {
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    $sqlm = '';
    $r = false;
    $e="Faltan datos";

    foreach ($data as $key => $value) {
      $sqlm.= "UPDATE `jornadas` SET ".
      "dia_jor='$value->dia', ".
      "id_hor_jor='$value->hor', ".
      "id_mat_jor='$value->mat', ".
      "id_ano_jor='$value->ano' ".
      "WHERE id_jor='$value->jor';";
    }
    if ($db->multi_query($sqlm)) {
      $r = true;
      $e = "Jornada modificada";
    } else {
      $r = false;
      $e = "Ocurrió un error modificando la jornada: ".$db->error;
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function rolCambiar($db,$id) {
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    $sqlm = '';
    $r = true;
    $e="Faltan datos";

    if (!empty($data[0]->rol) && $data[0]->rol == '4') {
      foreach ($data as $key => $c) {
        $sqlm.="INSERT INTO `prof_guia` (`id_person_guia`, `id_ano_guia`, `fec_cre_guia`) ".
        "VALUES ('$c->id', '$c->ano', NOW());";
      }
      if ($db->multi_query($sqlm)) {
        $r = true;
        while ($db->next_result()) {;}
      } else {
        $r = false;
        $e = "Ocurrió un error registrando las secciones del guía: ".$db->error;
      }
    }
    if (!empty($data[0]->id) && !empty($data[0]->rol) && $r) {
      $sql = "UPDATE `personal` SET id_car_person='".$data[0]->rol."' WHERE id_person='".$data[0]->id."';";
      if ($data[0]->rol !== '4') {
        $sql.="DELETE FROM `prof_guia` WHERE `id_person_guia`='".$data[0]->id."';";
      }
      if ($db->multi_query($sql)) {
        $r = true;
        $e = "Rol modificado";
      } else {
        $r = false;
        $e = "Ocurrió un error modificando el rol: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
  function rolGuiaEliminar($db,$id) {
    extract($_POST);
    $r = false;
    $e="Faltan datos";

    if (!empty($ano)) {
      $sql = "DELETE FROM `prof_guia` WHERE `id_ano_guia`='$ano';";
      if ($db->multi_query($sql)) {
        $e = "Año desvinculado correctamente";
        $r = true;
      } else {
        $r = false;
        $e = "Ocurrió un error desvinculando el año: ".$db->error;
      }
    }

    return array(
      "r"=>$r,
      "e"=>$e
    );
  }
?>