-- --------------------------------------------------------

--
-- table structure of ip addresses that should be blocked
--

CREATE TABLE IF NOT EXISTS ip_ranges
(
    ip_range_id bigint      NOT NULL COMMENT 'the internal unique primary index',
    ip_from     varchar(46) NOT NULL,
    ip_to       varchar(46) NOT NULL,
    reason      text        NOT NULL,
    is_active   smallint    NOT NULL DEFAULT 1
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'of ip addresses that should be blocked';

--
-- AUTO_INCREMENT for table ip_ranges
--
ALTER TABLE ip_ranges
    MODIFY ip_range_id int(11) NOT NULL AUTO_INCREMENT;
