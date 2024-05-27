CREATE OR REPLACE FUNCTION source_delete_log
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_source_name smallint,
     _source_name          text,
     _source_id            bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_name,_source_name,_source_id ;

    DELETE
      FROM sources
     WHERE source_id = _source_id;

END
$$ LANGUAGE plpgsql;

SELECT source_delete_log
       (1::bigint,
        3::smallint,
        57::smallint,
        'The International System of Units'::text,
        3::bigint);