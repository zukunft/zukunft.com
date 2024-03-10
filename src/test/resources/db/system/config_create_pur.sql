
CREATE TABLE IF NOT EXISTS config
(
    config_id BIGSERIAL PRIMARY KEY,
    config_name varchar(255) DEFAULT NULL,
    code_id     varchar(255)     NOT NULL,
    value       varchar(255) DEFAULT NULL,
    description text         DEFAULT NULL
);
