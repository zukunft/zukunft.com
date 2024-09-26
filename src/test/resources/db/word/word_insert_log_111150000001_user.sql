CREATE OR REPLACE FUNCTION word_insert_log_111150000001_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_word_name      smallint,
     _word_name               text,
     _word_id                 bigint,
     _field_id_description    smallint,
     _description             text,
     _field_id_phrase_type_id smallint,
     _phrase_type_name        text,
     _phrase_type_id          smallint,
     _field_id_protect_id     smallint,
     _protect_id              smallint) RETURNS bigint AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name,_word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,_word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,        new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_name,_phrase_type_id,_word_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,     new_value,  row_id)
         SELECT         _user_id,_change_action_id,_field_id_protect_id,_protect_id,_word_id ;

    INSERT INTO user_words
                (word_id, user_id, word_name, description, phrase_type_id, protect_id)
         SELECT _word_id,_user_id,_word_name,_description,_phrase_type_id,_protect_id ;

END
$$ LANGUAGE plpgsql;

PREPARE word_insert_log_111150000001_user_call
        (bigint, smallint, smallint, text, bigint, smallint, text, smallint, text, smallint, smallint, smallint) AS
    SELECT word_insert_log_111150000001_user
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12);

SELECT word_insert_log_111150000001_user
        (1::bigint,
         1::smallint,
         10::smallint,
         'Mathematics'::text,
         1::bigint,
         11::smallint,
         'Mathematics is an area of knowledge that includes the topics of numbers and formulas'::text,
         12::smallint,
         'default'::text,
         1::smallint,
         87::smallint,
         3::smallint);