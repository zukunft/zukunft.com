DROP PROCEDURE IF EXISTS user_insert_log_111111115151511111555;
CREATE PROCEDURE user_insert_log_111111115151511111555
    (_user_name                   text,
     _user_id                     bigint,
     _change_action_id            smallint,
     _field_id_user_name          smallint,
     _field_id_ip_address         smallint,
     _ip_address                  text,
     _field_id_email              smallint,
     _email                       text,
     _field_id_password           smallint,
     _password                    text,
     _field_id_activation_key     smallint,
     _activation_key              text,
     _field_id_activation_timeout smallint,
     _activation_timeout          timestamp,
     _field_id_last_login         smallint,
     _last_login                  timestamp,
     _field_id_last_logoff        smallint,
     _last_logoff                 timestamp,
     _field_id_user_profile_id    smallint,
     _user_profile_name           text,
     _user_profile_id             smallint,
     _field_id_code_id            smallint,
     _code_id                     text,
     _field_id_user_type_id       smallint,
     _type_name                   text,
     _user_type_id                smallint,
     _field_id_right_level        smallint,
     _right_level                 smallint,
     _field_id_user_status_id     smallint,
     _user_status_name            text,
     _user_status_id              smallint,
     _field_id_excluded           smallint,
     _excluded                    smallint,
     _field_id_created            smallint,
     _created                     timestamp,
     _field_id_description        smallint,
     _description                 text,
     _field_id_first_name         smallint,
     _first_name                  text,
     _field_id_last_name          smallint,
     _last_name                   text,
     _field_id_term_id            smallint,
     _term_name                   text,
     _term_id                     bigint,
     _field_id_view_id            smallint,
     _view_name                   text,
     _view_id                     bigint,
     _field_id_source_id          smallint,
     _source_name                 text,
     _source_id                   bigint)

BEGIN

    INSERT INTO users ( user_name)
         SELECT        _user_name ;

    SELECT LAST_INSERT_ID() AS @new_user_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_name,         _user_name,                          @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_address,        _ip_address,                         @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_email,             _email,                              @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_password,          _password,                           @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_activation_key,    _activation_key,                     @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_activation_timeout,_activation_timeout,                 @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_last_login,        _last_login,                         @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_last_logoff,       _last_logoff,                        @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,         new_id,           row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_profile_id,   _user_profile_name,_user_profile_id, @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
        SELECT          _user_id,_change_action_id,_field_id_code_id,           _code_id,                            @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,         new_id,           row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_type_id,      _type_name,        _user_type_id,    @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_right_level,       _right_level,                        @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,         new_id,           row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_status_id,    _user_status_name, _user_status_id,  @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,          _excluded,                           @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_created,           _created,                            @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,       _description,                        @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_first_name,        _first_name,                         @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,                           row_id)
         SELECT          _user_id,_change_action_id,_field_id_last_name,         _last_name,                          @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,         new_id,           row_id)
         SELECT          _user_id,_change_action_id,_field_id_term_id,           _term_name,        _term_id,         @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,         new_id,           row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_id,           _view_name,        _view_id,         @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,             new_value,         new_id,           row_id)
         SELECT          _user_id,_change_action_id,_field_id_source_id,         _source_name,      _source_id,       @new_user_id ;


         UPDATE users
            SET ip_address         = _ip_address,
                email              = _email,
                password           = _password,
                activation_key     = _activation_key,
                activation_timeout = _activation_timeout,
                last_login         = _last_login,
                last_logoff        = _last_logoff,
                user_profile_id    = _user_profile_id,
                code_id            = _code_id,
                user_type_id       = _user_type_id,
                right_level        = _right_level,
                user_status_id     = _user_status_id,
                excluded           = _excluded,
                created            = _created,
                description        = _description,
                first_name         = _first_name,
                last_name          = _last_name,
                term_id            = _term_id,
                view_id            = _view_id,
                source_id          = _source_id
          WHERE users.user_id = @new_user_id;

END;

PREPARE user_insert_log_111111115151511111555_call FROM
    'SELECT user_insert_log_111111115151511111555 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT user_insert_log_111111115151511111555
        ('zukunft.com system write test user',
         2,
         1,
         211,
         75,
         '258.257.256.255',
         76,
         'test@zukunft.com',
         null,
         '$2y$12$ptUFPCd9OihCWBlz4.3daOcLAVDFp8tnbRHVJg42915QeTHnAZiQy',
         null,
         '376913',
         null,
         2026-02-03 17:59:59,
         229,
         2026-02-02 17:59:59,
         230,
         2026-02-02 18:59:59,
         81,
         'ip only',
         1,
         74,
         '376913',
         214,
         'Guest',
         1,
         215,
         1,
         227,
         'active',
         1,
         790,
         1,
         228,
         2026-02-02 17:59:59,
         213,
         'test description if it can be added to the user via import',
         77,
         'zukunft.com system write test user',
         78,
         'zukunft.com system write test user last name',
         79,
         'mathematics',
         1,
         226,
         'The International System of Units',
         1,
         80,
         'The International System of Units',
         1);