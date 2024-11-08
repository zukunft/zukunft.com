CREATE OR REPLACE FUNCTION view_term_link_insert_log_015505
    (_view_id bigint,
     _view_link_type_id smallint,
     _term_id bigint,
     _user_id bigint,
     _change_action_id smallint,
     _change_table_id smallint,
     _new_text_from text,
     _new_text_link text,
     _new_text_to text,
     _field_id_user_id smallint) RETURNS bigint AS

$$ DECLARE new_view_term_link_id bigint;
BEGIN
    INSERT INTO view_term_links (view_id, view_link_type_id, term_id)
         SELECT                 _view_id,_view_link_type_id,_term_id
      RETURNING view_term_link_id INTO new_view_term_link_id;

    INSERT INTO change_links (user_id, change_action_id, change_table_id, new_text_from, new_text_link, new_text_to, new_from_id,       new_link_id, new_to_id,                row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_new_text_from,_new_text_link,_new_text_to,    _view_id,_view_link_type_id,  _term_id, new_view_term_link_id ;

    INSERT INTO changes (user_id, change_action_id,  change_field_id, new_value,               row_id)
         SELECT         _user_id,_change_action_id,_field_id_user_id,  _user_id,new_view_term_link_id ;

    UPDATE view_term_links
       SET user_id = _user_id
     WHERE view_term_links.view_term_link_id = new_view_term_link_id;

    RETURN new_view_term_link_id;

END $$ LANGUAGE plpgsql;

PREPARE view_term_link_insert_log_015505_call
    (bigint, smallint, bigint, bigint, smallint, smallint, text, text, text, smallint) AS
SELECT view_term_link_insert_log_015505
    ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10);

SELECT view_term_link_insert_log_015505
    (1::bigint,
     1::smallint,
     1::bigint,
     1::bigint,
     1::smallint,
     89::smallint,
     'Start view'::text,
     'default'::text,
     'Mathematics'::text,
     725::smallint);