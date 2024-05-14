DROP PROCEDURE IF EXISTS triple_delete_log;
CREATE PROCEDURE triple_delete_log
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_triple_name smallint,
     _triple_name          text,
     _triple_id            bigint)

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name,_triple_id ;

    DELETE
      FROM triples
     WHERE triple_id = _triple_id;

END;

SELECT triple_delete_log
       (1,
        3,
        18,
        'Mathematical constant',
        1);