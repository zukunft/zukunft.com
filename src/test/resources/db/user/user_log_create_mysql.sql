-- --------------------------------------------------------

--
-- table structure for the sessions of the users with the number and the time of their api requests
--

CREATE TABLE IF NOT EXISTS user_log
(
    user_log_id   bigint    NOT NULL COMMENT 'the internal unique primary index',
    user_id       bigint    NOT NULL COMMENT 'the user whose api requests are counted',
    session_start timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'the start time of the first api request of the session',
    session_end   timestamp DEFAULT NULL COMMENT 'the end time of the last api request of the session',
    requests      bigint    NOT NULL COMMENT 'the number of api requests of the session',
    updated       bigint    NOT NULL COMMENT 'the number of api requests of the session that have changed data',
    request_time  bigint    NOT NULL COMMENT 'the sum of the milliseconds of all api requests of the session',
    update_time   bigint    NOT NULL COMMENT 'the sum of the milliseconds of the api requests of the session that have changed data',
    PRIMARY KEY (user_log_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the sessions of the users with the number and the time of their api requests';

--
-- AUTO_INCREMENT for table user_log
--
ALTER TABLE user_log
    MODIFY user_log_id bigint NOT NULL AUTO_INCREMENT;
