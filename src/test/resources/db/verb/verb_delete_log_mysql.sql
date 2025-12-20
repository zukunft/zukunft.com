DROP PROCEDURE IF EXISTS verb_delete_log;
CREATE PROCEDURE verb_delete_log
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_verb_name smallint,
     _verb_name          text,
     _verb_id            bigint)

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_verb_name,_verb_name,_verb_id ;

    DELETE
      FROM verbs
     WHERE verb_id = _verb_id;

END;

SELECT verb_delete_log
       (1,
        3,
        23,
        'not set',
        1);