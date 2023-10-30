-- --------------------------------------------------------

--
-- table structure for the original sources for the numeric, time and geo values
--

CREATE TABLE IF NOT EXISTS sources
(
    source_id      BIGSERIAL    PRIMARY KEY,
    user_id        bigint       DEFAULT NULL,
    source_name    varchar(255)     NOT NULL,
    description    text         DEFAULT NULL,
    source_type_id bigint       DEFAULT NULL,
    url            text         DEFAULT NULL,
    code_id        varchar(100) DEFAULT NULL,
    excluded       smallint     DEFAULT NULL,
    share_type_id  smallint     DEFAULT NULL,
    protect_id     smallint     DEFAULT NULL
);

COMMENT ON TABLE sources                 IS 'for the original sources for the numeric, time and geo values';
COMMENT ON COLUMN sources.source_id      IS 'the internal unique primary index ';
COMMENT ON COLUMN sources.user_id        IS 'the owner / creator of the value';
COMMENT ON COLUMN sources.source_name    IS 'the unique name of the source used e.g. as the primary search key';
COMMENT ON COLUMN sources.description    IS 'the user specific description of the source for mouse over helps';
COMMENT ON COLUMN sources.source_type_id IS 'link to the source type';
COMMENT ON COLUMN sources.url            IS 'the url of the source';
COMMENT ON COLUMN sources.code_id        IS 'to select sources used by this program';
COMMENT ON COLUMN sources.excluded       IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN sources.share_type_id  IS 'to restrict the access';
COMMENT ON COLUMN sources.protect_id     IS 'to protect against unwanted changes';

--
-- table structure for the original sources for the numeric, time and geo values
--

CREATE TABLE IF NOT EXISTS user_sources
(
    source_id      bigint           NOT NULL,
    user_id        bigint           NOT NULL,
    source_name    varchar(255)     NOT NULL,
    description    text         DEFAULT NULL,
    source_type_id bigint       DEFAULT NULL,
    url            text         DEFAULT NULL,
    code_id        varchar(100) DEFAULT NULL,
    excluded       smallint     DEFAULT NULL,
    share_type_id  smallint     DEFAULT NULL,
    protect_id     smallint     DEFAULT NULL
);

COMMENT ON TABLE user_sources                 IS 'for the original sources for the numeric, time and geo values';
COMMENT ON COLUMN user_sources.source_id      IS 'with the user_id the internal unique primary index ';
COMMENT ON COLUMN user_sources.user_id        IS 'the changer of the ';
COMMENT ON COLUMN user_sources.source_name    IS 'the unique name of the source used e.g. as the primary search key';
COMMENT ON COLUMN user_sources.description    IS 'the user specific description of the source for mouse over helps';
COMMENT ON COLUMN user_sources.source_type_id IS 'link to the source type';
COMMENT ON COLUMN user_sources.url            IS 'the url of the source';
COMMENT ON COLUMN user_sources.code_id        IS 'to select sources used by this program';
COMMENT ON COLUMN user_sources.excluded       IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_sources.share_type_id  IS 'to restrict the access';
COMMENT ON COLUMN user_sources.protect_id     IS 'to protect against unwanted changes';
