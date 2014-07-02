CREATE TABLE `event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `description` text,
  `firstTime` int(11) NOT NULL,
  `endTime` int(11) DEFAULT NULL,
  `repeatTime` int(11) DEFAULT NULL COMMENT '-1 means never, unix seconds otherwise',
  `duration` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `eventOption` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventId` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_eventOption_event1_idx` (`eventId`),
  CONSTRAINT `fk_eventOption_event1` FOREIGN KEY (`eventId`) REFERENCES `event` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

CREATE TABLE `eventOptionValue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventOptionId` int(11) NOT NULL,
  `value` varchar(45) DEFAULT NULL,
  `default` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_eventOptionValue_eventOption1_idx` (`eventOptionId`),
  CONSTRAINT `fk_eventOptionValue_eventOption1` FOREIGN KEY (`eventOptionId`) REFERENCES `eventOption` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

CREATE TABLE `participant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventId` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_participant_event1_idx` (`eventId`),
  CONSTRAINT `fk_participant_event1` FOREIGN KEY (`eventId`) REFERENCES `event` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

CREATE TABLE `specificEvent` (
  `eventId` int(11) NOT NULL,
  `eventTime` int(11) NOT NULL,
  `description` text,
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eventTime`,`eventId`),
  KEY `fk_table1_event_idx` (`eventId`),
  CONSTRAINT `fk_table1_event` FOREIGN KEY (`eventId`) REFERENCES `event` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `specificEventParticipation` (
  `eventTime` int(11) NOT NULL,
  `eventId` int(11) NOT NULL,
  `participantId` int(11) NOT NULL,
  `participation` varchar(45) DEFAULT NULL,
  `additionalParticipant` int(11) DEFAULT NULL,
  PRIMARY KEY (`eventTime`,`eventId`,`participantId`),
  KEY `fk_table1_has_participant_participant1_idx` (`participantId`),
  KEY `fk_table1_has_participant_table11_idx` (`eventTime`,`eventId`),
  CONSTRAINT `fk_table1_has_participant_participant1` FOREIGN KEY (`participantId`) REFERENCES `participant` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_table1_has_participant_table11` FOREIGN KEY (`eventTime`, `eventId`) REFERENCES `specificEvent` (`eventTime`, `eventId`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `specificEventParticipationValue` (
  `eventTime` int(11) NOT NULL,
  `eventId` int(11) NOT NULL,
  `participantId` int(11) NOT NULL,
  `eventOptionId` int(11) NOT NULL,
  `eventOptionValueId` int(11) NOT NULL,
  PRIMARY KEY (`eventTime`,`eventId`,`participantId`,`eventOptionId`),
  KEY `fk_eventOptionValue_has_specificEventParticipation_specific_idx` (`eventTime`,`eventId`,`participantId`),
  KEY `fk_eventOptionValue_has_specificEventParticipation_eventOpt_idx` (`eventOptionValueId`),
  KEY `fk_specificEventParticipationValue_eventOption1_idx` (`eventOptionId`),
  CONSTRAINT `fk_eventOptionValue_has_specificEventParticipation_eventOptio1` FOREIGN KEY (`eventOptionValueId`) REFERENCES `eventOptionValue` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_eventOptionValue_has_specificEventParticipation_specificEv1` FOREIGN KEY (`eventTime`, `eventId`, `participantId`) REFERENCES `specificEventParticipation` (`eventTime`, `eventId`, `participantId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_specificEventParticipationValue_eventOption1` FOREIGN KEY (`eventOptionId`) REFERENCES `eventOption` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
