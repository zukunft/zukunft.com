CREATE OR REPLACE FUNCTION component_link_insert_log_01558185111
    (_view_id                   bigint,
     _component_link_type_id    smallint,
     _component_id              bigint,
     _user_id                   bigint,
     _change_action_id          smallint,
     _change_table_id           smallint,
     _new_text_from             text,
     _new_text_link             text,
     _new_text_to               text,
     _field_id_user_id          smallint,
     _field_id_order_nbr        smallint,
     _order_nbr                 bigint,
     _field_id_position_type_id smallint,
     _position_old              text,
     _position_type_id_old      smallint,
     _position                  text,
     _position_type_id          smallint,
     _field_id_view_style_id    smallint,
     _view_style_name           text,
     _view_style_id             smallint,
     _field_id_excluded         smallint,
     _excluded                  smallint,
     _field_id_share_type_id    smallint,
     _share_type_id             smallint,
     _field_id_protect_id       smallint,
     _protect_id                smallint) RETURNS bigint AS
$$
DECLARE new_component_link_id bigint;
BEGIN

    INSERT INTO component_links (view_id,component_link_type_id,component_id)
         SELECT _view_id,_component_link_type_id,_component_id
      RETURNING component_link_id INTO new_component_link_id;

    INSERT INTO change_links (user_id, change_action_id, change_table_id, new_text_from, new_text_link, new_text_to, new_from_id, new_link_id,             new_to_id,   row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_new_text_from,_new_text_link,_new_text_to,_view_id,    _component_link_type_id, _component_id,new_component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,  new_value,row_id)
         SELECT         _user_id,_change_action_id,_field_id_user_id,_user_id,  new_component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,    new_value,row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr,new_component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,           old_value,    new_value, old_id,               new_id,          row_id)
         SELECT         _user_id,_change_action_id,_field_id_position_type_id,_position_old,_position, _position_type_id_old,_position_type_id,new_component_link_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,  new_id,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_style_id,_view_style_name,_view_style_id,new_component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,   new_value,row_id)
         SELECT         _user_id,_change_action_id,_field_id_excluded,_excluded, new_component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,        new_value,    row_id)
         SELECT         _user_id,_change_action_id,_field_id_share_type_id,_share_type_id,new_component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,     new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_protect_id,_protect_id,new_component_link_id ;

    UPDATE component_links
       SET user_id          = _user_id,
           order_nbr        = _order_nbr,
           position_type_id = _position_type_id,
           view_style_id    = _view_style_id,
           excluded         = _excluded,
           share_type_id    = _share_type_id,
           protect_id       = _protect_id
    WHERE component_links.component_link_id = new_component_link_id;

    RETURN new_component_link_id;

END
$$ LANGUAGE plpgsql;

PREPARE component_link_insert_log_01558185111_call
        (bigint,smallint,bigint,bigint,smallint,smallint,text,text,text,smallint,smallint,bigint,smallint,text,smallint,text,smallint,smallint,text,smallint,smallint,smallint,smallint,smallint,smallint,smallint) AS
SELECT component_link_insert_log_01558185111
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23, $24, $25, $26);

SELECT component_link_insert_log_01558185111
    (1::bigint,
     2::smallint,
     1::bigint,
     1::bigint,
     1::smallint,
     16::smallint,
     null::text,
     null::text,
     null::text,
     757::smallint,
     48::smallint,
     1::bigint,
     136::smallint,
     'below'::text,
     1::smallint,
     'side'::text,
     2::smallint,
     781::smallint,
     'sm-col-4'::text,
     1::smallint,
     49::smallint,
     1::smallint,
     137::smallint,
     3::smallint,
     138::smallint,
     2::smallint);
