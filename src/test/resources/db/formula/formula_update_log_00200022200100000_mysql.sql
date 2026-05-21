DROP PROCEDURE IF EXISTS formula_update_log_00200022200100000;
CREATE PROCEDURE formula_update_log_00200022200100000
    (_user_id                  bigint,
     _change_action_id         smallint,
     _field_id_formula_name    smallint,
     _formula_name_old         text,
     _formula_name             text,
     _formula_id               bigint,
     _field_id_formula_type_id smallint,
     _formula_type_id_old      smallint,
     _formula_type_id          smallint,
     _field_id_formula_text    smallint,
     _formula_text_old         text,
     _formula_text             text,
     _field_id_resolved_text   smallint,
     _resolved_text_old        text,
     _resolved_text            text)

BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,          old_value,           new_value,       row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_name,   _formula_name_old,   _formula_name,   _formula_id ;
    INSERT INTO changes (user_id,change_action_id,  change_field_id,          old_value,           new_value,       row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_type_id,_formula_type_id_old,_formula_type_id,_formula_id ;
    INSERT INTO changes (user_id,change_action_id,  change_field_id,          old_value,           new_value,       row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_text,   _formula_text_old,   _formula_text,   _formula_id ;
    INSERT INTO changes (user_id,change_action_id,  change_field_id,          old_value,           new_value,       row_id)
         SELECT         _user_id,_change_action_id,_field_id_resolved_text,  _resolved_text_old,  _resolved_text,  _formula_id ;

    UPDATE formulas
       SET formula_name    = _formula_name,
           formula_type_id = _formula_type_id,
           formula_text    = _formula_text,
           resolved_text   = _resolved_text,
           last_update = Now()
     WHERE formula_id = _formula_id;

END;

PREPARE formula_update_log_00200022200100000_call FROM
    'SELECT formula_update_log_00200022200100000 (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT formula_update_log_00200022200100000
    (3,
     2,
     30,
     'scale hour to sec',
     'System Test Formula Renamed',
     2,
     31,
     1,
     null,
     33,
     '{w24}={w105}*3600',
     null,
     32,
     '{w24}={w105}*3600',
     null);