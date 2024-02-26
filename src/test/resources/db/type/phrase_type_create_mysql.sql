-- --------------------------------------------------------

--
-- table structure for the predefined behaviour of e.g. a word, triple,...
--

CREATE TABLE IF NOT EXISTS phrase_types
(
    phrase_type_id bigint       NOT NULL     COMMENT 'the internal unique primary index',
    type_name      varchar(255) NOT NULL     COMMENT 'the unique name to select the type by the user',
    description    text         DEFAULT NULL COMMENT 'text that should be shown to the user on mouse over; to be replaced by a language form entry ',
    code_id        varchar(255) DEFAULT NULL COMMENT 'to link coded functionality to a specific word e.g. to get the values of the system configuration',
    scaling_factor bigint       DEFAULT NULL COMMENT 'e.g. for percent the scaling factor is 100',
    word_symbol    varchar(255) DEFAULT NULL COMMENT 'e.g. for percent the symbol is %'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the predefined behaviour of e.g. a word,triple,...';
