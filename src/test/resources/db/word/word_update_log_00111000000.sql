CREATE OR REPLACE FUNCTION word_update_log_00111000000
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_word_name      smallint,
     _word_name               text,
     _word_id                 bigint,
     _field_id_user_id        smallint,
     _field_id_description    smallint,
     _description             text,
     _field_id_phrase_type_id smallint,
     _phrase_type_id          bigint) RETURNS void AS
$$
BEGIN

    WITH
        change_insert_word_name AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
                 SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name,_word_id),
        change_insert_user_id
                     AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
                 SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,  _word_id),
        change_insert_description
                     AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
                 SELECT          _user_id,_change_action_id,_field_id_description,_description,_word_id),
        change_insert_phrase_type_id
                     AS (
            INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,      row_id)
                 SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_id,_word_id)

    UPDATE words
       SET word_name      = _word_name,
           user_id        = _user_id,
           description    = _description,
           phrase_type_id = _phrase_type_id
     WHERE word_id = _word_id;

END
$$ LANGUAGE plpgsql;

SELECT word_update_log_00111000000
       (1::bigint,
        1::smallint,
        10::smallint,
        'Mathematics'::text,
        1::smallint,
        9::smallint,
        11::smallint,
        'Mathematics is an area of knowledge that includes the topics of numbers and formulas'::text,
        12::smallint,
        1::bigint);