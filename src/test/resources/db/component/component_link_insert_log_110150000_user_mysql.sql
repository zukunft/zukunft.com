DROP PROCEDURE IF EXISTS component_link_insert_log_110150000_user;
CREATE PROCEDURE component_link_insert_log_110150000_user
    (_user_id                   bigint,
     _change_action_id          smallint,
     _field_id_order_nbr        smallint,
     _order_nbr                 bigint,
     _component_link_id         bigint,
     _field_id_position_type_id smallint,
     _position                  text,
     _position_type_id          smallint)
BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,           new_value,                   row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,       _order_nbr,                  _component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,           new_value, new_id,           row_id)
         SELECT         _user_id,_change_action_id,_field_id_position_type_id,_position, _position_type_id,_component_link_id ;

    INSERT INTO user_component_links (component_link_id, user_id, order_nbr, position_type_id)
         SELECT                      _component_link_id,_user_id,_order_nbr,_position_type_id ;

END;

PREPARE component_link_insert_log_110150000_user_call FROM
   'SELECT component_link_insert_log_110150000_user
           (?,?,?,?,?,?,?,?)';

SELECT component_link_insert_log_110150000_user
       (3,
        1,
        48,
        1,
        1,
        136,
        'below',
        1);
