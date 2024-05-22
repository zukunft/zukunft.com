DROP PROCEDURE IF EXISTS triple_insert_log_011111110000000;
CREATE PROCEDURE triple_insert_log_011111110000000
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
     _phrase_type_id          smallint)
BEGIN

    INSERT INTO triples ( from_phrase_id, verb_id, to_phrase_id)
         SELECT          _from_phrase_id,_verb_id,_to_phrase_id ;

    SELECT LAST_INSERT_ID() AS @new_triple_id; SELECT LAST_INSERT_ID() AS @new_triple_id;

    INSERT INTO change_links ( user_id, change_action_id, change_table_id, new_text_from, new_text_link, new_text_to, new_from_id,    new_link_id, new_to_id,    row_id)
         SELECT               _user_id,_change_action_id,_change_table_id,_new_text_from,_new_text_link,_new_text_to,_from_phrase_id,_verb_id,    _to_phrase_id,@new_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name,@new_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,   @new_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_triple_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,         new_value,        new_id,         row_id)
         SELECT         _user_id,_change_action_id,_field_id_phrase_type_id,_phrase_type_name,_phrase_type_id,@new_triple_id ;

    UPDATE triples
       SET user_id        = _user_id,
           description    = _description,
           phrase_type_id = _phrase_type_id,
           triple_name    = _triple_name
     WHERE triples.triple_id = @new_triple_id;

END;

PREPARE triple_insert_log_011111110000000_call FROM
'SELECT triple_insert_log_011111110000000 (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT triple_insert_log_011111110000000
        (2,
         3,
         1,
         1,
         1,
         7,
         'constant',
         'contains',
         'Mathematics',
         18,
         'Mathematical constant',
         262,
         68,
         'A mathematical constant that never changes e.g. Pi',
         69,
         'constant',
         17);
