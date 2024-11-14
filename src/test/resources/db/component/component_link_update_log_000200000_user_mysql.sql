DROP PROCEDURE IF EXISTS component_link_update_log_000200000_user;
CREATE PROCEDURE component_link_update_log_000200000_user
    (_user_id                bigint,
     _change_action_id       smallint,
     _field_id_order_nbr     smallint,
     _order_nbr_old          bigint,
     _order_nbr              bigint,
     _component_link_id      bigint)

BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr_old,_order_nbr,_component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr_old,_order_nbr,_component_link_id ;

    UPDATE user_component_links
       SET order_nbr = _order_nbr
     WHERE component_link_id = _component_link_id
       AND user_id = _user_id;

END;

PREPARE component_link_update_log_000200000_user_call FROM
    'SELECT component_link_update_log_000200000_user
            (?,?,?,?,?,?)';

SELECT component_link_update_log_000200000_user
       (1,
        2,
        48,
        1,
        2,
        1);
