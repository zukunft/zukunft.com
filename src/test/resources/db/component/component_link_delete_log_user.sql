CREATE OR REPLACE FUNCTION component_link_delete_log_user
    (_user_id                bigint,
     _change_action_id       smallint,
     _component_link_id      bigint) RETURNS void AS

$$
BEGIN

    INSERT INTO changes (user_id, change_action_id, row_id)
         SELECT         _user_id,_change_action_id,_component_link_id ;

    DELETE FROM user_component_links
          WHERE component_link_id = _component_link_id
            AND user_id = _user_id;

END
$$ LANGUAGE plpgsql;

SELECT component_link_delete_log_user
       (1::bigint,
        3::smallint,
        1::bigint);
