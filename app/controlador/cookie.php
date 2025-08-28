<?php
if (isset($_POST['oscuro'])) {
    $valor = $_POST['oscuro'];
    setcookie("oscuro", $valor, time() + (10 * 365 * 24 * 60 * 60), "/");
    exit;
}
?>