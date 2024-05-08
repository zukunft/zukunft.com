CREATE OR REPLACE FUNCTION triple_insert_log_011111110000000
    (_triple_name text,
     _user_id bigint,
     _change_action_id smallint,
     _field_id_triple_name smallint,
     _field_id_user_id smallint,
     _field_id_from_phrase_id smallint,
     _from_phrase_id bigint,
     _field_id_to_phrase_id smallint,
     _to_phrase_id bigint,
     _field_id_description smallint,
     _description text,
     _field_id_verb_id smallint,
     _verb_id bigint) RETURNS bigint AS
$$
DECLARE new_triple_id bigint;
BEGIN

    INSERT INTO triples ( triple_name)
         SELECT        _triple_name
      RETURNING         triple_id INTO new_triple_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name, new_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,   new_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,new_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,         new_value,     row_id)
         SELECT          _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_id,new_triple_id ;

    UPDATE triples
       SET user_id        = _user_id,
           description    = _description,
           phrase_type_id = _phrase_type_id
     WHERE triples.triple_id = new_triple_id;

    RETURN new_triple_id;

END
$$ LANGUAGE plpgsql;

PREPARE triple_insert_log_011111110000000_call
    (text, bigint, smallint, smallint, smallint, smallint, text, smallint, bigint) AS
SELECT triple_insert_log_011111110000000
    ($1,$2, $3, $4, $5, $6, $7, $8, $9);