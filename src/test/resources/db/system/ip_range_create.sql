-- --------------------------------------------------------

--
-- table structure of ip addresses that should be blocked
--

CREATE TABLE IF NOT EXISTS ip_ranges
(
    ip_range_id BIGSERIAL PRIMARY KEY,
    ip_from     varchar(46) NOT NULL,
    ip_to       varchar(46) NOT NULL,
    reason      text        NOT NULL,
    is_active   smallint    NOT NULL DEFAULT 1
);

COMMENT ON TABLE ip_ranges IS 'of ip addresses that should be blocked';
COMMENT ON COLUMN ip_ranges.ip_range_id IS 'the internal unique primary index';
