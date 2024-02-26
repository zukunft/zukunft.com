-- --------------------------------------------------------

--
-- table structure for the core configuration of this pod e.g. the program version or pod url
--

CREATE TABLE IF NOT EXISTS config
(
    config_id BIGSERIAL PRIMARY KEY,
    config_name varchar(255) DEFAULT NULL,
    code_id     varchar(255)     NOT NULL,
    value       varchar(255) DEFAULT NULL,
    description text         DEFAULT NULL
);

COMMENT ON TABLE config IS 'for the core configuration of this pod e.g. the program version or pod url';
COMMENT ON COLUMN config.config_id IS 'the internal unique primary index';
COMMENT ON COLUMN config.config_name IS 'short name of the configuration entry to be shown to the admin';
COMMENT ON COLUMN config.code_id IS 'unique id text to select a configuration value from the code';
COMMENT ON COLUMN config.value IS 'the configuration value as a string';
COMMENT ON COLUMN config.description IS 'text to explain the config value to an admin user';
