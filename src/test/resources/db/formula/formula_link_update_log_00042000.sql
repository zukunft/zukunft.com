CREATE OR REPLACE FUNCTION formula_link_update_log_00042000
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_phrase_id smallint,
     _to_phrase_name_old text,
     _phrase_id_old      bigint,
     _to_phrase_name     text,
     _phrase_id          bigint,
     _formula_link_id    bigint,
     _field_id_order_nbr smallint,
     _order_nbr_old      bigint,
     _order_nbr          bigint) RETURNS void AS

$$ BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,    old_value,          new_value,      old_id,        new_id,    row_id)
         SELECT         _user_id,_change_action_id,_field_id_phrase_id,_to_phrase_name_old,_to_phrase_name,_phrase_id_old,_phrase_id,_formula_link_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,    old_value,          new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr_old,     _order_nbr,_formula_link_id ;

    UPDATE formula_links
       SET phrase_id = _phrase_id,
           order_nbr = _order_nbr
     WHERE formula_link_id = _formula_link_id;

END $$ LANGUAGE plpgsql;

PREPARE formula_link_update_log_00042000_call
    (bigint, smallint, smallint, text, bigint, text, bigint, bigint, smallint, bigint, bigint) AS
SELECT formula_link_update_log_00042000
    ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11);

SELECT formula_link_update_log_00042000 (
               3::bigint,
               2::smallint,
               702::smallint,
               'minute'::text,
               103::bigint,
               null::text,
               0::bigint,
               1::bigint,
               700::smallint,
               2::bigint,
               null::bigint);