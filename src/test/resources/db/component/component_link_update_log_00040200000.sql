CREATE OR REPLACE FUNCTION component_link_update_log_00040200000
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
     _order_nbr              bigint) RETURNS void AS

$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       old_value,             new_value,         old_id,           new_id,       row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_id,_to_component_name_old,_to_component_name,_component_id_old,_component_id,_component_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_order_nbr,_order_nbr_old,_order_nbr,_component_link_id ;

    UPDATE component_links
       SET component_id = _component_id,
           order_nbr    = _order_nbr
     WHERE component_link_id = _component_link_id;

END
$$ LANGUAGE plpgsql;

PREPARE component_link_update_log_00040200000_call
        (bigint, smallint, smallint, text, bigint, text, bigint, bigint, smallint, bigint, bigint) AS
SELECT component_link_update_log_00040200000
        ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11);

SELECT component_link_update_log_00040200000
       (3::bigint,
        2::smallint,
        756::smallint,
        'Word'::text,
        1::bigint,
        ''::text,
        0::bigint,
        1::bigint,
        48::smallint,
        1::bigint,
        null::bigint);
