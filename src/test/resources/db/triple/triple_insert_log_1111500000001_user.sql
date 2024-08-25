CREATE OR REPLACE FUNCTION triple_insert_log_1111500000001_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_triple_name    smallint,
     _triple_name             text,
     _triple_id               bigint,
     _field_id_description    smallint,
     _description             text,
     _field_id_phrase_type_id smallint,
     _phrase_type_name        text,
     _phrase_type_id          smallint,
     _field_id_protect_id     smallint,
     _protect_id              smallint) RETURNS bigint AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name,_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,_triple_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,         new_value,        new_id,,      row_id)
         SELECT         _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_name,_phrase_type_id,_triple_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,     new_value,  row_id)
         SELECT         _user_id,_change_action_id,_field_id_protect_id,_protect_id,_triple_id ;

    INSERT INTO user_triples ( triple_id, user_id, triple_name, description, phrase_type_id, protect_id)
         SELECT               _triple_id,_user_id,_triple_name,_description,_phrase_type_id,_protect_id ;

END
$$ LANGUAGE plpgsql;

PREPARE triple_insert_log_1111500000001_user_call
        (bigint, smallint, smallint, text, bigint, smallint, text, smallint, text, smallint, smallint, smallint) AS
    SELECT triple_insert_log_1111500000001_user
        ($1,$2,$3,$4,$5,$6,$7,$8,$9, $10, $11, $12);

SELECT triple_insert_log_1111500000001_user (
               1::bigint,
               1::smallint,
               18::smallint,
               'Mathematical constant'::text,
               1::bigint,
               68::smallint,
               'A mathematical constant that never changes e.g. Pi'::text,
               69::smallint,
               'constant'::text,
               17::smallint,
               97::smallint,
               3::smallint);