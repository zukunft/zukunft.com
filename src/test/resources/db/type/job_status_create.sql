-- --------------------------------------------------------

--
-- table structure predefined status of batch task as a database table e.g. so that admin can change the description
--

CREATE TABLE IF NOT EXISTS job_statuum
(
    job_status_id SERIAL PRIMARY KEY,
    status_name varchar(255) NOT NULL,
    code_id     varchar(255) DEFAULT NULL,
    description text         DEFAULT NULL,
    priority    smallint     DEFAULT NULL
);

COMMENT ON TABLE job_statuum IS 'predefined status of batch task as a database table e.g. so that admin can change the description';
COMMENT ON COLUMN job_statuum.job_status_id IS 'the internal unique primary index';
COMMENT ON COLUMN job_statuum.status_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN job_statuum.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN job_statuum.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
COMMENT ON COLUMN job_statuum.priority IS 'execution priority offset based on the job status';
