CREATE OR REPLACE FUNCTION component_link_insert_log_11010100_user
    (_user_id                bigint,
     _change_action_id       smallint,
     _change_table_id        smallint,
     _old_text_from          text,
     _old_text_link          text,
     _old_text_to            text,
     _new_text_from          text,
     _new_text_link          text,
     _new_text_to            text,
     _old_from_id            bigint,
     _old_link_id            smallint,
     _old_to_id              bigint,
     _new_from_id            bigint,
     _new_link_id            smallint,
     _new_to_id              bigint,
     _field_id_order_nbr     smallint,
     _order_nbr              bigint,
     _component_link_id      bigint,
     _field_id_excluded      smallint,
     _excluded               smallint) RETURNS bigint AS
$$
BEGIN

    INSERT INTO change_links (user_id, change_action_id, change_table_id, old_text_from, old_text_link, old_text_to, new_text_from, new_text_link, new_text_to, old_from_id, old_link_id, old_to_id, new_from_id, new_link_id, new_to_id, row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_old_text_from,_old_text_link,_old_text_to,_new_text_from,_new_text_link,_new_text_to,_old_from_id,_old_link_id,_old_to_id,_new_from_id,_new_link_id,_new_to_id,_component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr,_component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,   new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_excluded,_excluded, _component_link_id ;

    INSERT INTO user_component_links (component_link_id, user_id, order_nbr, excluded)
         SELECT                      _component_link_id,_user_id,_order_nbr,_excluded ;

END
$$ LANGUAGE plpgsql;

PREPARE component_link_insert_log_11010100_user_call
    (bigint,smallint,smallint,text,text,text,text,text,text,bigint,smallint,bigint,bigint,smallint,bigint,smallint,bigint,bigint,smallint,smallint) AS
SELECT component_link_insert_log_11010100_user
    ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20);

SELECT component_link_insert_log_11010100_user
    (1::bigint,
     1::smallint,
     16::smallint,
     'Start view'::text,
     null::text,
     'Word'::text,
     null::text,
     null::text,
     null::text,
     1::bigint,
     null::smallint,
     1::bigint,
     null::bigint,
     null::smallint,
     null::bigint,
     48::smallint,
     1::bigint,
     1::bigint,
     49::smallint,
     1::smallint);
