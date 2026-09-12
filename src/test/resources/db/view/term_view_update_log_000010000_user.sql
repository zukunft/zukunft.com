CREATE OR REPLACE FUNCTION term_view_update_log_000010000_user
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_order_nbr smallint,
     _order_nbr_old      bigint,
     _order_nbr          bigint,
     _term_view_id       bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_order_nbr,_order_nbr_old,_order_nbr,_term_view_id ;

    UPDATE user_term_views
       SET order_nbr = _order_nbr
     WHERE term_view_id = _term_view_id
       AND user_id = _user_id;

END
$$ LANGUAGE plpgsql;

PREPARE term_view_update_log_000010000_user_call
        (bigint, smallint, smallint, bigint, bigint, bigint) AS
SELECT term_view_update_log_000010000_user
        ($1,$2,$3,$4,$5,$6);

SELECT term_view_update_log_000010000_user
       (3::bigint,
        2::smallint,
        900::smallint,
        null::bigint,
        1::bigint,
        0::bigint);