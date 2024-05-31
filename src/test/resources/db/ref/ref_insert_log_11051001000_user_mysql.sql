DROP PROCEDURE IF EXISTS ref_insert_log_11051001000_user;
CREATE PROCEDURE ref_insert_log_11051001000_user (
    _user_id bigint,
    _change_action_id smallint,
    _field_id_phrase_id smallint,
    _phrase_name text,
    _phrase_id smallint,
    _ref_id bigint,
    _field_id_external_key smallint,
    _external_key text,
    _field_id_description smallint,
    _description text)

BEGIN

    INSERT INTO changes (user_id,change_action_id,change_field_id,new_value,new_id,row_id)
    SELECT _user_id,_change_action_id,_field_id_phrase_id,_phrase_name,_phrase_id,_ref_id ;

    INSERT INTO changes (user_id,change_action_id,change_field_id,old_value,new_value,row_id)
    SELECT _user_id,_change_action_id,_field_id_external_key,_external_key_old,_external_key,_ref_id ;

    INSERT INTO changes (user_id,change_action_id,change_field_id,new_value,row_id)
    SELECT _user_id,_change_action_id,_field_id_description,_description,_ref_id ;

    INSERT INTO user_refs (ref_id,user_id,description)
    SELECT _ref_id,_user_id,_description ;

END;

PREPARE ref_insert_log_11051001000_user_call FROM
    'SELECT ref_insert_log_11051001000_user (?,?,?,?,?,?,?,?,?,?)';

SELECT ref_insert_log_11051001000_user (
               1,
               1,
               159,
               'contains',
               3,4,
               160,
               'Q167',
               65,
               'ratio of the circumference of a circle to its diameter');