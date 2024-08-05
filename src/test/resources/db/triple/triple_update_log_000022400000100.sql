CREATE OR REPLACE FUNCTION triple_update_log_000022400000100
        (_user_id                 bigint,
         _change_action_id        smallint,
         _field_id_triple_name    smallint,
         _triple_name_old         text,
         _triple_name             text,
         _triple_id               bigint,
         _field_id_description    smallint,
         _description_old         text,
         _description             text,
         _field_id_phrase_type_id smallint,
         _phrase_type_name_old    text,
         _phrase_type_id_old      smallint,
         _phrase_type_name        text,
         _phrase_type_id          smallint,
         _field_id_excluded       smallint,
         _excluded_old            smallint,
         _excluded                smallint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name_old,_triple_name,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,            new_value,        old_id,             new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_name_old,_phrase_type_name,_phrase_type_id_old,_phrase_type_id,_triple_id ;
    INSERT INTO changes (user_id, change_action_id, change_field_id,   old_value,    new_value,row_id)
         SELECT         _user_id,_change_action_id,_field_id_excluded,_excluded_old,_excluded,_triple_id ;

    UPDATE triples
       SET triple_name    = _triple_name,
           description    = _description,
           phrase_type_id = _phrase_type_id,
           excluded       = _excluded
     WHERE triple_id = _triple_id;

END
$$ LANGUAGE plpgsql;

PREPARE triple_update_log_000022400000100_call
        (bigint, smallint, smallint, text, text, bigint, smallint, text, text, smallint, text, smallint, text, smallint, smallint, smallint, smallint) AS
SELECT triple_update_log_000022400000100
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12, $13, $14,$15, $16, $17);

SELECT triple_update_log_000022400000100
        (1::bigint,
         2::smallint,
         18::smallint,
         'Mathematical constant'::text,
         ''::text,
         1::bigint,
         68::smallint,
         'A mathematical constant that never changes e.g. Pi'::text,
         ''::text,
         69::smallint,
         'constant'::text,
         17::smallint,
         null::text,
         0::smallint,
         21::smallint,
         null::smallint,
         1::smallint);