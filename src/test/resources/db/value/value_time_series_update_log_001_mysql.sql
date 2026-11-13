DROP PROCEDURE IF EXISTS value_time_series_update_log_001;
CREATE PROCEDURE value_time_series_update_log_001
    (_group_id text)
BEGIN

    UPDATE values_time_series
       SET last_update = Now()
     WHERE group_id = _group_id;

END;

PREPARE value_time_series_update_log_001_call FROM
    'SELECT value_time_series_update_log_001 (?)';

SELECT value_time_series_update_log_001
       ('1FajJ2-4LYK3-8jId-I1A-Yz-/.-Z-9-/+A+a+3s+1Ao+/vLC+//ZSB+ZSahL+');