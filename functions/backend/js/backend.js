jQuery(document).ready(function($) {

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Overview --> Ändern der Saison --> Reload der Seite
\*--------------------------------------------------------------------------------------------------------------------------------------------*/	
	/*$('#boatplace').bind('change', function () {
          
          var url = "./admin.php?page=settings&platz=" + $(this).val(); // get selected value



          if (url) { // require a URL
              if($(this).val() == 'delete'){
                  return false;
              }else{
                  window.location = url; // redirect
              }

          }
          return false;
      });*/

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Neuer Auftritt --> Datum auswählen mit jQuery UI
\*--------------------------------------------------------------------------------------------------------------------------------------------*/	
      
     var oldate = $("#datepicker").val();
     $('#datepicker').datepicker({
		inline: true,
		dateFormat: "dd.mm.yy",
		setDate: oldate,
		prevText: '&#x3c;zurück', prevStatus: '',
        prevJumpText: '&#x3c;&#x3c;', prevJumpStatus: '',
        nextText: 'Vor&#x3e;', nextStatus: '',
        nextJumpText: '&#x3e;&#x3e;', nextJumpStatus: '',
        currentText: 'heute', currentStatus: '',
        todayText: 'heute', todayStatus: '',
        clearText: '-', clearStatus: '',
        closeText: 'schließen', closeStatus: '',
        monthNames: ['Januar','Februar','März','April','Mai','Juni',
        'Juli','August','September','Oktober','November','Dezember'],
        monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',
        'Jul','Aug','Sep','Okt','Nov','Dez'],
        dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
        dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
        dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
	      showMonthAfterYear: false,
	      showOn: 'both',
	});
});