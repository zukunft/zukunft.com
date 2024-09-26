DROP PROCEDURE IF EXISTS word_insert_log_011150000001;
CREATE PROCEDURE word_insert_log_011150000001
    (_word_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_word_name      smallint,
     _field_id_user_id        smallint,
     _field_id_description    smallint,
     _description             text,
     _field_id_phrase_type_id smallint,
     _phrase_type_name        text,
     _phrase_type_id          smallint,
     _field_id_protect_id     smallint,
     _protect_id              smallint)
BEGIN

    INSERT INTO words ( word_name)
         SELECT        _word_name ;

    SELECT LAST_INSERT_ID() AS @new_word_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name,@new_word_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,  @new_word_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_word_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,        new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_name,_phrase_type_id,@new_word_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,     new_value,  row_id)
         SELECT         _user_id,_change_action_id,_field_id_protect_id,_protect_id,@new_word_id ;

    UPDATE words
       SET user_id        = _user_id,
           description    = _description,
           phrase_type_id = _phrase_type_id,
           protect_id     = _protect_id
     WHERE words.word_id = @new_word_id;

END;

PREPARE word_insert_log_011150000001_call FROM
    'SELECT word_insert_log_011150000001 (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT word_insert_log_011150000001
    ('Mathematics',
     1,
     1,
     10,
     9,
     11,
     'Mathematics is an area of knowledge that includes the topics of numbers and formulas',
     12,
     'default',
     1,
     87,
     3);