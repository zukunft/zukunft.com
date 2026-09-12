CREATE OR REPLACE FUNCTION source_update_log_0000000500000_user
    (_user_id          bigint,
     _change_action_id smallint,
     _field_id_view_id smallint,
     _view_name_old    text,
     _view_id_old      bigint,
     _view_name        text,
     _view_id          bigint,
     _source_id        bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, old_id,      new_id,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_id,_view_name,_view_id_old,_view_id,_source_id ;

    UPDATE user_sources
       SET view_id = _view_id
     WHERE source_id = _source_id
       AND user_id = _user_id;

END
$$ LANGUAGE plpgsql;

PREPARE source_update_log_0000000500000_user_call
        (bigint, smallint, smallint, text, bigint, text, bigint, bigint) AS
SELECT source_update_log_0000000500000_user
        ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT source_update_log_0000000500000_user
       (3::bigint,
        2::smallint,
        906::smallint,
        null::text,
        null::bigint,
        null::text,
        93::bigint,
        1::bigint);