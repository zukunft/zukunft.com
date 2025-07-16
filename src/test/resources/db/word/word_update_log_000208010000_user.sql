CREATE OR REPLACE FUNCTION word_update_log_000208010000_user
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
     _plural                  text) RETURNS void AS
$$
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

END
$$ LANGUAGE plpgsql;

PREPARE word_update_log_000208010000_user_call
        (bigint, smallint, smallint, text, text, bigint, smallint, text, smallint, text, smallint, smallint, text, text) AS
    SELECT word_update_log_000208010000_user
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11, $12,$13,$14);

SELECT word_update_log_000208010000_user
        (1::bigint,
         2::smallint,
         11::smallint,
         'Mathematics is an area of knowledge that includes the topics of numbers and formulas'::text,
         'System Test Word Renamed'::text,
         1::bigint,
         12::smallint,
         'default'::text,
         1::smallint,
         'time'::text,
         2::smallint,
         13::smallint,
         null::text,
         'System Test Word Renamed'::text);
