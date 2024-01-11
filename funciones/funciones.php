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
?>