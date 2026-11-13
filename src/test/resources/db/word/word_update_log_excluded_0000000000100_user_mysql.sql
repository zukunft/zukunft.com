DROP PROCEDURE IF EXISTS word_update_log_excluded_0000000000100_user;
CREATE PROCEDURE word_update_log_excluded_0000000000100_user
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_excluded  smallint,
     _excluded_old       smallint,
     _excluded           smallint,
     _word_id            bigint,
     _field_id_word_name smallint,
     _word_name_old      text)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded, _excluded_old, _excluded, _word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,                row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name_old,           _word_id ;

    UPDATE user_words
       SET excluded = _excluded
     WHERE word_id = _word_id
       AND user_id = _user_id;

END;

PREPARE word_update_log_excluded_0000000000100_user_call FROM
    'SELECT word_update_log_excluded_0000000000100_user (?,?,?,?,?,?,?,?)';

SELECT word_update_log_excluded_0000000000100_user
       (3,
        2,
        134,
        null,
        1,
        1,
        127,
        'mathematics');