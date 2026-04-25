CREATE OR REPLACE FUNCTION value_update_log_8010000
    (_user_id           bigint,
     _change_action_id  smallint,
     _field_id_user_id  smallint,
     _user_name_old     text,
     _user_id_old       bigint,
     _user_name         text,
     _group_id          text) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,  old_value,     new_value, old_id,      new_id,  group_id)
         SELECT                     _user_id,_change_action_id,_field_id_user_id,_user_name_old,_user_name,_user_id_old,_user_id,_group_id ;

    UPDATE values
       SET user_id = _user_id,
           last_update = Now()
    WHERE group_id = _group_id;


END
$$ LANGUAGE plpgsql;

PREPARE value_update_log_8010000_call
        (bigint, smallint, smallint, text, bigint, text, text) AS
    SELECT value_update_log_8010000
        ($1, $2, $3, $4, $5, $6, $7);

SELECT value_update_log_8010000
       (4::bigint,
        1::smallint,
        370::smallint,
        'zukunft.com system test'::text,
        3::bigint,
        'zukunft.com system test partner'::text,
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+'::text);
