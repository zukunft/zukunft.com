CREATE OR REPLACE FUNCTION value_big_insert_log_110000_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_numeric_value  smallint,
     _numeric_value           numeric,
     _group_id                text) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_big ( user_id, change_action_id, change_field_id,        new_value,     group_id)
         SELECT                      _user_id,_change_action_id,_field_id_numeric_value,_numeric_value,_group_id ;

    INSERT INTO values_big (group_id, user_id, numeric_value, last_update)
         SELECT            _group_id,_user_id,_numeric_value, Now();

END
$$ LANGUAGE plpgsql;

PREPARE value_big_insert_log_110000_user_call
        (bigint, smallint, smallint, numeric, text) AS
    SELECT value_big_insert_log_110000_user
        ($1,$2, $3, $4, $5);

SELECT value_big_insert_log_110000_user
       (1::bigint,
        1::smallint,
        1::smallint,
        3.1415926535898::numeric,
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+'::text);
