-- --------------------------------------------------------

--
-- indexes for table language_forms
--

ALTER TABLE language_forms
    ADD KEY language_forms_language_form_name_idx (language_form_name),
    ADD KEY language_forms_language_idx (language_id);
