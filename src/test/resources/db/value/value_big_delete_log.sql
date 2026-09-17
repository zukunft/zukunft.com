CREATE OR REPLACE FUNCTION value_big_delete_log
    (_user_id                bigint,
     _change_action_id       smallint,
     _field_id_numeric_value smallint,
     _numeric_value          numeric,
     _group_id               text) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_big ( user_id, change_action_id, change_field_id,        old_value,     group_id)
         SELECT                    _user_id,_change_action_id,_field_id_numeric_value,_numeric_value,_group_id ;

    DELETE
      FROM values_big
     WHERE group_id = _group_id;

END
$$ LANGUAGE plpgsql;

SELECT value_big_delete_log
       (3::bigint,
        3::smallint,
        1::smallint,
        3.1415926535898::numeric,
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+.uraWl+'::text);