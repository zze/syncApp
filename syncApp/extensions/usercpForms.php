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

    public function ExecuteSoapCommand($command)
    {
        try
        {
            $cliente = new SoapClient(NULL, array(
                "location" => $this->settings['syncapp_soap_ip'], //"http://127.0.0.1:7878/",
                "uri"   => "urn:TC",
                "style" => SOAP_RPC,
                "login" => $this->settings['syncapp_soap_user'],
                "password" => $this->settings['syncapp_soap_password']));
                $result = $cliente->executeCommand(new SoapParam($command, "command"));
        }
        catch(Exception $e)
        {
            return array('sent' => false, 'message' => $e->getMessage());
        }
        return array('sent' => true, 'message' => $result);
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
           //return;
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
            $array[] = array( 'url'    => 'area=gamecp',
                              'title'  => 'Game CP',
                              'active' => $this->request['tab'] == 'myapp' && $this->request['area'] == 'gamecp' ? 1 : 0,
                              'area'   => 'gamecp');

            return $array;

    }

    public function runCustomEvent( $currentArea )
    {
        return $html;
    }

    public function showForm( $current_area, $errors=array() )
    {
        ipsRegistry::instance()->getClass('class_localization')->loadLanguageFile( array( 'public_lang' ), 'syncApp' );

        if ($current_area == 'sync')
        {
            $row = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'forum_id='  .intval( $this->memberData['member_id'] )));

            if($row['forum_id'])
            {
                $current_area = 'gamecp';
                $this->registry->output->silentRedirect($this->settings['base_url'].'app=core&module=usercp&tab=syncApp&area=gamecp');
                return;
            }
            else
            {
                return $this->output .= $this->registry->output->getTemplate('syncApp')->usercpForm();
            }
        }

        if ($current_area == 'gamecp')
        {
             $this->hide_form_and_save_button = true;
             $this->registry->dbFunctions()->setDB( 'mysql', 'realm_DB', array(
                          'sql_database'                  => $this->settings['syncapp_realm_database'],
                          'sql_user'                      => $this->settings['syncapp_mysql_user'],
                          'sql_pass'                      => $this->settings['syncapp_mysql_password'],
                          'sql_host'                      => $this->settings['syncapp_mysql_ip'],
                        )
                    );

            $realm_list = array();

            ipsRegistry::DB('realm_DB')->build(array('select' => '*', 'from' => 'realmlist'));
            $rlist = ipsRegistry::DB('realm_DB')->execute();

            while( $realms = ipsRegistry::DB('realm_DB')->fetch($rlist))
                {
                    $realm_list[$realms['id']] = $realms;
                }
                ipsRegistry::DB('realm_DB')->freeResult($rlist);
                if(count($realm_list)>0)
                {
                    //$realms        = array();
                    $realm_form  = array();

                    foreach($realm_list as $realm)
                    {
                        $realm_form[$realm['id']] = '<option value="'.$realm['id'].'">'.$realm['name'].'</option>';
                        //$realms[$realm['id']] = $realm;
                    }
                }

            //$classToLoad = IPSLib::loadLibrary( IPS_KERNEL_PATH . 'classAjax.php', 'classAjax' );
            //$ajax      = new $classToLoad();
            //$ajax->returnString('test');
            //$data = array('teir1' => array('teir2' => array('name' => 'test', 'lastname' => $_POST['lastname'])));
            //print $data['teir1']['teir2']['name'];

            //print_r($data);
            //print_r($realm_form[$this->request['realm']]['name']);

            if(isset($this->request['realm_selected']))
            {
                $character_list     =   array();
                $char_form          =   array();
                $realm_id           =   $this->request['realm'];
                $characters         =   array();

                /* Temp */
                $databases = array();
                $databases['1'] = array('db' => "characters",);
                $databases['2'] = array('db' => "2");

                /* Debug */
                //print_r($databases[$id]);
                //print $databases[$id]['id'];
                //print_r($this->request);
                //print $ae['id'];


                //if(!isset($this->request['character_selected']))
                    $this->registry->dbFunctions()->setDB( 'mysql', 'character_DB', array(
                              'sql_database'                  => $databases[$realm_id]['db'], // $this->settings['syncapp_character_database'],
                              'sql_user'                      => $this->settings['syncapp_mysql_user'],
                              'sql_pass'                      => $this->settings['syncapp_mysql_password'],
                              'sql_host'                      => $this->settings['syncapp_mysql_ip'],
                            )
                        );

                    $account_link = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'forum_id='  .$this->memberData['member_id']));

                    ipsRegistry::DB('character_DB')->build(array('select' => '*', 'from' => 'characters', 'where' => 'account=' .$account_link['account_id']));
                    $charlist = ipsRegistry::DB('character_DB')->execute();
                    while( $chars = ipsRegistry::DB('character_DB')->fetch($charlist))
                    {
                        $character_list[] = $chars;
                    }
                    ipsRegistry::DB('character_DB')->freeResult($charlist);

                    foreach ($character_list as $character)
                    {
                        $char_form[] = '<option value="'.$character['guid'].'">'.$character['name'].'</option>';
                        $characters[$character['guid']] = $character;
                    }

                    if(count($character_list)>0)
                    {
                        if(isset($this->request['function_selected']))
                        {
                            //print($characters[$this->request['character_guid']]['name']);
                            switch($this->request['function'])
                            {
                                case 1:
                                $cmdLineToSend = 'revive '.$characters[$this->request['character_guid']]['name'];
                                $soap_command = $this->ExecuteSoapCommand($cmdLineToSend);
                                if($soap_command['sent'])
                                    $this->registry->output->redirectScreen("Character ".$characters[$this->request['character_guid']]['name']." has been revived!");
                                else
                                    $this->registry->output->redirectScreen("Was unable to revive character ".$characters[$this->request['character_guid']]['name']);
                                break;
                                case 2:
                                $cmdLineToSend = array();
                                $soap_passed = array();
                                $cmdLineToSend[] = 'revive '.$characters[$this->request['character_guid']]['name'];
                                $cmdLineToSend[] = 'tele name '.$characters[$this->request['character_guid']]['name'].' $home';
                                $soap_command = $this->ExecuteSoapCommand($cmdLineToSend);
                                foreach($cmdLineToSend as $cmd)
                                {
                                    $soap_command = $this->ExecuteSoapCommand($cmd);
                                    if($soap_command['sent'])
                                        $soap_passed[$cmd] = 'passed';
                                    else
                                        $soap_passed[$cmd] = 'failed';
                                }
                                break;
                            }
                        }
                    }
            }
            return $this->output .= $this->registry->output->getTemplate('syncApp')->gameCP($realm_form,$char_form);
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
        if ( $current_area == 'sync' )
        {
            ipsRegistry::instance()->getClass('class_localization')->loadLanguageFile( array( 'public_lang' ), 'syncApp' );
            $this->registry->dbFunctions()->setDB( 'mysql', 'realm_DB', array(
                      'sql_database'                  => $this->settings['syncapp_realm_database'],
                      'sql_user'                      => $this->settings['syncapp_mysql_user'],
                      'sql_pass'                      => $this->settings['syncapp_mysql_password'],
                      'sql_host'                      => $this->settings['syncapp_mysql_ip'],
                )
            );

                // has account
            if ($this->request['exist'] == 1)
            {
                $user = strtoupper(ipsRegistry::DB('realm_DB')->addSlashes($this->request['syncapp_user']));
                $row = ipsRegistry::DB('realm_DB')->buildAndFetch(array('select' => '*', 'from' => 'account', 'where' => "username='{$user}'"));

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
                            $this->registry->output->redirectScreen(ipsRegistry::instance()->getClass('class_localization')->words['failed_account_already_synced']);
                            return;
                        }
                        else
                        {
                            /* Create id sync table */
                            ipsRegistry::DB()->insert('syncapp_members', array(
                            'forum_id'      =>  intval($this->memberData['member_id']),
                            'account_id'    =>  intval($row['id'])));

                            $this->registry->output->redirectScreen(ipsRegistry::instance()->getClass('class_localization')->words['accounct_synced'], $this->settings['base_url'].'app=core&module=usercp&tab=syncApp&area=gamecp');
                            return;
                        }
                    }
                    else
                    {
                        $this->registry->output->redirectScreen(ipsRegistry::instance()->getClass('class_localization')->words['failed_authenticating_password']);
                        return;
                    }
                }
                else
                {
                    $this->registry->output->redirectScreen(ipsRegistry::instance()->getClass('class_localization')->words['failed_account_non_existent']);
                    return;
                }
            }
                // dose not have account
            if ($this->request['exist'] == 2)
            {
                $user = ipsRegistry::DB('realm_DB')->addSlashes($this->request['syncapp_user']);
                $row = ipsRegistry::DB('realm_DB')->buildAndFetch(array('select' => '*', 'from' => 'account', 'where' => "username='{$user}'"));

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
                    $this->registry->output->redirectScreen(ipsRegistry::instance()->getClass('class_localization')->words['failed_username_exists']);
                    return;
                }
                else
                {
                    $board_acctid = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'forum_id=' . intval($this->memberData['member_id'])));
                    $sync_acctid = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'account_id='  .intval($row['id'])));

                    if ($board_acctid || $sync_acctid)
                    {
                        $this->registry->output->redirectScreen(ipsRegistry::instance()->getClass('class_localization')->words['failed_account_exists_in_sync']);
                        return;
                    }
                    else
                    {
                        /* Set up Variables */
                        $username = $this->request['syncapp_user'];
                        $password = strtoupper($this->request['syncapp_password']);
                        $sha_NameAndPass = strtoupper(SHA1("".$username.":".$password.""));
                        /* End variables */

                        /* create WoW account */
                        ipsRegistry::DB('realm_DB')->insert('account', array(
                        'username'      =>  $username,
                        'sha_pass_hash' =>  $sha_NameAndPass,
                        'email'         =>  $this->memberData['email'],
                        'expansion'     =>  intval(2)));

                        /* Grab id from the above query */
                        $account_ID =   ipsRegistry::DB('realm_DB')->getInsertId();

                        /* Create id sync table */
                        ipsRegistry::DB()->insert('syncapp_members', array(
                        'forum_id'      =>  intval($this->memberData['member_id']),
                        'account_id'    =>  intval($account_ID)));

                        $this->registry->output->redirectScreen(ipsRegistry::instance()->getClass('class_localization')->words['congratulations_account_synced'], $this->settings['base_url'].'app=core&module=usercp&tab=syncApp&area=gamecp');
                        return;
                    }
                }
            }
        }
    }
}