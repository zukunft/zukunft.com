DROP PROCEDURE IF EXISTS formula_link_update_log_002000_user;
CREATE PROCEDURE formula_link_update_log_002000_user
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_order_nbr smallint,
     _order_nbr_old      bigint,
     _order_nbr          bigint,
     _formula_link_id    bigint)

BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr_old,_order_nbr,_formula_link_id ;

    UPDATE user_formula_links
       SET order_nbr = _order_nbr
     WHERE formula_link_id = _formula_link_id
       AND user_id = _user_id;

END;

PREPARE formula_link_update_log_002000_user_call FROM
    'SELECT formula_link_update_log_002000_user (?,?,?,?,?,?)';

SELECT formula_link_update_log_002000_user (
               1,
               2,
               700,
               2,
               1,
               1);