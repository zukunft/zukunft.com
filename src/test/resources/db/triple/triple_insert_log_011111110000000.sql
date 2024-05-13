CREATE OR REPLACE FUNCTION triple_insert_log_011111110000000
    (_from_phrase_id bigint,
     _verb_id smallint,
     _to_phrase_id bigint,
     _user_id bigint,
     _change_action_id smallint,
     _change_table_id smallint,
     _field_id_triple_name smallint,
     _triple_name text,
     _field_id_user_id smallint,
     _field_id_description smallint,
     _description text) RETURNS bigint AS
$$
DECLARE new_triple_id bigint;
BEGIN

    INSERT INTO triples ( from_phrase_id, verb_id, to_phrase_id)
         SELECT          _from_phrase_id,_verb_id,_to_phrase_id
      RETURNING triple_id INTO new_triple_id;

    INSERT INTO change_links ( user_id, change_action_id, change_table_id, new_from_id,    new_link_id, new_to_id,   row_id)
         SELECT               _user_id,_change_action_id,_change_table_id,_from_phrase_id,_verb_id,    _to_phrase_id,new_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name,new_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,   new_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,new_triple_id ;

    UPDATE triples
       SET user_id     = _user_id,
           description = _description,
           triple_name = _triple_name
     WHERE triples.triple_id = new_triple_id;

    RETURN new_triple_id;

END
$$ LANGUAGE plpgsql;

PREPARE triple_insert_log_011111110000000_call
    (bigint, smallint, bigint, bigint, smallint, smallint, smallint, text, smallint, smallint, text) AS
SELECT triple_insert_log_011111110000000
($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11);
