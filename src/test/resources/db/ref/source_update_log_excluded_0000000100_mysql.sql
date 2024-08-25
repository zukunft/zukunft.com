DROP PROCEDURE IF EXISTS source_update_log_excluded_0000000100;
CREATE PROCEDURE source_update_log_excluded_0000000100
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_excluded       smallint,
     _excluded_old            smallint,
     _excluded                smallint,
     _source_id               bigint,
     _field_id_source_name    smallint,
     _source_name_old         text)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,   old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,_excluded_old, _excluded, _source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_name,_source_name_old,_source_id ;


    UPDATE sources
       SET excluded       = _excluded
     WHERE source_id = _source_id;

END;

PREPARE source_update_log_excluded_0000000100_call FROM
    'SELECT source_update_log_excluded_0000000100 (?,?,?,?,?,?,?,?)';

SELECT source_update_log_excluded_0000000100
       (1,
        2,
        169,
        null,
        1,
        1,
        57,
        'The International System of Units');