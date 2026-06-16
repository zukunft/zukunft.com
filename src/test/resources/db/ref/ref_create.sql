-- --------------------------------------------------------

--
-- table structure to link external data to internal for synchronisation
--

CREATE TABLE IF NOT EXISTS refs
(
    ref_id BIGSERIAL PRIMARY KEY,
    user_id       bigint           DEFAULT NULL,
    external_key  varchar(255)         NOT NULL,
    url           text             DEFAULT NULL,
    source_id     bigint           DEFAULT NULL,
    description   text             DEFAULT NULL,
    phrase_id     bigint           DEFAULT NULL,
    ref_type_id   smallint             NOT NULL,
    impact        double precision DEFAULT NULL,
    last_update   timestamp        DEFAULT NULL,
    excluded      smallint         DEFAULT NULL,
    share_type_id smallint         DEFAULT NULL,
    protect_id    smallint         DEFAULT NULL
);

COMMENT ON TABLE refs IS 'to link external data to internal for synchronisation';
COMMENT ON COLUMN refs.ref_id IS 'the internal unique primary index';
COMMENT ON COLUMN refs.user_id IS 'the owner / creator of the ref';
COMMENT ON COLUMN refs.external_key IS 'the unique external key used in the other system';
COMMENT ON COLUMN refs.url IS 'the concrete url for the entry including the item id';
COMMENT ON COLUMN refs.source_id IS 'if the reference does not allow a full automatic bidirectional update use the source to define an as good as possible import or at least a check if the reference is still valid';
COMMENT ON COLUMN refs.phrase_id IS 'the phrase for which the external data should be synchronised';
COMMENT ON COLUMN refs.ref_type_id IS 'to link code functionality to a list of references';
COMMENT ON COLUMN refs.impact IS 'a cached number used for default sorting of objects and an indication of the importance as defined by the formula specified in the user config by the words "impact calculation" e.g. for math const the time of discovery is used or for currencies the average daily turnover and is used as fallback value for sorting';
COMMENT ON COLUMN refs.last_update IS 'timestamp of the last successful update of the reference used to trigger the next refresh job';
COMMENT ON COLUMN refs.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN refs.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN refs.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user-specific changes to link external data to internal for synchronisation
--

CREATE TABLE IF NOT EXISTS user_refs
(
    ref_id        bigint           NOT NULL,
    user_id       bigint           NOT NULL,
    external_key  varchar(255) DEFAULT NULL,
    url           text         DEFAULT NULL,
    source_id     bigint       DEFAULT NULL,
    description   text         DEFAULT NULL,
    excluded      smallint     DEFAULT NULL,
    share_type_id smallint     DEFAULT NULL,
    protect_id    smallint     DEFAULT NULL
);

COMMENT ON TABLE user_refs IS 'to link external data to internal for synchronisation';
COMMENT ON COLUMN user_refs.ref_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_refs.user_id IS 'the changer of the ref';
COMMENT ON COLUMN user_refs.external_key IS 'the unique external key used in the other system';
COMMENT ON COLUMN user_refs.url IS 'the concrete url for the entry including the item id';
COMMENT ON COLUMN user_refs.source_id IS 'if the reference does not allow a full automatic bidirectional update use the source to define an as good as possible import or at least a check if the reference is still valid';
COMMENT ON COLUMN user_refs.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_refs.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_refs.protect_id IS 'to protect against unwanted changes';
