CREATE OR REPLACE FUNCTION verb_update_log_122200000
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_verb_name   smallint,
     _verb_name_old        text,
     _verb_name            text,
     _verb_id              bigint,
     _field_id_code_id     smallint,
     _code_id_old          text,
     _code_id              text,
     _field_id_description smallint,
     _description_old      text,
     _description          text) RETURNS bigint AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_verb_name,_verb_name_old,_verb_name,_verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  old_value,   new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,_code_id_old,_code_id,  _verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_verb_id ;

    UPDATE verbs
       SET verb_name   = _verb_name,
           code_id     = _code_id,
           description = _description
     WHERE verb_id = _verb_id;

END
$$ LANGUAGE plpgsql;

PREPARE verb_update_log_122200000_call
    (bigint, smallint, smallint, text, text, bigint, smallint, text, text, smallint, text, text) AS
SELECT verb_update_log_122200000
    ($1,$2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12);

SELECT verb_update_log_122200000
        (3::bigint,
         1::smallint,
         23::smallint,
         'not set'::text,
         'System Test Verb Renamed'::text,
         1::bigint,
         24::smallint,
         'not_set'::text,
         null::text,
         25::smallint,
         'no verb / predicate selected'::text,
         null::text);