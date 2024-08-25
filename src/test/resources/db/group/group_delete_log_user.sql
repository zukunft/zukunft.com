CREATE OR REPLACE FUNCTION group_delete_log_user
    (_user_id bigint,
     _change_action_id smallint,
     _field_id_group_name smallint,
     _group_name text,
     _group_id text) RETURNS void AS

$$ BEGIN

    INSERT INTO changes_norm (user_id,change_action_id,change_field_id,old_value,row_id)
         SELECT _user_id,_change_action_id,_field_id_group_name,_group_name,_group_id ;

    DELETE FROM user_groups
          WHERE group_id = _group_id
            AND user_id = _user_id;

END $$ LANGUAGE plpgsql;

SELECT group_delete_log_user
    (1::bigint,
     3::smallint,
     320::smallint,
     'Pi'::text,
     '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+'::text);