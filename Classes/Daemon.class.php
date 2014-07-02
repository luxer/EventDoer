<?php
class Daemon {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function run() {
        $this->createSpecificEvents();
    }

    private function createSpecificEvents() {
        $eventsStmt = $this->db->prepare('SELECT `id`,`firstTime`,`repeatTime`,`duration` FROM `event` WHERE `endTime` IS NULL');
        $eventsStmt->execute();

        while ($event = $eventsStmt->fetch()) {
            if ($event['repeatTime'] > -1) {
                $this->specificEventCheckPeriodic($event);
            } else {
                $this->specificEventCheck($event);
            }
        }
    }

    private function specificEventCheck($event) {
        $specificEventStmt = $this->db->prepare('SELECT `eventTime` FROM `specificEvent` WHERE `eventId`=? ORDER BY `eventTime` DESC LIMIT 1');
        $specificEventStmt->execute(array($event['id']));
        $specificEvent = $specificEventStmt->fetch();
        $nextEventTime = $event['firstTime'];

        if (!($specificEvent !== false)) {
            $this->specificEventCreate($event, $nextEventTime);
        }
    }

    private function specificEventCheckPeriodic($event) {
        $specificEventStmt = $this->db->prepare('SELECT `eventTime` FROM `specificEvent` WHERE `eventId`=? ORDER BY `eventTime`');
        $specificEventStmt->execute(array($event['id']));
        $specificEvents = $specificEventStmt->fetchAll();
        $repeatTime = ceil((time() - $event['duration'] - $event['firstTime']) / $event['repeatTime']);
        $newestEventTime = $event['firstTime'] + $repeatTime * $event['repeatTime'];

        for ($nextEventTime = $event['firstTime']; $nextEventTime <= $newestEventTime; $nextEventTime += $event['repeatTime']) {
            $exists = false;
            foreach ($specificEvents as $specificEvent) {
                if ($specificEvent['eventTime'] == $nextEventTime) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $this->specificEventCreate($event, $nextEventTime);
            }
        }
    }

    private function specificEventCreate($event,$nextEventTime) {
        $inStmt = $this->db->prepare('INSERT INTO `specificEvent` (`eventId`,`eventTime`) VALUES (?,?)');
        $inStmt->execute(array($event['id'], $nextEventTime));
    }
}