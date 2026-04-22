CREATE OR REPLACE FUNCTION component_update_log_00220040000000000000
    (_user_id                    bigint,
     _change_action_id           smallint,
     _field_id_component_name    smallint,
     _component_name_old         text,
     _component_name             text,
     _component_id               bigint,
     _field_id_description       smallint,
     _description_old            text,
     _description                text,
     _field_id_component_type_id smallint,
     _type_name_old              text,
     _component_type_id_old      smallint,
     _type_name                  text,
     _component_type_id          smallint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_name,_component_name_old,_component_name,_component_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_component_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,            old_value,     new_value, old_id,                new_id,            row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_type_id,_type_name_old,_type_name,_component_type_id_old,_component_type_id,_component_id ;

    UPDATE components
       SET component_name    = _component_name,
           description       = _description,
           component_type_id = _component_type_id
     WHERE component_id = _component_id;

END
$$ LANGUAGE plpgsql;

PREPARE component_update_log_00220040000000000000_call
        (bigint, smallint, smallint, text, text, bigint, smallint, text, text, smallint, text, smallint, text, smallint) AS
SELECT component_update_log_00220040000000000000
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12, $13, $14);

SELECT component_update_log_00220040000000000000
       (3::bigint,
        2::smallint,
        51::smallint,
        'system form field name'::text,
        'System Test View Component Renamed'::text,
        4::bigint,
        52::smallint,
        'the name field in a form'::text,
        null::text,
        53::smallint,
        'word name'::text,
        8::smallint,
        null::text,
        null::smallint);