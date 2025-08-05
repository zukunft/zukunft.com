-- --------------------------------------------------------

--
-- table structure remember which phrases are stored in which table and pod
--

CREATE TABLE IF NOT EXISTS phrase_tables
(
    phrase_table_id BIGSERIAL PRIMARY KEY,
    phrase_id                bigint NOT NULL,
    pod_id                   bigint NOT NULL,
    phrase_table_status_id smallint NOT NULL
);

COMMENT ON TABLE phrase_tables IS 'remember which phrases are stored in which table and pod';
COMMENT ON COLUMN phrase_tables.phrase_table_id IS 'the internal unique primary index';
COMMENT ON COLUMN phrase_tables.phrase_id IS 'the values and results of this phrase are primary stored in dynamic tables on the given pod';
COMMENT ON COLUMN phrase_tables.pod_id IS 'the primary pod where the values and results related to this phrase saved';
