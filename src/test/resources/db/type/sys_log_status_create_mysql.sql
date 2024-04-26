-- --------------------------------------------------------

--
-- table structure to define the status of internal errors
--

CREATE TABLE IF NOT EXISTS sys_log_status
(
    sys_log_status_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name         varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id           varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description       text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    action            varchar(255) DEFAULT NULL COMMENT 'description of the action to get to this status'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to define the status of internal errors';

--
-- AUTO_INCREMENT for table sys_log_status
--
ALTER TABLE sys_log_status
    MODIFY sys_log_status_id int(11) NOT NULL AUTO_INCREMENT;
