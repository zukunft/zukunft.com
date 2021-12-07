--
-- upgrade the zukunft.com MySQL database to Version 0.0.3
--

-- --------------------------------------------------------

--
-- Structure for view`phrases`
--
DROP TABLE IF EXISTS `phrases`;

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost`SQL
    SECURITY DEFINER VIEW `phrases` AS
select `words`.`word_id`            AS `phrase_id`,
       `words`.`user_id`            AS `user_id`,
       `words`.`word_name`          AS `phrase_name`,
       `words`.`excluded`           AS `excluded`,
       `words`.`share_type_id`      AS `share_type_id`,
       `words`.`protection_type_id` AS `protection_type_id`
from `words`
union
select (`word_links`.`word_link_id` * -(1)) AS `phrase_id`,
       `word_links`.`user_id`               AS `user_id`,
       if(`word_links`.`description` is null, `word_links`.`word_link_name`,
          `word_links`.`description`)       AS `phrase_name`,
       `word_links`.`excluded`              AS `excluded`,
       `word_links`.`share_type_id`         AS `share_type_id`,
       `word_links`.`protection_type_id`    AS `protection_type_id`
from `word_links`;
