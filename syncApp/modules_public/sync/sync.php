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
class public_syncApp_sync_sync extends ipsCommand
{
    /**
     * doExecute is the method that is ran when the section is accessed
     */
    public function doExecute( ipsRegistry $registry )
    {
        //-----------------------------------------
        // Load the language file (in the cache/lang_cache directory)
        //-----------------------------------------

        $this->registry->class_localization->loadLanguageFile( array( 'public_lang_syncApp' ) ); // Will automatically prepend "(app)_"

        $this->html = $this->registry->output->loadTemplate( 'cp_skin_syncApp' );
        //-----------------------------------------
        // Set Page title
        //-----------------------------------------

        $this->registry->output->setTitle( $this->lang->words['page_title'] );

        //-----------------------------------------
        // Set Pagination
        //-----------------------------------------

        $this->registry->output->addNavigation( $this->lang->words['page_title'], 'app=syncApp' );

        //-----------------------------------------
        // Display a function from the skin file
        //-----------------------------------------
        // $test = '50%';
        // $this->output .= $this->registry->output->getTemplate('syncApp')->appIndexTemplate(  $test );

        //-----------------------------------------
        // Output
        //-----------------------------------------

        //$this->registry->output->addContent( $test );

        $this->registry->output->addContent( $this->output );
        $this->registry->output->sendOutput();
    }
}