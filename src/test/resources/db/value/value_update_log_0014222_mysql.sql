DROP PROCEDURE IF EXISTS value_update_log_0014222;
CREATE PROCEDURE value_update_log_0014222
    (_user_id                bigint,
     _change_action_id       smallint,
     _field_id_source_id     smallint,
     _source_name_old        text,
     _source_id_old          bigint,
     _source_name            text,
     _source_id              bigint,
     _field_id_excluded      smallint,
     _excluded_old           smallint,
     _excluded               smallint,
     _field_id_share_type_id smallint,
     _share_type_id_old      smallint,
     _share_type_id          smallint,
     _field_id_protect_id    smallint,
     _protect_id_old         smallint,
     _protect_id             smallint,
     _group_id               text)
BEGIN

    INSERT INTO changes_norm ( user_id, change_action_id, change_field_id,    old_value,       old_id,        row_id)
         SELECT               _user_id,_change_action_id,_field_id_source_id,_source_name_old,_source_id_old,_group_id ;
    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        old_value,         group_id)
         SELECT                     _user_id,_change_action_id,_field_id_excluded,     _excluded_old,     _group_id ;
    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        old_value,         group_id)
         SELECT                     _user_id,_change_action_id,_field_id_share_type_id,_share_type_id_old,_group_id ;
    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        old_value,         group_id)
         SELECT                     _user_id,_change_action_id,_field_id_protect_id,   _protect_id_old,   _group_id ;

    UPDATE `values`
       SET source_id     = _source_id,
           share_type_id = _share_type_id,
           protect_id    = _protect_id,
           last_update   = Now()
     WHERE group_id = _group_id;

END;

PREPARE value_update_log_0014222_call FROM
    'SELECT value_update_log_0014222 (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT value_update_log_0014222
       (3,
        1,
        2,
        'The International System of Units',
        1,
        null,
        0,
        5,
        1,
        null,
        3,
        3,
        null,
        4,
        2,
        null,
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+');