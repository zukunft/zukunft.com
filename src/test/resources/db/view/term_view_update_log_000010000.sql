CREATE OR REPLACE FUNCTION term_view_update_log_000010000
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_description smallint,
     _description_old      text,
     _description          text,
     _term_view_id         bigint) RETURNS void AS

$$
BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,_description_old,_description,_term_view_id ;

    UPDATE term_views
       SET description = _description
     WHERE term_view_id = _term_view_id;

END
$$ LANGUAGE plpgsql;

PREPARE term_view_update_log_000010000_call
    (bigint, smallint, smallint, text, text, bigint) AS
SELECT term_view_update_log_000010000
    ($1,$2,$3,$4,$5,$6);

SELECT term_view_update_log_000010000
    (3::bigint,
     2::smallint,
     727::smallint,
     null::text,
     'System Test description for a view term link'::text,
     0::bigint);
