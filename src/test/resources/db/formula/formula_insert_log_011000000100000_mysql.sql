DROP PROCEDURE IF EXISTS formula_insert_log_011000000100000;
CREATE PROCEDURE formula_insert_log_011000000100000
    (_formula_name            text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_formula_name   smallint,
     _field_id_user_id        smallint)
BEGIN

    INSERT INTO formulas ( formula_name)
         SELECT           _formula_name ;

    SELECT LAST_INSERT_ID() AS @new_formula_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       new_value,    row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_name,_formula_name,@new_formula_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,  @new_formula_id ;

    UPDATE formulas
       SET user_id = _user_id,
           last_update = Now()
     WHERE formulas.formula_id = @new_formula_id;

END;

PREPARE formula_insert_log_011000000100000_call FROM
    'SELECT formula_insert_log_011000000100000 (?,?,?,?,?)';

SELECT formula_insert_log_011000000100000 (
               '"one" = "millions" * 1000000',
               1,
               1,
               30,
               173);