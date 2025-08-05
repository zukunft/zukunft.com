-- --------------------------------------------------------

--
-- table structure to keep the original field name even if a table name has changed
--

CREATE TABLE IF NOT EXISTS change_fields
(
    change_field_id   smallint         NOT NULL COMMENT 'the internal unique primary index',
    table_id          smallint         NOT NULL COMMENT 'because every field must only be unique within a table',
    change_field_name varchar(255)     NOT NULL COMMENT 'the real name',
    code_id           varchar(255) DEFAULT NULL COMMENT 'to display the change with some linked information',
    description       text         DEFAULT NULL,
    PRIMARY KEY (change_field_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to keep the original field name even if a table name has changed';

--
-- AUTO_INCREMENT for table change_fields
--
ALTER TABLE change_fields
    MODIFY change_field_id smallint NOT NULL AUTO_INCREMENT;
