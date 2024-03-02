-- --------------------------------------------------------

--
-- table structure to control the user frontend sessions
--

CREATE TABLE IF NOT EXISTS sessions
(
    session_id  bigint           NOT NULL COMMENT 'the internal unique primary index',
    uid         bigint           NOT NULL COMMENT 'the user session id as get by the frontend',
    hash        varchar(255)     NOT NULL,
    expire_date timestamp        NOT NULL,
    ip          varchar(46)      NOT NULL,
    agent       varchar(255) DEFAULT NULL,
    cookie_crc  text         DEFAULT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to control the user frontend sessions';
