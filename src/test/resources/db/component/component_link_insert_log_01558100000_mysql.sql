DROP PROCEDURE IF EXISTS component_link_insert_log_01558100000;
CREATE PROCEDURE component_link_insert_log_01558100000
    (_view_id                bigint,
     _component_link_type_id smallint,
     _component_id           bigint,
     _user_id                bigint,
     _change_action_id       smallint,
     _change_table_id        smallint,
     _new_text_from          text,
     _new_text_link          text,
     _new_text_to            text,
     _field_id_user_id       smallint,
     _field_id_order_nbr     smallint,
     _order_nbr              bigint)
BEGIN

    INSERT INTO component_links (view_id,component_link_type_id,component_id)
         SELECT                 _view_id,_component_link_type_id,_component_id ;

    SELECT LAST_INSERT_ID() AS @new_component_link_id;

    INSERT INTO change_links (user_id, change_action_id, change_table_id, new_text_from, new_text_link, new_text_to, new_from_id, new_link_id,             new_to_id,    row_id)
         SELECT              _user_id,_change_action_id,_change_table_id,_new_text_from,_new_text_link,_new_text_to,_view_id,    _component_link_type_id, _component_id,@new_component_link_id ;

    INSERT INTO changes (user_id,change_action_id,change_field_id,new_value,row_id)
         SELECT         _user_id,_change_action_id,_field_id_user_id,_user_id,@new_component_link_id ;

    INSERT INTO changes (user_id,change_action_id,change_field_id,new_value,row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr,@new_component_link_id ;

    UPDATE component_links
       SET user_id = _user_id,
           order_nbr = _order_nbr
     WHERE component_links.component_link_id = @new_component_link_id;

END;

PREPARE component_link_insert_log_01558100000_call FROM
    'SELECT component_link_insert_log_01558100000 (?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT component_link_insert_log_01558100000
       (1,
        1,
        1,
        1,
        1,
        16,
        'Start view',
        'always',
        'Word',
        757,
        48,
        1);