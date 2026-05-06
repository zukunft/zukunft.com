DROP PROCEDURE IF EXISTS language_insert_log_1111111;
CREATE PROCEDURE language_insert_log_1111111
    (_language_name           text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_language_name  smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text,
     _field_id_wikimedia_code smallint,
     _wikimedia_code          text,
     _field_id_local_name     smallint,
     _local_name              text,
     _field_id_usage          smallint,
     _usage                   bigint)
BEGIN

    INSERT INTO languages ( language_name)
         SELECT            _language_name ;

         SELECT LAST_INSERT_ID()
             AS @new_language_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_language_name,_language_name,  @new_language_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,      _code_id,        @new_language_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,  _description,    @new_language_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,     row_id)
         SELECT          _user_id,_change_action_id,_field_id_wikimedia_code,_wikimedia_code,@new_language_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,     row_id)
         SELECT          _user_id,_change_action_id,_field_id_local_name,    _local_name,    @new_language_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,     row_id)
         SELECT          _user_id,_change_action_id,_field_id_usage,         _usage,         @new_language_id ;

        UPDATE languages
           SET code_id        = _code_id,
               description    = _description,
               wikimedia_code = _wikimedia_code,
               local_name     = _local_name,
               `usage`        = _usage
         WHERE languages.language_id = @new_language_id;

END;

PREPARE language_insert_log_1111111_call
    FROM 'SELECT language_insert_log_1111111 (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT language_insert_log_1111111
    ('German',
     1,
     1,
     296,
     297,
     'de',
     298,
     'a translation to standard German',
     299,
     'de',
     884,
     'Deutsch',
     885,
     95000000);
