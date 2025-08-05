DROP PROCEDURE IF EXISTS formula_insert_log_011101111151111;
CREATE PROCEDURE formula_insert_log_011101111151111
(_formula_name               text,
 _user_id                    bigint,
 _change_action_id           smallint,
 _field_id_formula_name      smallint,
 _field_id_user_id           smallint,
 _field_id_description       smallint,
 _description                text,
 _field_id_formula_type_id   smallint,
 _formula_type_id            smallint,
 _field_id_formula_text      smallint,
 _formula_text               text,
 _field_id_resolved_text     smallint,
 _resolved_text              text,
 _field_id_all_values_needed smallint,
 _all_values_needed          smallint,
 _field_id_view_id           smallint,
 _view_name                  text,
 _view_id                    bigint,
 _field_id_usage             smallint,
 _usage                      bigint,
 _field_id_excluded          smallint,
 _excluded                   smallint,
 _field_id_share_type_id     smallint,
 _share_type_id              smallint,
 _field_id_protect_id        smallint,
 _protect_id                 smallint)
BEGIN

    INSERT INTO formulas ( formula_name)
         SELECT           _formula_name ;

    SELECT LAST_INSERT_ID() AS @new_formula_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,           new_value,                    row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_name,    _formula_name,        @new_formula_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,           new_value,                    row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,         _user_id,             @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                    row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,      _description,         @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                    row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_type_id,  _formula_type_id,     @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                    row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_text,     _formula_text,        @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                    row_id)
         SELECT         _user_id,_change_action_id,_field_id_resolved_text,    _resolved_text,       @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                    row_id)
         SELECT         _user_id,_change_action_id,_field_id_all_values_needed,_all_values_needed,   @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,  new_id,           row_id)
         SELECT         _user_id,_change_action_id,_field_id_view_id,          _view_name, _view_id, @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                    row_id)
         SELECT         _user_id,_change_action_id,_field_id_usage,            _usage,               @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                    row_id)
         SELECT         _user_id,_change_action_id,_field_id_excluded,         _excluded,            @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                    row_id)
         SELECT         _user_id,_change_action_id,_field_id_share_type_id,    _share_type_id,       @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                    row_id)
         SELECT         _user_id,_change_action_id,_field_id_protect_id,       _protect_id,          @new_formula_id ;

    UPDATE formulas
       SET user_id           = _user_id,
           description       = _description,
           formula_type_id   = _formula_type_id,
           formula_text      = _formula_text,
           resolved_text     = _resolved_text,
           all_values_needed = _all_values_needed,
           last_update       = Now(),
           view_id           = _view_id,
           `usage`           = _usage,
           excluded          = _excluded,
           share_type_id     = _share_type_id,
           protect_id        = _protect_id
     WHERE formulas.formula_id = @new_formula_id;

END;

PREPARE formula_insert_log_011101111151111_call FROM
    'SELECT formula_insert_log_011101111151111 (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT formula_insert_log_011101111151111 (
               'scale minute to sec',
               1,
               1,
               30,
               173,
               34,
               'to convert times in minutes to seconds and the other way round',
               31,
               1,
               33,
               '{w24}={w104}*60',
               32,
               '"second" = "minute" * 60',
               35,
               1,
               655,
               '',
               1,
               656,
               2,
               71,
               1,
               117,
               3,
               118,
               2);