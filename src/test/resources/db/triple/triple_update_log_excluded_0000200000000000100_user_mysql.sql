DROP PROCEDURE IF EXISTS triple_update_log_excluded_0000200000000000100_user;
CREATE PROCEDURE triple_update_log_excluded_0000200000000000100_user
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_triple_name smallint,
     _triple_name_old      text,
     _triple_name          text,
     _triple_id            bigint,
     _field_id_excluded    smallint,
     _excluded_old         smallint,
     _excluded             smallint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name_old,_triple_name,_triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,   _excluded_old,   _excluded,   _triple_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,                    row_id)
         SELECT          _user_id,_change_action_id,_field_id_triple_name,_triple_name_old,             _triple_id ;

    UPDATE user_triples
       SET triple_name = _triple_name,
           excluded    = _excluded
     WHERE triple_id = _triple_id
       AND user_id = _user_id;

END;

PREPARE triple_update_log_excluded_0000200000000000100_user_call FROM
    'SELECT triple_update_log_excluded_0000200000000000100_user (?,?,?,?,?,?,?,?,?)';

SELECT triple_update_log_excluded_0000200000000000100_user
       (3,
        2,
        175,
        'mathematical constant',
        null,
        1,
        185,
        null,
        1);