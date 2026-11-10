DROP PROCEDURE IF EXISTS value_time_series_insert_log_01_user;
CREATE PROCEDURE value_time_series_insert_log_01_user
    (_group_id text,
     _user_id  bigint)
BEGIN

    INSERT INTO user_values_time_series ( group_id, user_id,last_update)
         SELECT                          _group_id,_user_id,Now() ;

END;

PREPARE value_time_series_insert_log_01_user_call FROM
    'SELECT value_time_series_insert_log_01_user (?,?)';

SELECT value_time_series_insert_log_01_user
       ('1FajJ2-4LYK3-8jId-I1A-Yz-/.-Z-9-/+A+a+3s+1Ao+/vLC+//ZSB+ZSahL+',
        3);