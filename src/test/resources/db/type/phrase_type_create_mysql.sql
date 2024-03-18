-- --------------------------------------------------------

--
-- table structure for the phrase type to set the predefined behaviour of a word or triple
--

CREATE TABLE IF NOT EXISTS phrase_types
(
    phrase_type_id bigint           NOT NULL COMMENT 'the internal unique primary index',
    type_name      varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id        varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description    text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    scaling_factor bigint       DEFAULT NULL COMMENT 'e.g. for percent the scaling factor is 100',
    word_symbol    varchar(255) DEFAULT NULL COMMENT 'e.g. for percent the symbol is %'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the phrase type to set the predefined behaviour of a word or triple';

--
-- AUTO_INCREMENT for table phrase_types
--
ALTER TABLE phrase_types
    MODIFY phrase_type_id int(11) NOT NULL AUTO_INCREMENT;
