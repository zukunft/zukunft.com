-- --------------------------------------------------------

--
-- table structure to assign predefined behaviour to a formula link
--

CREATE TABLE IF NOT EXISTS formula_link_types
(
    formula_link_type_id BIGSERIAL PRIMARY KEY,
    type_name            varchar(255)     NOT NULL,
    code_id              varchar(255) DEFAULT NULL,
    description          text         DEFAULT NULL,
    formula_id           bigint           NOT NULL,
    phrase_type_id       bigint           NOT NULL,
    link_type_id         bigint           NOT NULL
);

COMMENT ON TABLE formula_link_types IS 'to assign predefined behaviour to a formula link';
COMMENT ON COLUMN formula_link_types.formula_link_type_id IS 'the internal unique primary index';
COMMENT ON COLUMN formula_link_types.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN formula_link_types.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN formula_link_types.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
