CREATE OR REPLACE FUNCTION component_insert_log_011111000000000
    (_component_name             text,
     _user_id                    bigint,
     _change_action_id           smallint,
     _field_id_component_name    smallint,
     _field_id_user_id           smallint,
     _field_id_description       smallint,
     _description                text,
     _field_id_component_type_id smallint,
     _component_type_id          bigint,
     _field_id_code_id           smallint,
     _code_id                    text) RETURNS bigint AS
$$
DECLARE new_component_id bigint;
BEGIN

    INSERT INTO components ( component_name)
         SELECT        _component_name
      RETURNING         component_id INTO new_component_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_name,_component_name, new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,   new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,        row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_type_id,_component_type_id,new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value,        row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,_code_id,new_component_id ;

    UPDATE components
       SET user_id           = _user_id,
           description       = _description,
           component_type_id = _component_type_id,
           code_id           = _code_id
     WHERE components.component_id = new_component_id;

    RETURN new_component_id;

END
$$ LANGUAGE plpgsql;

PREPARE component_insert_log_011111000000000_call
    (text, bigint, smallint, smallint, smallint, smallint, text, smallint, bigint, smallint, text) AS
SELECT component_insert_log_011111000000000
    ($1,$2, $3, $4, $5, $6, $7, $8, $9, $10, $11);