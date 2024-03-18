-- --------------------------------------------------------

--
-- table structure remember which phrases are stored in which table and pod
--

CREATE TABLE IF NOT EXISTS phrase_tables
(
    phrase_table_id        bigint NOT NULL COMMENT 'the internal unique primary index',
    phrase_id              bigint NOT NULL COMMENT 'the values and results of this phrase are primary stored in dynamic tables on the given pod',
    pod_id                 bigint NOT NULL COMMENT 'the primary pod where the values and results related to this phrase saved',
    phrase_table_status_id bigint NOT NULL
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'remember which phrases are stored in which table and pod';

--
-- AUTO_INCREMENT for table phrase_tables
--
ALTER TABLE phrase_tables
    MODIFY phrase_table_id int(11) NOT NULL AUTO_INCREMENT;
