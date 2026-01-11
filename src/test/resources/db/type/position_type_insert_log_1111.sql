CREATE OR REPLACE FUNCTION position_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text) RETURNS bigint AS
$$
DECLARE new_position_type_id bigint;
BEGIN

        INSERT INTO position_types (type_name)
             SELECT              _type_name
          RETURNING position_type_id INTO new_position_type_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  new_position_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    new_position_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description,new_position_type_id ;

             UPDATE position_types
                SET code_id     = _code_id,
                    description = _description
              WHERE position_types.position_type_id = new_position_type_id;

             RETURN new_position_type_id;

END
$$ LANGUAGE plpgsql;

PREPARE position_type_insert_log_1111_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT position_type_insert_log_1111
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT position_type_insert_log_1111
    ('below'::text,
     1::bigint,
     1::smallint,
     764::smallint,
     765::smallint,
     'below'::text,
     766::smallint,
     'below the previous entry'::text);