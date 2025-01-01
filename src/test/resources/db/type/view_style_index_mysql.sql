-- --------------------------------------------------------

--
-- indexes for table view_styles
--

ALTER TABLE view_styles
    ADD PRIMARY KEY (view_style_id),
    ADD KEY view_styles_view_style_name_idx (view_style_name);
