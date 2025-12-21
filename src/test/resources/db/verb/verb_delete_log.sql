CREATE OR REPLACE FUNCTION verb_delete_log
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_verb_name smallint,
     _verb_name          text,
     _verb_id            bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_verb_name,_verb_name,_verb_id ;

    DELETE
      FROM verbs
     WHERE verb_id = _verb_id;

END
$$ LANGUAGE plpgsql;

SELECT verb_delete_log
       (1::bigint,
        3::smallint,
        23::smallint,
        'not set'::text,
        1::bigint);