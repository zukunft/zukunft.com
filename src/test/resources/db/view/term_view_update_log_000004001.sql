CREATE OR REPLACE FUNCTION term_view_update_log_000004001
    (_user_id                    bigint,
     _change_action_id           smallint,
     _field_id_view_link_type_id smallint,
     _type_name_old              text,
     _view_link_type_id_old      smallint,
     _type_name                  text,
     _view_link_type_id          smallint,
     _term_view_id               bigint,
     _field_id_protect_id        smallint,
     _protect_id_old             smallint,
     _protect_id                 smallint) RETURNS void AS

$$
BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,                   old_value,     new_value, old_id,                new_id,            row_id)
         SELECT         _user_id,_change_action_id,       _field_id_view_link_type_id,_type_name_old,_type_name,_view_link_type_id_old,_view_link_type_id,_term_view_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,                old_value,        new_value,                                          row_id)
         SELECT          _user_id,_change_action_id,_field_id_protect_id,           _protect_id_old,  _protect_id,                                        _term_view_id ;

    UPDATE term_views
       SET view_link_type_id = _view_link_type_id,
           protect_id        = _protect_id
     WHERE term_view_id = _term_view_id;

END
$$ LANGUAGE plpgsql;

PREPARE term_view_update_log_000004001_call
    (bigint, smallint, smallint, text, smallint, text, smallint, bigint, smallint, smallint, smallint) AS
SELECT term_view_update_log_000004001
    ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11);

SELECT term_view_update_log_000004001
    (3::bigint,
     2::smallint,
     726::smallint,
     'main word'::text,
     1::smallint,
     null::text,
     null::smallint,
     1::bigint,
     730::smallint,
     null::smallint,
     3::smallint);
