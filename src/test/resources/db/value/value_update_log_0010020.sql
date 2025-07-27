CREATE OR REPLACE FUNCTION value_update_log_0010020
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_share_type_id  smallint,
     _share_type_id_old       smallint,
     _share_type_id           smallint,
     _group_id                text) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        old_value,     group_id)
         SELECT                      _user_id,_change_action_id,_field_id_share_type_id,_share_type_id_old,_group_id ;

    UPDATE values
       SET share_type_id = _share_type_id,
           last_update = Now()
     WHERE group_id = _group_id;

END
$$ LANGUAGE plpgsql;

PREPARE value_update_log_0010020_call
        (bigint, smallint, smallint, smallint, smallint, text) AS
    SELECT value_update_log_0010020
        ($1,$2, $3, $4, $5, $6);

SELECT value_update_log_0010020
       (1::bigint,
        1::smallint,
        3::smallint,
        3::smallint,
        null::smallint,
        '....0A+....0R+....0T+....0n+....0p+....26+....2D+......+......+......+......+......+......+......+......+......+'::text);
