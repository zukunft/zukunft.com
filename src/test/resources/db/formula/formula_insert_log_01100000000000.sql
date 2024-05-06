CREATE OR REPLACE FUNCTION formula_insert_log_01100000000000
    (_formula_name            text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_formula_name   smallint,
     _field_id_user_id        smallint) RETURNS bigint AS
$$
DECLARE new_formula_id bigint;
BEGIN

    INSERT INTO formulas ( formula_name)
         SELECT           _formula_name
      RETURNING            formula_id INTO new_formula_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_name,_formula_name,new_formula_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,   new_formula_id ;

    UPDATE formulas
       SET user_id        = _user_id
     WHERE formulas.formula_id = new_formula_id;

    RETURN new_formula_id;

END
$$ LANGUAGE plpgsql;

PREPARE formula_insert_log_01100000000000_call
        (text,bigint,smallint,smallint,smallint) AS
    SELECT formula_insert_log_01100000000000
        ($1,$2,$3,$4,$5);