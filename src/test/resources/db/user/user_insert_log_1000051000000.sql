CREATE OR REPLACE FUNCTION user_insert_log_1000051000000
    (_user_name                text,
     _user_id                  bigint,
     _change_action_id         smallint,
     _field_id_user_name       smallint,
     _field_id_user_profile_id smallint,
     _type_name                text,
     _user_profile_id          smallint,
     _field_id_email           smallint,
     _email                    text) RETURNS bigint AS
$$
DECLARE new_user_id bigint;
BEGIN

    INSERT INTO users ( user_name)
         SELECT        _user_name
      RETURNING         user_id INTO new_user_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_name,_user_name, new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,          new_value, new_id,          row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_profile_id,_type_name,_user_profile_id, new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value,row_id)
         SELECT          _user_id,_change_action_id,_field_id_email, _email,    new_user_id ;

         UPDATE users
            SET user_profile_id = _user_profile_id,
                email = _email
          WHERE users.user_id = new_user_id;

         RETURN new_user_id;

END
$$ LANGUAGE plpgsql;

PREPARE user_insert_log_1000051000000_call
    (text, bigint, smallint, smallint, smallint, text, smallint, smallint, text) AS
SELECT user_insert_log_1000051000000
    ($1,$2, $3, $4, $5, $6, $7, $8, $9);

SELECT user_insert_log_1000051000000
        ('zukunft.com system test'::text,
         2::bigint,
         1::smallint,
         211::smallint,
         81::smallint,
         'system test'::text,
         16::smallint,
         76::smallint,
         'test@zukunft.com'::text);