-- --------------------------------------------------------

--
-- table structure to link code functionality to a list of references
--

CREATE TABLE IF NOT EXISTS ref_types
(
    ref_type_id SERIAL PRIMARY KEY,
    type_name   varchar(255)     NOT NULL,
    code_id     varchar(255) DEFAULT NULL,
    description text         DEFAULT NULL,
    base_url    text         DEFAULT NULL
);

COMMENT ON TABLE ref_types IS 'to link code functionality to a list of references';
COMMENT ON COLUMN ref_types.ref_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN ref_types.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN ref_types.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN ref_types.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
COMMENT ON COLUMN ref_types.base_url IS 'the base url to create the urls for the assigned references';
