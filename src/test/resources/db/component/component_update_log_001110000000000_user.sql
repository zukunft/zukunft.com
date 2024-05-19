CREATE OR REPLACE FUNCTION component_update_log_001110000000000_user
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
     _component_type_id_old      smallint,
     _component_type_id          smallint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_name,_component_name_old,_component_name,_component_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_component_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,            old_value,             new_value,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_type_id,_component_type_id_old,_component_type_id,_component_id ;

    UPDATE user_components
       SET component_name    = _component_name,
           description       = _description,
           component_type_id = _component_type_id
     WHERE component_id = _component_id
       AND user_id = _user_id;

END
$$ LANGUAGE plpgsql;

PREPARE component_update_log_001110000000000_user_call
        (bigint,smallint,smallint,text,text,bigint,smallint,text,text,smallint,smallint,smallint) AS
SELECT component_update_log_001110000000000_user
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12);

SELECT component_update_log_001110000000000_user
       (1::bigint,
        2::smallint,
        51::smallint,
        'Word'::text,
        'System Test View Component Renamed'::text,
        1::bigint,
        52::smallint,
        'simply show the word name'::text,
        null::text,
        53::smallint,
        8::smallint,
        null::smallint);