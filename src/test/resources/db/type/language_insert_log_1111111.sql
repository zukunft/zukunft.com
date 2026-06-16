CREATE OR REPLACE FUNCTION language_insert_log_1111111
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
     _usage                   bigint) RETURNS bigint AS
$$
DECLARE new_language_id bigint;
BEGIN

        INSERT INTO languages (language_name)
             SELECT           _language_name
          RETURNING language_id INTO new_language_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,      row_id)
             SELECT          _user_id,_change_action_id,_field_id_language_name,_language_name,  new_language_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,      row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,      _code_id,        new_language_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,      row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,  _description,    new_language_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,     row_id)
             SELECT          _user_id,_change_action_id,_field_id_wikimedia_code,_wikimedia_code,new_language_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,     row_id)
             SELECT          _user_id,_change_action_id,_field_id_local_name,    _local_name,    new_language_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,     row_id)
             SELECT          _user_id,_change_action_id,_field_id_usage,         _usage,         new_language_id ;

             UPDATE languages
                SET code_id        = _code_id,
                    description    = _description,
                    wikimedia_code = _wikimedia_code,
                    local_name     = _local_name,
                    usage          = _usage
              WHERE languages.language_id = new_language_id;

             RETURN new_language_id;

END
$$ LANGUAGE plpgsql;

PREPARE language_insert_log_1111111_call
    (text, bigint, smallint, smallint, smallint, text, smallint, text, smallint, text, smallint, text, smallint, bigint) AS
SELECT language_insert_log_1111111
    ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14);

SELECT language_insert_log_1111111
    ('German'::text,
     1::bigint,
     1::smallint,
     296::smallint,
     297::smallint,
     'de'::text,
     298::smallint,
     'a translation to standard German'::text,
     299::smallint,
     'de'::text,
     884::smallint,
     'Deutsch'::text,
     885::smallint,
     95000000::bigint);