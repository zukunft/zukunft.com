DROP PROCEDURE IF EXISTS value_insert_log_110000;
CREATE PROCEDURE value_insert_log_110000
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_numeric_value  smallint,
     _numeric_value           numeric,
     _group_id                text)

BEGIN

    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        new_value,     group_id)
         SELECT                     _user_id,_change_action_id,_field_id_numeric_value,_numeric_value,_group_id ;

    INSERT INTO `values` (group_id, user_id, numeric_value, last_update)
         SELECT          _group_id,_user_id,_numeric_value,     Now();

END;

PREPARE value_insert_log_110000_call FROM

    'SELECT value_insert_log_110000
       (?,?, ?, ?, ?)';

SELECT value_insert_log_110000
       (1,
        1,
        1,
        3.1415926535898,
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+');