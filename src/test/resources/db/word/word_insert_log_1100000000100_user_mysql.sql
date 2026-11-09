DROP PROCEDURE IF EXISTS word_insert_log_1100000000100_user;
CREATE PROCEDURE word_insert_log_1100000000100_user
    (_user_id           bigint,
     _change_action_id  smallint,
     _field_id_excluded smallint,
     _excluded          smallint,
     _word_id           bigint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,   new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,_excluded, _word_id ;
    INSERT INTO user_words ( word_id, user_id, excluded)
         SELECT             _word_id,_user_id,_excluded ;

END;

PREPARE word_insert_log_1100000000100_user_call FROM
    'SELECT word_insert_log_1100000000100_user (?,?,?,?,?)';

SELECT word_insert_log_1100000000100_user
       (3,
        1,
        134,
        1,
        1);