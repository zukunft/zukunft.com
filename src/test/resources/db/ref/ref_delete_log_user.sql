CREATE OR REPLACE FUNCTION ref_delete_log_user
    (_user_id bigint,
     _change_action_id smallint,
     _ref_id bigint) RETURNS void AS $$

BEGIN

    INSERT INTO changes (user_id, change_action_id, row_id)
         SELECT         _user_id,_change_action_id,_ref_id ;

    DELETE FROM user_refs
          WHERE ref_id = _ref_id
            AND user_id = _user_id;

END $$ LANGUAGE plpgsql;

SELECT ref_delete_log_user (
               1::bigint,
               3::smallint,
               12::bigint);