DROP PROCEDURE IF EXISTS formula_insert_log_1110001110100000_user;
CREATE PROCEDURE formula_insert_log_1110001110100000_user
    (_user_id                  bigint,
     _change_action_id         smallint,
     _field_id_formula_name    smallint,
     _formula_name             text,
     _formula_id               bigint,
     _field_id_formula_type_id smallint,
     _formula_type_id          smallint,
     _field_id_formula_text    smallint,
     _formula_text             text,
     _field_id_resolved_text   smallint,
     _resolved_text            text)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       new_value,     row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_name,_formula_name,_formula_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,          new_value,       row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_type_id,_formula_type_id,_formula_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       new_value,    row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_text,_formula_text,_formula_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,     row_id)
         SELECT          _user_id,_change_action_id,_field_id_resolved_text,_resolved_text,_formula_id ;

    INSERT INTO user_formulas
                (formula_id, user_id, formula_name, formula_type_id, formula_text, resolved_text, last_update)
         SELECT _formula_id,_user_id,_formula_name,_formula_type_id,_formula_text,_resolved_text, Now() ;

END;

PREPARE formula_insert_log_1110001110100000_user_call FROM
    'SELECT formula_insert_log_1110001110100000_user (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT formula_insert_log_1110001110100000_user (
               3,
               1,
               30,
               'scale minute to sec',
               1,
               31,
               1,
               33,
               '{w24}={w104}*60',
               32,
               '"second" = "minute" * 60');