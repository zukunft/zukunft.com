DROP PROCEDURE IF EXISTS component_link_update_log_00040200000;
CREATE PROCEDURE component_link_update_log_00040200000
    (_user_id                bigint,
     _change_action_id       smallint,
     _field_id_component_id  smallint,
     _to_component_name_old  text,
     _component_id_old       bigint,
     _to_component_name      text,
     _component_id           bigint,
     _component_link_id      bigint,
     _field_id_order_nbr     smallint,
     _order_nbr_old          bigint,
     _order_nbr              bigint)

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       old_value,             new_value,         old_id,           new_id,       row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_id,_to_component_name_old,_to_component_name,_component_id_old,_component_id,_component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr_old,_order_nbr,_component_link_id ;

    UPDATE component_links
       SET component_id = _component_id,
           order_nbr = _order_nbr
     WHERE component_link_id = _component_link_id;

END;

PREPARE component_link_update_log_00040200000_call FROM
    'SELECT component_link_update_log_00040200000
            (?,?,?,?,?,?,?,?,?,?,?)';

SELECT component_link_update_log_00040200000
       (3,
        2,
        756,
        'Word',
        1,
        '',
        0,
        1,
        48,
        1,
        null);
