DROP PROCEDURE IF EXISTS view_term_link_delete_log_user;
CREATE PROCEDURE view_term_link_delete_log_user
    (_user_id bigint,
     _change_action_id smallint,
     _view_term_link_id bigint)

BEGIN

    INSERT INTO changes (user_id, change_action_id, row_id)
         SELECT         _user_id,_change_action_id,_view_term_link_id ;

    DELETE FROM user_view_term_links
          WHERE view_term_link_id = _view_term_link_id
            AND user_id = _user_id;

END;

SELECT view_term_link_delete_log_user
    (1,
     3,
     0);