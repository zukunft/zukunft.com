CREATE OR REPLACE FUNCTION view_relation_delete_log
    (_user_id          bigint,
     _change_action_id smallint,
     _change_table_id  smallint,
     _old_text_from    text,
     _old_text_link    text,
     _old_text_to      text,
     _old_from_id      bigint,
     _old_link_id      smallint,
     _old_to_id        bigint,
     _view_relation_id  bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO change_links ( user_id, change_action_id, change_table_id, old_text_from, old_text_link, old_text_to, old_from_id, old_link_id, old_to_id, row_id)
         SELECT               _user_id,_change_action_id,_change_table_id,_old_text_from,_old_text_link,_old_text_to,_old_from_id,_old_link_id,_old_to_id,_view_relation_id ;

    DELETE
      FROM user_view_relations
     WHERE view_relation_id = _view_relation_id
       AND excluded = 1;

    DELETE
      FROM view_relations
     WHERE view_relation_id = _view_relation_id;

END
$$ LANGUAGE plpgsql;

SELECT view_relation_delete_log
       (3::bigint,
        3::smallint,
        108::smallint,
        'word_edit'::text,
        'add components'::text,
        'word_usage'::text,
        3::bigint,
        1::smallint,
        5::bigint,
        1::bigint);