-- --------------------------------------------------------

--
-- table structure to control the user frontend sessions
--

CREATE TABLE IF NOT EXISTS sessions
(
    session_id  BIGSERIAL PRIMARY KEY,
    uid         bigint           NOT NULL,
    hash        varchar(255)     NOT NULL,
    expire_date timestamp        NOT NULL,
    ip          varchar(46)      NOT NULL,
    agent       varchar(255) DEFAULT NULL,
    cookie_crc  text         DEFAULT NULL
);

COMMENT ON TABLE sessions IS 'to control the user frontend sessions';
COMMENT ON COLUMN sessions.session_id IS 'the internal unique primary index';
COMMENT ON COLUMN sessions.uid IS 'the user session id as get by the frontend';
