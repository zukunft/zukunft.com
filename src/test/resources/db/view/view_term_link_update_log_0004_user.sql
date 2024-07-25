CREATE OR REPLACE FUNCTION view_term_link_update_log_0004_user
    (_user_id bigint,
     _change_action_id smallint,
     _field_id_view_link_type_id smallint,
     _type_name_old text,
     _view_link_type_id_old smallint,
     _type_name text,
     _view_link_type_id smallint,
     _view_term_link_id bigint) RETURNS void AS

$$
BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,                   old_value,     new_value, old_id,                new_id,            row_id)
         SELECT         _user_id,_change_action_id,       _field_id_view_link_type_id,_type_name_old,_type_name,_view_link_type_id_old,_view_link_type_id,_view_term_link_id ;

    UPDATE user_view_term_links
       SET view_link_type_id = _view_link_type_id
     WHERE view_term_link_id = _view_term_link_id
       AND user_id = _user_id;

END
$$ LANGUAGE plpgsql;

PREPARE view_term_link_update_log_0004_user_call
    (bigint, smallint, smallint, text, smallint, text, smallint, bigint) AS
SELECT view_term_link_update_log_0004_user
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT view_term_link_update_log_0004_user
    (1::bigint,
     2::smallint,
     726::smallint,
     'default'::text,
     1::smallint,
     null::text,
     null::smallint,
     0::bigint);
