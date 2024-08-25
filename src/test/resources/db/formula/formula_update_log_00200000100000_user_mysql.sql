DROP PROCEDURE IF EXISTS formula_update_log_00200000100000_user;
CREATE PROCEDURE formula_update_log_00200000100000_user
    (_user_id bigint,
     _change_action_id smallint,
     _field_id_formula_name smallint,
     _formula_name_old text,
     _formula_name text,
     _formula_id bigint)

BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,       old_value,        new_value,    row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_name,_formula_name_old,_formula_name,_formula_id ;

    UPDATE user_formulas
       SET formula_name = _formula_name,
           last_update = Now()
     WHERE formula_id = _formula_id
       AND user_id = _user_id;

END;

PREPARE formula_update_log_00200000100000_user_call FROM
    'SELECT formula_update_log_00200000100000_user (?,?,?,?,?,?)';

SELECT formula_update_log_00200000100000_user
    (1,
     2,
     30,
     '"one" = "millions" * 1000000',
     'System Test Formula Renamed',
     1);