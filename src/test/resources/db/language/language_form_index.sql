-- --------------------------------------------------------

--
-- indexes for table language_forms
--

CREATE INDEX language_forms_language_form_name_idx ON language_forms (language_form_name);
CREATE INDEX language_forms_language_idx ON language_forms (language_id);
