<?php
/*
 * Copyright (C) syncApp Zze jz3731@gmail.com
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * The class must be (type)_(app)_(module)_(section)
 */
class admin_syncApp_syncacp_syncacp extends ipsCommand
{
	/**
	 * doExecute is the method that is ran when the section is accessed
	 */
	public function doExecute( ipsRegistry $registry )
	{
		//-----------------------------------------
		// Load the ACP skin file (in the skin_cp directory)
		//-----------------------------------------

		$this->html = $this->registry->output->loadTemplate( 'cp_skin_syncApp' );

		//-----------------------------------------
		// Load the language file (in the cache/lang_cache directory)
		//-----------------------------------------

		$this->lang->loadLanguageFile( array( 'admin_lang' ) ); // Will automatically prepend "(app)_"

		//-----------------------------------------
		// Display a function from the skin file
		//-----------------------------------------

		$this->registry->output->html .= $this->html->syncApp();

		//-----------------------------------------
		// Output
		//-----------------------------------------
		//$this->registry->output->addContent('');
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}
}