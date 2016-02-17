<?php
// Init
$sql = array();

//Create intermediare table for the products
$sql['importer_products'] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'importer_products`;
	CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'importer_products` (
		`id_importer` int(11) NOT NULL AUTO_INCREMENT,
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';