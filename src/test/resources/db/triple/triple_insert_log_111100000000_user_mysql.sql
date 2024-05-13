DROP PROCEDURE IF EXISTS triple_insert_log_111100000000_user;
CREATE PROCEDURE triple_insert_log_111100000000_user
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_triple_name smallint,
     _triple_name          text,
     _triple_id            bigint,
     _field_id_description smallint,
     _description          text)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name,_triple_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,_triple_id ;

    INSERT INTO user_triples ( triple_id, user_id, triple_name, description)
    SELECT                    _triple_id,_user_id,_triple_name,_description ;

END;

PREPARE triple_insert_log_111100000000_user_call FROM
'SELECT triple_insert_log_111100000000_user (?,?,?,?,?,?,?)';
