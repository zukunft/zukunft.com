CREATE OR REPLACE FUNCTION word_insert_log_1110000000100_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_word_name      smallint,
     _word_name               text,
     _word_id                 bigint,
     _field_id_excluded       smallint,
     _excluded                smallint) RETURNS bigint AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name,_word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,_excluded,_word_id ;

    INSERT INTO user_words
                (word_id, user_id, word_name, excluded)
         SELECT _word_id,_user_id,_word_name,_excluded ;

END
$$ LANGUAGE plpgsql;

PREPARE word_insert_log_1110000000100_user_call
        (bigint, smallint, smallint, text, bigint, smallint, smallint) AS
    SELECT word_insert_log_1110000000100_user
        ($1,$2,$3,$4,$5,$6,$7);

SELECT word_insert_log_1110000000100_user
        (4::bigint,
         1::smallint,
         10::smallint,
         'company'::text,
         190::bigint,
         14::smallint,
         1::smallint);