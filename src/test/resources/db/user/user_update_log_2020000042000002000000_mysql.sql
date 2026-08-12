DROP PROCEDURE IF EXISTS user_update_log_2020000042000002000000;
CREATE PROCEDURE user_update_log_2020000042000002000000
    (_user_id                  bigint,
     _change_action_id         smallint,
     _field_id_user_name       smallint,
     _user_name_old            text,
     _user_name                text,
     _field_id_email           smallint,
     _email_old                text,
     _email                    text,
     _field_id_user_profile_id smallint,
     _user_profile_name_old    text,
     _user_profile_id_old      smallint,
     _user_profile_name        text,
     _user_profile_id          smallint,
     _field_id_code_id         smallint,
     _code_id_old              text,
     _code_id                  text,
     _field_id_description     smallint,
     _description_old          text,
     _description              text)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,          old_value,             new_value,                                               row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_name,      _user_name_old,        _user_name,                                              _user_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,          old_value,             new_value,                                               row_id)
         SELECT          _user_id,_change_action_id,_field_id_email,          _email_old,            _email,                                                  _user_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,          old_value,             new_value,         old_id,              new_id,          row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_profile_id,_user_profile_name_old,_user_profile_name,_user_profile_id_old,_user_profile_id,_user_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,          old_value,             new_value,                                               row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,        _code_id_old,          _code_id,                                                _user_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,          old_value,             new_value,                                               row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,    _description_old,      _description,                                            _user_id ;

    UPDATE users
       SET user_name       = _user_name,
           email           = _email,
           user_profile_id = _user_profile_id,
           code_id         = _code_id,
           description     = _description
     WHERE user_id = _user_id;

END;

PREPARE user_update_log_2020000042000002000000_call FROM
    'SELECT user_update_log_2020000042000002000000 (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT user_update_log_2020000042000002000000
       (2,
        2,
        211,
        'zukunft.com system test',
        'zukunft.com system test partner',
        76,
        'test@zukunft.com',
        null,
        81,
        'system test',
        17,
        null,
        null,
        74,
        'test',
        null,
        213,
        'the internal zukunft.com user used for integration tests that should never be shown to the user but is used to check if integration test data is completely removed after the tests',
        null);