<?php

/**
 * Abstraktion der List Tables Klasse für meine Zwecke :-)
 */

if(! class_exists('Tour_List_Table')){
	die("Table Class not reachable. Please try again.");
}

class Tour_Overview extends Tour_List_Table{
		
	/**
	 * Laden der Inhalte aus der Datenbank und als assoziatives Array zurückgeben
	 */

	 function read_tour_from_db(){
		 global $wpdb;
		 
		 //$tour_data = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'termine', ARRAY_A);
		 
		 $tour_data = $wpdb->get_results('
		 SELECT te.termin_id, tg.tagname, dt.datum, dt.datum_sql, te.nameLocality,te.meetingTime,te.playTime,te.trailerStand,tr.transport_icon, te.makeUp, te.notice 
		 FROM '.$wpdb->prefix.'termine te
		 JOIN '.$wpdb->prefix.'tage tg ON te.tag_id = tg.tagid
		 JOIN '.$wpdb->prefix.'datum dt ON te.datum_id = dt.id
		 JOIN '.$wpdb->prefix.'transport tr ON te.transport_id = tr.transport_id
		 ORDER BY dt.datum_sql ASC', ARRAY_A);
			 	 
		 return $tour_data;
	 }
	

	
	/**
	 * Vorbereiten des Inhaltes zur Präsentation	
	 */
	function prepare_items(){
		
		$columns      = $this->get_columns();
		$hidden       = array();
		$sortable     = array();
		
		//Ausgabe der Titel
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		//Ausgabe des Inhaltes
		$this->items = $this->read_tour_from_db( );

		
	}
	
	/**
	 * Definieren der Title über und unter der Tabelle
	 * @return: titles array 	
	 */
	
	function get_columns(){
		
		$columns = array(
				   'termin_id'		=> 'termin_id',
				   'tagname'		=> 'tagname',
				   'datum'       	=> 'datum',
				   'datum_sql'       	=> 'datum_sql',
				   'nameLocality'   => 'nameLocality',
				   'meetingTime'  	=> 'meetingTime',
				   'playTime'    	=> 'playTime',
				   'transport_icon'   => 'transport_icon',
				   'trailerStand'   => 'trailerStand',
				   'makeUp' 		=> 'makeUp',
				   'notice' 		=> 'notice'
				   );
		return $columns;
	}
	
	/**
	 *	Zuweisung der Inhalte auf die Spalten
	 */
	function column_default($item, $column_name){
		
		switch($column_name){
			case 'termin_id':
			case 'tagname':
			case 'datum':
			case 'datum_sql':
			case 'nameLocality':
			case 'meetingTime':
			case 'playTime':
			case 'transport_icon':
			case 'trailerStand':
			case 'makeUp':
			case 'notice':
				return $item[ $column_name ];
			break;
			default:
				return print_r( $item, true ); // Für debugging, Ausgabe des gesamten Array, falls die Zuweisung fehlschlägt 
		}
		
	}
	
	/**
	 * definieren der Spalten welche man sortieren kann
	 */
	 
	 /*function get_sortable_columns(){
		 
		 $sortable_columns = array(
							'datum'       => array('datum', false),
							'anlass'      => array('anlass', false),
							'saison'	  => array('saison', false),
							'organisator' => array('organisator', false),
							'ort'         => array('ort', false),
							'besammlung'  => array('besammlung', false),
							'auftritt'    => array('auftritt', false),
							'vs'          => array('vs', false),
							'anhaenger'   => array('anhaenger', false)
							);
	
		 return $sortable_columns;
		 
	 }*/
	 
	 /**
	  * Definieren von Actions
	  */
	  
	  function column_nameLocality($item) {
		  $actions = array(
		            'edit'      => sprintf('<a href="?page=%s&action=%s&id=%s">Bearbeiten</a>',$_REQUEST['page'],'edit',$item['termin_id']),
		            'delete'    => sprintf('<a href="?page=%s&action=%s&id=%s">Löschen</a>',$_REQUEST['page'],'delete',$item['termin_id']),
		        );
		
		  return sprintf('%1$s %2$s', $item['nameLocality'], $this->row_actions($actions) );
	  }	  
	  

	
}