CREATE OR REPLACE FUNCTION ref_delete_log_user
    (_user_id bigint,
     _change_action_id smallint,
     _change_table_id  smallint,
     _old_text_from    text,
     _old_text_link    text,
     _old_text_to      text,
     _old_from_id      bigint,
     _old_link_id      smallint,
     _ref_id           bigint) RETURNS void AS $$

BEGIN

    INSERT INTO change_links (user_id, change_action_id, change_table_id, old_text_from, old_text_link, old_text_to, old_from_id, old_link_id, row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_old_text_from,_old_text_link,_old_text_to,_old_from_id,_old_link_id,_ref_id ;

    DELETE FROM user_refs
          WHERE ref_id = _ref_id
            AND user_id = _user_id;

END $$ LANGUAGE plpgsql;

SELECT ref_delete_log_user
    (1::bigint,
     3::smallint,
     22::smallint,
     'global warming potential'::text,
     null::text,
     null::text,
     1070::bigint,
     null::smallint,
     12::bigint);