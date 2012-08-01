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

if ( ! defined( 'IN_IPB' ) )
{
    print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
    exit();
}

class usercpForms_syncApp extends public_core_usercp_manualResolver implements interface_usercp
{
    /**
     * Tab name
     * This can be left blank and the application title will
     * be used
     *
     * @var     string
     */
    public $tab_name = "Settings";

    /**
     * Default area code
     *
     * @var     string
     */
    public $defaultAreaCode = 'sync';

    /**
     * OK Message
     * This is an optional message to return back to the framework
     * to replace the standard 'Settings saved' message
     *
     * @var     string
     */
    public $ok_message = '';

    /**
     * Hide 'save' button and form elements
     * Useful if you have custom output that doesn't
     * need to use it
     *
     * @var     bool
     */
    public $hide_form_and_save_button = false;

    /**
     * Flag to indicate compatibility
     *
     * @var     int
     */
    public $version = 32;

    /**
     * Initiate this module
     *
     * @return  @e void
     */
    public function init()
    {
        $this->tab_name = '';
    }

    public function getLinks()
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
                return;
            }

        $row = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'forum_id='  .intval($this->memberData['member_id'])));

        if( $row['forum_id'] )
        {
            return;
        }
        else
        {
            $array = array();
            $array[] = array( 'url'    => 'area=sync',
                              'title'  => 'Account Sync',
                              'active' => $this->request['tab'] == 'myapp' && $this->request['area'] == 'sync' ? 1 : 0,
                              'area'   => 'sync');
            return $array;
        }
    }

    public function runCustomEvent( $currentArea )
    {
        return $html;
    }

    public function showForm( $current_area, $errors=array() )
    {
      //  ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_lang' ), 'syncapp' );

        $row = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'forum_id='  .intval( $this->memberData['member_id'] )));
        if($row['forum_id'])
        {
            $this->registry->output->silentRedirect($this->settings['base_url']);
            return;
        }
        else
        {
            return $this->output .= $this->registry->output->getTemplate('syncApp')->usercpFrom();
        }
    }

    /**
     * UserCP Form Check
     *
     * @author  Matt Mecham
     * @param   string      Current area as defined by 'get_links'
     * @return  string      Processed HTML
     */
    public function saveForm( $current_area )
    {
        $this->registry->dbFunctions()->setDB( 'mysql', 'appSyncWoWqqDB', array(
                  'sql_database'                  => $this->settings['syncapp_realm_database'],
                  'sql_user'                      => $this->settings['syncapp_mysql_user'],
                  'sql_pass'                      => $this->settings['syncapp_mysql_password'],
                  'sql_host'                      => $this->settings['syncapp_mysql_ip'],
            )
        );

            // has account
        if ($this->request['exist'] == 1)
        {
            $user = strtoupper(ipsRegistry::DB('appSyncWoWqqDB')->addSlashes($this->request['syncapp_user']));
            $row = ipsRegistry::DB('appSyncWoWqqDB')->buildAndFetch(array('select' => '*', 'from' => 'account', 'where' => "username='{$user}'"));
            if ($row)
            {
                $username = strtoupper($this->request['syncapp_user']);
                $password = strtoupper($this->request['syncapp_password']);
                $sha_NameAndPass = strtoupper(SHA1("".$username.":".$password.""));

                if($row['sha_pass_hash'] === $sha_NameAndPass)
                {
                    $acctid = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'account_id='  .intval($row['id'])));
                    $forumid = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'forum_id='  .intval($this->memberData['member_id'])));

                    if($acctid || $forumid)
                    {
                        $this->registry->output->redirectScreen("Failed: This game Account has already been synced");
                        return;
                    }
                    else
                    {
                        /* Create id sync table */
                        ipsRegistry::DB()->insert('syncapp_members', array(
                        'forum_id'      =>  intval($this->memberData['member_id']),
                        'account_id'    =>  intval($row['id'])));

                        $this->registry->output->redirectScreen('Congratulations: Accounct synced!',$this->settings['base_url']);
                        return;
                    }
                }
                else
                {
                    $this->registry->output->redirectScreen("Failed: Could not authenticate password.");
                    return;
                }
            }
            else
            {
                $this->registry->output->redirectScreen("Failed: Username dose not exist, please create an account instead.");
                return;
            }
        }
            // dose not have account
        if ($this->request['exist'] == 2)
        {
            $user = ipsRegistry::DB('appSyncWoWqqDB')->addSlashes($this->request['syncapp_user']);
            $row = ipsRegistry::DB('appSyncWoWqqDB')->buildAndFetch(array('select' => '*', 'from' => 'account', 'where' => "username='{$user}'"));

            //TODO -
            // CHECK FOR LOOPING
        /*  if($this->settings['syncapp_forum_server_name_match'] == 1)
            {
            $val1 = strtoupper($this->request['syncapp_user']);
            $val2 = strtoupper($this->memberData['member_display_name']);

                if($val1 != $val2 )
                {
                    $this->registry->output->redirectScreen("Failed: Account name must match Forum username");
                    return;
                }
            } */

            if ($row)
            {
                $this->registry->output->redirectScreen("Failed: Account name exists in WoW DB");
                return;
            }
            else
            {
                $board_acctid = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'forum_id=' . intval($this->memberData['member_id'])));
                $sync_acctid = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'account_id='  .intval($row['id'])));

                if ($board_acctid || $sync_acctid)
                {
                    $this->registry->output->redirectScreen("Failed: Account exists in sync DB");
                    return;
                }
                else
                {
                    /* Set up Variables */
                    $username = $this->request['syncapp_user'];
                    $password = $this->request['syncapp_password'];
                    $sha_NameAndPass = strtoupper(SHA1("".$username.":".$password.""));
                    /* End variables */

                    /* create WoW account */
                    ipsRegistry::DB('appSyncWoWqqDB')->insert('account', array(
                    'username'      =>  $username,
                    'sha_pass_hash' =>  $sha_NameAndPass,
                    'email'         =>  $this->memberData['email'],
                    'expansion'     =>  intval(2)));

                    /* Grab id from the above query */
                    $account_ID =   ipsRegistry::DB('appSyncWoWqqDB')->getInsertId();

                    /* Create id sync table */
                    ipsRegistry::DB()->insert('syncapp_members', array(
                    'forum_id'      =>  intval($this->memberData['member_id']),
                    'account_id'    =>  intval($account_ID)));

                    $this->registry->output->redirectScreen( "Congratulations: Account created and synced!", $this->settings['base_url']);
                    return;
                }
            }
        }
    }
}