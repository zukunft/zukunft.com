DROP PROCEDURE IF EXISTS value_update_log_0010000;
CREATE PROCEDURE value_update_log_0010000
    (_group_id text)
BEGIN

    UPDATE `values`
       SET last_update = Now()
     WHERE group_id = _group_id;

END;

PREPARE value_update_log_0010000_call FROM
    'SELECT value_update_log_0010000 (?)';

SELECT value_update_log_0010000
       ('06+0S+0U+10+12+1I+1R+');