CREATE OR REPLACE FUNCTION group_prime_insert_log_110
       (_user_id            bigint,
       _change_action_id    smallint,
       _field_id_group_name smallint,
       _group_name          text,
       _field_id_user_id    smallint,
       _group_id            bigint) RETURNS void AS

$$ BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,     new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_group_name,_group_name,_group_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,     new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,   _user_id,   _group_id ;

    INSERT INTO groups_prime ( group_id, group_name, user_id)
         SELECT               _group_id,_group_name,_user_id ;

END
$$ LANGUAGE plpgsql;

PREPARE group_prime_insert_log_110_call
    (bigint, smallint, smallint, text, smallint, bigint) AS
SELECT group_prime_insert_log_110
    ($1, $2, $3, $4, $5, $6);

SELECT group_prime_insert_log_110
        (3::bigint,
         1::smallint,
         320::smallint,
         'Pi (math)'::text,
         319::smallint,
         32770::bigint);