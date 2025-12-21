-- --------------------------------------------------------

--
-- table structure for the core configuration of this pod e.g. the program version or pod url
--

CREATE TABLE IF NOT EXISTS config
(
    config_id   bigint           NOT NULL COMMENT 'the internal unique primary index',
    config_name varchar(255) DEFAULT NULL COMMENT 'short name of the configuration entry to be shown to the admin',
    code_id     varchar(255)     NOT NULL COMMENT 'unique id text to select a configuration value from the code',
    `value`     varchar(255) DEFAULT NULL COMMENT 'the configuration value as a string',
    description text         DEFAULT NULL COMMENT 'text to explain the config value to an admin user',
    PRIMARY KEY (config_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the core configuration of this pod e.g. the program version or pod url';

--
-- AUTO_INCREMENT for table config
--
ALTER TABLE config
    MODIFY config_id bigint NOT NULL AUTO_INCREMENT;
