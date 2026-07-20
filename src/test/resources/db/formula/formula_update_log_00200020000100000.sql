CREATE OR REPLACE FUNCTION formula_update_log_00200020000100000
    (_user_id                  bigint,
     _change_action_id         smallint,
     _field_id_formula_name    smallint,
     _formula_name_old         text,
     _formula_name             text,
     _formula_id               bigint,
     _field_id_formula_type_id smallint,
     _formula_type_id_old      smallint,
     _formula_type_id          smallint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,          old_value,           new_value,       row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_name,   _formula_name_old,   _formula_name,   _formula_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,          old_value,           new_value,       row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_type_id,_formula_type_id_old,_formula_type_id,_formula_id ;

    UPDATE formulas
       SET formula_name    = _formula_name,
           formula_type_id = _formula_type_id,
           last_update     = Now()
     WHERE formula_id = _formula_id;

END
$$ LANGUAGE plpgsql;

PREPARE formula_update_log_00200020000100000_call
        (bigint, smallint, smallint, text, text, bigint, smallint, smallint, smallint) AS
SELECT formula_update_log_00200020000100000
        ($1,$2,$3,$4,$5,$6,$7,$8,$9);

SELECT formula_update_log_00200020000100000
       (3::bigint,
        2::smallint,
        30::smallint,
        'scale hour to sec'::text,
        'System Test Formula Renamed'::text,
        2::bigint,
        31::smallint,
        1::smallint,
        null::smallint);