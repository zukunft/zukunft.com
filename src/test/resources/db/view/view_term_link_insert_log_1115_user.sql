CREATE OR REPLACE FUNCTION view_term_link_insert_log_1115_user
    (_user_id bigint,
     _change_action_id smallint,
     _field_id_description smallint,
     _description text,
     _view_term_link_id bigint,
     _field_id_view_link_type_id smallint,
     _type_name text,
     _view_link_type_id smallint) RETURNS bigint AS

$$
BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,_description,_view_term_link_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,            new_value,            new_id,            row_id)
         SELECT         _user_id,_change_action_id,_field_id_view_link_type_id,_type_name,_view_link_type_id,_view_term_link_id ;

    INSERT INTO user_view_term_links (view_term_link_id, user_id, description, view_link_type_id)
         SELECT                      _view_term_link_id,_user_id,_description,_view_link_type_id ;

END $$ LANGUAGE plpgsql;

PREPARE view_term_link_insert_log_1115_user_call
    (bigint, smallint, smallint, text, bigint, smallint, text, smallint) AS
SELECT view_term_link_insert_log_1115_user
    ($1,$2,$3,$4,$5,$6,$7,$8);

SELECT view_term_link_insert_log_1115_user
    (1::bigint,
     1::smallint,
     727::smallint,
     'System Test description for a view term link'::text,
     0::bigint,
     726::smallint,
     'default'::text,
     1::smallint);