CREATE OR REPLACE FUNCTION user_delete_log
    (_req_user_id        bigint,
     _change_action_id   smallint,
     _field_id_user_name smallint,
     _user_name          text,
     _user_id            bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id,     change_action_id, change_field_id,    old_value, row_id)
         SELECT          _req_user_id,_change_action_id,_field_id_user_name,_user_name,_user_id ;

    DELETE
      FROM users
     WHERE user_id = _user_id;

END
$$ LANGUAGE plpgsql;

SELECT user_delete_log
       (2::bigint,
        3::smallint,
        211::smallint,
        'zukunft.com system test'::text,
        3::bigint);