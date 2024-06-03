DROP PROCEDURE IF EXISTS ref_delete_log_user;
CREATE PROCEDURE ref_delete_log_user
    (_user_id bigint,
     _change_action_id smallint,
     _ref_id bigint)

BEGIN

    INSERT INTO changes (user_id, change_action_id, row_id)
         SELECT         _user_id,_change_action_id,_ref_id ;

    DELETE FROM user_refs
          WHERE ref_id = _ref_id
            AND user_id = _user_id;

END;

SELECT ref_delete_log_user (
               1,
               3,
               12);