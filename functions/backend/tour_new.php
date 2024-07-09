<?php

/**
 *  Neuen Anlass erfassen oder einen Anlass bearbeiten, je nach GET Variable
 */

// Wordpress Database Class
global $wpdb;

// Abrufen der Tourenpläne aus der settings Tabelle
$years = $wpdb->get_results("SELECT value FROM `trom_tour_settings` ", ARRAY_A); 

?>

    <style type="text/css">
	  input[type="radio"], input[type="radio"]+label img {  vertical-align: middle;  }
      .img-list{  list-style-type: none; }
      .img-list li { display: inline; margin-right: 10px;}
    </style>

    <div class="wrap">
        <?php
	        if( $_GET['action'] !=''){
	            
	        $art = 'edit';
	
	        $auftritt = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.TOUR." WHERE id = ".$_GET['id'], ARRAY_A);
	
	        switch( $auftritt[ 'vs' ] ){
	            case 'car':
	                $car = 'checked';
	            break;
	            
	            default:
	                $auto = 'checked';
	            break;
	        }
	
	    ?>

        <h2>Auftritt bearbeiten <span><a class="button-secondary" href="./admin.php?page=trb_tour">zurück zur Übersicht</a></span></h2>

        <p>Bitte die Änderungen erfassen.</p>
        <?php } else { 
            $art = 'new';   
        ?>

        <h2>Neuen Auftritt erfassen</h2>

        <p>Bitte die Daten für einen neuen Auftritt eingeben.</p><?php } ?>

        <form>
            <table>
                <tr>
                    <td><label for="saison">Saison:</label></td>

                    <td><select name="saison">
                        <?php foreach( $years as $year ){
							if( $auftritt['saison'] == $year['value']){ $selected = 'selected'; }
						?>
                        <option value="<?php print $year['value']; ?>" <?php $selected; ?>>
                            <?php print $year['value']; ?>
                        </option><?php } ?>
                    </select></td>
                </tr>

                <tr>
                    <td><label for="auftritt">Auftritt:</label></td>

                    <td><input type="text" class="regular-text" name="auftritt" id="auftritt" value="<?php print $auftritt['anlass']; ?>"></td>
                </tr>

                <tr>
                    <td><label for="tag">Tag:</label></td>

                    <td><select name="tag">
                        <?php if( $auftritt['tag'] ){  }?>

                        <option>
                            Montag
                        </option>

                        <option>
                            Dienstag
                        </option>

                        <option>
                            Mittwoch
                        </option>

                        <option>
                            Donnerstag
                        </option>

                        <option>
                            Freitag
                        </option>

                        <option>
                            Samstag
                        </option>

                        <option>
                            Sonntag
                        </option>
                    </select></td>
                </tr>

                <tr>
                    <td><label for="datum">Datum:</label></td>

                    <td><input type="text" class="regular-text auftritt-datum" name="datum" id="datepicker" value="<?php print $auftritt['datum']; ?>"></td>
                </tr>

                <tr>
                    <td><label for="organisator">Organisator:</label></td>

                    <td><input type="text" class="regular-text" name="organisator" id="organisator" value="<?php print $auftritt['organisator']; ?>"></td>
                </tr>

                <tr>
                    <td><label for="ort">Ort:</label></td>

                    <td><input type="text" class="regular-text" name="ort" id="ort" value="<?php print $auftritt['ort']; ?>"></td>
                </tr>

                <tr>
                    <td><label for="besammlung">Besammlung:</label></td>

                    <td><input type="text" class="regular-text" name="besammlung" id="besammlung" value="<?php print $auftritt['besammlung']; ?>"></td>
                </tr>

                <tr>
                    <td><label for="auftritt">Auftritt:</label></td>

                    <td><input type="text" class="regular-text" name="auftritt" id="auftritt" value="<?php print $auftritt['auftritt']; ?>"></td>
                </tr>

                <tr>
                    <td><label for="transport">Transport:</label></td>

                    <td>
                        <ul class="img-list">
                            <li><input type="radio" name="vs" value="car" id="car" <?php print $car; ?>> <label for="car"><img src="<?php print plugins_url( 'backend/car.png', dirname( __FILE__ ) ); ?>" width="50"></label></li>

                            <li><input type="radio" name="vs" value="auto" id="auto" <?php print $auto; ?>> <label for="auto"><img src="<?php print plugins_url( 'backend/auto.png', dirname( __FILE__ ) ); ?>" width="50"></label></li>
                        </ul>
                    </td>
                </tr>

                <tr>
                    <td><label for="anhaenger">Anhänger:</label></td>

                    <td><input type="text" class="regular-text" name="anhaenger" id="anhaenger" value="<?php print $auftritt['anhaenger']; ?>"></td>
                </tr>

                <tr>
                    <td><input class="button-primary" type="submit" name="Example" value="speichern"></td>

                    <td><input type="hidden" name="id" value="<?php print $auftritt['id']; ?>"><input type="hidden" name="action" value="<?php print $art; ?>"></td>
                </tr>
            </table>
        </form>
    </div>