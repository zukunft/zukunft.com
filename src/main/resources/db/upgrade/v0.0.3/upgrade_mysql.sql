--
-- upgrade the zukunft.com MySQL database to Version 0.0.3
--

-- --------------------------------------------------------

--
-- Structure for view`phrases`
--
DROP TABLE IF EXISTS `phrases`;

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost` SQL SECURITY DEFINER VIEW `phrases` AS
select `words`.`word_id`            AS `phrase_id`,
       `words`.`user_id`            AS `user_id`,
       `words`.`word_name`          AS `name_used`,
       `words`.`description`        AS `description`,
       `words`.`values`             AS `values`,
       `words`.`word_type_id`       AS `word_type_id`,
       `words`.`excluded`           AS `excluded`,
       `words`.`share_type_id`      AS `share_type_id`,
       `words`.`protect_id` AS `protect_id`
from `words`
union
select (`word_links`.`word_link_id` * -(1)) AS `phrase_id`,
       `word_links`.`user_id`               AS `user_id`,
       if(`word_links`.`name_given` is null, `word_links`.`name_generated`,
          `word_links`.`name_given`)    AS `name_used`,
       `word_links`.`description`           AS `description`,
       `word_links`.`values`                AS `values`,
       `word_links`.`word_type_id`          AS `word_type_id`,
       `word_links`.`excluded`              AS `excluded`,
       `word_links`.`share_type_id`         AS `share_type_id`,
       `word_links`.`protect_id`    AS `protect_id`
from `word_links`;

--
-- Structure for view`user_phrases`
--
DROP TABLE IF EXISTS `user_phrases`;

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost` SQL SECURITY DEFINER VIEW `user_phrases` AS
select `user_words`.`word_id`            AS `phrase_id`,
       `user_words`.`user_id`            AS `user_id`,
       `user_words`.`word_name`          AS `name_used`,
       `user_words`.`description`        AS `description`,
       `user_words`.`values`             AS `values`,
       `user_words`.`excluded`           AS `excluded`,
       `user_words`.`share_type_id`      AS `share_type_id`,
       `user_words`.`protect_id` AS `protect_id`
from `user_words`
union
select (`user_word_links`.`word_link_id` * -(1)) AS `phrase_id`,
        `user_word_links`.`user_id`              AS `user_id`,
       if(`user_word_links`.`name_given` is null, `user_word_links`.`name_generated`,
          `user_word_links`.`name_given`)    AS `name_used`,
       `user_word_links`.`description`           AS `description`,
       `user_word_links`.`values`                AS `values`,
       `user_word_links`.`excluded`              AS `excluded`,
       `user_word_links`.`share_type_id`         AS `share_type_id`,
       `user_word_links`.`protect_id`    AS `protect_id`
from `user_word_links`;


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
