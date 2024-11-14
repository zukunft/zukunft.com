-- --------------------------------------------------------

--
-- table structure the display style for a view or component e.g. number of columns to use
--

CREATE TABLE IF NOT EXISTS view_styles
(
    view_style_id SERIAL PRIMARY KEY,
    view_style_name varchar(255)     NOT NULL,
    code_id         varchar(255) DEFAULT NULL,
    description     text         DEFAULT NULL
);

COMMENT ON TABLE view_styles IS 'the display style for a view or component e.g. number of columns to use';
COMMENT ON COLUMN view_styles.view_style_id IS 'the internal unique primary index';
COMMENT ON COLUMN view_styles.view_style_name IS 'the unique type name as shown to the user and used for the selection';
COMMENT ON COLUMN view_styles.code_id IS 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration';
COMMENT ON COLUMN view_styles.description IS 'text to explain the type to the user as a tooltip; to be replaced by a language form entry';
