DROP PROCEDURE IF EXISTS verb_insert_log_1100000000;
CREATE PROCEDURE verb_insert_log_1100000000
    (_verb_name            text,
     _user_id              bigint,
     _change_action_id     smallint,
     _field_id_verb_name   smallint)
BEGIN

    INSERT INTO verbs ( verb_name)
         SELECT        _verb_name ;

    SELECT LAST_INSERT_ID() AS @new_verb_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_verb_name,_verb_name,@new_verb_id ;

END;

PREPARE verb_insert_log_1100000000_call FROM
    'SELECT verb_insert_log_1100000000 (?, ?, ?, ?)';

SELECT verb_insert_log_1100000000
    ('System Test Verb',
     3,
     1,
     23);