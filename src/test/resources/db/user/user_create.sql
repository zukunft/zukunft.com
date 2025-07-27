-- --------------------------------------------------------

--
-- table structure for users including system users; only users can add data
--

CREATE TABLE IF NOT EXISTS users
(
    user_id            BIGSERIAL PRIMARY KEY,
    user_name          varchar(255) NOT NULL,
    ip_address         varchar(100) DEFAULT NULL,
    password           varchar(255) DEFAULT NULL,
    description        text         DEFAULT NULL,
    code_id            varchar(100) DEFAULT NULL,
    user_profile_id    bigint       DEFAULT NULL,
    user_type_id       bigint       DEFAULT NULL,
    excluded           smallint     DEFAULT NULL,
    right_level        smallint     DEFAULT NULL,
    email              varchar(255) DEFAULT NULL,
    email_status       smallint     DEFAULT NULL,
    email_alternative  varchar(255) DEFAULT NULL,
    mobile_number      varchar(100) DEFAULT NULL,
    mobile_status      smallint     DEFAULT NULL,
    activation_key     varchar(255) DEFAULT NULL,
    activation_timeout timestamp    DEFAULT NULL,
    first_name         varchar(255) DEFAULT NULL,
    last_name          varchar(255) DEFAULT NULL,
    name_triple_id     bigint       DEFAULT NULL,
    geo_triple_id      bigint       DEFAULT NULL,
    geo_status_id      smallint     DEFAULT NULL,
    official_id        varchar(255) DEFAULT NULL,
    official_id_type   smallint     DEFAULT NULL,
    official_id_status smallint     DEFAULT NULL,
    term_id            bigint       DEFAULT NULL,
    view_id            bigint       DEFAULT NULL,
    source_id          bigint       DEFAULT NULL,
    user_status_id     smallint     DEFAULT NULL,
    created            timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login         timestamp    DEFAULT NULL,
    last_logoff        timestamp    DEFAULT NULL
);

COMMENT ON TABLE users IS 'for users including system users; only users can add data';
COMMENT ON COLUMN users.user_id IS 'the internal unique primary index';
COMMENT ON COLUMN users.user_name IS 'the user name unique for this pod';
COMMENT ON COLUMN users.ip_address IS 'all users a first identified with the ip address';
COMMENT ON COLUMN users.password IS 'the hash value of the password';
COMMENT ON COLUMN users.description IS 'for system users the description to explain the profile to human users';
COMMENT ON COLUMN users.code_id IS 'to select e.g. the system batch user';
COMMENT ON COLUMN users.user_profile_id IS 'to define the user roles and read and write rights';
COMMENT ON COLUMN users.user_type_id IS 'to set the confirmation level of a user';
COMMENT ON COLUMN users.excluded IS 'true if the user is deactivated but cannot be deleted due to log entries';
COMMENT ON COLUMN users.right_level IS 'the access right level to prevent not permitted right gaining';
COMMENT ON COLUMN users.email IS 'the primary email for verification';
COMMENT ON COLUMN users.email_status IS 'if the email has been verified or if a password reset has been send';
COMMENT ON COLUMN users.email_alternative IS 'an alternative email for account recovery';
COMMENT ON COLUMN users.name_triple_id IS 'triple that contains e.g. the given name, family name, selected name or title of the person';
COMMENT ON COLUMN users.geo_triple_id IS 'the post address with street,city or any other form of geo location for physical transport';
COMMENT ON COLUMN users.official_id IS 'e.g. the number of the passport';
COMMENT ON COLUMN users.term_id IS 'the last term that the user had used';
COMMENT ON COLUMN users.view_id IS 'the last mask that the user has used';
COMMENT ON COLUMN users.source_id IS 'the last source used by this user to have a default for the next value';
COMMENT ON COLUMN users.user_status_id IS 'e.g. to exclude inactive users';

