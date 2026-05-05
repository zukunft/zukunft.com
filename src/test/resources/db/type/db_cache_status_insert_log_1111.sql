CREATE OR REPLACE FUNCTION db_cache_status_insert_log_1111
    (_status_name          text,
     _user_id              bigint,
     _change_action_id     smallint,
     _field_id_status_name smallint,
     _field_id_code_id     smallint,
     _code_id              text,
     _field_id_description smallint,
     _description          text) RETURNS bigint AS
$$
DECLARE new_status_id bigint;
BEGIN

        INSERT INTO db_cache_statuum (status_name)
             SELECT                  _status_name
          RETURNING status_id INTO new_status_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
             SELECT          _user_id,_change_action_id,_field_id_status_name,_status_name, new_status_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,     new_status_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description, new_status_id ;

             UPDATE db_cache_statuum
                SET code_id     = _code_id,
                    description = _description
              WHERE db_cache_statuum.status_id = new_status_id;

             RETURN new_status_id;

END
$$ LANGUAGE plpgsql;

PREPARE db_cache_status_insert_log_1111_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text) AS
SELECT db_cache_status_insert_log_1111
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT db_cache_status_insert_log_1111
    ('clean'::text,
     1::bigint,
     1::smallint,
     871::smallint,
     872::smallint,
     'clean'::text,
     873::smallint,
     'no reason known why the cache should NOT be used'::text);