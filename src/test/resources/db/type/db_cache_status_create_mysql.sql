-- --------------------------------------------------------

--
-- table structure  for predefined database cache status e.g. dirty,updating or outdated
--

CREATE TABLE IF NOT EXISTS db_cache_statuum
(
    status_id     smallint    NOT NULL COMMENT 'the internal unique primary index',
    status_name   varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id       varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description   text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    PRIMARY KEY (status_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for predefined database cache status e.g. dirty,updating or outdated';

--
-- AUTO_INCREMENT for table db_cache_statuum
--
ALTER TABLE db_cache_statuum
    MODIFY status_id smallint NOT NULL AUTO_INCREMENT;
