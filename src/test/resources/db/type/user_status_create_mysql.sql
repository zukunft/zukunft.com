-- --------------------------------------------------------

--
-- table structure to reduce short-term the internal permissions for a user without changing the profile
--

CREATE TABLE IF NOT EXISTS user_statuum
(
    user_status_id   smallint         NOT NULL COMMENT 'the internal unique primary index',
    user_status_name varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id          varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description      text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    PRIMARY KEY (user_status_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to reduce short-term the internal permissions for a user without changing the profile';

--
-- AUTO_INCREMENT for table user_statuum
--
ALTER TABLE user_statuum
    MODIFY user_status_id smallint NOT NULL AUTO_INCREMENT;
