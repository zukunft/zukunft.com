DROP FUNCTION IF EXISTS word_insert_log_01111000000;
CREATE FUNCTION word_insert_log_01111000000
(_word_name               text,
 _user_id                 bigint,
 _change_action_id        smallint,
 _field_id_word_name      smallint,
 _field_id_user_id        smallint,
 _field_id_description    smallint,
 _description             text,
 _field_id_phrase_type_id smallint,
 _phrase_type_id          bigint) RETURNS void
BEGIN
    WITH
        word_insert  AS (
            INSERT INTO words ( word_name)
                 VALUES       (_word_name)
              RETURNING         word_id ),

        change_insert_word_name AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value,            row_id)
                 SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name,word_insert.word_id
                   FROM word_insert),
        change_insert_user_id
                     AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value,            row_id)
                 SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,  word_insert.word_id
                   FROM word_insert),
        change_insert_description
                     AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,              row_id)
                 SELECT          _user_id,_change_action_id,_field_id_description,_description,word_insert.word_id
                   FROM word_insert),
        change_insert_phrase_type_id
                     AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,                 row_id)
                 SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_id,word_insert.word_id
                   FROM word_insert)
    UPDATE words
       SET user_id        = _user_id,
           description    = _description,
           phrase_type_id = _phrase_type_id
      FROM word_insert
     WHERE words.word_id = word_insert.word_id;

END;

SELECT word_insert_log_01111000000
       ('Mathematics'::text,
        1::bigint,
        1::smallint,
        10::smallint,
        9::smallint,
        11::smallint,
        'Mathematics is an area of knowledge that includes the topics of numbers and formulas'::text,
        12::smallint,
        1::bigint);