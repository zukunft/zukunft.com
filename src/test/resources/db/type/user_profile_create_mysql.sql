-- --------------------------------------------------------

--
-- table structure to define the user roles and read and write rights
--

CREATE TABLE IF NOT EXISTS user_profiles
(
    user_profile_id smallint      NOT NULL COMMENT 'the internal unique primary index',
    type_name    varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id      varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description  text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    right_level  smallint     DEFAULT NULL COMMENT 'the access right level to prevent unpermitted right gaining'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to define the user roles and read and write rights';

--
-- AUTO_INCREMENT for table user_profiles
--
ALTER TABLE user_profiles
    MODIFY user_profile_id int(11) NOT NULL AUTO_INCREMENT;
