-- --------------------------------------------------------

--
-- table structure cache for fast update of formula resolved text
--

CREATE TABLE IF NOT EXISTS elements
(
    element_id      bigint     NOT NULL COMMENT 'the internal unique primary index',
    formula_id      bigint     NOT NULL COMMENT 'each element can only be used for one formula',
    order_nbr       bigint     NOT NULL,
    element_type_id smallint   NOT NULL,
    user_id         bigint DEFAULT NULL,
    ref_id          bigint DEFAULT NULL COMMENT 'either a term,verb or formula id',
    resolved_text   varchar(255) DEFAULT NULL,
    PRIMARY KEY (element_id)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COMMENT 'cache for fast update of formula resolved text';

--
-- AUTO_INCREMENT for table elements
--
ALTER TABLE elements
    MODIFY element_id bigint NOT NULL AUTO_INCREMENT;
