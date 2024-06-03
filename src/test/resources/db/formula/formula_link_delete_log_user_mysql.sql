DROP PROCEDURE IF EXISTS formula_link_delete_log_user;
CREATE PROCEDURE formula_link_delete_log_user
    (_user_id bigint,
     _change_action_id smallint,
     _formula_link_id bigint)

BEGIN

    INSERT INTO changes (user_id, change_action_id, row_id)
         SELECT         _user_id,_change_action_id,_formula_link_id ;

    DELETE FROM user_formula_links
          WHERE formula_link_id = _formula_link_id
            AND user_id = _user_id;

END;

SELECT formula_link_delete_log_user (
               1,
               3,
               1);