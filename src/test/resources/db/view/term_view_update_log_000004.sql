CREATE OR REPLACE FUNCTION term_view_update_log_000004
    (_user_id bigint,
     _change_action_id smallint,
     _field_id_view_link_type_id smallint,
     _type_name_old text,
     _view_link_type_id_old smallint,
     _type_name text,
     _view_link_type_id smallint,
     _term_view_id bigint) RETURNS void AS

$$
BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,                   old_value,     new_value, old_id,                new_id,            row_id)
         SELECT         _user_id,_change_action_id,       _field_id_view_link_type_id,_type_name_old,_type_name,_view_link_type_id_old,_view_link_type_id,_term_view_id ;

    UPDATE term_views
       SET view_link_type_id = _view_link_type_id
     WHERE term_view_id = _term_view_id;

END
$$ LANGUAGE plpgsql;

PREPARE term_view_update_log_000004_call
    (bigint, smallint, smallint, text, smallint, text, smallint, bigint) AS
SELECT term_view_update_log_000004
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT term_view_update_log_000004
    (1::bigint,
     2::smallint,
     726::smallint,
     'default'::text,
     1::smallint,
     null::text,
     null::smallint,
     0::bigint);
