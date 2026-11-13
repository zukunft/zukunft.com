CREATE OR REPLACE FUNCTION word_update_log_0001000000000_user
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_description smallint,
     _description_old      text,
     _description          text,
     _word_id              bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_word_id ;

    UPDATE user_words
       SET description = _description
     WHERE word_id = _word_id
       AND user_id = _user_id;

END
$$ LANGUAGE plpgsql;

PREPARE word_update_log_0001000000000_user_call
        (bigint, smallint, smallint, text, text, bigint) AS
SELECT word_update_log_0001000000000_user
        ($1,$2,$3,$4,$5,$6);

SELECT word_update_log_0001000000000_user
       (3::bigint,
        2::smallint,
        129::smallint,
        null::text,
        'Mathematics is an area of knowledge that includes the topics of numbers and formulas'::text,
        1::bigint);