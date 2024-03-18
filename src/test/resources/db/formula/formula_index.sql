-- --------------------------------------------------------

--
-- indexes for table formulas
--
CREATE INDEX formulas_user_idx ON formulas (user_id);
CREATE UNIQUE INDEX formulas_formula_name_idx ON formulas (formula_name);
CREATE INDEX formulas_formula_type_idx ON formulas (formula_type_id);
CREATE INDEX formulas_view_idx ON formulas (view_id);

--
-- indexes for table user_formulas
--
ALTER TABLE user_formulas ADD CONSTRAINT user_formulas_pkey PRIMARY KEY (formula_id,user_id);
CREATE INDEX user_formulas_formula_idx ON user_formulas (formula_id);
CREATE INDEX user_formulas_user_idx ON user_formulas (user_id);
CREATE INDEX user_formulas_formula_name_idx ON user_formulas (formula_name);
CREATE INDEX user_formulas_formula_type_idx ON user_formulas (formula_type_id);
CREATE INDEX user_formulas_view_idx ON user_formulas (view_id);


