<?php error_reporting(E_ALL); ?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="res/css/reset.css">
  <link rel="stylesheet" type="text/css" href="res/css/main.css">
  <meta name="viewport" content="width=device-width, user-scalable=yes">
  <title>EventDoer</title>
</head>
<body>
<div class="wrapper">
<?php
require 'Config/Main.php';
if (array_key_exists('path', $_GET) && count(explode('/',$_GET['path'])) > 0) {
  $path = explode('/',$_GET['path']);
  $event = new Event($db, $path[0]);
    if ($event->exists()) {
        echo '<header><h1>'.$event->getName().'</h1>';
        echo '<p>'.$event->getDescription().'</p></header>';

        $specificEvent = $event->getNewestSpecificEvent();
        if ($specificEvent) {
            $timezone = new DateTimeZone('Europe/Berlin');
            $date = new DateTime("now",$timezone);
            $date->setTimestamp($specificEvent->getEventTime());
            echo '<main><h2>'.$LANG[LANGUAGE]['event_next'].': '.$date->format('d.m.y - H:i').'</h2>';

            if (!empty($specificEvent->getDescription())) {
                echo '<p>'.$specificEvent->getDescription().'</p>';
            }


            if ($specificEvent->isCanceled()) {
                echo '<p class="status">'.$LANG[LANGUAGE]['event_canceled'];
            } else {
                if ($specificEvent->isJoinable()) {
                    $showForm = true;
                    echo '<div class="join"><h3>'.$LANG[LANGUAGE]['participation'].'</h3>';
                    if (array_key_exists('sendForm',$_POST)) {
                        $showForm = !$specificEvent->addParticipant($_POST);
                    }

                    if ($showForm) {
                        echo '<form method="post" action="#">';
                        echo '<label for="participant">'.$LANG[LANGUAGE]['who'].'</label><select name="participant">';
                        $participant = $event->getParticipants();
                        foreach ($participant as $key => $value) {
                            echo '<option value="'.$key.'">'.$value.'</option>';
                        }
                        echo '</select>';
                        echo '<br><label for="additional">'.$LANG[LANGUAGE]['additional_people'].'</label><input type="number" placeholder="0" name="additional">';

                        $options = $event->getOptions();
                        foreach ($options as $optionId => $optionValues) {
                            echo '<br><label for="opt'.$optionId.'">'.$optionValues['name'].'</label><select name="opt'.$optionId.'">';
                            if (array_key_exists('values', $optionValues) && is_array($optionValues['values'])) {
                                foreach($optionValues['values'] as $valueId => $value) {
                                    echo '<option value="'.$valueId.'">'.$value.'</option>';
                                }
                            }
                            echo '</select>';
                        }

                        echo '<br><input type="hidden" name="sendForm" value="true"><label>'.$LANG[LANGUAGE]['participate'].'</label>'
                        .'<input type="submit" value="'.$LANG[LANGUAGE]['participate_cancel'].'" name="0">'
                        .'<input type="submit" value="'.$LANG[LANGUAGE]['participate_maybe'].'" name="1">'
                        .'<input type="submit" value="'.$LANG[LANGUAGE]['participate_join'].'" name="2">'
                        .'</form>';
                    } else {
                        echo '<p>'.$LANG[LANGUAGE]['participate_sent'].'</p>';
                    }
                    echo '</div>';

                } else {
                    echo $LANG[LANGUAGE]['participate_cant'];
                }
            }

            echo '<div class="infos"><h3>'.$LANG[LANGUAGE]['information'].'</h3>';
            $specificParts = $specificEvent->getParticipants();
            for ($i = 0; $i < 3; $i++) {
                if (count($specificParts[$i]) > 0) {
                    echo '<div>';
                    switch ($i) {
                        case 0: echo '<label>'.$LANG[LANGUAGE]['partipations_cancel'].':</label> '; break;
                        case 1: echo '<label>'.$LANG[LANGUAGE]['partipations_maybe'].':</label> '; break;
                        case 2: echo '<label>'.$LANG[LANGUAGE]['partipations_join'].':</label> '; break;
                     }

                    foreach ($specificParts[$i] as $part) {
                      echo $part['name'];
                      if ($part['adds'] > 0) {
                          echo ' ('.$part['adds'].')';
                      }
                      echo ', ';
                    }

                    echo '</div>';
                }
            }

            $specificOptions = $specificEvent->getOptions();
            if (is_array($specificOptions)) {
                foreach ($specificOptions as $option) {
                    echo '<div><label>'.$option['name'].':</label> ';
                    if (array_key_exists('values', $option) && is_array($option['values'])) {
                        foreach ($option['values'] as $value) {
                            echo $value['count'].'x '.$value['name'].', ';
                        }
                    }
                    echo '</div>';
                }
            }

            echo '</div>';
            echo '</main>';

            echo '<footer></footer>';
        } else {
            echo '<header><h1>'.$LANG[LANGUAGE]['event_notfound_title'].'</h1></header><main><p>'.$LANG[LANGUAGE]['specificevent_notfound_text'].'</p></main>';
        }

    } else {
        echo '<header><h1>'.$LANG[LANGUAGE]['error'].'</h1></header><main><p>'.$LANG[LANGUAGE]['event_notfound'].'</p></main>';
    }
} else {
    echo '<header><h1>'.$LANG[LANGUAGE]['error'].'</h1></header><main><p>'.$LANG[LANGUAGE]['event_notgiven'].'</p></main>';
}

?>
</div>
</body>
