DROP PROCEDURE IF EXISTS word_insert_log_011000000000;
CREATE PROCEDURE word_insert_log_011000000000
    (_word_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_word_name      smallint,
     _field_id_user_id        smallint)
BEGIN

    INSERT INTO words ( word_name)
         SELECT        _word_name ;

    SELECT LAST_INSERT_ID() AS @new_word_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name,@new_word_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,  @new_word_id ;

    UPDATE words
       SET user_id        = _user_id
     WHERE words.word_id = @new_word_id;

END;

PREPARE word_insert_log_011000000000_call FROM
    'SELECT word_insert_log_011000000000 (?,?, ?, ?, ?)';

SELECT word_insert_log_011000000000
    ('System Test Word',
     1,
     1,
     10,
     9);