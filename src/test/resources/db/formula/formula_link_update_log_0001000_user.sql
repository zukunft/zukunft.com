CREATE OR REPLACE FUNCTION formula_link_update_log_0001000_user
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_description smallint,
     _description_old      text,
     _description          text,
     _formula_link_id      bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_formula_link_id ;

    UPDATE user_formula_links
       SET description = _description
     WHERE formula_link_id = _formula_link_id
       AND user_id = _user_id;

END
$$ LANGUAGE plpgsql;

PREPARE formula_link_update_log_0001000_user_call
        (bigint, smallint, smallint, text, text, bigint) AS
SELECT formula_link_update_log_0001000_user
        ($1,$2,$3,$4,$5,$6);

SELECT formula_link_update_log_0001000_user
       (3::bigint,
        2::smallint,
        904::smallint,
        null::text,
        'System Test description for a formula link'::text,
        1::bigint);