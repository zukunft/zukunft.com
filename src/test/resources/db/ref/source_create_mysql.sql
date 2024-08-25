-- --------------------------------------------------------

--
-- table structure for the original sources for the numeric, time and geo values
--

CREATE TABLE IF NOT EXISTS sources (
    source_id      bigint           NOT NULL COMMENT 'the internal unique primary index',
    user_id        bigint       DEFAULT NULL COMMENT 'the owner / creator of the source',
    source_name    varchar(255)     NOT NULL COMMENT 'the unique name of the source used e.g. as the primary search key',
    description    text         DEFAULT NULL COMMENT 'the user specific description of the source for mouse over helps',
    source_type_id smallint     DEFAULT NULL COMMENT 'link to the source type',
    `url`          text         DEFAULT NULL COMMENT 'the url of the source',
    code_id        varchar(100) DEFAULT NULL COMMENT 'to select sources used by this program',
    excluded       smallint     DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id  smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id     smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the original sources for the numeric,time and geo values';

--
-- AUTO_INCREMENT for table sources
--
ALTER TABLE sources
    MODIFY source_id int(11) NOT NULL AUTO_INCREMENT;

--
-- table structure to save user specific changes for the original sources for the numeric, time and geo values
--

CREATE TABLE IF NOT EXISTS user_sources (
    source_id      bigint           NOT NULL COMMENT 'with the user_id the internal unique primary index',
    user_id        bigint           NOT NULL COMMENT 'the changer of the source',
    source_name    varchar(255) DEFAULT NULL COMMENT 'the unique name of the source used e.g. as the primary search key',
    description    text         DEFAULT NULL COMMENT 'the user specific description of the source for mouse over helps',
    source_type_id smallint     DEFAULT NULL COMMENT 'link to the source type',
    `url`          text         DEFAULT NULL COMMENT 'the url of the source',
    code_id        varchar(100) DEFAULT NULL COMMENT 'to select sources used by this program',
    excluded       smallint     DEFAULT NULL COMMENT 'true if a user,but not all,have removed it',
    share_type_id  smallint     DEFAULT NULL COMMENT 'to restrict the access',
    protect_id     smallint     DEFAULT NULL COMMENT 'to protect against unwanted changes'
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the original sources for the numeric,time and geo values';
