<?php
$connSQL=new DB();
$all_module=$connSQL->getResults('SELECT * FROM perm_module ORDER BY module, menu_order, component');
$cpt_module=count($all_module);

?>