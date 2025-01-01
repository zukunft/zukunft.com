DROP PROCEDURE IF EXISTS word_update_log_000280010000_user;
CREATE PROCEDURE word_update_log_000280010000_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_description    smallint,
     _description_old         text,
     _description             text,
     _word_id                 bigint,
     _field_id_phrase_type_id smallint,
     _phrase_type_name_old    text,
     _phrase_type_id_old      smallint,
     _phrase_type_name        text,
     _phrase_type_id          smallint,
     _field_id_plural         smallint,
     _plural_old              text,
     _plural                  text)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,            new_value,        old_id,             new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_name_old,_phrase_type_name,_phrase_type_id_old,_phrase_type_id,_word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id, old_value,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_plural,_plural_old,_plural,   _word_id ;

    UPDATE user_words
       SET description    = _description,
           phrase_type_id = _phrase_type_id,
           plural         = _plural
     WHERE word_id = _word_id
       AND user_id = _user_id;

END;

PREPARE word_update_log_000280010000_user_call FROM
    'SELECT word_update_log_000280010000_user (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT word_update_log_000280010000_user
       (1,
        2,
        11,
        'Mathematics is an area of knowledge that includes the topics of numbers and formulas',
        'System Test Word Renamed',
        1,
        12,
        'default',
        1,
        'time',
        2,
        13,
        null,
        'System Test Word Renamed');