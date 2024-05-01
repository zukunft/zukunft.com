CREATE OR REPLACE FUNCTION formula_insert_log_01100000000000
    (_formula_name            text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_formula_name   smallint,
     _field_id_user_id        smallint) RETURNS void AS
$$
BEGIN

    WITH
        formula_insert  AS (
            INSERT INTO formulas ( formula_name)
                 VALUES          (_formula_name)
              RETURNING            formula_id ),

        change_insert_formula_name AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,       new_value,   row_id)
                 SELECT          _user_id,_change_action_id,_field_id_formula_name,_formula_name,formula_insert.formula_id
                   FROM formula_insert),
        change_insert_user_id
                     AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
                 SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,   formula_insert.formula_id
                   FROM formula_insert)
    UPDATE formulas
       SET user_id        = _user_id
      FROM formula_insert
     WHERE formulas.formula_id = formula_insert.formula_id;

END
$$ LANGUAGE plpgsql;

SELECT formula_insert_log_01100000000000
       ('"one" = "millions" * 1000000'::text,
        1::bigint,
        1::smallint,
        30::smallint,
        173::smallint);