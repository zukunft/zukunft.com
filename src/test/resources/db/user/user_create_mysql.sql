-- --------------------------------------------------------

--
-- table structure for users including system users; only users can add data
--

CREATE TABLE IF NOT EXISTS users
(
    user_id            bigint           NOT NULL COMMENT 'the internal unique primary index',
    user_name          varchar(255)     NOT NULL COMMENT 'the user name unique for this pod',
    ip_address         varchar(100) DEFAULT NULL COMMENT 'all users a first identified with the ip address',
    password           varchar(255) DEFAULT NULL COMMENT 'the hash value of the password',
    description        text         DEFAULT NULL COMMENT 'for system users the description to explain the profile to human users',
    code_id            varchar(100) DEFAULT NULL COMMENT 'to select e.g. the system batch user',
    user_profile_id    smallint     DEFAULT NULL COMMENT 'to define the user roles and read and write rights',
    user_type_id       smallint     DEFAULT NULL COMMENT 'to set the confirmation level of a user',
    excluded           smallint     DEFAULT NULL COMMENT 'true if the user is deactivated but cannot be deleted due to log entries',
    right_level        smallint     DEFAULT NULL COMMENT 'the access right level to prevent not permitted right gaining',
    email              varchar(255) DEFAULT NULL COMMENT 'the primary email for verification',
    email_status       smallint     DEFAULT NULL COMMENT 'if the email has been verified or if a password reset has been send',
    email_alternative  varchar(255) DEFAULT NULL COMMENT 'an alternative email for account recovery',
    mobile_number      varchar(100) DEFAULT NULL,
    mobile_status      smallint     DEFAULT NULL,
    activation_key     varchar(255) DEFAULT NULL,
    activation_timeout timestamp    DEFAULT NULL,
    first_name         varchar(255) DEFAULT NULL,
    last_name          varchar(255) DEFAULT NULL,
    name_triple_id     bigint       DEFAULT NULL COMMENT 'triple that contains e.g. the given name,family name,selected name or title of the person',
    geo_triple_id      bigint       DEFAULT NULL COMMENT 'the post address with street,city or any other form of geo location for physical transport',
    geo_status_id      smallint     DEFAULT NULL,
    official_id        varchar(255) DEFAULT NULL COMMENT 'e.g. the number of the passport',
    official_id_type   smallint     DEFAULT NULL,
    official_id_status smallint     DEFAULT NULL,
    term_id            bigint       DEFAULT NULL COMMENT 'the last term that the user had used',
    view_id            bigint       DEFAULT NULL COMMENT 'the last mask that the user has used',
    source_id          bigint       DEFAULT NULL COMMENT 'the last source used by this user to have a default for the next value',
    user_status_id     smallint     DEFAULT NULL COMMENT 'e.g. to exclude inactive users',
    created            timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login         timestamp    DEFAULT NULL,
    last_logoff        timestamp    DEFAULT NULL,
    PRIMARY KEY (user_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for users including system users; only users can add data';

--
-- AUTO_INCREMENT for table users
--
ALTER TABLE users
    MODIFY user_id bigint NOT NULL AUTO_INCREMENT;
