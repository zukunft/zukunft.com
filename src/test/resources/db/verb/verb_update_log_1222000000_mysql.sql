DROP PROCEDURE IF EXISTS verb_update_log_1222000000;
CREATE PROCEDURE verb_update_log_1222000000
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_verb_name   smallint,
     _verb_name_old        text,
     _verb_name            text,
     _verb_id              bigint,
     _field_id_code_id     smallint,
     _code_id_old          text,
     _code_id              text,
     _field_id_description smallint,
     _description_old      text,
     _description          text)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_verb_name,_verb_name_old,_verb_name,_verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  old_value,   new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,_code_id_old,_code_id,  _verb_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_verb_id ;

    UPDATE verbs
       SET verb_name     = _verb_name,
           code_id        = _code_id,
           description    = _description
     WHERE verb_id = _verb_id;

END;

PREPARE verb_update_log_1222000000_call FROM
    'SELECT verb_update_log_1222000000 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT verb_update_log_1222000000
    (3,
     1,
     23,
     'not set',
     'System Test Verb Renamed',
     1,
     24,
     'not_set',
     null,
     25,
     'no verb / predicate selected',
     null);