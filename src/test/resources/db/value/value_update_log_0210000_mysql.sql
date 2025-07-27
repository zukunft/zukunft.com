DROP PROCEDURE IF EXISTS value_update_log_0210000;
CREATE PROCEDURE value_update_log_0210000
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_numeric_value  smallint,
     _numeric_value_old       numeric,
     _numeric_value           numeric,
     _group_id                text)

BEGIN

    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        old_value,         new_value,     group_id)
         SELECT                      _user_id,_change_action_id,_field_id_numeric_value,_numeric_value_old,_numeric_value,_group_id ;

    UPDATE `values`
       SET numeric_value = _numeric_value,
           last_update = Now()
    WHERE group_id = _group_id;

END;

PREPARE value_update_log_0210000_call FROM

    'SELECT value_update_log_0210000
       (?,?, ?, ?, ?, ?)';

SELECT value_update_log_0210000
       (1,
        1,
        1,
        123.456,
        3.1415926535898,
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+');