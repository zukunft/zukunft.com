DROP PROCEDURE IF EXISTS sys_log_update_log_10000000008;
CREATE PROCEDURE sys_log_update_log_10000000008
(_user_id                    bigint,
 _change_action_id           smallint,
 _field_id_sys_log_status_id smallint,
 _status_name_old              text,
 _sys_log_status_id_old      smallint,
 _status_name                  text,
 _sys_log_status_id          smallint,
 _sys_log_id                 bigint)

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            old_value,       new_value,   old_id,                new_id,            row_id)
         SELECT          _user_id,_change_action_id,_field_id_sys_log_status_id,_status_name_old,_status_name,_sys_log_status_id_old,_sys_log_status_id,_sys_log_id ;


    UPDATE sys_log
       SET sys_log_status_id = _sys_log_status_id
     WHERE sys_log_id = _sys_log_id;

END;

PREPARE sys_log_update_log_10000000008_call FROM
    'SELECT sys_log_update_log_10000000008 (?,?,?,?,?,?,?,?)';

SELECT sys_log_update_log_10000000008
       (1,
        1,
        927,
        'resolved',
        3,
        'closed',
        4,
        2);
