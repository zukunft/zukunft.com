-- --------------------------------------------------------

--
-- table structure predefined status of batch task as a database table e.g. so that admin can change the description
--

CREATE TABLE IF NOT EXISTS job_statuus
(
    job_status_id smallint         NOT NULL COMMENT 'the internal unique primary index',
    type_name     varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id       varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description   text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    priority      smallint     DEFAULT NULL COMMENT 'execution priority offset based on the job status',
    PRIMARY KEY (job_status_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'predefined status of batch task as a database table e.g. so that admin can change the description';

--
-- AUTO_INCREMENT for table job_statuus
--
ALTER TABLE job_statuus
    MODIFY job_status_id smallint NOT NULL AUTO_INCREMENT;
