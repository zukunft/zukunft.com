DROP PROCEDURE IF EXISTS view_term_link_delete_log;
CREATE PROCEDURE view_term_link_delete_log
    (_user_id bigint,
     _change_action_id smallint,
     _view_term_link_id bigint)

BEGIN

    INSERT INTO changes (user_id, change_action_id, row_id)
         SELECT         _user_id,_change_action_id,_view_term_link_id ;

    DELETE FROM view_term_links
          WHERE view_term_link_id = _view_term_link_id;

END;

SELECT view_term_link_delete_log
    (1,
     3,
     0);