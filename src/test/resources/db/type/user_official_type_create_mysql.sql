-- --------------------------------------------------------

--
-- table structure for person identification types e.g. passports
--

CREATE TABLE IF NOT EXISTS user_official_types
(
    user_official_type_id smallint NOT NULL COMMENT 'the internal unique primary index',
    type_name    varchar(255)      NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id      varchar(255)  DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description  text          DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    PRIMARY KEY (user_official_type_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for person identification types e.g. passports';

--
-- AUTO_INCREMENT for table user_official_types
--
ALTER TABLE user_official_types
    MODIFY user_official_type_id smallint NOT NULL AUTO_INCREMENT;
