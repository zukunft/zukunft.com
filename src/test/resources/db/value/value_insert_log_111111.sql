CREATE OR REPLACE FUNCTION value_insert_log_111111
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_numeric_value  smallint,
     _numeric_value           numeric,
     _group_id                text,
     _field_id_source_id      smallint,
     _source_id               bigint,
     _field_id_excluded       smallint,
     _excluded                smallint,
     _field_id_share_type_id  smallint,
     _share_type_id           smallint,
     _field_id_protect_id     smallint,
     _protect_id              smallint) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_norm ( user_id, change_action_id, change_field_id,        new_value,     group_id)
         SELECT                     _user_id,_change_action_id,_field_id_numeric_value,_numeric_value,_group_id ;

    INSERT INTO change_values_norm (user_id, change_action_id, change_field_id,    new_value, group_id)
         SELECT                     _user_id,_change_action_id,_field_id_source_id,_source_id,_group_id ;

    INSERT INTO change_values_norm (user_id, change_action_id, change_field_id,   new_value, group_id)
         SELECT                     _user_id,_change_action_id,_field_id_excluded,_excluded, _group_id ;

    INSERT INTO change_values_norm (user_id, change_action_id, change_field_id,        new_value,     group_id)
         SELECT                     _user_id,_change_action_id,_field_id_share_type_id,_share_type_id,_group_id ;

    INSERT INTO change_values_norm (user_id, change_action_id, change_field_id,     new_value,  group_id)
         SELECT                     _user_id,_change_action_id,_field_id_protect_id,_protect_id,_group_id ;

    INSERT INTO values (group_id, user_id, numeric_value, share_type_id, protect_id, last_update)
         SELECT        _group_id,_user_id,_numeric_value,_share_type_id,_protect_id, Now();

END
$$ LANGUAGE plpgsql;

PREPARE value_insert_log_111111_call
        (bigint, smallint, smallint, numeric, text, smallint, bigint, smallint, smallint, smallint, smallint, smallint, smallint) AS
    SELECT value_insert_log_111111
        ($1,$2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13);

SELECT value_insert_log_111111
       (1::bigint,
        1::smallint,
        1::smallint,
        3.1415926535898::numeric,
        '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+'::text,
        2::smallint,
        1::bigint,
        5::smallint,
        1::smallint,
        3::smallint,
        3::smallint,
        4::smallint,
        2::smallint);
