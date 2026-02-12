CREATE OR REPLACE FUNCTION sys_log_status_insert_log_1111
    (_status_name             text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_status_name    smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text) RETURNS bigint AS
$$
DECLARE new_sys_log_status_id bigint;
BEGIN

        INSERT INTO sys_log_statuus (status_name)
             SELECT                 _status_name
          RETURNING sys_log_status_id INTO new_sys_log_status_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,    row_id)
             SELECT          _user_id,_change_action_id,_field_id_status_name,_status_name, new_sys_log_status_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,    row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,      new_sys_log_status_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,    row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description,  new_sys_log_status_id ;

             UPDATE sys_log_statuus
                SET code_id     = _code_id,
                    description = _description
              WHERE sys_log_statuus.sys_log_status_id = new_sys_log_status_id;

             RETURN new_sys_log_status_id;

END
$$ LANGUAGE plpgsql;

PREPARE sys_log_status_insert_log_1111_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT sys_log_status_insert_log_1111
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT sys_log_status_insert_log_1111
    ('new'::text,
     1::bigint,
     1::smallint,
     843::smallint,
     844::smallint,
     'new'::text,
     845::smallint,
     'the error has just being logged and no one has yet looked at it'::text);