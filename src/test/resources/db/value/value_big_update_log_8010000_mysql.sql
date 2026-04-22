DROP PROCEDURE IF EXISTS value_big_update_log_8010000;
CREATE PROCEDURE value_big_update_log_8010000
    (_user_id           bigint,
     _change_action_id  smallint,
     _field_id_user_id  smallint,
     _user_name_old     text,
     _user_id_old       bigint,
     _user_name         text,
     _group_id          text)

BEGIN

    INSERT INTO change_values_big ( user_id, change_action_id, change_field_id,  old_value,     new_value, old_id,      new_id,  group_id)
         SELECT                    _user_id,_change_action_id,_field_id_user_id,_user_name_old,_user_name,_user_id_old,_user_id,_group_id ;

    UPDATE values_big
       SET user_id = _user_id,
           last_update = Now()
     WHERE group_id = _group_id;

END;

PREPARE value_big_update_log_8010000_call FROM

    'SELECT value_big_update_log_8010000
       (?, ?, ?, ?, ?, ?, ?)';

SELECT value_big_update_log_8010000
       (4,
        1,
        370,
        'zukunft.com system test',
        3,
        'zukunft.com system test partner',
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+.uraWl+');