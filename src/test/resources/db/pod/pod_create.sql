-- --------------------------------------------------------

--
-- table structure for the technical details of the mash network pods
--

CREATE TABLE IF NOT EXISTS pods
(
    pod_id          BIGSERIAL PRIMARY KEY,
    type_name       varchar(255)     NOT NULL,
    code_id         varchar(255) DEFAULT NULL,
    description     text         DEFAULT NULL,
    pod_type_id     smallint     DEFAULT NULL,
    pod_url         varchar(255)     NOT NULL,
    pod_status_id   smallint     DEFAULT NULL,
    param_triple_id bigint       DEFAULT NULL
);

COMMENT ON TABLE pods IS 'for the technical details of the mash network pods';
COMMENT ON COLUMN pods.pod_id IS 'the internal unique primary index';
COMMENT ON COLUMN pods.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN pods.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN pods.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
