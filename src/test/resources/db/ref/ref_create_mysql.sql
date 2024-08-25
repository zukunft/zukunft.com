-- --------------------------------------------------------

--
-- table structure to link external data to internal for syncronisation
--

CREATE TABLE IF NOT EXISTS refs
(
    ref_id        bigint       NOT NULL COMMENT 'the internal unique primary index',
    user_id       bigint   DEFAULT NULL COMMENT 'the owner / creator of the ref',
    external_key  varchar(255) NOT NULL COMMENT 'the unique external key used in the other system',
    `url`         text     DEFAULT NULL COMMENT 'the concrete url for the entry inluding the item id',
    source_id     bigint   DEFAULT NULL COMMENT 'if the reference does not allow a full automatic bidirectional update use the source to define an as good as possible import or at least a check if the reference is still valid',
    description   text     DEFAULT NULL,
    phrase_id     bigint   DEFAULT NULL COMMENT 'the phrase for which the external data should be syncronised',
    ref_type_id   bigint       NOT NULL COMMENT 'to link code functionality to a list of references',
    excluded      smallint DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id smallint DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link external data to internal for syncronisation';

--
-- AUTO_INCREMENT for table refs
--
ALTER TABLE refs
    MODIFY ref_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes to link external data to internal for syncronisation
--

CREATE TABLE IF NOT EXISTS user_refs
(
    ref_id        bigint           NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id       bigint           NOT NULL COMMENT 'the changer of the ref',
    external_key  varchar(255) DEFAULT NULL COMMENT 'the unique external key used in the other system',
    `url`         text         DEFAULT NULL COMMENT 'the concrete url for the entry inluding the item id',
    source_id     bigint       DEFAULT NULL COMMENT 'if the reference does not allow a full automatic bidirectional update use the source to define an as good as possible import or at least a check if the reference is still valid',
    description   text         DEFAULT NULL,
    excluded      smallint     DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'to link external data to internal for syncronisation';
