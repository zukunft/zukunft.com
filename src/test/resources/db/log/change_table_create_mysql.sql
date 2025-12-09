-- --------------------------------------------------------

--
-- table structure to keep the original table name even if a table name has changed and to avoid log changes in case a table is renamed
--

CREATE TABLE IF NOT EXISTS change_tables
(
    change_table_id   smallint         NOT NULL COMMENT 'the internal unique primary index',
    change_table_name varchar(255)     NOT NULL COMMENT 'the real name',
    code_id           varchar(255) DEFAULT NULL COMMENT 'with this field tables can be combined in case of renaming',
    description       text         DEFAULT NULL COMMENT 'the user readable name',
    PRIMARY KEY (change_table_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to keep the original table name even if a table name has changed and to avoid log changes in case a table is renamed';

--
-- AUTO_INCREMENT for table change_tables
--
ALTER TABLE change_tables
    MODIFY change_table_id smallint NOT NULL AUTO_INCREMENT;
