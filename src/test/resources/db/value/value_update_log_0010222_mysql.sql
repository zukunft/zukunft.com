DROP PROCEDURE IF EXISTS value_update_log_0010222;
CREATE PROCEDURE value_update_log_0010222
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
     _group_id                text)

BEGIN

    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        old_value,     new_value, group_id)
         SELECT                      _user_id,_change_action_id,_field_id_excluded,_excluded_old,_excluded,_group_id ;

    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        old_value,     group_id)
         SELECT                      _user_id,_change_action_id,_field_id_share_type_id,_share_type_id_old,_group_id ;

    INSERT INTO change_values_norm ( user_id,change_action_id,change_field_id,old_value,group_id)
         SELECT                      _user_id,_change_action_id,_field_id_protect_id,_protect_id_old,_group_id ;

    UPDATE `values`
       SET share_type_id = _share_type_id,
           protect_id = _protect_id,
           last_update = Now()
    WHERE group_id = _group_id;

END;

PREPARE value_update_log_0010222_call FROM

    'SELECT value_update_log_0010222
       (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT value_update_log_0010222
       (1,
        1,
        5,
        1,
        0,
        3,
        3,
        null,
        4,
        2,
        null,
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+');