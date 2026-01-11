CREATE OR REPLACE FUNCTION component_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text) RETURNS bigint AS
$$
DECLARE new_component_type_id bigint;
BEGIN

        INSERT INTO component_types (type_name)
             SELECT              _type_name
          RETURNING component_type_id INTO new_component_type_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  new_component_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    new_component_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description,new_component_type_id ;

             UPDATE component_types
                SET code_id     = _code_id,
                    description = _description
              WHERE component_types.component_type_id = new_component_type_id;

             RETURN new_component_type_id;

END
$$ LANGUAGE plpgsql;

PREPARE component_type_insert_log_1111_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT component_type_insert_log_1111
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT component_type_insert_log_1111
    ('spreadsheet'::text,
     1::bigint,
     1::smallint,
     747::smallint,
     748::smallint,
     'calc_sheet'::text,
     749::smallint,
     'changeable spreadsheet with words,number and formulas that allow changes'::text);