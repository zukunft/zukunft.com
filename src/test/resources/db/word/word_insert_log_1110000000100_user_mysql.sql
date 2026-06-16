DROP PROCEDURE IF EXISTS word_insert_log_1110000000100_user;
CREATE PROCEDURE word_insert_log_1110000000100_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_word_name      smallint,
     _word_name               text,
     _word_id                 bigint,
     _field_id_excluded       smallint,
     _excluded                smallint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name,_word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,_excluded,_word_id ;

    INSERT INTO user_words
                (word_id, user_id, word_name, excluded)
         SELECT _word_id,_user_id,_word_name,_excluded ;

END;

PREPARE word_insert_log_1110000000100_user_call FROM
    'SELECT word_insert_log_1110000000100_user (?,?,?,?,?,?,?)';

SELECT word_insert_log_1110000000100_user
        (4,
         1,
         10,
         'company',
         190,
         14,
         1);