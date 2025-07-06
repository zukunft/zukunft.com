CREATE OR REPLACE FUNCTION word_update_log_002243222222
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
     _field_id_code_id        smallint,
     _code_id_old             text,
     _code_id                 text,
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
     _protect_id              smallint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,            new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_word_name,     _word_name_old,       _word_name,                                           _word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,            new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,   _description_old,     _description,                                         _word_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,            new_value,        old_id,             new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_name_old,_phrase_type_name,_phrase_type_id_old,_phrase_type_id,_word_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,          old_value,            new_value,        old_id,             new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_id,        _view_name_old,       _view_name,       _view_id_old,       _view_id,       _word_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,          old_value,            new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,         _code_id_old,        _code_id,                                             _word_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,          old_value,            new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_plural,         _plural_old,          _plural,                                              _word_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,          old_value,            new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_values,         _values_old,          _values,                                              _word_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,          old_value,            new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,       _excluded_old,        _excluded,                                            _word_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,          old_value,            new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_share_type_id,  _share_type_id_old,   _share_type_id,                                       _word_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,          old_value,            new_value,                                            row_id)
         SELECT          _user_id,_change_action_id,_field_id_protect_id,     _protect_id_old,      _protect_id,                                          _word_id ;

    UPDATE words
       SET word_name      = _word_name,
           description    = _description,
           phrase_type_id = _phrase_type_id,
           view_id        = _view_id,
           code_id        = _code_id,
           plural         = _plural,
           values         = _values,
           excluded       = _excluded,
           share_type_id  = _share_type_id,
           protect_id     = _protect_id
     WHERE word_id = _word_id;

END
$$ LANGUAGE plpgsql;

PREPARE word_update_log_002243222222_call
        (bigint, smallint, smallint, text, text, bigint, smallint, text, text, smallint, text, smallint, text, smallint, smallint, text, bigint, text, bigint, smallint, text, text, smallint, text, text, smallint, bigint, bigint, smallint, smallint, smallint, smallint, smallint, smallint, smallint, smallint, smallint) AS
SELECT word_update_log_002243222222
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,$26,$27,$28,$29,$30,$31,$32,$33,$34,$35,$36,$37);

SELECT word_update_log_002243222222
       (1::bigint,
        2::smallint,
        10::smallint,
        'mathematics'::text,
        'System Test Word Renamed'::text,
        1::bigint,
        11::smallint,
        'Mathematics is an area of knowledge that includes the topics of numbers and formulas'::text,
        null::text,
        12::smallint,
        'scaling'::text,
        7::smallint,
        null::text,
        null::smallint,
        85::smallint,
        ''::text,
        1::bigint,
        null::text,
        null::bigint,
        307::smallint,
        'mathematics'::text,
        null::text,
        13::smallint,
        'mathematics'::text,
        null::text,
        84::smallint,
        2::bigint,
        null::bigint,
        14::smallint,
        1::smallint,
        0::smallint,
        86::smallint,
        3::smallint,
        null::smallint,
        87::smallint,
        2::smallint,
        null::smallint);