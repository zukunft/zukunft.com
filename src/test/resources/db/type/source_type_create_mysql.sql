-- --------------------------------------------------------

--
-- table structure to link predefined behaviour to a source
--

CREATE TABLE IF NOT EXISTS source_types
(
    source_type_id bigint          NOT NULL COMMENT 'the internal unique primary index',
    type_name     varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id       varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description   text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link predefined behaviour to a source';

--
-- AUTO_INCREMENT for table source_types
--
ALTER TABLE source_types
    MODIFY source_type_id int(11) NOT NULL AUTO_INCREMENT;
