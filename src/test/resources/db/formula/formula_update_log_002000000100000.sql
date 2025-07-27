CREATE OR REPLACE FUNCTION formula_update_log_002000000100000
    (_user_id bigint,
     _change_action_id smallint,
     _field_id_formula_name smallint,
     _formula_name_old text,
     _formula_name text,
     _formula_id bigint) RETURNS void AS

$$ BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,       old_value,        new_value,    row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_name,_formula_name_old,_formula_name,_formula_id ;

    UPDATE formulas
       SET formula_name = _formula_name,
           last_update = Now()
     WHERE formula_id = _formula_id;

END $$ LANGUAGE plpgsql;

PREPARE formula_update_log_002000000100000_call
    (bigint,smallint,smallint,text,text,bigint) AS
SELECT formula_update_log_002000000100000
    ($1,$2,$3,$4,$5,$6);

SELECT formula_update_log_002000000100000
    (1::bigint,
     2::smallint,
     30::smallint,
     '"one" = "millions" * 1000000'::text,
     'System Test Formula Renamed'::text,
     1::bigint);