-- --------------------------------------------------------

--
-- table structure for the sessions of the users with the number and the time of their api requests
--

CREATE TABLE IF NOT EXISTS user_log
(
    user_log_id   BIGSERIAL PRIMARY KEY,
    user_id       bigint        NOT NULL,
    session_start timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    session_end   timestamp DEFAULT NULL,
    requests      bigint        NOT NULL,
    updated       bigint        NOT NULL,
    request_time  bigint        NOT NULL,
    update_time   bigint        NOT NULL
);

COMMENT ON TABLE user_log IS 'for the sessions of the users with the number and the time of their api requests';
COMMENT ON COLUMN user_log.user_log_id IS 'the internal unique primary index';
COMMENT ON COLUMN user_log.user_id IS 'the user whose api requests are counted';
COMMENT ON COLUMN user_log.session_start IS 'the start time of the first api request of the session';
COMMENT ON COLUMN user_log.session_end IS 'the end time of the last api request of the session';
COMMENT ON COLUMN user_log.requests IS 'the number of api requests of the session';
COMMENT ON COLUMN user_log.updated IS 'the number of api requests of the session that have changed data';
COMMENT ON COLUMN user_log.request_time IS 'the sum of the milliseconds of all api requests of the session';
COMMENT ON COLUMN user_log.update_time IS 'the sum of the milliseconds of the api requests of the session that have changed data';
