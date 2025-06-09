DROP PROCEDURE IF EXISTS term_view_delete_log;
CREATE PROCEDURE term_view_delete_log
    (_user_id bigint,
     _change_action_id smallint,
     _change_table_id   smallint,
     _old_text_from     text,
     _old_text_link     text,
     _old_text_to       text,
     _old_from_id       bigint,
     _old_link_id       smallint,
     _old_to_id         bigint,
     _term_view_id bigint)

BEGIN

    INSERT INTO change_links (user_id, change_action_id, change_table_id, old_text_from, old_text_link, old_text_to, old_from_id, old_link_id, old_to_id, row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_old_text_from,_old_text_link,_old_text_to,_old_from_id,_old_link_id,_old_to_id,_term_view_id ;

    DELETE FROM user_term_views
          WHERE term_view_id = _term_view_id
            AND excluded = 1;

    DELETE FROM term_views
          WHERE term_view_id = _term_view_id;

END;

SELECT term_view_delete_log
    (1,
     3,
     89,
     'Start view',
     'default',
     'mathematics',
     1,
     1,
     1,
     0);