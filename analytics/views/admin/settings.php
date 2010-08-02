<?php $this->load->view('_nav'); ?>
<div class="grid_16">
	
	<?php if(isset($error)) {
		echo '<div class="error">'.$error,'</div>';
	} ?>
	<?php if(validation_errors()) {
		echo '<div class="error"><h3>'.lang('lang_errors_occured').'</h3> '.validation_errors().'</div>';
	}
	?>
	
<?php
	// We need a hidden field called 'file' whose value matches this extension's url slug. (Apparently?)
	echo form_open($this->uri->uri_string());
	
	$tmpl = array (
	                    'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" width="80%" align="center">',

	                    'heading_row_start'   => '<tr>',
	                    'heading_row_end'     => '</tr>',
	                    'heading_cell_start'  => '<th>',
	                    'heading_cell_end'    => '</th>',

	                    'row_start'           => '<tr>',
	                    'row_end'             => '</tr>',
	                    'cell_start'          => '<td>',
	                    'cell_end'            => '</td>',

	                    'row_alt_start'       => '<tr>',
	                    'row_alt_end'         => '</tr>',
	                    'cell_alt_start'      => '<td>',
	                    'cell_alt_end'        => '</td>',

	                    'table_close'         => '</table>'
	              );
	$this->table->set_template($tmpl);
	
	$this->table->set_heading(
		array('data'=> ''), array('data'=> '')
	);
	
	// Show username/password inputs, or current authentication
	if ( ! isset($authenticated) || $authenticated == 'n' || $authenticated == FALSE)
	{
		$this->table->add_row(
			form_label(lang('analytics_username'), 'user'),
			form_input('user', set_value('user', @$user), 'id="user"')
		);
		$this->table->add_row(
			form_label(lang('analytics_password'), 'password'),
			form_input('password', set_value('password'), 'id="password"')
		);
	}
	else
	{
		$this->table->add_row(
			lang('analytics_username'),
			lang('analytics_authenticated_as').
			' <b>'.$user.'</b> &nbsp; ('.anchor('admin/analytics/reset', lang('analytics_reset')).')'.
			form_hidden('user', $user)
		);		
	}

	// Show profile chooser, or relevant message	
	if(!isset($authenticated) || $authenticated == FALSE)
	{
		$this->table->add_row(
			lang('analytics_profile'),
			lang('analytics_need_credentials')
		);
	}
	elseif($authenticated == 'n')
	{
		$this->table->add_row(
			lang('analytics_profile'),
			'<span class="failure">'.lang('analytics_bad_credentials').'</span>'
		);	
	}
	else
	{
		if (isset($ga_profiles))
		{
			$this->table->add_row(
				form_label(lang('analytics_profile'), 'profile'),
				form_dropdown('profile', $ga_profiles, (isset($profile)) ? $profile : '')
			);		
		}
		else
		{
			$this->table->add_row(
				lang('analytics_profile'),
				'<span class="failure">'.lang('analytics_no_accounts').'</span>'
			);			
		}
	}
									
	echo $this->table->generate();
	
	echo form_submit(array('name' => 'submit', 'value' => lang('analytics_save_settings'), 'class' => 'submit'));
	echo form_close();
?>
</div>