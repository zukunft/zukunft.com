CREATE OR REPLACE FUNCTION verb_insert_log_1111000000
    (_verb_name            text,
     _user_id              bigint,
     _change_action_id     smallint,
     _field_id_verb_name   smallint,
     _field_id_code_id     smallint,
     _code_id              text,
     _field_id_description smallint,
     _description          text) RETURNS bigint AS
$$
DECLARE new_verb_id bigint;
BEGIN

    INSERT INTO verbs ( verb_name)
         SELECT        _verb_name
      RETURNING         verb_id INTO new_verb_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_verb_name,_verb_name, new_verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,_code_id,new_verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,new_verb_id ;

    UPDATE verbs
       SET code_id       = _code_id,
           description   = _description
     WHERE verbs.verb_id = new_verb_id;

    RETURN new_verb_id;

END
$$ LANGUAGE plpgsql;

PREPARE verb_insert_log_1111000000_call
    (text, bigint, smallint, smallint, smallint, text, smallint, text) AS
SELECT verb_insert_log_1111000000
    ($1,$2, $3, $4, $5, $6, $7, $8);

SELECT verb_insert_log_1111000000
        ('not set'::text,
         3::bigint,
         1::smallint,
         23::smallint,
         24::smallint,
         'not_set'::text,
         25::smallint,
         'no verb / predicate selected'::text);