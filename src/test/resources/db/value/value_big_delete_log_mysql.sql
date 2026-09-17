DROP PROCEDURE IF EXISTS value_big_delete_log;
CREATE PROCEDURE value_big_delete_log
    (_user_id                bigint,
     _change_action_id       smallint,
     _field_id_numeric_value smallint,
     _numeric_value          numeric,
     _group_id               text)

BEGIN

    INSERT INTO change_values_big ( user_id, change_action_id, change_field_id,        old_value,     group_id)
         SELECT                    _user_id,_change_action_id,_field_id_numeric_value,_numeric_value,_group_id ;

    DELETE
      FROM values_big
     WHERE group_id = _group_id;

END;

SELECT value_big_delete_log
       (3,
        3,
        1,
        3.1415926535898,
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+.uraWl+');