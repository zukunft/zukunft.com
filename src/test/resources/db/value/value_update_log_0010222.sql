CREATE OR REPLACE FUNCTION value_update_log_0010222
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_excluded       smallint,
     _excluded_old            smallint,
     _excluded                smallint,
     _field_id_share_type_id  smallint,
     _share_type_id_old       smallint,
     _share_type_id           smallint,
     _field_id_protect_id     smallint,
     _protect_id_old          smallint,
     _protect_id              smallint,
     _group_id                text) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        old_value,     new_value, group_id)
         SELECT                      _user_id,_change_action_id,_field_id_excluded,_excluded_old,_excluded,_group_id ;

    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        old_value,     group_id)
         SELECT                      _user_id,_change_action_id,_field_id_share_type_id,_share_type_id_old,_group_id ;

    INSERT INTO change_values_norm ( user_id,change_action_id,change_field_id,old_value,group_id)
         SELECT                      _user_id,_change_action_id,_field_id_protect_id,_protect_id_old,_group_id ;

    UPDATE values
       SET share_type_id = _share_type_id,
           protect_id = _protect_id,
           last_update = Now()
     WHERE group_id = _group_id;

END
$$ LANGUAGE plpgsql;

PREPARE value_update_log_0010222_call
        (bigint, smallint, smallint, smallint, smallint, smallint, smallint, smallint, smallint, smallint, smallint, text) AS
    SELECT value_update_log_0010222
        ($1,$2, $3, $4, $5, $6,$7,$8,$9,$10,$11,$12);

SELECT value_update_log_0010222
       (1::bigint,
        1::smallint,
        5::smallint,
        1::smallint,
        0::smallint,
        3::smallint,
        3::smallint,
        null::smallint,
        4::smallint,
        2::smallint,
        null::smallint,
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+'::text);
