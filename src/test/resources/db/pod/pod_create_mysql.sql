-- --------------------------------------------------------

--
-- table structure for the technical details of the mash network pods
--

CREATE TABLE IF NOT EXISTS pods
(
    pod_id          bigint           NOT NULL COMMENT 'the internal unique primary index',
    type_name       varchar(255)     NOT NULL COMMENT 'the unique type name as shown to the user and used for the selection',
    code_id         varchar(255) DEFAULT NULL COMMENT 'this id text is unique for all code links,is used for system im- and export and is used to link coded functionality to a specific word e.g. to get the values of the system configuration',
    description     text         DEFAULT NULL COMMENT 'text to explain the type to the user as a tooltip; to be replaced by a language form entry',
    pod_type_id     smallint     DEFAULT NULL,
    pod_url         varchar(255)     NOT NULL,
    pod_status_id   smallint     DEFAULT NULL,
    param_triple_id bigint       DEFAULT NULL,
    PRIMARY KEY (pod_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'for the technical details of the mash network pods';

--
-- AUTO_INCREMENT for table pods
--
ALTER TABLE pods
    MODIFY pod_id bigint NOT NULL AUTO_INCREMENT;
