DROP PROCEDURE IF EXISTS formula_insert_log_01101110000000;
CREATE PROCEDURE formula_insert_log_01101110000000
(_formula_name             text,
 _user_id                  bigint,
 _change_action_id         smallint,
 _field_id_formula_name    smallint,
 _field_id_user_id         smallint,
 _field_id_formula_type_id smallint,
 _formula_type_id          bigint,
 _field_id_formula_text    smallint,
 _formula_text             text,
 _field_id_resolved_text   smallint,
 _resolved_text            text)
BEGIN

    INSERT INTO formulas ( formula_name)
         SELECT           _formula_name ;

    SELECT LAST_INSERT_ID() AS @new_formula_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,               row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_name,  _formula_name,   @new_formula_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,               row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,       _user_id,        @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,          new_value,               row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_type_id,_formula_type_id,@new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,          new_value,               row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_text,   _formula_text,   @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,          new_value,               row_id)
         SELECT         _user_id,_change_action_id,_field_id_resolved_text,  _resolved_text,  @new_formula_id ;

    UPDATE formulas
       SET user_id         = _user_id,
           formula_type_id = _formula_type_id,
           formula_text    = _formula_text,
           resolved_text   = _resolved_text
     WHERE formulas.formula_id = @new_formula_id;

END;

PREPARE formula_insert_log_01101110000000_call FROM
    'SELECT formula_insert_log_01101110000000 (?,?,?,?,?,?,?,?,?,?,?)';