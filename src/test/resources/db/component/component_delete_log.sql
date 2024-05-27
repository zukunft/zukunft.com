CREATE OR REPLACE FUNCTION component_delete_log
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_component_name smallint,
     _component_name          text,
     _component_id            bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_name,_component_name,_component_id ;

    DELETE
      FROM components
     WHERE component_id = _component_id;

END
$$ LANGUAGE plpgsql;

SELECT component_delete_log
       (1::bigint,
        3::smallint,
        51::smallint,
        'Word'::text,
        1::bigint);