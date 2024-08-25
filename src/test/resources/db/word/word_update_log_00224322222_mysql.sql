DROP PROCEDURE IF EXISTS word_update_log_00224322222;
CREATE PROCEDURE word_update_log_00224322222
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
     _field_id_view_id        smallint,
     _view_name_old           text,
     _view_id_old             bigint,
     _view_name               text,
     _view_id                 bigint,
     _field_id_plural         smallint,
     _plural_old              text,
     _plural                  text,
     _field_id_values         smallint,
     _values_old              bigint,
     _values                  bigint,
     _field_id_excluded       smallint,
     _excluded_old            smallint,
     _excluded                smallint,
     _field_id_share_type_id  smallint,
     _share_type_id_old       smallint,
     _share_type_id           smallint,
     _field_id_protect_id     smallint,
     _protect_id_old          smallint,
     _protect_id              smallint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,            new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_name,     _word_name_old,       _word_name,                                           _word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,            new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,   _description_old,     _description,                                         _word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,            new_value,        old_id,             new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_name_old,_phrase_type_name,_phrase_type_id_old,_phrase_type_id,_word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,            new_value,        old_id,             new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_id,       _view_name_old,       _view_name,       _view_id_old,       _view_id,       _word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,        old_value,             new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_plural,       _plural_old,           _plural,                                              _word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,        old_value,             new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_values,       _values_old,           _values,                                              _word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,        old_value,             new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,     _excluded_old,         _excluded,                                            _word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,        old_value,             new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_share_type_id,_share_type_id_old,    _share_type_id,                                       _word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,        old_value,             new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_protect_id,   _protect_id_old,       _protect_id,                                          _word_id ;

    UPDATE words
       SET word_name      = _word_name,
           description    = _description,
           phrase_type_id = _phrase_type_id,
           view_id        = _view_id,
           plural         = _plural,
           `values`       = _values,
           excluded       = _excluded,
           share_type_id  = _share_type_id,
           protect_id     = _protect_id
     WHERE word_id = _word_id;

END;

PREPARE word_update_log_00224322222_call FROM
    'SELECT word_update_log_00224322222 (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT word_update_log_00224322222
       (1,
        2,
        10,
        'Mathematics',
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
        85,
        '',
        1,
        null,
        null,
        13,
        'Mathematics',
        null,
        84,
        2,
        null,
        14,
        1,
        0,
        86,
        3,
        null,
        87,
        2,
        null);