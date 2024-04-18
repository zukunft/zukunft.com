CREATE OR REPLACE FUNCTION word_insert_log_00111000000
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_name        smallint,
     _new_word             text,
     _field_id_user_id     bigint,
     _field_id_description bigint,
     _description          text,
     _field_id_type        bigint,
     _type_id              bigint) RETURNS void AS
$$
BEGIN

    WITH
        insert_word  AS (
            INSERT INTO words ( word_name)
                 VALUES       (_new_word)
              RETURNING         word_id ),

        insert_log_id AS (
            INSERT INTO changes ( user_id, change_action_id,             row_id, change_field_id, new_value)
                 SELECT          _user_id,_change_action_id,insert_word.word_id,_field_id_name,  _new_word
                   FROM insert_word),
        insert_log_user_id
                     AS (
            INSERT INTO changes ( user_id, change_action_id,             row_id, change_field_id,      new_value)
                 SELECT          _user_id,_change_action_id,insert_word.word_id,_field_id_user_id,    _user_id
                   FROM insert_word),
        insert_log_description
                     AS (
            INSERT INTO changes ( user_id, change_action_id,             row_id, change_field_id,      new_value)
                 SELECT          _user_id,_change_action_id,insert_word.word_id,_field_id_description,_description
                   FROM insert_word),
        insert_log_type
                     AS (
            INSERT INTO changes ( user_id, change_action_id,             row_id, change_field_id,      new_value)
                 SELECT          _user_id,_change_action_id,insert_word.word_id,_field_id_type,       _type_id
                   FROM insert_word)
    UPDATE words
       SET user_id        = _user_id,
           description    = _description,
           phrase_type_id = _type_id
      FROM insert_word
     WHERE words.word_id = insert_word.word_id;

END
$$ LANGUAGE plpgsql;

SELECT word_insert_log_00111000000
       (2::bigint,
        1::smallint,
        1::smallint,
        'new_word2'::text,
        1::bigint,
        1::bigint,
        'des'::text,
        1::bigint,
        1::bigint);