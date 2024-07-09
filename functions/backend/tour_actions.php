<?php

/**
 * Überprüfen der GET Variablen und laden der entsprechenden Funktion
 */
 
 switch( $_GET['action'] ){
	 case 'edit':
	 	include_once("tour_new.php");
	 break;
	 
	 case 'delete':
	 	include_once("tour_delete.php");
	 break;
 }