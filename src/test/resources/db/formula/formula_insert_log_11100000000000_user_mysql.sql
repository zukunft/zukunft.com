DROP PROCEDURE IF EXISTS formula_insert_log_11100000000000_user;
CREATE PROCEDURE formula_insert_log_11100000000000_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_formula_name   smallint,
     _formula_name            text,
     _formula_id              bigint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       new_value,     row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_name,_formula_name,_formula_id ;

    INSERT INTO user_formulas
                (formula_id, user_id, formula_name)
         SELECT _formula_id,_user_id,_formula_name ;

END;

SELECT formula_insert_log_11100000000000_user
       (1,
        1,
        30,
        '"one" = "millions" * 1000000',
        0);