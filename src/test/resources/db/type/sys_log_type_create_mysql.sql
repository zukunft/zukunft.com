-- --------------------------------------------------------

--
-- table structure for system log types e.g. info,warning and error
--

CREATE TABLE IF NOT EXISTS sys_log_types
(
    sys_log_type_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name         varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id           varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description       text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for system log types e.g. info,warning and error';

--
-- AUTO_INCREMENT for table sys_log_types
--
ALTER TABLE sys_log_types
    MODIFY sys_log_type_id int(11) NOT NULL AUTO_INCREMENT;
