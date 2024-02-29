-- --------------------------------------------------------

--
-- table structure for predefined batch jobs that can be triggered by a user action or scheduled e.g. data synchronisation
--

CREATE TABLE IF NOT EXISTS job_types
(
    job_type_id bigint           NOT NULL COMMENT 'the internal unique primary index',
    type_name   varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id     varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for predefined batch jobs that can be triggered by a user action or scheduled e.g. data synchronisation';
