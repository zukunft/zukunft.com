DROP PROCEDURE IF EXISTS component_insert_log_011111111111111;
CREATE PROCEDURE component_insert_log_011111111111111
    (_component_name             text,
     _user_id                    bigint,
     _change_action_id           smallint,
     _field_id_component_name    smallint,
     _field_id_user_id           smallint,
     _field_id_description       smallint,
     _description                text,
     _field_id_component_type_id smallint,
     _component_type_id          smallint,
     _field_id_code_id           smallint,
     _code_id                    text,
     _field_id_ui_msg_code_id    smallint,
     _ui_msg_code_id             text,
     _field_id_word_id_row       smallint,
     _word_id_row                bigint,
     _field_id_word_id_col       smallint,
     _word_id_col                bigint,
     _field_id_word_id_col2      smallint,
     _word_id_col2               bigint,
     _field_id_formula_id        smallint,
     _formula_id                 bigint,
     _field_id_link_type_id      smallint,
     _link_type_id               smallint,
     _field_id_excluded          smallint,
     _excluded                   smallint,
     _field_id_share_type_id     smallint,
     _share_type_id              smallint,
     _field_id_protect_id        smallint,
     _protect_id                 smallint)
BEGIN

    INSERT INTO components ( component_name)
         SELECT             _component_name ;

    SELECT LAST_INSERT_ID() AS @new_component_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                   row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_name,   _component_name,   @new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                   row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,          _user_id,          @new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,      _description,      @new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                   row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_type_id,_component_type_id,@new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,          _code_id,         @new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT          _user_id,_change_action_id,_field_id_ui_msg_code_id,   _ui_msg_code_id,  @new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_id_row,      _word_id_row,     @new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_id_col,      _word_id_col,     @new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_id_col2,     _word_id_col2,    @new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_id,       _formula_id,      @new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT          _user_id,_change_action_id,_field_id_link_type_id,     _link_type_id,    @new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,         _excluded,        @new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT          _user_id,_change_action_id,_field_id_share_type_id,    _share_type_id,   @new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                  row_id)
         SELECT          _user_id,_change_action_id,_field_id_protect_id,       _protect_id,      @new_component_id ;


    UPDATE components
       SET user_id           = _user_id,
           description       = _description,
           component_type_id = _component_type_id,
           code_id           = _code_id,
           ui_msg_code_id    = _ui_msg_code_id,
           word_id_row       = _word_id_row,
           word_id_col       = _word_id_col,
           word_id_col2      = _word_id_col2,
           formula_id        = _formula_id,
           link_type_id      = _link_type_id,
           excluded          = _excluded,
           share_type_id     = _share_type_id,
           protect_id        = _protect_id
      WHERE components.component_id = @new_component_id;

END;

PREPARE component_insert_log_011111111111111_call FROM
    'SELECT component_insert_log_011111111111111 (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT component_insert_log_011111111111111 (
               'Word',
               1,
               1,
               51,
               743,
               52,
               'simply show the word name',
               53,
               2,
               63,
               'form_title',
               64,
               'please_select',
               143,
               11,
               146,
               192,
               147,
               193,
               145,
               1,
               144,
               2,
               73,
               1,
               148,
               3,
               149,
               2);