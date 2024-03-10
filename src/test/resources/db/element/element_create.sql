-- --------------------------------------------------------

--
-- table structure cache for fast update of formula resolved text
--

CREATE TABLE IF NOT EXISTS elements
(
    element_id BIGSERIAL PRIMARY KEY,
    formula_id      bigint           NOT NULL,
    order_nbr       bigint           NOT NULL,
    element_type_id bigint           NOT NULL,
    user_id         bigint       DEFAULT NULL,
    ref_id          bigint       DEFAULT NULL,
    resolved_text   varchar(255) DEFAULT NULL
);

COMMENT ON TABLE elements IS 'cache for fast update of formula resolved text';
COMMENT ON COLUMN elements.element_id IS 'the internal unique primary index';
COMMENT ON COLUMN elements.formula_id IS 'each element can only be used for one formula';
COMMENT ON COLUMN elements.ref_id IS 'either a term, verb or formula id';
