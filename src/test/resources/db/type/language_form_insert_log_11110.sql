CREATE OR REPLACE FUNCTION language_form_insert_log_11110
    (_language_form_name           text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_language_form_name  smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text) RETURNS bigint AS
$$
DECLARE new_language_form_id bigint;
BEGIN

        INSERT INTO language_forms (language_form_name)
             SELECT           _language_form_name
          RETURNING language_form_id INTO new_language_form_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_language_form_name,_language_form_name,  new_language_form_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,      _code_id,    new_language_form_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,  _description,new_language_form_id ;

             UPDATE language_forms
                SET code_id     = _code_id,
                    description = _description
              WHERE language_forms.language_form_id = new_language_form_id;

             RETURN new_language_form_id;

END
$$ LANGUAGE plpgsql;

PREPARE language_form_insert_log_11110_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT language_form_insert_log_11110
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT language_form_insert_log_11110
    ('plural'::text,
     1::bigint,
     1::smallint,
     301::smallint,
     302::smallint,
     'standard'::text,
     303::smallint,
     'The noun denotes a quantity greater than the default quantity represented by that noun. This default quantity is most commonly one'::text);