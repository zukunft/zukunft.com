DROP PROCEDURE IF EXISTS db_cache_status_insert_log_1111;
CREATE PROCEDURE db_cache_status_insert_log_1111
    (_status_name          text,
     _user_id              bigint,
     _change_action_id     smallint,
     _field_id_status_name smallint,
     _field_id_code_id     smallint,
     _code_id              text,
     _field_id_description smallint,
     _description          text)
BEGIN

    INSERT INTO db_cache_statuum ( status_name)
         SELECT                   _status_name ;

         SELECT LAST_INSERT_ID()
             AS @new_status_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_status_name,_status_name,@new_status_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_status_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_status_id ;

        UPDATE db_cache_statuum
           SET code_id     = _code_id,
               description = _description
         WHERE db_cache_statuum.status_id = @new_status_id;

END;

PREPARE db_cache_status_insert_log_1111_call
    FROM 'SELECT db_cache_status_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT db_cache_status_insert_log_1111
    ('clean',
     1,
     1,
     871,
     872,
     'clean',
     873,
     'no reason known why the cache should NOT be used');
