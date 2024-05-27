CREATE OR REPLACE FUNCTION triple_delete_log_user
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_triple_name smallint,
     _triple_name          text,
     _triple_id            bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name,_triple_id ;

    DELETE
      FROM user_triples
     WHERE triple_id = _triple_id
       AND user_id = _user_id;

END
$$ LANGUAGE plpgsql;

SELECT triple_delete_log_user
       (1::bigint,
        3::smallint,
        18::smallint,
        'Mathematical constant'::text,
        1::bigint);