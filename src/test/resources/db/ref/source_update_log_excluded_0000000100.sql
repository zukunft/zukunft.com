CREATE OR REPLACE FUNCTION source_update_log_excluded_0000000100
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_excluded       smallint,
     _excluded_old            smallint,
     _excluded                smallint,
     _source_id               bigint,
     _field_id_source_name    smallint,
     _source_name             text) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,   old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,_excluded_old, _excluded, _source_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_name,_source_name,_source_id ;


    UPDATE sources
       SET excluded  = _excluded
     WHERE source_id = _source_id;

END
$$ LANGUAGE plpgsql;

PREPARE source_update_log_excluded_0000000100_call
        (bigint,smallint,smallint,smallint,smallint,bigint,smallint,text) AS
SELECT source_update_log_excluded_0000000100
        ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT source_update_log_excluded_0000000100
       (1::bigint,
        2::smallint,
        169::smallint,
        null::smallint,
        1::smallint,
        1::bigint,
        57::smallint,
        'The International System of Units'::text);