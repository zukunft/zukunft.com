DROP PROCEDURE IF EXISTS sys_log_update_log_10000010128;
CREATE PROCEDURE sys_log_update_log_10000010128
    (_user_id                      bigint,
     _change_action_id             smallint,
     _field_id_sys_log_update_time smallint,
     _sys_log_update_time_old      timestamp,
     _sys_log_update_time          timestamp,
     _sys_log_id                   bigint,
     _field_id_sys_log_description smallint,
     _sys_log_description_old      text,
     _sys_log_description          text,
     _field_id_solver_id           smallint,
     _solver_id_old                bigint,
     _solver_id                    bigint,
     _field_id_sys_log_status_id   smallint,
     _status_name_old              text,
     _sys_log_status_id_old        smallint,
     _status_name                  text,
     _sys_log_status_id            smallint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,              old_value,               new_value,           row_id)
         SELECT          _user_id,_change_action_id,_field_id_sys_log_update_time,_sys_log_update_time_old,_sys_log_update_time,_sys_log_id ;

    INSERT INTO changes ( user_id,change_action_id,  change_field_id,              old_value,               new_value,           row_id)
         SELECT          _user_id,_change_action_id,_field_id_sys_log_description,_sys_log_description_old,_sys_log_description,_sys_log_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,              old_value,               new_value,           row_id)
         SELECT          _user_id,_change_action_id,_field_id_solver_id,          _solver_id_old,          _solver_id,          _sys_log_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            old_value,     new_value,     old_id,                new_id,            row_id)
         SELECT          _user_id,_change_action_id,_field_id_sys_log_status_id,_status_name_old,_status_name,_sys_log_status_id_old,_sys_log_status_id,_sys_log_id ;

    UPDATE sys_log
       SET sys_log_update_time = _sys_log_update_time,
           sys_log_description = _sys_log_description,
           solver_id           = _solver_id,
           sys_log_status_id   = _sys_log_status_id
     WHERE sys_log_id = _sys_log_id;

END;

PREPARE sys_log_update_log_10000010128_call FROM
    'SELECT sys_log_update_log_10000010128 (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT sys_log_update_log_10000010128
    (1,
     1,
     null,
     null,
     2023-01-04 09:12:34,
     2,
     205,
     null,
     'the error has been replicated and the fix will be deployed',
     208,
     2,
     1,
     209,
     'assigned',
     2,
     'resolved',
     3);