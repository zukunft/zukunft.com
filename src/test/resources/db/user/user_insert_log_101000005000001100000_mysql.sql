DROP PROCEDURE IF EXISTS user_insert_log_101000005000001100000;
CREATE PROCEDURE user_insert_log_101000005000001100000
    (_user_name                text,
     _user_id                  bigint,
     _change_action_id         smallint,
     _field_id_user_name       smallint,
     _field_id_email           smallint,
     _email                    text,
     _field_id_user_profile_id smallint,
     _user_profile_name        text,
     _user_profile_id          smallint,
     _field_id_created         smallint,
     _created                  timestamp,
     _field_id_description     smallint,
     _description              text)

BEGIN

    INSERT INTO users ( user_name)
         SELECT        _user_name ;

    SELECT LAST_INSERT_ID() AS @new_user_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,                             new_value,       row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_name,                         _user_name,      @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,                             new_value,       row_id)
         SELECT          _user_id,_change_action_id,_field_id_email,                             _email,          @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,          new_value,         new_id,          row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_profile_id,_user_profile_name,_user_profile_id,@new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                       row_id)
         SELECT          _user_id,_change_action_id,_field_id_created,           _created,                        @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,                             new_value,       row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,                       _description,    @new_user_id ;

         UPDATE users
            SET email           = _email,
                user_profile_id = _user_profile_id,
                created         = _created,
                description     = _description
          WHERE users.user_id = @new_user_id;

END;

PREPARE user_insert_log_101000005000001100000_call FROM
    'SELECT user_insert_log_101000005000001100000 (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT user_insert_log_101000005000001100000
        ('zukunft.com system test',
         2,
         1,
         211,
         76,
         'test@zukunft.com',
         81,
         'system test',
         16,
         228,
         2026-02-02 17:59:59,
         213,
         'the internal zukunft.com user used for integration tests that should never be shown to the user but is used to check if integration test data is completely removed after the tests');