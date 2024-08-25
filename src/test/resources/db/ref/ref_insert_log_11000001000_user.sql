CREATE OR REPLACE FUNCTION ref_insert_log_11000001000_user
    (_user_id               bigint,
     _change_action_id      smallint,
     _field_id_description  smallint,
     _description           text,
     _ref_id                bigint) RETURNS bigint AS

$$
BEGIN

    INSERT INTO changes (user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT         _user_id,_change_action_id,_field_id_description,_description,_ref_id ;

    INSERT INTO user_refs (ref_id, user_id, description)
         SELECT           _ref_id,_user_id,_description ;

END
$$ LANGUAGE plpgsql;

PREPARE ref_insert_log_11000001000_user_call
        (bigint,smallint,smallint,text,bigint) AS
SELECT ref_insert_log_11000001000_user
        ($1,$2,$3,$4,$5);

SELECT ref_insert_log_11000001000_user (
               1::bigint,
               1::smallint,
               65::smallint,
               'pi - ratio of the circumference of a circle to its diameter'::text,
               4::bigint);
