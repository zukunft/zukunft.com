DROP PROCEDURE IF EXISTS language_form_insert_log_11110;
CREATE PROCEDURE language_form_insert_log_11110
    (_language_form_name           text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_language_form_name  smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO language_forms ( language_form_name)
         SELECT            _language_form_name ;

         SELECT LAST_INSERT_ID()
             AS @new_language_form_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_language_form_name,_language_form_name,  @new_language_form_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,      _code_id,    @new_language_form_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,  _description,@new_language_form_id ;

        UPDATE language_forms
           SET code_id     = _code_id,
               description = _description
         WHERE language_forms.language_form_id = @new_language_form_id;

END;

PREPARE language_form_insert_log_11110_call
    FROM 'SELECT language_form_insert_log_11110 (?,?,?,?,?,?,?,?)';

SELECT language_form_insert_log_11110
    ('plural',
     1,
     1,
     301,
     302,
     'standard',
     303,
     'The noun denotes a quantity greater than the default quantity represented by that noun. This default quantity is most commonly one');
