CREATE OR REPLACE FUNCTION word_insert_log_011000000000
    (_word_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_word_name      smallint,
     _field_id_user_id        smallint) RETURNS bigint AS
$$
DECLARE new_word_id bigint;
BEGIN

    INSERT INTO words ( word_name)
         SELECT        _word_name
      RETURNING         word_id INTO new_word_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name, new_word_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value,row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id, _user_id,new_word_id ;

         UPDATE words
            SET user_id = _user_id
          WHERE words.word_id = new_word_id;

         RETURN new_word_id;

END
$$ LANGUAGE plpgsql;

PREPARE word_insert_log_011000000000_call
    (text, bigint, smallint, smallint, smallint) AS
SELECT word_insert_log_011000000000
    ($1,$2, $3, $4, $5);

SELECT word_insert_log_011000000000
        ('System Test Word'::text,
         1::bigint,
         1::smallint,
         10::smallint,
         9::smallint);