CREATE OR REPLACE FUNCTION value_update_log_0210000
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_numeric_value  smallint,
     _numeric_value_old       numeric,
     _numeric_value           numeric,
     _group_id                text) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        old_value,         new_value,     group_id)
         SELECT                      _user_id,_change_action_id,_field_id_numeric_value,_numeric_value_old,_numeric_value,_group_id ;

    UPDATE values
       SET numeric_value = _numeric_value,
           last_update = Now()
     WHERE group_id = _group_id;

END
$$ LANGUAGE plpgsql;

PREPARE value_update_log_0210000_call
        (bigint, smallint, smallint, numeric, numeric, text) AS
    SELECT value_update_log_0210000
        ($1,$2, $3, $4, $5, $6);

SELECT value_update_log_0210000
       (1::bigint,
        1::smallint,
        1::smallint,
        123.456::numeric,
        3.1415926535898::numeric,
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+'::text);
