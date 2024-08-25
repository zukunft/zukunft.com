-- --------------------------------------------------------

--
-- table structure to define the user roles and read and write rights
--

CREATE TABLE IF NOT EXISTS user_profiles
(
    user_profile_id SERIAL PRIMARY KEY,
    type_name    varchar(255) NOT NULL,
    code_id      varchar(255) DEFAULT NULL,
    description  text         DEFAULT NULL,
    right_level  smallint     DEFAULT NULL
);

COMMENT ON TABLE user_profiles IS 'to define the user roles and read and write rights';
COMMENT ON COLUMN user_profiles.user_profile_id IS 'the internal unique primary index';
COMMENT ON COLUMN user_profiles.type_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN user_profiles.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN user_profiles.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
COMMENT ON COLUMN user_profiles.right_level IS 'the access right level to prevent unpermitted right gaining';