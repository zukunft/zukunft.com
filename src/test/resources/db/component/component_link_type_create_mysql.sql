-- --------------------------------------------------------

--
-- table structure to assign predefined behaviour to a component view link
--

CREATE TABLE IF NOT EXISTS component_link_types
(
    component_link_type_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name              varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id                varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description            text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    PRIMARY KEY (component_link_type_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to assign predefined behaviour to a component view link';

--
-- AUTO_INCREMENT for table component_link_types
--
ALTER TABLE component_link_types
    MODIFY component_link_type_id smallint NOT NULL AUTO_INCREMENT;
