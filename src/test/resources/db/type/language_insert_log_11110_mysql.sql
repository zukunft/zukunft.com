DROP PROCEDURE IF EXISTS language_insert_log_11110;
CREATE PROCEDURE language_insert_log_11110
    (_language_name           text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_language_name  smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO languages ( language_name)
         SELECT            _language_name ;

         SELECT LAST_INSERT_ID()
             AS @new_language_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_language_name,_language_name,  @new_language_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,      _code_id,    @new_language_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,  _description,@new_language_id ;

        UPDATE languages
           SET code_id     = _code_id,
               description = _description
         WHERE languages.language_id = @new_language_id;

END;

PREPARE language_insert_log_11110_call
    FROM 'SELECT language_insert_log_11110 (?,?,?,?,?,?,?,?)';

SELECT language_insert_log_11110
    ('English',
     1,
     1,
     296,
     297,
     'en',
     298,
     'the system language,so each word must be unique for all users in this language');
