DROP PROCEDURE IF EXISTS term_view_insert_log_015505;
CREATE PROCEDURE term_view_insert_log_015505
    (_view_id bigint,
     _view_link_type_id smallint,
     _term_id bigint,
     _user_id bigint,
     _change_action_id smallint,
     _change_table_id smallint,
     _new_text_from text,
     _new_text_link text,
     _new_text_to text,
     _field_id_user_id smallint)

BEGIN

    INSERT INTO term_views (view_id, view_link_type_id, term_id)
         SELECT                 _view_id,_view_link_type_id,_term_id ;

    SELECT LAST_INSERT_ID() AS @new_term_view_id;

    INSERT INTO change_links (user_id, change_action_id, change_table_id, new_text_from, new_text_link, new_text_to, new_from_id,       new_link_id, new_to_id,                 row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_new_text_from,_new_text_link,_new_text_to,    _view_id,_view_link_type_id,  _term_id, @new_term_view_id ;

    INSERT INTO changes (user_id, change_action_id,  change_field_id, new_value,               row_id)
         SELECT         _user_id,_change_action_id,_field_id_user_id,  _user_id,@new_term_view_id ;

    UPDATE term_views
       SET user_id = _user_id
     WHERE term_views.term_view_id = @new_term_view_id;

END;

PREPARE term_view_insert_log_015505_call FROM
    'SELECT term_view_insert_log_015505 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT term_view_insert_log_015505 (
               1,
               1,
               1,
               1,
               1,
               89,
               'Start view',
               'default',
               'mathematics',
               725);