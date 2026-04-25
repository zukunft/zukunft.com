CREATE OR REPLACE FUNCTION language_insert_log_11110
    (_language_name           text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_language_name  smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text) RETURNS bigint AS
$$
DECLARE new_language_id bigint;
BEGIN

        INSERT INTO languages (language_name)
             SELECT           _language_name
          RETURNING language_id INTO new_language_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_language_name,_language_name,  new_language_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,      _code_id,    new_language_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,  _description,new_language_id ;

             UPDATE languages
                SET code_id     = _code_id,
                    description = _description
              WHERE languages.language_id = new_language_id;

             RETURN new_language_id;

END
$$ LANGUAGE plpgsql;

PREPARE language_insert_log_11110_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT language_insert_log_11110
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT language_insert_log_11110
    ('English'::text,
     1::bigint,
     1::smallint,
     296::smallint,
     297::smallint,
     'en'::text,
     298::smallint,
     'the system language,so each word must be unique for all users in this language'::text);