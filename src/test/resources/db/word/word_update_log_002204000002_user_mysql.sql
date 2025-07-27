DROP PROCEDURE IF EXISTS word_update_log_002204000002_user;
CREATE PROCEDURE word_update_log_002204000002_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_word_name      smallint,
     _word_name_old           text,
     _word_name               text,
     _word_id                 bigint,
     _field_id_description    smallint,
     _description_old         text,
     _description             text,
     _field_id_phrase_type_id smallint,
     _phrase_type_name_old    text,
     _phrase_type_id_old      smallint,
     _phrase_type_name        text,
     _phrase_type_id          smallint,
     _field_id_protect_id     smallint,
     _protect_id_old          smallint,
     _protect_id              smallint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_name,_word_name_old,_word_name,_word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,            new_value,        old_id,             new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_name_old,_phrase_type_name,_phrase_type_id_old,_phrase_type_id,_word_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,     old_value,      new_value,  row_id)
         SELECT         _user_id,_change_action_id,_field_id_protect_id,_protect_id_old,_protect_id,_word_id ;

    UPDATE user_words
       SET word_name      = _word_name,
           description    = _description,
           phrase_type_id = _phrase_type_id,
           protect_id     = _protect_id
     WHERE word_id = _word_id
       AND user_id = _user_id;

END;

PREPARE word_update_log_002204000002_user_call FROM
    'SELECT word_update_log_002204000002_user (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT word_update_log_002204000002_user
       (1,
        2,
        10,
        'mathematics',
        'System Test Word Renamed',
        1,
        11,
        'Mathematics is an area of knowledge that includes the topics of numbers and formulas',
        null,
        12,
        'default',
        1,
        null,
        null,
        87,
        3,
        null);