
-- --------------------------------------------------------

--
-- table structure to cache the formula public unprotected numeric results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard_prime
(
    group_id      bigint     NOT NULL COMMENT 'the 64-bit prime index to find the numeric result',
    numeric_value double     NOT NULL COMMENT 'the numeric value given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to cache the formula public unprotected numeric results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

