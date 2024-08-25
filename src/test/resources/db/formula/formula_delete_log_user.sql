CREATE OR REPLACE FUNCTION formula_delete_log_user
    (_user_id               bigint,
     _change_action_id      smallint,
     _field_id_formula_name smallint,
     _formula_name          text,
     _formula_id            bigint) RETURNS void AS

$$ BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,       old_value,    row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_name,_formula_name,_formula_id ;

    DELETE FROM user_formulas
          WHERE formula_id = _formula_id
            AND user_id = _user_id;

END $$ LANGUAGE plpgsql;

SELECT formula_delete_log_user
    (1::bigint,
     3::smallint,
     30::smallint,
     '"one" = "millions" * 1000000'::text,
     1::bigint);