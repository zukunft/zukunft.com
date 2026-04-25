CREATE OR REPLACE FUNCTION phrase_type_insert_log_111100
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text) RETURNS bigint AS
$$
DECLARE new_phrase_type_id bigint;
BEGIN

        INSERT INTO phrase_types (type_name)
             SELECT              _type_name
          RETURNING phrase_type_id INTO new_phrase_type_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  new_phrase_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    new_phrase_type_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description,new_phrase_type_id ;

             UPDATE phrase_types
                SET code_id     = _code_id,
                    description = _description
              WHERE phrase_types.phrase_type_id = new_phrase_type_id;

             RETURN new_phrase_type_id;

END
$$ LANGUAGE plpgsql;

PREPARE phrase_type_insert_log_111100_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT phrase_type_insert_log_111100
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT phrase_type_insert_log_111100
    ('standard'::text,
     1::bigint,
     1::smallint,
     835::smallint,
     836::smallint,
     'default'::text,
     837::smallint,
     '1'::text);