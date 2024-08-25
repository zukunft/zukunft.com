CREATE OR REPLACE FUNCTION formula_link_insert_log_111000_user
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_order_nbr smallint,
     _order_nbr          bigint,
     _formula_link_id    bigint) RETURNS bigint AS

$$ BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr,_formula_link_id ;

    INSERT INTO user_formula_links (formula_link_id,user_id,order_nbr)
         SELECT                    _formula_link_id,_user_id,_order_nbr ;

END $$ LANGUAGE plpgsql;

PREPARE formula_link_insert_log_111000_user_call
    (bigint,smallint,smallint,bigint,bigint) AS
SELECT formula_link_insert_log_111000_user
    ($1,$2,$3,$4,$5);

SELECT formula_link_insert_log_111000_user (
               1::bigint,
               1::smallint,
               700::smallint,
               2::bigint,
               1::bigint);