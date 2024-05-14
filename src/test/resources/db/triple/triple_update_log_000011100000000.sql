CREATE OR REPLACE FUNCTION triple_update_log_000011100000000
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
 _phrase_type_id_old      smallint,
 _phrase_type_id          smallint) RETURNS bigint AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name_old,_triple_name,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,         old_value,          new_value,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_id_old,_phrase_type_id,_triple_id ;

    UPDATE triples
       SET triple_name    = _triple_name,
           description    = _description,
           phrase_type_id = _phrase_type_id
     WHERE triple_id = _triple_id;

END
$$ LANGUAGE plpgsql;

SELECT triple_update_log_000011100000000
        (1::bigint,
         2::smallint,
         18::smallint,
         'Mathematical constant'::text,
         'System Test Word Renamed'::text,
         1::bigint,
         68::smallint,
         'A mathematical constant that never changes e.g. Pi'::text,
         null::text,
         69::smallint,
         17::smallint,
         null::smallint);