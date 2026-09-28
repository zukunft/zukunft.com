CREATE OR REPLACE FUNCTION term_view_update_log_00880400001
    (_user_id                    bigint,
     _change_action_id           smallint,
     _field_id_view_id           smallint,
     _from_view_name_old         text,
     _view_id_old                bigint,
     _from_view_name             text,
     _view_id                    bigint,
     _term_view_id               bigint,
     _field_id_term_id           smallint,
     _to_term_name_old           text,
     _term_id_old                bigint,
     _to_term_name               text,
     _term_id                    bigint,
     _field_id_view_link_type_id smallint,
     _type_name_old              text,
     _view_link_type_id_old      smallint,
     _type_name                  text,
     _view_link_type_id          smallint,
     _field_id_protect_id        smallint,
     _protect_id_old             smallint,
     _protect_id                 smallint) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            old_value,          new_value,      old_id,                new_id,            row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_id,          _from_view_name_old,_from_view_name,_view_id_old,          _view_id,          _term_view_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,            old_value,          new_value,      old_id,                new_id,            row_id)
         SELECT          _user_id,_change_action_id,_field_id_term_id,          _to_term_name_old,  _to_term_name,  _term_id_old,          _term_id,          _term_view_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,            old_value,          new_value,      old_id,                new_id,            row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_link_type_id,_type_name_old,     _type_name,     _view_link_type_id_old,_view_link_type_id,_term_view_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,            old_value,          new_value,                                                row_id)
         SELECT          _user_id,_change_action_id,_field_id_protect_id,       _protect_id_old,    _protect_id,                                              _term_view_id ;

    UPDATE term_views
       SET view_id           = _view_id,
           term_id           = _term_id,
           view_link_type_id = _view_link_type_id,
           protect_id        = _protect_id
     WHERE term_view_id = _term_view_id;

END
$$ LANGUAGE plpgsql;

PREPARE term_view_update_log_00880400001_call
        (bigint, smallint, smallint, text, bigint, text, bigint, bigint, smallint, text, bigint, text, bigint, smallint, text, smallint, text, smallint, smallint, smallint, smallint) AS
SELECT term_view_update_log_00880400001
        ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16,$17,$18,$19,$20,$21);

SELECT term_view_update_log_00880400001
       (3::bigint,
        2::smallint,
        757::smallint,
        'Mathematical constant'::text,
        161::bigint,
        'Start view'::text,
        1::bigint,
        1::bigint,
        756::smallint,
        'mathematical constant'::text,
        -1::bigint,
        'mathematics'::text,
        1::bigint,
        758::smallint,
        'main word'::text,
        1::smallint,
        null::text,
        null::smallint,
        765::smallint,
        null::smallint,
        3::smallint);