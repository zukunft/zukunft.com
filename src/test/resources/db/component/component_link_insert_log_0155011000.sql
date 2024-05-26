CREATE OR REPLACE FUNCTION component_link_insert_log_0155011000
    (_view_id            bigint,
     _position_type_id   smallint,
     _component_id       bigint,
     _user_id            bigint,
     _change_action_id   smallint,
     _change_table_id    smallint,
     _new_text_from      text,
     _new_text_link      text,
     _new_text_to        text,
     _field_id_user_id   smallint,
     _field_id_order_nbr smallint,
     _order_nbr          bigint) RETURNS bigint AS
$$
DECLARE new_component_link_id bigint;
BEGIN

    INSERT INTO component_links (view_id,position_type_id,component_id)
         SELECT _view_id,_position_type_id,_component_id
      RETURNING component_link_id INTO new_component_link_id;

    INSERT INTO change_links (user_id, change_action_id, change_table_id, new_text_from, new_text_link, new_text_to, new_from_id, new_to_id,   row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_new_text_from,_new_text_link,_new_text_to,_view_id,    _component_id,new_component_link_id ;

    INSERT INTO changes (user_id,change_action_id,change_field_id,new_value,row_id)
         SELECT         _user_id,_change_action_id,_field_id_user_id,_user_id,new_component_link_id ;

    INSERT INTO changes (user_id,change_action_id,change_field_id,new_value,row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr,new_component_link_id ;

    UPDATE component_links
       SET user_id = _user_id,
           order_nbr = _order_nbr
     WHERE component_links.component_link_id = new_component_link_id;

    RETURN new_component_link_id;

END
$$ LANGUAGE plpgsql;

PREPARE component_link_insert_log_0155011000_call
    (bigint,smallint,bigint,bigint,smallint,smallint,text,text,text,smallint,smallint,bigint) AS
SELECT component_link_insert_log_0155011000
    ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12);

SELECT component_link_insert_log_0155011000
    (1::bigint,
     null::smallint,
     1::bigint,
     1::bigint,
     1::smallint,
     16::smallint,
     'Word'::text,
     null::text,
     'Word'::text,
     757::smallint,
     48::smallint,
     1::bigint);