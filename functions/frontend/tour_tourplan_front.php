<?php
$response = file_get_contents('https://trbapi.thievent.org/api/v1/tour/?format=json');
$res = json_decode($response, true);
$dates = $res['data'];
$oldtitle= NULL;
$olddate = NULL;
$oldevent = NULL;
$i = 0;
echo "

<div class=\"col-md-12\">
<h3 class=\"wp-block-heading\">Tourdaten ". $res['season'] ."</h3>
<table class=\"table table-sm table-responsive\">
    <tbody>
    <tr>
        <th class=\"col-3\" style=\"border-top: 1px solid black\">Datum</th>
        <th class=\"col-8\" style=\"border-top: 1px solid black\">Anlass</th>
        <th class=\"col-1\" style=\"border-top: 1px solid black\">Auftrittszeit</th>
    </tr>";

    foreach ($dates as $date) {
        if ($date['evening_count'] > 0 && $date['public']) {
            foreach ($date['evenings'] as $evening) {
                if ($dates[$i]['title'] != $oldtitle) {
					echo "<tr class=\"bg-secondary text-light\">";
					echo "<td colspan=\"3\" style=\"background-color: #d1d1d1\" class=\"col-sm-12 col-12 bg-secondary text-light\">" . $dates[$i]['title'] . "</td>";
					echo "</tr>";
                }
                foreach ($evening as $event) {
                    if ($evening["public"] == 1) {
                        if ($evening['name'] != $oldevent && $evening['fix']) {
                            ?>
                            <tr>
                                <td class="col-3">
                                    <?php
                                    if ($evening['date'] != $olddate) {
                                        echo $evening['date'];
                                    }
                                    ?>
                                </td>
                                <td class="col-8"><?php echo $evening['name']; ?> </td>
                                <td class="col-1"><?php echo $evening['play']; ?></td>
                            </tr>
                            <?php
                        }
                        $olddate  = $evening['date'];
                        $oldevent = $evening['name'];
                    }
                }
                $oldtitle = $dates[$i]['title'];
            }
        }
        $i++;
    }
echo "
    </tbody>
</table>
</div>";
//ob_end_flush();
