CREATE OR REPLACE FUNCTION component_insert_log_111110000000000_user
    (_user_id                    bigint,
     _change_action_id           smallint,
     _field_id_component_name    smallint,
     _component_name             text,
     _component_id               bigint,
     _field_id_description       smallint,
     _description                text,
     _field_id_component_type_id smallint,
     _component_type_id          smallint) RETURNS bigint AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_name,_component_name,_component_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,_component_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_type_id,_component_type_id,_component_id ;

    INSERT INTO user_components
                (component_id, user_id, component_name, description, component_type_id)
         SELECT _component_id,_user_id,_component_name,_description,_component_type_id ;

END
$$ LANGUAGE plpgsql;

PREPARE component_insert_log_111110000000000_user_call
        (bigint, smallint, smallint, text, bigint, smallint, text, smallint, smallint) AS
    SELECT component_insert_log_111110000000000_user
        ($1,$2,$3,$4,$5,$6,$7,$8,$9);

SELECT component_insert_log_111110000000000_user (
               1::bigint,
               1::smallint,
               51::smallint,
               'Word'::text,
               1::bigint,
               52::smallint,
               'simply show the word name'::text,
               53::smallint,
               8::smallint);