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

class admin_group_form__syncApp implements admin_group_form
{
    /**
    * Tab name
    */
    public $tab_name = "";

    /**
    * Returns content for the page
    */
    public function getDisplayContent( $group=array(), $tabsUsed = 4 )
    {
        #Load html template
        $this->html = ipsRegistry::getClass('output')->loadTemplate('cp_skin_syncApp_group_form', 'syncApp' );


        #return display stuff
        return array( 'tabs' => $this->html->acp_group_form_tabs( $group, ( $tabsUsed + 1 ) ), 'content' => $this->html->acp_group_form_main( $group, ( $tabsUsed + 1 ) ), 'tabsUsed' => 1 );
    }

    /**
    * Process the entries for saving and return
    */
    public function getForSave()
    {
        return array(
        'syncapp_realm_id' => intval(ipsRegistry::$request['syncapp_realm_id']),
        'syncapp_server_prem' => intval(ipsRegistry::$request['syncapp_server_prem'])

        );
    }
}