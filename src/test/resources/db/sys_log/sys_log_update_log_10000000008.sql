CREATE OR REPLACE FUNCTION sys_log_update_log_10000000008
    (_user_id                    bigint,
     _change_action_id           smallint,
     _field_id_sys_log_status_id smallint,
     _status_name_old              text,
     _sys_log_status_id_old      smallint,
     _status_name                  text,
     _sys_log_status_id          smallint,
     _sys_log_id                 bigint) RETURNS void AS

$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            old_value,       new_value,   old_id,                new_id,            row_id)
         SELECT          _user_id,_change_action_id,_field_id_sys_log_status_id,_status_name_old,_status_name,_sys_log_status_id_old,_sys_log_status_id,_sys_log_id ;

    UPDATE sys_log
       SET sys_log_status_id = _sys_log_status_id
     WHERE sys_log_id = _sys_log_id;

END
$$ LANGUAGE plpgsql;

PREPARE sys_log_update_log_10000000008_call
    (bigint, smallint, smallint, text, smallint, text, smallint, bigint) AS
SELECT sys_log_update_log_10000000008
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT sys_log_update_log_10000000008
    (1::bigint,
     1::smallint,
     209::smallint,
     'resolved'::text,
     3::smallint,
     'closed'::text,
     4::smallint,
     2::bigint);