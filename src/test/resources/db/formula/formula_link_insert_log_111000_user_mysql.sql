DROP PROCEDURE IF EXISTS formula_link_insert_log_111000_user;
CREATE PROCEDURE formula_link_insert_log_111000_user
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_order_nbr smallint,
     _order_nbr          bigint,
     _formula_link_id    bigint)

BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr,_formula_link_id ;

    INSERT INTO user_formula_links (formula_link_id, user_id, order_nbr)
         SELECT                    _formula_link_id,_user_id,_order_nbr ;

END;

PREPARE formula_link_insert_log_111000_user_call FROM
    'SELECT formula_link_insert_log_111000_user (?,?,?,?,?)';

SELECT formula_link_insert_log_111000_user (
               1,
               1,
               700,
               2,
               1);