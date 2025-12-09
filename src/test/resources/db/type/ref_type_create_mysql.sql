-- --------------------------------------------------------

--
-- table structure to link code functionality to a list of references
--

CREATE TABLE IF NOT EXISTS ref_types
(
    ref_type_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name   varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id     varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    base_url    text         DEFAULT NULL COMMENT 'the base url to create the urls for the assigned references',
    PRIMARY KEY (ref_type_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link code functionality to a list of references';

--
-- AUTO_INCREMENT for table ref_types
--
ALTER TABLE ref_types
    MODIFY ref_type_id smallint NOT NULL AUTO_INCREMENT;
