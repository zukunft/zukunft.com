CREATE OR REPLACE FUNCTION db_cache_type_insert_log_1111
    (_type_name             text,
     _user_id               bigint,
     _change_action_id      smallint,
     _field_id_type_name    smallint,
     _field_id_code_id      smallint,
     _code_id               text,
     _field_id_description  smallint,
     _description           text) RETURNS bigint AS
$$
DECLARE new_type_id bigint;
BEGIN

        INSERT INTO db_cache_types (type_name)
             SELECT                _type_name
          RETURNING type_id INTO new_type_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
             SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name, new_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,     new_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description, new_type_id ;

             UPDATE db_cache_types
                SET code_id     = _code_id,
                    description = _description
              WHERE db_cache_types.type_id = new_type_id;

             RETURN new_type_id;

END
$$ LANGUAGE plpgsql;

PREPARE db_cache_type_insert_log_1111_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT db_cache_type_insert_log_1111
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT db_cache_type_insert_log_1111
    ('system configuration'::text,
     1::bigint,
     1::smallint,
     875::smallint,
     876::smallint,
     'system_config'::text,
     877::smallint,
     'the complete json of the system configuration'::text);