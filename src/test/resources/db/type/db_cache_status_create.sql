-- --------------------------------------------------------

--
-- table structure for predefined database cache status e.g. dirty,updating or outdated
--

CREATE TABLE IF NOT EXISTS db_cache_statuum
(
    status_id SERIAL PRIMARY KEY,
    status_name varchar(255) NOT NULL,
    code_id     varchar(255) DEFAULT NULL,
    description text         DEFAULT NULL
);

COMMENT ON TABLE db_cache_statuum IS 'for predefined database cache status e.g. dirty,updating or outdated';
COMMENT ON COLUMN db_cache_statuum.status_id IS 'the internal unique primary index';
COMMENT ON COLUMN db_cache_statuum.status_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN db_cache_statuum.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN db_cache_statuum.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
