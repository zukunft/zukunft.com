CREATE OR REPLACE FUNCTION triple_insert_log_015511550000001
    (_from_phrase_id          bigint,
     _verb_id                 smallint,
     _to_phrase_id            bigint,
     _user_id                 bigint,
     _change_action_id        smallint,
     _change_table_id         smallint,
     _new_text_from           text,
     _new_text_link           text,
     _new_text_to             text,
     _field_id_triple_name    smallint,
     _triple_name             text,
     _field_id_user_id        smallint,
     _field_id_description    smallint,
     _description             text,
     _field_id_phrase_type_id smallint,
     _phrase_type_name        text,
     _phrase_type_id          smallint,
     _field_id_protect_id     smallint,
     _protect_id              smallint) RETURNS bigint AS
$$
DECLARE new_triple_id bigint;
BEGIN

    INSERT INTO triples ( from_phrase_id, verb_id, to_phrase_id)
         SELECT          _from_phrase_id,_verb_id,_to_phrase_id
      RETURNING triple_id INTO new_triple_id;

    INSERT INTO change_links ( user_id, change_action_id, change_table_id, new_text_from, new_text_link, new_text_to, new_from_id,    new_link_id, new_to_id,   row_id)
         SELECT               _user_id,_change_action_id,_change_table_id,_new_text_from,_new_text_link,_new_text_to,_from_phrase_id,_verb_id,    _to_phrase_id,new_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name,new_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,   new_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,new_triple_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,         new_value,        new_id,     row_id)
         SELECT         _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_name,_phrase_type_id,new_triple_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,     new_value, row_id)
         SELECT         _user_id,_change_action_id,_field_id_protect_id,_protect_id,new_triple_id ;

    UPDATE triples
       SET user_id        = _user_id,
           description    = _description,
           phrase_type_id = _phrase_type_id,
           protect_id     = _protect_id,
           triple_name    = _triple_name
     WHERE triples.triple_id = new_triple_id;

    RETURN new_triple_id;

END
$$ LANGUAGE plpgsql;

PREPARE triple_insert_log_015511550000001_call
        (bigint, smallint, bigint, bigint, smallint, smallint, text, text, text, smallint, text, smallint, smallint, text, smallint, text, smallint, smallint, smallint) AS
SELECT triple_insert_log_015511550000001
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19);

SELECT triple_insert_log_015511550000001
        (2::bigint,
         3::smallint,
         1::bigint,
         1::bigint,
         1::smallint,
         7::smallint,
         'constant'::text,
         'contains'::text,
         'Mathematics'::text,
         18::smallint,
         'Mathematical constant'::text,
         262::smallint,
         68::smallint,
         'A mathematical constant that never changes e.g. Pi'::text,
         69::smallint,
         'constant'::text,
         17::smallint,
         97::smallint,
         3::smallint);
