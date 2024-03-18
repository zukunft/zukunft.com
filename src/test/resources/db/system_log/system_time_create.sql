-- --------------------------------------------------------

--
-- table structure for system execution time tracking
--

CREATE TABLE IF NOT EXISTS system_times
(
    system_time_id BIGSERIAL PRIMARY KEY,
    start_time          timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    end_time            timestamp DEFAULT NULL,
    system_time_type_id bigint        NOT NULL,
    milliseconds        bigint        NOT NULL
);

COMMENT ON TABLE system_times IS 'for system execution time tracking';
COMMENT ON COLUMN system_times.system_time_id IS 'the internal unique primary index';
COMMENT ON COLUMN system_times.start_time IS 'start time of the monitoring period';
COMMENT ON COLUMN system_times.end_time IS 'end time of the monitoring period';
COMMENT ON COLUMN system_times.system_time_type_id IS 'the area of the execution time e.g. db write';
COMMENT ON COLUMN system_times.milliseconds IS 'the execution time in milliseconds';
