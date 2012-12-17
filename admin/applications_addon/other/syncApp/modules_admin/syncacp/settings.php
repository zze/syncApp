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

class admin_syncApp_syncacp_settings extends ipsCommand
{
        public function doExecute( ipsRegistry $registry )
        {
            //-----------------------------------------
            // Set up some shortcuts for our urls
            //-----------------------------------------

            $this->form_code      = 'module=syncacp&amp;section=settings';
            $this->form_code_js   = 'module=syncacp&section=settings';

            //-------------------------------
            // Grab the settings controller, instantiate and set up shortcuts
            //-------------------------------

            $classToLoad = IPSLib::loadActionOverloader( IPSLib::getAppDir('core') . '/modules_admin/settings/settings.php', 'admin_core_settings_settings' );
            $settings    = new $classToLoad();
            $settings->makeRegistryShortcuts( $this->registry );

            //-------------------------------
            // Load language file that will be needed
            //-------------------------------

            ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'admin_tools' ), 'core' );

            //-------------------------------
            // Load the skin file the settings file will need and pass shortcuts
            //-------------------------------

            $settings->html         = $this->registry->output->loadTemplate( 'cp_skin_settings', 'core' );
            $settings->form_code    = $settings->html->form_code    = 'module=settings&amp;section=settings';
            $settings->form_code_js = $settings->html->form_code_js = 'module=settings&section=settings';

            //-------------------------------
            // Here we specify the setting group key
            //-------------------------------
            $this->request['conf_title_keyword'] = 'syncapp';

            //-------------------------------
            // Here we specify where to send the admin after submitting the form
            //-------------------------------
            $settings->return_after_save = $this->settings['base_url'] . $this->form_code;

            //-------------------------------
            // View the settings configuration page
            //-------------------------------

            $settings->_viewSettings();

            //-----------------------------------------
            // And finally, output
            //-----------------------------------------

            $this->registry->getClass('output')->html_main .= $this->registry->getClass('output')->global_template->global_frame_wrapper();
            $this->registry->getClass('output')->sendOutput();
        }
}