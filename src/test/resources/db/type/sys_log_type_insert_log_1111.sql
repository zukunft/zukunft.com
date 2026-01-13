CREATE OR REPLACE FUNCTION sys_log_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text) RETURNS bigint AS
$$
DECLARE new_sys_log_type_id bigint;
BEGIN

        INSERT INTO sys_log_types (type_name)
             SELECT              _type_name
          RETURNING sys_log_type_id INTO new_sys_log_type_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  new_sys_log_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    new_sys_log_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description,new_sys_log_type_id ;

             UPDATE sys_log_types
                SET code_id     = _code_id,
                    description = _description
              WHERE sys_log_types.sys_log_type_id = new_sys_log_type_id;

             RETURN new_sys_log_type_id;

END
$$ LANGUAGE plpgsql;

PREPARE sys_log_type_insert_log_1111_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT sys_log_type_insert_log_1111
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT sys_log_type_insert_log_1111
    ('Info'::text,
     1::bigint,
     1::smallint,
     839::smallint,
     840::smallint,
     'log_info'::text,
     841::smallint,
     'Information only message for debugging and execution time details'::text);