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

class syncApp_installCheck
{
   /**
    * Check for any problems and report errors if any exist
    *
    * @access    public
    * @return    array
    */
   public function checkForProblems()
   {
       $info  = array( 'notexist' => array(), 'notwrite' => array(), 'other' => array() );

      if(!class_exists ('SoapClient')){ $info['other'][]    = 'You must enable PHP\'s soap extension';}

       return $info;
   }
}