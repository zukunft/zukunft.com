--
-- upgrade the zukunft.com MySQL database to Version 0.0.3
--

-- --------------------------------------------------------

--
-- Table structure for table`user_refs`
--

CREATE TABLE IF NOT EXISTS `user_refs`
(
    `ref_id`        int(11) NOT NULL,
    `user_id`       int(11) NOT NULL,
    `url`           text         DEFAULT NULL,
    `description`   text         DEFAULT NULL,
    `excluded`      tinyint(4)   DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

--
-- Indexes for table`user_refs`
--
ALTER TABLE `user_refs`
    ADD UNIQUE KEY `ref_id` (`ref_id`, `user_id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `ref_id_2` (`ref_id`);

--
-- Constraints for table`user_refs`
--
ALTER TABLE `user_refs`
    ADD CONSTRAINT `user_refs_fk_1` FOREIGN KEY (`ref_id`) REFERENCES `refs` (`ref_id`),
    ADD CONSTRAINT `user_refs_fk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

-- --------------------------------------------------------

--
-- Table structure for table`user_view_term_links`
--

CREATE TABLE IF NOT EXISTS `user_view_term_links`
(
    `view_term_link_id` int(11) NOT NULL,
    `type_id`           int(11) NOT NULL DEFAULT '1' COMMENT '1 = from_term_id is link the terms table; 2=link to the term_links table;3=to term_groups',
    `link_type_id`      int(11)          DEFAULT NULL,
    `user_id`           int(11) NOT NULL,
    `description`       text             DEFAULT NULL,
    `excluded`          tinyint(4)       DEFAULT NULL,
    `share_type_id`     smallint         DEFAULT NULL,
    `protect_id`        smallint         DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='used to define the default mask for a term or a term group';

-- --------------------------------------------------------

--
-- Structure for view`phrases`
--

DROP TABLE IF EXISTS `phrases`;

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost` SQL SECURITY DEFINER VIEW `phrases` AS
select `words`.`word_id`            AS `phrase_id`,
       `words`.`user_id`            AS `user_id`,
       `words`.`word_name`          AS `phrase_name`,
       `words`.`description`        AS `description`,
       `words`.`values`             AS `values`,
       `words`.`phrase_type_id`     AS `phrase_type_id`,
       `words`.`excluded`           AS `excluded`,
       `words`.`share_type_id`      AS `share_type_id`,
       `words`.`protect_id` AS `protect_id`
from `words`
union
select (`triples`.`triple_id` * -(1)) AS `phrase_id`,
       `triples`.`user_id`               AS `user_id`,
       if(`triples`.`triple_name` is null,
          if(`triples`.`name_given` is null,
           `triples`.`name_generated`,
           `triples`.`name_given`),
          `triples`.`triple_name`) AS `phrase_name`,
       `triples`.`description`           AS `description`,
       `triples`.`values`                AS `values`,
       `triples`.`phrase_type_id`        AS `phrase_type_id`,
       `triples`.`excluded`              AS `excluded`,
       `triples`.`share_type_id`         AS `share_type_id`,
       `triples`.`protect_id`    AS `protect_id`
from `triples`;

--
-- Structure for view`user_phrases`
--
DROP TABLE IF EXISTS `user_phrases`;

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost` SQL SECURITY DEFINER VIEW `user_phrases` AS
select `user_words`.`word_id`       AS `phrase_id`,
       `user_words`.`user_id`       AS `user_id`,
       `user_words`.`word_name`     AS `phrase_name`,
       `user_words`.`description`   AS `description`,
       `user_words`.`values`        AS `values`,
       `user_words`.`excluded`      AS `excluded`,
       `user_words`.`share_type_id` AS `share_type_id`,
       `user_words`.`protect_id`    AS `protect_id`
from `user_words`
union
select (`user_triples`.`triple_id` * -(1)) AS `phrase_id`,
       `user_triples`.`user_id`               AS `user_id`,
       if(`user_triples`.`triple_name` is null,
          if(`user_triples`.`name_given` is null,
             `user_triples`.`name_generated`,
             `user_triples`.`name_given`),
          `user_triples`.`triple_name`) AS `phrase_name`,
       `user_triples`.`description`           AS `description`,
       `user_triples`.`values`                AS `values`,
       `user_triples`.`excluded`              AS `excluded`,
       `user_triples`.`share_type_id`         AS `share_type_id`,
       `user_triples`.`protect_id`            AS `protect_id`
from `user_triples`;


--
-- Structure for view`terms`
--
DROP TABLE IF EXISTS `terms`;

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost` SQL SECURITY DEFINER VIEW `terms` AS
select ((`words`.`word_id` * 2) - 1) AS `term_id`,
       `words`.`user_id`           AS `user_id`,
       `words`.`word_name`         AS `term_name`,
       `words`.`description`       AS `description`,
       `words`.`values`            AS `usage`,
       `words`.`phrase_type_id`    AS `term_type_id`,
       `words`.`excluded`          AS `excluded`,
       `words`.`share_type_id`     AS `share_type_id`,
       `words`.`protect_id`        AS `protect_id`,
       ''                          AS `formula_text`,
       ''                          AS `resolved_text`
from `words`
where `words`.`phrase_type_id` <> 10 OR `words`.`phrase_type_id` is null
union
select ((`triples`.`triple_id` * -2) + 1) AS `term_id`,
       `triples`.`user_id`                 AS `user_id`,
       if(`triples`.`triple_name` is null,
          if(`triples`.`name_given` is null,
             `triples`.`name_generated`,
             `triples`.`name_given`),
          `triples`.`triple_name`) AS `phrase_name`,
       `triples`.`description`             AS `description`,
       `triples`.`values`                  AS `usage`,
       `triples`.`phrase_type_id`          AS `term_type_id`,
       `triples`.`excluded`                AS `excluded`,
       `triples`.`share_type_id`           AS `share_type_id`,
       `triples`.`protect_id`              AS `protect_id`,
       ''                                  AS `formula_text`,
       ''                                  AS `resolved_text`
from `triples`
union
select (`formulas`.`formula_id` * 2) AS `term_id`,
       `formulas`.`user_id`         AS `user_id`,
       `formulas`.`formula_name`    AS `term_name`,
       `formulas`.`description`     AS `description`,
       `formulas`.`usage`           AS `usage`,
       `formulas`.`formula_type_id` AS `term_type_id`,
       `formulas`.`excluded`        AS `excluded`,
       `formulas`.`share_type_id`   AS `share_type_id`,
       `formulas`.`protect_id`      AS `protect_id`,
       `formulas`.`formula_text`    AS `formula_text`,
       `formulas`.`resolved_text`   AS `resolved_text`
from `formulas`
union
select (`verbs`.`verb_id` * -2) AS `term_id`,
       NULL                    AS `user_id`,
       `verbs`.`formula_name`  AS `term_name`,
       `verbs`.`description`   AS `description`,
       `verbs`.`words`         AS `usage`,
       NULL                    AS `term_type_id`,
       NULL                    AS `excluded`,
       1                       AS `share_type_id`,
       3                       AS `protect_id`,
       ''                      AS `formula_text`,
       ''                      AS `resolved_text`
from `verbs`
;

--
-- Structure for view `user_terms`
--
DROP TABLE IF EXISTS `user_terms`;

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost`SQL
    SECURITY DEFINER VIEW `user_terms` AS
select ((`user_words`.`word_id` * 2) - 1) AS `term_id`,
       `user_words`.`user_id`             AS `user_id`,
       `user_words`.`word_name`           AS `term_name`,
       `user_words`.`description`         AS `description`,
       `user_words`.`values`              AS `usage`,
       `user_words`.`excluded`            AS `excluded`,
       `user_words`.`share_type_id`       AS `share_type_id`,
       `user_words`.`protect_id`          AS `protect_id`,
       ''                                 AS `formula_text`,
       ''                                 AS `resolved_text`
from `user_words`
where `user_words`.`phrase_type_id` <> 10
union
select ((`user_triples`.`triple_id` * -2) + 1) AS `term_id`,
       `user_triples`.`user_id`                   AS `user_id`,
       if(`user_triples`.`triple_name` is null,
          if(`user_triples`.`name_given` is null,
             `user_triples`.`name_generated`,
             `user_triples`.`name_given`),
          `user_triples`.`triple_name`) AS `phrase_name`,
       `user_triples`.`description`               AS `description`,
       `user_triples`.`values`                    AS `usage`,
       `user_triples`.`excluded`                  AS `excluded`,
       `user_triples`.`share_type_id`             AS `share_type_id`,
       `user_triples`.`protect_id`                AS `protect_id`,
       ''                                         AS `formula_text`,
       ''                                         AS `resolved_text`
from `user_triples`
union
select (`user_formulas`.`formula_id` * 2) AS `term_id`,
       `user_formulas`.`user_id`          AS `user_id`,
       `user_formulas`.`formula_name`     AS `term_name`,
       `user_formulas`.`description`      AS `description`,
       `user_formulas`.`usage`            AS `usage`,
       `user_formulas`.`excluded`         AS `excluded`,
       `user_formulas`.`share_type_id`    AS `share_type_id`,
       `user_formulas`.`protect_id`       AS `protect_id`,
       `user_formulas`.`formula_text`     AS `formula_text`,
       `user_formulas`.`resolved_text`    AS `resolved_text`
from `user_formulas`
union
select (`verbs`.`verb_id` * -2) AS `term_id`,
       NULL                     AS `user_id`,
       `verbs`.`formula_name`   AS `term_name`,
       `verbs`.`description`    AS `description`,
       `verbs`.`words`          AS `usage`,
       NULL                     AS `excluded`,
       1                        AS `share_type_id`,
       3                        AS `protect_id`,
       ''                       AS `formula_text`,
       ''                       AS `resolved_text`
from `verbs`
;

--
-- Structure for view`change_table_fields`
--
DROP TABLE IF EXISTS `change_table_fields`;

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost`SQL
    SECURITY DEFINER VIEW `change_table_fields` AS
select `change_fields`.`change_field_id`                                              AS `change_table_field_id`,
       CONCAT(`change_tables`.`change_table_id`, `change_fields`.`change_field_name`) AS `change_table_field_name`,
       `change_fields`.`description`                                                  AS `description`,
       IF(`change_fields`.`code_id` IS NULL,
          CONCAT(`change_tables`.`change_table_id`, `change_fields`.`change_field_name`),
          `change_fields`.`code_id`) AS `code_id`
from `change_fields`,
     `change_tables`
WHERE `change_fields`.table_id = `change_tables`.change_table_id;

-- --------------------------------------------------------

--
-- Table structure for table`user_value_time_series`
--

CREATE TABLE IF NOT EXISTS `user_value_time_series`
(
    `value_time_series_id` int(11)   NOT NULL,
    `user_id`              int(11)   NOT NULL,
    `source_id`            int(11)        DEFAULT NULL,
    `excluded`             tinyint(4)     DEFAULT NULL,
    `share_type_id`        int(11)        DEFAULT NULL,
    `protect_id`   int(11)   NOT NULL,
    `last_update`          timestamp NULL DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='common parameters for a user specific list of intraday values';

--
-- Indexes for table`user_value_time_series`
--
ALTER TABLE `user_value_time_series`
    ADD PRIMARY KEY (`value_time_series_id`, `user_id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `source_id` (`source_id`),
    ADD KEY `value_id` (`value_time_series_id`),
    ADD KEY `share_type` (`share_type_id`),
    ADD KEY `protect_id` (`protect_id`);

--
-- Constraints for table`user_value_time_series`
--
ALTER TABLE `user_value_time_series`
    ADD CONSTRAINT `user_value_time_series_fk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
    ADD CONSTRAINT `user_value_time_series_fk_2` FOREIGN KEY (`source_id`) REFERENCES `sources` (`source_id`),
    ADD CONSTRAINT `user_value_time_series_fk_3` FOREIGN KEY (`share_type_id`) REFERENCES `share_types` (`share_type_id`),
    ADD CONSTRAINT `user_value_time_series_fk_4` FOREIGN KEY (`protect_id`) REFERENCES `protection_types` (`protection_type_id`);

--
-- database corrections
--
--
-- Constraints for table`user_values`
--
ALTER TABLE `user_values`
    ADD CONSTRAINT `user_values_fk_4` FOREIGN KEY (`protect_id`) REFERENCES `protection_types` (`protection_type_id`);

-- --------------------------------------------------------
-- September 2023 changes
-- --------------------------------------------------------

--
-- Table structure to log the value changes done by the users
--

CREATE TABLE IF NOT EXISTS `changes_values`
(
    `change_id`        int(11)   NOT NULL,
    `change_time`      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP COMMENT 'time when the value has been changed',
    `user_id`          int(11)   NOT NULL,
    `change_action_id` int(11)   NOT NULL,
    `change_field_id`  int(11)   NOT NULL,
    `group_id`         char(112) NOT NULL,
    `old_value`        varchar(300)       DEFAULT NULL,
    `new_value`        varchar(300)       DEFAULT NULL
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8 COMMENT ='to log all number changes';

--
-- Table structure to log changes of numbers related to not more than four prime phrases
--

CREATE TABLE IF NOT EXISTS `changes_values_prime`
(
    `change_id`        int(11)   NOT NULL,
    `change_time`      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP COMMENT 'time when the value has been changed',
    `user_id`          int(11)   NOT NULL,
    `change_action_id` int(11)   NOT NULL,
    `change_field_id`  int(11)   NOT NULL,
    `group_id`         int(11)   NOT NULL,
    `old_value`        varchar(300)       DEFAULT NULL,
    `new_value`        varchar(300)       DEFAULT NULL
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8 COMMENT = 'to log changes of numbers related to not more than four prime phrases';

--
-- Table structure to log changes of numbers related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS `changes_values_big`
(
    `change_id`        int(11)   NOT NULL,
    `change_time`      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP COMMENT 'time when the value has been changed',
    `user_id`          int(11)   NOT NULL,
    `change_action_id` int(11)   NOT NULL,
    `change_field_id`  int(11)   NOT NULL,
    `group_id`         varchar   NOT NULL,
    `old_value`        varchar(300)       DEFAULT NULL,
    `new_value`        varchar(300)       DEFAULT NULL
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8 COMMENT = 'to log changes of numbers related to more than 16 phrases';

-- --------------------------------------------------------

--
-- Table structure to remember which phrases are stored in which table and pod
--

CREATE TABLE IF NOT EXISTS `phrase_tables`
(
    `table_id`  int(11) NOT NULL,
    `phrase_id` int(11) NOT NULL,
    `pod_url`   text,
    `active`    smallint DEFAULT NULL
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8 COMMENT ='to remember which phrases are stored in which table and pod';

-- --------------------------------------------------------

-- modify phrase_groups to groups

rename table `phrase_groups` to `groups`;
alter table `groups` drop column word_ids;
alter table `groups` drop column triple_ids;
alter table `groups` drop column id_order;
alter table `groups` change auto_description description varchar(4000) null comment 'the automatic created user readable description';
alter table `groups` change phrase_group_name group_name varchar(4000) null comment 'the automatic created user readable description';

-- modify user_phrase_groups to user_groups

rename table user_groups to `user_groups`;
alter table `groups` change auto_description description varchar(4000) null comment 'the automatic created user readable description';
alter table `groups` change phrase_group_name group_name varchar(4000) null comment 'the automatic created user readable description';
alter table `groups` drop column id_order;

-- --------------------------------------------------------

--
-- Table structure for phrase group names of up to four prime phrases
--

CREATE TABLE IF NOT EXISTS `groups_prime`
(
    `group_id`    int(11) NOT NULL,
    `group_name`  varchar(1000) DEFAULT NULL COMMENT 'if this is set a manual group for fast selection',
    `description` varchar(4000) DEFAULT NULL COMMENT 'the automatic created user readable description'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT = 'to add a user given name using a 64 bit bigint group id index for up to four 16 bit phrase ids including the order';

--
-- Table structure for saving a user specific group name
--

CREATE TABLE IF NOT EXISTS `user_groups_prime`
(
    `group_id`    int(11) NOT NULL,
    `user_id`     int(11) NOT NULL,
    `group_name`  varchar(1000) DEFAULT NULL COMMENT 'if this is set a manual group for fast selection',
    `description` varchar(4000) DEFAULT NULL COMMENT 'the automatic created user readable description'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT = 'to link the user specific name to the group';

--
-- Table structure for phrase group names of more than 16 phrases
--

CREATE TABLE IF NOT EXISTS `groups_big`
(
    `group_id`    text NOT NULL,
    `group_name`  varchar(1000) DEFAULT NULL COMMENT 'if this is set a manual group for fast selection',
    `description` varchar(4000) DEFAULT NULL COMMENT 'the automatic created user readable description'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT = 'to add a user given name using text group id index for an almost unlimited number of phrase ids including the order';

--
-- Table structure for saving a user specific group name for more than 16 phrases
--

CREATE TABLE IF NOT EXISTS `user_groups_big`
(
    `group_id`    text NOT NULL,
    `user_id`     int(11) NOT NULL,
    `group_name`  varchar(1000) DEFAULT NULL COMMENT 'if this is set a manual group for fast selection',
    `description` varchar(4000) DEFAULT NULL COMMENT 'the automatic created user readable description'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT = 'for saving a user specific group name for more than 16 phrases';

-- --------------------------------------------------------

--
-- Table structure to link phrases to a group
-- TODO deprecate and use like on group_id instead
--

CREATE TABLE IF NOT EXISTS `group_links`
(
    `group_id`  char(112) NOT NULL,
    `phrase_id` int(11) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT = 'link phrases to a phrase group for database based selections';

--
-- Table structure to store user specific ex- or includes of single link of phrases to groups
--

CREATE TABLE IF NOT EXISTS `user_group_links`
(
    `group_id`  char(112) NOT NULL,
    `phrase_id` int(11) NOT NULL,
    `user_id`   int(11) DEFAULT NULL,
    `excluded`  smallint DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT = 'to store user specific ex- or includes of single link of phrases to groups';

--
-- Table structure to link phrases to a group
-- TODO deprecate and use like on binary format of group_id instead
--

CREATE TABLE IF NOT EXISTS `group_prime_links`
(
    `group_id`  int(11) NOT NULL,
    `phrase_id` int(11) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT = 'link phrases to a short phrase group for database based selections';

--
-- Table structure to store user specific ex- or includes of single link of phrases to groups
--

CREATE TABLE IF NOT EXISTS `user_group_prime_links`
(
    `group_id`  int(11) NOT NULL,
    `phrase_id` int(11) NOT NULL,
    `user_id`   int(11) DEFAULT NULL,
    `excluded`  smallint DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT = 'user specific link to groups with up to four prime phrase';

--
-- Table structure to link up more than 16 phrases to a group
-- TODO deprecate and use like on group_id instead
--

CREATE TABLE IF NOT EXISTS `group_big_links`
(
    `group_id`  text NOT NULL,
    `phrase_id` int(11) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT = 'link phrases to a short phrase group for database based selections';

--
-- Table structure to store user specific ex- or includes of single link of phrases to groups
--

CREATE TABLE IF NOT EXISTS `user_group_big_links`
(
    `group_id`  text NOT NULL,
    `phrase_id` int(11) NOT NULL,
    `user_id`   int(11) DEFAULT NULL,
    `excluded`  smallint DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT = 'user specific link to groups with up to four prime phrase';

-- --------------------------------------------------------

--
-- Table structure for public values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS `value_standard_prime`
(
    `group_id`        int(11)   NOT NULL COMMENT 'the prime index to find the value',
    `numeric_value`   double    NOT NULL,
    `source_id`       int(11)   DEFAULT NULL COMMENT 'the prime source'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT = 'for public unprotected values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';


--
-- Table structure for public values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS `value_standard`
(
    `group_id`        char(112) NOT NULL COMMENT 'the prime index to find the value',
    `numeric_value`   double    NOT NULL,
    `source_id`       int(11)   DEFAULT NULL COMMENT 'the prime source'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT = 'for public unprotected values that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------
