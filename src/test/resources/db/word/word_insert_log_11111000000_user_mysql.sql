DROP PROCEDURE IF EXISTS word_insert_log_11111000000_user;
CREATE PROCEDURE word_insert_log_11111000000_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_word_name      smallint,
     _word_name               text,
     _word_id                 bigint,
     _field_id_description    smallint,
     _description             text,
     _field_id_phrase_type_id smallint,
     _phrase_type_id          smallint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name,_word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,_word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_id,_word_id ;

    INSERT INTO user_words
                (word_id, user_id, word_name, description, phrase_type_id)
         SELECT _word_id,_user_id,_word_name,_description,_phrase_type_id ;

END;

PREPARE word_insert_log_11111000000_user_call FROM
    'SELECT word_insert_log_11111000000_user (?,?,?,?,?,?,?,?,?)';

SELECT word_insert_log_11111000000_user
        (1,
         1,
         10,
         'Mathematics',
         1,
         11,
         'Mathematics is an area of knowledge that includes the topics of numbers and formulas',
         12,
         1);