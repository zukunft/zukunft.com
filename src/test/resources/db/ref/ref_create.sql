-- --------------------------------------------------------

--
-- table structure to link external data to internal for syncronisation
--

CREATE TABLE IF NOT EXISTS refs
(
    ref_id BIGSERIAL PRIMARY KEY,
    user_id       bigint    DEFAULT NULL,
    url           text      DEFAULT NULL,
    description   text      DEFAULT NULL,
    phrase_id     bigint    DEFAULT NULL,
    external_key  varchar(255)  NOT NULL,
    ref_type_id   bigint        NOT NULL,
    source_id     bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE refs IS 'to link external data to internal for syncronisation';
COMMENT ON COLUMN refs.ref_id IS 'the internal unique primary index';
COMMENT ON COLUMN refs.user_id IS 'the owner / creator of the ref';
COMMENT ON COLUMN refs.url IS 'the concrete url for the entry inluding the item id';
COMMENT ON COLUMN refs.phrase_id IS 'the phrase for which the external data should be syncronised';
COMMENT ON COLUMN refs.external_key IS 'the unique external key used in the other system';
COMMENT ON COLUMN refs.ref_type_id IS 'to link code functionality to a list of references';
COMMENT ON COLUMN refs.source_id IS 'if the reference does not allow a full automatic bidirectional update use the source to define an as good as possible import or at least a check if the reference is still valid';
COMMENT ON COLUMN refs.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN refs.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN refs.protect_id IS 'to protect against unwanted changes';

--
-- table structure to save user specific changes to link external data to internal for syncronisation
--

CREATE TABLE IF NOT EXISTS user_refs
(
    ref_id        bigint       NOT NULL,
    user_id       bigint       NOT NULL,
    url           text     DEFAULT NULL,
    description   text     DEFAULT NULL,
    excluded      smallint DEFAULT NULL,
    share_type_id smallint DEFAULT NULL,
    protect_id    smallint DEFAULT NULL
);

COMMENT ON TABLE user_refs IS 'to link external data to internal for syncronisation';
COMMENT ON COLUMN user_refs.ref_id IS 'with the user_id the internal unique primary index';
COMMENT ON COLUMN user_refs.user_id IS 'the changer of the ref';
COMMENT ON COLUMN user_refs.url IS 'the concrete url for the entry inluding the item id';
COMMENT ON COLUMN user_refs.excluded IS 'true if a user,but not all,have removed it';
COMMENT ON COLUMN user_refs.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_refs.protect_id IS 'to protect against unwanted changes';
