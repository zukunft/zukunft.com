DROP PROCEDURE IF EXISTS value_delete_log_user;
CREATE PROCEDURE value_delete_log_user
    (_user_id                bigint,
     _change_action_id       smallint,
     _field_id_numeric_value smallint,
     _numeric_value          numeric,
     _group_id               text,
     _source_id              bigint)

BEGIN

    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        old_value,     group_id)
         SELECT                     _user_id,_change_action_id,_field_id_numeric_value,_numeric_value,_group_id ;

    DELETE
      FROM user_values
     WHERE group_id = _group_id
       AND user_id = _user_id
       AND source_id <=> _source_id;

END;

SELECT value_delete_log_user
       (3,
        3,
        6,
        3.1415926535898,
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+',
        null);