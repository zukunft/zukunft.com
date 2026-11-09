CREATE OR REPLACE FUNCTION triple_update_log_excluded_0000200000000000100_user
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_triple_name smallint,
     _triple_name_old      text,
     _triple_name          text,
     _triple_id            bigint,
     _field_id_excluded    smallint,
     _excluded_old         smallint,
     _excluded             smallint) RETURNS void AS
$$
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

END
$$ LANGUAGE plpgsql;

PREPARE triple_update_log_excluded_0000200000000000100_user_call
        (bigint, smallint, smallint, text, text, bigint, smallint, smallint, smallint) AS
SELECT triple_update_log_excluded_0000200000000000100_user
        ($1,$2,$3,$4,$5,$6,$7,$8,$9);

SELECT triple_update_log_excluded_0000200000000000100_user
       (3::bigint,
        2::smallint,
        175::smallint,
        'mathematical constant'::text,
        null::text,
        1::bigint,
        185::smallint,
        null::smallint,
        1::smallint);