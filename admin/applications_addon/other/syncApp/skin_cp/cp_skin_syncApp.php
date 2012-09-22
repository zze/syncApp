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
 * The classname must match the filename
 */
class cp_skin_syncApp extends output
{
    public $path_to_ipb = '';

    /**
     * We must declare a destructor
     */
    public function __destruct()
    {
    }

    /**
     * Functions are used to display output
     */
    public function syncApp()
    {
        $IPBHTML = "";
        //--starthtml--//


        $IPBHTML .= <<<HTML


        <div class='acp-box'> <h3>Table With Control Strips</h3> <table class='ipsTable'>

        <tr>
        <th>Title</th> <th>Stats</th> <th>Stats</th> <th class='col_buttons'>&nbsp;</th>
        </tr>

        <tr class='ipsControlRow'>
        <td>
            <span class='larger_text'>Foo</td> <td>Bar</td> <td>Bar</td>
        <td>
            <ul class='ipsControlStrip'> <li class='i_add'><a href='#'>Add</a></li></ul>
        </td>
        </tr>

        <tr class='ipsControlRow'>
        <td>
            <span class='larger_text'>Foo</td> <td>Bar</td> <td>Bar</td>
        <td>
            <ul class='ipsControlStrip'> <li class='i_edit'><a href='#'>Edit</a></ul>
        </td>
        </tr>
        </table>


HTML;

        //--endhtml--//
        return $IPBHTML;

    }

}