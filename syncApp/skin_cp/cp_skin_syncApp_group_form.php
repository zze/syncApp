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

class cp_skin_syncApp_group_form extends output
{

/**
 * Prevent our main destructor being called by this class
 */
public function __destruct()
{
}

/**
 * Show forums group form
 */
public function acp_group_form_main( $group, $tabId )
{
    $form = array();
    $val = $this->caches['group_cache'][ $group['g_id']]['syncapp_server_prem'];
    $form['realm_id']  = $this->registry->output->formInput( "syncapp_realm_id", '-1' );
    $form_server_prem  = ipsRegistry::getClass('output')->formDropdown( "syncapp_server_prem", array( array( '3', 'Administrator' ), array( '2', 'Moderator' ), array( '1', 'Member'), array( '0', 'Banned' ) ), $val );

	$IPBHTML = "";

	$IPBHTML .= <<<EOF

    <div id='tab_GROUPS_{$tabId}_content'>
            <table class='ipsTable double_pad'>
                    <tr>
                            <th colspan='2'>Group options</th>
                    </tr>
                     <tr>
                             <td class='field_title'>
                                    <strong class='title'>Realm id:</strong>
                            </td>
                             <td class='field_field'>
                             {$form['realm_id']}<br />
                             <span class='desctext'></span>
                     </td>
                     </tr>
                     <tr>
                             <td class='field_title'>
                                    <strong class='title'>Server premissions group:</strong>
                            </td>
                             <td class='field_field'>
                             {$form_server_prem}<br />
                             <span class='desctext'></span>
                     </td>
                     </tr>
       </table>
</div>

EOF;


return $IPBHTML;
}

/**
 * Display forum group form tabs
 */
public function acp_group_form_tabs( $group, $tabId )
{
	$IPBHTML = "";

	$IPBHTML .= <<<EOF
    <li id='tab_GROUPS_{$tabId}' class=''>syncApp</li>
EOF;

return $IPBHTML;
}

}