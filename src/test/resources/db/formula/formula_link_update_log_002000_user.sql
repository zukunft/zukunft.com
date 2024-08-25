CREATE OR REPLACE FUNCTION formula_link_update_log_002000_user
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_order_nbr smallint,
     _order_nbr_old      bigint,
     _order_nbr          bigint,
     _formula_link_id    bigint) RETURNS void AS

$$ BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr_old,_order_nbr,_formula_link_id ;

    UPDATE user_formula_links
       SET order_nbr = _order_nbr
     WHERE formula_link_id = _formula_link_id
       AND user_id = _user_id;

END $$ LANGUAGE plpgsql;

PREPARE formula_link_update_log_002000_user_call
    (bigint,smallint,smallint,bigint,bigint,bigint) AS
SELECT formula_link_update_log_002000_user
    ($1,$2,$3,$4,$5,$6);

SELECT formula_link_update_log_002000_user (
               1::bigint,
               2::smallint,
               700::smallint,
               2::bigint,
               1::bigint,
               1::bigint);