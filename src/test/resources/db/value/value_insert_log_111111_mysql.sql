DROP PROCEDURE IF EXISTS value_insert_log_111111;
CREATE PROCEDURE value_insert_log_111111
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_numeric_value  smallint,
     _numeric_value           numeric,
     _group_id                text,
     _field_id_source_id      smallint,
     _source_id               bigint,
     _field_id_excluded       smallint,
     _excluded                smallint,
     _field_id_share_type_id  smallint,
     _share_type_id           smallint,
     _field_id_protect_id     smallint,
     _protect_id              smallint)

BEGIN

    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        new_value,     group_id)
         SELECT                     _user_id,_change_action_id,_field_id_numeric_value,_numeric_value,_group_id ;

    INSERT INTO change_values_norm (user_id, change_action_id, change_field_id,    new_value, group_id)
         SELECT                     _user_id,_change_action_id,_field_id_source_id,_source_id,_group_id ;

    INSERT INTO change_values_norm (user_id, change_action_id, change_field_id,   new_value, group_id)
         SELECT                     _user_id,_change_action_id,_field_id_excluded,_excluded, _group_id ;

    INSERT INTO change_values_norm (user_id, change_action_id, change_field_id,        new_value,     group_id)
         SELECT                     _user_id,_change_action_id,_field_id_share_type_id,_share_type_id,_group_id ;

    INSERT INTO change_values_norm (user_id, change_action_id, change_field_id,     new_value,  group_id)
         SELECT                     _user_id,_change_action_id,_field_id_protect_id,_protect_id,_group_id ;

    INSERT INTO `values` (group_id, user_id, numeric_value, share_type_id, protect_id, last_update)
         SELECT          _group_id,_user_id,_numeric_value,_share_type_id,_protect_id,     Now();

END;

PREPARE value_insert_log_111111_call FROM

    'SELECT value_insert_log_111111
       (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT value_insert_log_111111
       (1,
        1,
        1,
        3.1415926535898,
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+',
        2,
        1,
        5,
        1,
        3,
        3,
        4,
        2);