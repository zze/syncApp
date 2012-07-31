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

class dashboardNotifications__syncApp
{

    public function __construct()
    {
            $this->registry = ipsRegistry::instance();
            $this->settings = ipsRegistry::fetchSettings();
    }

    public function get()
    {
        $warnings = array();

        if ($this->settings['syncapp_enabled_soap'] == 1)
        {
            if( !$this->settings['syncapp_soap_user'] || !$this->settings['syncapp_soap_password'])
            {
                $warnings[] = array( "SyncApp Soap connection info missing!", "Go to System Settings → SyncApp → General" );
            }
        }

        if (!$this->settings['syncapp_mysql_user'])
        {
            $warnings[] = array( "SyncApp SQL connection info missing!", "Go to System Settings → SyncApp → General" );
        }
        else
        {
            $classname = "db_driver_Mysql";

                $sync_DB = new $classname;

                $sync_DB->obj['sql_database']  = $this->settings['syncapp_realm_database'];
                $sync_DB->obj['sql_user']      = $this->settings['syncapp_mysql_user'];
                $sync_DB->obj['sql_pass']      = $this->settings['syncapp_mysql_password'];
                $sync_DB->obj['sql_host']      = $this->settings['syncapp_mysql_ip'];

                $sync_DB->return_die = true;

                if ( ! $sync_DB->connect() )
                {
                    $warnings[] = array( "SyncApp Cannot connect to external DB", $sync_DB->error );
                }
        }

        return $warnings;
    }

}