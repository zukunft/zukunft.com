CREATE OR REPLACE FUNCTION component_insert_log_0111155111111111
    (_component_name             text,
     _user_id                    bigint,
     _change_action_id           smallint,
     _field_id_component_name    smallint,
     _field_id_user_id           smallint,
     _field_id_description       smallint,
     _description                text,
     _field_id_code_id           smallint,
     _code_id                    text,
     _field_id_component_type_id smallint,
     _type_name                  text,
     _component_type_id          smallint,
     _field_id_view_style_id     smallint,
     _view_style_name            text,
     _view_style_id              smallint,
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
     _protect_id                 smallint) RETURNS bigint AS
$$
DECLARE new_component_id bigint;
BEGIN

    INSERT INTO components ( component_name)
         SELECT        _component_name
      RETURNING         component_id INTO new_component_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                             row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_name,  _component_name,               new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                             row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,          _user_id,                     new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                             row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,      _description,                 new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                             row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,          _code_id,                     new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,  new_id,                    row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_type_id,_type_name,_component_type_id,new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,  new_id,                    row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_style_id,_view_style_name,_view_style_id,new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                             row_id)
         SELECT          _user_id,_change_action_id,_field_id_ui_msg_code_id,   _ui_msg_code_id,              new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                             row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_id_row,      _word_id_row,                 new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                             row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_id_col,      _word_id_col,                 new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                             row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_id_col2,     _word_id_col2,                new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                             row_id)
         SELECT          _user_id,_change_action_id,_field_id_formula_id,       _formula_id,                  new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                             row_id)
         SELECT          _user_id,_change_action_id,_field_id_link_type_id,     _link_type_id,                new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                             row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,         _excluded,                    new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                             row_id)
         SELECT          _user_id,_change_action_id,_field_id_share_type_id,    _share_type_id,               new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,                             row_id)
         SELECT          _user_id,_change_action_id,_field_id_protect_id,       _protect_id,                  new_component_id ;

    UPDATE components
       SET user_id           = _user_id,
           description       = _description,
           code_id           = _code_id,
           component_type_id = _component_type_id,
           view_style_id     = _view_style_id,
           ui_msg_code_id    = _ui_msg_code_id,
           word_id_row       = _word_id_row,
           word_id_col       = _word_id_col,
           word_id_col2      = _word_id_col2,
           formula_id        = _formula_id,
           link_type_id      = _link_type_id,
           excluded          = _excluded,
           share_type_id     = _share_type_id,
           protect_id        = _protect_id
     WHERE components.component_id = new_component_id;

    RETURN new_component_id;

END
$$ LANGUAGE plpgsql;

PREPARE component_insert_log_0111155111111111_call
    (text, bigint, smallint, smallint, smallint, smallint, text, smallint, text, smallint, text, smallint, smallint, text, smallint, smallint, text, smallint, bigint, smallint, bigint, smallint, bigint, smallint, bigint, smallint, smallint, smallint, smallint, smallint, smallint, smallint, smallint) AS
SELECT component_insert_log_0111155111111111
    ($1,$2, $3, $4, $5, $6, $7, $8, $9, $10, $11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,$26,$27,$28,$29, $30, $31, $32, $33);

SELECT component_insert_log_0111155111111111 (
               'Word'::text,
               1::bigint,
               1::smallint,
               51::smallint,
               743::smallint,
               52::smallint,
               'simply show the word or triple name'::text,
               63::smallint,
               'form_title'::text,
               53::smallint,
               'text'::text,
               3::smallint,
               779::smallint,
               'col-md-4'::text,
               1::smallint,
               64::smallint,
               'please_select'::text,
               143::smallint,
               134::bigint,
               146::smallint,
               281::bigint,
               147::smallint,
               282::bigint,
               145::smallint,
               1::bigint,
               144::smallint,
               2::smallint,
               73::smallint,
               1::smallint,
               148::smallint,
               3::smallint,
               149::smallint,
               2::smallint);