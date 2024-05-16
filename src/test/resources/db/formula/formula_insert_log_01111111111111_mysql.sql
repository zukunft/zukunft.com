DROP PROCEDURE IF EXISTS formula_insert_log_01111111111111;
CREATE PROCEDURE formula_insert_log_01111111111111
(_formula_name               text,
 _user_id                    bigint,
 _change_action_id           smallint,
 _field_id_formula_name      smallint,
 _field_id_user_id           smallint,
 _field_id_description       smallint,
 _description                text,
 _field_id_formula_type_id   smallint,
 _formula_type_id            bigint,
 _field_id_formula_text      smallint,
 _formula_text               text,
 _field_id_resolved_text     smallint,
 _resolved_text              text,
 _field_id_all_values_needed smallint,
 _all_values_needed          bigint,
 _field_id_last_update       smallint,
 _last_update                bigint,
 _field_id_view_id           smallint,
 _view_id                    bigint,
 _field_id_usage             smallint,
 _usage                      bigint,
 _field_id_excluded          smallint,
 _excluded                   bigint,
 _field_id_share_type_id     smallint,
 _share_type_id              bigint,
 _field_id_protect_id        smallint,
 _protect_id                 bigint)
BEGIN

    INSERT INTO formulas ( formula_name)
         SELECT           _formula_name ;

    SELECT LAST_INSERT_ID() AS @new_formula_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,           new_value,                 row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_name,    _formula_name,     @new_formula_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,           new_value,                 row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,         _user_id,          @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                 row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,      _description,      @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                 row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_type_id,  _formula_type_id,  @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                 row_id)
         SELECT         _user_id,_change_action_id,_field_id_formula_text,     _formula_text,     @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                 row_id)
         SELECT         _user_id,_change_action_id,_field_id_resolved_text,    _resolved_text,    @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                 row_id)
         SELECT         _user_id,_change_action_id,_field_id_all_values_needed,_all_values_needed,@new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                 row_id)
         SELECT         _user_id,_change_action_id,_field_id_last_update,      _last_update,      @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                 row_id)
         SELECT         _user_id,_change_action_id,_field_id_view_id,          _view_id,          @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                 row_id)
         SELECT         _user_id,_change_action_id,_field_id_usage,            _usage,            @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                 row_id)
         SELECT         _user_id,_change_action_id,_field_id_excluded,         _excluded,         @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                 row_id)
         SELECT         _user_id,_change_action_id,_field_id_share_type_id,    _share_type_id,    @new_formula_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,                 row_id)
         SELECT         _user_id,_change_action_id,_field_id_protect_id,       _protect_id,       @new_formula_id ;

    UPDATE formulas
       SET user_id           = _user_id,
           description       = _description,
           formula_type_id   = _formula_type_id,
           formula_text      = _formula_text,
           resolved_text     = _resolved_text,
           all_values_needed = _all_values_needed,
           last_update       = _last_update,
           view_id           = _view_id,
           `usage`           = _usage,
           excluded          = _excluded,
           share_type_id     = _share_type_id,
           protect_id        = _protect_id
     WHERE formulas.formula_id = @new_formula_id;

END;

PREPARE formula_insert_log_01111111111111_call FROM
    'SELECT formula_insert_log_01111111111111 (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';