-- --------------------------------------------------------

--
-- table structure for system execution time tracking
--

CREATE TABLE IF NOT EXISTS system_times
(
    system_time_id bigint NOT NULL COMMENT 'the internal unique primary index',
    start_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'start time of the monitoring period',
    end_time timestamp DEFAULT NULL COMMENT 'end time of the monitoring period',
    system_time_type_id bigint NOT NULL COMMENT 'the area of the execution time e.g. db write',
    milliseconds bigint NOT NULL COMMENT 'the execution time in milliseconds'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for system execution time tracking';

--
-- AUTO_INCREMENT for table system_times
--
ALTER TABLE system_times
    MODIFY system_time_id int(11) NOT NULL AUTO_INCREMENT;
