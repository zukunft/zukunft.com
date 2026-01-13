CREATE OR REPLACE FUNCTION view_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text) RETURNS bigint AS
$$
DECLARE new_view_type_id bigint;
BEGIN

        INSERT INTO view_types (type_name)
             SELECT              _type_name
          RETURNING view_type_id INTO new_view_type_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  new_view_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    new_view_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description,new_view_type_id ;

             UPDATE view_types
                SET code_id     = _code_id,
                    description = _description
              WHERE view_types.view_type_id = new_view_type_id;

             RETURN new_view_type_id;

END
$$ LANGUAGE plpgsql;

PREPARE view_type_insert_log_1111_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT view_type_insert_log_1111
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT view_type_insert_log_1111
    ('standard'::text,
     1::bigint,
     1::smallint,
     888::smallint,
     889::smallint,
     'default'::text,
     890::smallint,
     'the base display mask without additional functionalities'::text);