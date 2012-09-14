<?php

define('UF_BASE',realpath(dirname(__FILE__).'/..'));
require_once(UF_BASE.'/core/umvc.php');
header("Pragma: no-cache");
uf_application::init();
uf_baker::bake_all();
header('Location: /');
?>
