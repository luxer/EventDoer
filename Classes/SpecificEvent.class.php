<?php
class SpecificEvent {
    private $db;
    private $event;

    private $eventTime = false;
    private $description;
    private $status = 0;

    public function __construct($db, $event, $eventTime) {
        $this->db = $db;
        $this->event = $event;

        $prepEventStmt = $this->db->prepare('SELECT * FROM `specificEvent` WHERE `eventId`=? AND `eventTime`=? LIMIT 1');
        $prepEventStmt->execute(array($this->event->getId(), $eventTime));
        $prepEvent = $prepEventStmt->fetch();

        if (is_array($prepEvent)) {
            foreach($prepEvent as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function getEventTime() {
        return $this->eventTime;
    }

    public function getDescription() {
        return $this->description;
    }

    public function exists() {
        return $this->eventTime !== false;
    }

    public function isCanceled() {
        switch ($this->status) {
            case 0:
                return false;
            case 1:
                return true;
        }
    }

    public function isJoinable() {
        return $this->eventTime > time();
    }

    public function getParticipants() {
        $partStmt = $this->db->prepare('SELECT `participantId`, `name`, `additionalParticipant`, `participation` FROM `specificEventParticipation`'.
        ' INNER JOIN `participant` ON `participant`.`eventId`=`specificEventParticipation`.`eventId` AND `specificEventParticipation`.`participantId`=`participant`.`id`'.
        ' WHERE `specificEventParticipation`.`eventId`=? AND `eventTime`=?');
        $partStmt->execute(array($this->event->getId(),$this->eventTime));
        $parts = array(
            array(), // decline
            array(), // maybe
            array(), // accept
        );
        while ($part = $partStmt->fetch()) {
            $parts[$part['participation']][$part['participantId']] = array(
                'name' => $part['name'],
                'adds' => $part['additionalParticipant'],
            );
        }
        return $parts;
    }

    public function getOptions() {
        $optionStmt = $this->db->prepare('SELECT `id`,`name` FROM `eventOption` WHERE `eventId`=?');
        $optionStmt->execute(array($this->event->getId()));
        $options = array();
        while ($option = $optionStmt->fetch()) {
            $options[$option['id']]['name'] = $option['name'];
            $valueStmt = $this->db->prepare('
                SELECT count(*), `value` FROM
                    (SELECT `specificEventParticipationValue`.* FROM `specificEventParticipationValue` INNER JOIN `specificEventParticipation` ON `specificEventParticipation`.`eventTime`=`specificEventParticipationValue`.`eventTime` AND `specificEventParticipation`.`eventId`=`specificEventParticipationValue`.`eventId` AND `specificEventParticipation`.`participantId`=`specificEventParticipationValue`.`participantId` WHERE `specificEventParticipation`.`participation` > 0 AND `specificEventParticipation`.`eventTime`=? AND `specificEventParticipation`.`eventId`=? AND `eventOptionId`=?) AS subTable
                INNER JOIN `eventOptionValue` ON `eventOptionValue`.`id`=`subTable`.`eventOptionValueId` GROUP BY `eventOptionValue`.`id`');
            $valueStmt->execute(array($this->eventTime, $this->event->getId(), $option['id']));

            while ($value = $valueStmt->fetch()) {
                $options[$option['id']]['values'][] = array (
                    'name' => $value['value'],
                    'count' => $value['count(*)'],
                );
            }
        }

        return $options;
    }

    public function addParticipant($postData) {
        if (empty($postData['additional'])) {
            $postData['additional'] = 0;
        }

        if (!empty($postData['0'])) {
            $participation = 0;
        }
        if (!empty($postData['1'])) {
            $participation = 1;
        }
        if (!empty($postData['2'])) {
            $participation = 2;
        }


        $partStmt = $this->db->prepare('INSERT INTO `specificEventParticipation` (`eventTime`,`eventId`,`participantId`,`participation`,`additionalParticipant`)'
            .' VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE `participation`=?,`additionalParticipant`=?');

        $partStmt->execute(array(
            $this->eventTime, $this->event->getId(), $postData['participant'], $participation, $postData['additional'],
            $participation, $postData['additional'],
        ));

        $stmt = 'INSERT INTO `specificEventParticipationValue` (`eventTime`, `eventId`, `participantId`, `eventOptionId`, `eventOptionValueId`) VALUES';
        $data = array();
        foreach ($postData as $key => $value) {
            if (substr($key, 0, 3) === 'opt') {
                $key = substr($key, 3);
                $data[] = $this->eventTime;
                $data[] = $this->event->getId();
                $data[] = $postData['participant'];
                $data[] = $key;
                $data[] = $value;
                $stmt .= ' (?,?,?,?,?),';
            }
        }
        $stmt = substr($stmt,0,strlen($stmt)-1);
        $stmt .= ' ON DUPLICATE KEY UPDATE `eventOptionValueId`= VALUES(`eventOptionValueId`)';

        $optionStmt = $this->db->prepare($stmt);
        $optionStmt->execute($data);

        return true;
    }

}