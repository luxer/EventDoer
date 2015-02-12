<?php
class Event {
    private $db;

    private $id = false;
    private $name;
    private $description;
    private $firstTime;
    private $endTime;
    private $repeatTime;
    private $duration;

    public function __construct($db, $name) {
        $this->db = $db;

        $prepEventStmt = $this->db->prepare('SELECT * FROM event WHERE name=? LIMIT 1');
        $prepEventStmt->execute(array($name));
        $prepEvent = $prepEventStmt->fetch();

        if (is_array($prepEvent)) {
            foreach($prepEvent as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function exists() {
        return $this->id !== false;
    }

    public function getNewestSpecificEvent() {
        $currTime = time();
        $repeatTime = ceil(($currTime - $this->firstTime) / $this->repeatTime);
        $prevEventTime = $this->firstTime + ($repeatTime - 1) * $this->repeatTime;

        if ($currTime  < $prevEventTime + $this->duration) {
            $repeatTime--;
        }

        $nextEventTime = $this->firstTime + $repeatTime * $this->repeatTime;

        $specificEvent = new SpecificEvent($this->db, $this, $nextEventTime);
        return $specificEvent->exists() ? $specificEvent : false;
    }

    public function getParticipants() {
        $partStmt = $this->db->prepare('SELECT `id`,`name` FROM `participant` WHERE eventId=? ORDER BY name ASC');
        $partStmt->execute(array($this->id));
        $parts = array();
        while ($part = $partStmt->fetch()) {
            $parts[$part['id']] = $part['name'];
        }
        return $parts;
    }

    public function getOptions() {
        $optionStmt = $this->db->prepare('SELECT `id`,`name` FROM `eventOption` WHERE `eventId`=? ORDER BY name ASC');
        $optionStmt->execute(array($this->id));
        $options = array();
        while ($option = $optionStmt->fetch()) {
            $options[$option['id']]['name'] = $option['name'];

            $valueStmt = $this->db->prepare('SELECT `id`,`value` FROM `eventOptionValue` WHERE eventOptionId=? ORDER BY RAND()');
            $valueStmt->execute(array($option['id']));
            while ($value = $valueStmt->fetch()) {
                $options[$option['id']]['values'][$value['id']] = $value['value'];
            }
        }
        return $options;
    }
}