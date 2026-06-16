CREATE OR REPLACE FUNCTION triple_update_log_00442244000000000
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_from_phrase_id smallint,
     _from_phrase_name_old    text,
     _from_phrase_id_old      bigint,
     _from_phrase_name        text,
     _from_phrase_id          bigint,
     _triple_id               bigint,
     _field_id_to_phrase_id   smallint,
     _to_phrase_name_old      text,
     _to_phrase_id_old        bigint,
     _to_phrase_name          text,
     _to_phrase_id            bigint,
     _field_id_triple_name    smallint,
     _triple_name_old         text,
     _triple_name             text,
     _field_id_description    smallint,
     _description_old         text,
     _description             text,
     _field_id_phrase_type_id smallint,
     _phrase_type_name_old    text,
     _phrase_type_id_old      smallint,
     _phrase_type_name        text,
     _phrase_type_id          smallint,
     _field_id_verb_id        smallint,
     _verb_name_old           text,
     _verb_id_old             smallint,
     _verb_name               text,
     _verb_id                 smallint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,            new_value,        old_id,             new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_from_phrase_id,_from_phrase_name_old,_from_phrase_name,_from_phrase_id_old,_from_phrase_id,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,       old_value,          new_value,      old_id,           new_id,       row_id)
         SELECT          _user_id,_change_action_id,_field_id_to_phrase_id,_to_phrase_name_old,_to_phrase_name,_to_phrase_id_old,_to_phrase_id,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name_old,_triple_name,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,            new_value,        old_id,             new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_name_old,_phrase_type_name,_phrase_type_id_old,_phrase_type_id,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,  old_value,     new_value, old_id,      new_id,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_verb_id,_verb_name_old,_verb_name,_verb_id_old,_verb_id,_triple_id ;

    UPDATE triples
       SET from_phrase_id = _from_phrase_id,
           to_phrase_id   = _to_phrase_id,
           triple_name    = _triple_name,
           description    = _description,
           phrase_type_id = _phrase_type_id,
           verb_id        = _verb_id
     WHERE triple_id = _triple_id;

END
$$ LANGUAGE plpgsql;

PREPARE triple_update_log_00442244000000000_call
        (bigint, smallint, smallint, text, bigint, text, bigint, bigint, smallint, text, bigint, text, bigint, smallint, text, text, smallint, text, text, smallint, text, smallint, text, smallint, smallint, text, smallint, text, smallint) AS
SELECT triple_update_log_00442244000000000
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21,$22,$23,$24,$25,$26,$27,$28,$29);

SELECT triple_update_log_00442244000000000
       (3::bigint,
        2::smallint,
        259::smallint,
        'constant'::text,
        2::bigint,
        ''::text,
        0::bigint,
        1::bigint,
        261::smallint,
        'mathematics'::text,
        1::bigint,
        ''::text,
        0::bigint,
        18::smallint,
        'mathematical constant'::text,
        'System Test Triple renamed'::text,
        68::smallint,
        'A mathematical constant that never changes e.g. Pi'::text,
        null::text,
        69::smallint,
        'math constant'::text,
        17::smallint,
        null::text,
        null::smallint,
        260::smallint,
        'is part of'::text,
        3::smallint,
        null::text,
        null::smallint);