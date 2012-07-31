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
* Member Synchronization extension
*/

class syncAppMemberSync
{
        /**
         * Registry reference
         *
         * @var         object
         */
        public $registry;

        /**
         * CONSTRUCTOR
         *
         * @return      @e void
         */


        public function __construct()
        {
                $this->registry   =  ipsRegistry::instance();
                $this->DB         =  $this->registry->DB();
                $this->settings   =& $this->registry->fetchSettings();
                $this->request    =& $this->registry->fetchRequest();
                $this->lang       =  $this->registry->getClass('class_localization');
                $this->member     =  $this->registry->member();
                $this->memberData =& $this->registry->member()->fetchMemberData();
                $this->cache      =  $this->registry->cache();
                $this->caches     =& $this->registry->cache()->fetchCaches();


                $classname = "db_driver_Mysql";

                    $sync_DB = new $classname;

                    $sync_DB->obj['sql_database']  = $this->settings['syncapp_realm_database'];
                    $sync_DB->obj['sql_user']      = $this->settings['syncapp_mysql_user'];
                    $sync_DB->obj['sql_pass']      = $this->settings['syncapp_mysql_password'];
                    $sync_DB->obj['sql_host']      = $this->settings['syncapp_mysql_ip'];

                    $sync_DB->return_die = true;

                    if ( ! $sync_DB->connect() )
                    {
                        $fail = 1;
                        return $fail;
                        /* At this point we dont have a connection so ABORT! else database driver error */
                    }

                if ($this->settings['syncapp_mysql_user'] || $this->settings['syncapp_mysql_password'] || $fail != 1 )
                {
                    $this->registry->dbFunctions()->setDB( 'mysql', 'appSyncWoWqqDB', array(
                              'sql_database'                  => $this->settings['syncapp_realm_database'],
                              'sql_user'                      => $this->settings['syncapp_mysql_user'],
                              'sql_pass'                      => $this->settings['syncapp_mysql_password'],
                              'sql_host'                      => $this->settings['syncapp_mysql_ip'],
                            )
                        );
                    $this->registry->dbFunctions()->setDB( 'mysql', 'appSyncWoWcharacterDB', array(
                              'sql_database'                  => $this->settings['syncapp_character_database'],
                              'sql_user'                      => $this->settings['syncapp_mysql_user'],
                              'sql_pass'                      => $this->settings['syncapp_mysql_password'],
                              'sql_host'                      => $this->settings['syncapp_mysql_ip'],
                            )
                        );
                }
                else
                {
                    return;
                }
        }

        /**
         * Returns users external ip
         *
         * @return      @e void
         */
        public function get_ip_address()
        {
            foreach(array('HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_X_CLUSTER_CLIENT_IP','HTTP_FORWARDED_FOR','HTTP_FORWARDED','REMOTE_ADDR') as $key)
            {
                if (array_key_exists($key, $_SERVER) === true)
                {
                    foreach (explode(',', $_SERVER[$key]) as $ip)
                    {
                        if (filter_var($ip, FILTER_VALIDATE_IP) !== false)
                        {
                            return $ip;
                        }
                    }
                }
            }
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

        /**
         * This method is run when a member is flagged as a spammer
         *
         * @param       array    $member        Array of member data
         * @return      @e void
         */
        public function onSetAsSpammer( $member )
        {
        }

        /**
         * This method is run when a member is un-flagged as a spammer
         *
         * @param       array    $member        Array of member data
         * @return      @e void
         */
        public function onUnSetAsSpammer( $member )
        {
        }

        /**
         * This method is run when a new account is created
         *
         * @param       array    $member        Array of member data
         * @return      @e void
         */
        public function onCreateAccount( $member )
        {
            $user = strtoupper(ipsRegistry::DB('appSyncWoWqqDB')->addSlashes(strtoupper($member['members_display_name'])));
            $row = ipsRegistry::DB('appSyncWoWqqDB')->buildAndFetch(array('select' => '*', 'from' => 'account', 'where' => "username='{$user}'"));

            if($row)
            {
                $this->registry->output->redirectScreen("Failed: Account username exists in server DB");
                return;
            }
            else
            {

            $exists = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'forum_id=' . intval($this->memberData['member_id'])));
            $acctid = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'account_id='  .intval($row['id'])));

            if ($exists || $acctid)
            {
                $this->registry->output->redirectScreen("Failed: Account ID exists in sync DB");
                return;
            }
            else
            {

                /* Set Variables */
            if (isset($this->request['PassWord']))
                {
                    $password = $this->request['PassWord'];
                    }
                     else  // User or admin creating the account?
                    {
                        $password = $this->request['password'];
                }

            if ($this->settings['syncapp_email_vaildate'] == 1)
                {
                    $locked = intval(1);
                    }
                    else // Force user to vaildate email before allowing server access?
                    {
                        $locked = intval(0);
                }

            $username = $member['name'];
            $sha_NameAndPass = strtoupper(SHA1("".$username.":".$password.""));
            $ip = $this->get_ip_address();
                /* End variables */

            /* create WoW account */
            ipsRegistry::DB('appSyncWoWqqDB')->insert('account', array(
            'username'      =>  $username,
            'sha_pass_hash' =>  $sha_NameAndPass,
            'email'         =>  $this->memberData['email'],
            'last_ip'       =>  $ip,
            'locked'        =>  $locked,
            'expansion'     =>  intval(2)));

            $account_ID =   ipsRegistry::DB('appSyncWoWqqDB')->getInsertId();

            /* Create id sync table */
            ipsRegistry::DB()->insert('syncapp_members', array(
            'forum_id'      =>  $member['member_id'],
            'account_id'        =>  $account_ID));

                }
            }
        }

        /**
         * This method is run when the register form is displayed to a user
         *
         * @return      @e void
         */
        public function onRegisterForm()
        {
        }

        /**
         * This method is ren when a user successfully logs in
         *
         * @param       array    $member        Array of member data
         * @return      @e void
         */
        public function onLogin( $member )
        {
        }

        /**
         * This method is run when a user logs out
         *
         * @param       array    $member        Array of member data
         * @return      @e void
         */
        public function onLogOut( $member )
        {
        }

        /**
         * This method is called after a member account has been removed
         *
         * @param       string  $ids    SQL IN() clause
         * @return      @e void
         */
        public function onDelete( $mids )
        {
            if ($this->settings['syncapp_enabled_soap'] == 1){

                    $members = array();
                    ipsRegistry::DB()->build(array('select' => '*', 'from' => 'syncapp_members', 'where' => "forum_id ".$mids));

                    $memdb =  ipsRegistry::DB()->execute();
                    while( $mems = ipsRegistry::DB()->fetch($memdb))
                        {
                            $members[] = $mems['account_id'];
                        }
                    ipsRegistry::DB('appSyncWoWqqDB')->freeResult($memdb);

                    if(count($members)>0)
                    {
                        $account = array();
                        ipsRegistry::DB('appSyncWoWqqDB')->build(array('select' => 'username', 'from' => 'account', 'where' => "id IN('".implode("','", $members)."')"));
                        $acctdb =  ipsRegistry::DB('appSyncWoWqqDB')->execute();

                    while( $accts = ipsRegistry::DB('appSyncWoWqqDB')->fetch($acctdb))
                        {
                            $account[] = $accts['username'];
                        }
                    ipsRegistry::DB('appSyncWoWqqDB')->freeResult($acctdb);

                    if(count($account)>0)
                    {
                        foreach($account as $m)
                        {
                            //do stuff with $m
                            $cmdLineToSend = 'account delete '.$m;
                            $soap_command = $this->ExecuteSoapCommand($cmdLineToSend);
                        }
                    }
                }
            }
        }
            // Credit to: Marcher
            /* Big thanks */

        /**
         * This method is called after a member's account has been merged into another member's account
         *
         * @param       array   $member         Member account being kept
         * @param       array   $member2        Member account being removed
         * @return      @e void
         */
        public function onMerge( $member, $member2 )
        {
        }

        /**
         * This method is run after a users email address is successfully changed
         *
         * @param  integer  $id          Member ID
         * @param  string   $new_email  New email address
         * @param  string       $old_email      Old email address
         * @return void
         */
        public function onEmailChange( $id, $new_email, $old_email )
        {
            $row = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'forum_id='  .$id));
            $acctid = $row['account_id'];
            $row = ipsRegistry::DB('appSyncWoWqqDB')->buildAndFetch(array('select' => 'username', 'from' => 'account', 'where' => 'id=' .$id));

            ipsRegistry::DB('appSyncWoWqqDB')->update('account', array('email' =>   $new_email), "id=".$acctid);
        }

        /**
         * This method is run after a users password is successfully changed
         *
         * @param       integer $id                                             Member ID
         * @param       string  $new_plain_text_pass    The new password
         * @return      @e void
         */
        public function onPassChange( $id, $new_plain_text_pass )
        {
                $row = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'forum_id='  .$id));
                $acctid = $row['account_id'];
                $row = ipsRegistry::DB('appSyncWoWqqDB')->buildAndFetch(array('select' => 'username', 'from' => 'account', 'where' => 'id=' .$acctid));

                if ($this->settings['syncapp_enabled_soap'] == 1)
                {
                    $cmdLineToSend = 'account set password '.$row['username'].' '.$new_plain_text_pass.' '.$new_plain_text_pass;
                    $soap_command = $this->ExecuteSoapCommand($cmdLineToSend);
                            // print_r($row); exit();

                            //  Debug
                            /*
                            if($soap_command['sent']){
                                     print  "<b>-SUCCESS-</b> " . $soap_command['message']; exit();
                            }
                            else{ print "<b>-ERROR-</b>" . $soap_command['message'];  exit(); }
                            */

                }
                else
                {
                    $username = $row[0];
                    $password = $new_plain_text_pass;
                    $hash = strtoupper(SHA1("".$username.":".$password.""));
                    $row = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'forum_id=' .$id));

                    ipsRegistry::DB('appSyncWoWqqDB')->update('account', array('sha_pass_hash' => $hash, 'sessionkey' => '', 'v' => '', 's' => '' ), "id=".$row['account_id']);

                        //  Debug
                        // $this->registry->output->addContent( $username.' - '.$password.' - '.$hash);
                        // $this->registry->output->sendOutput();
                }
        }

        /**
         * This method is run after a users profile is successfully updated
         * $member will contain EITHER 'member_id' OR 'email' depending on what data was passed to
         * IPSMember::save().
         *
         * @param       array    $member                Array of values that were changed
         * @return      @e void
         */
        public function onProfileUpdate( $member )
        {
        }

        /**
         * This method is run after a users group is successfully changed
         *
         * @param       integer $id                     Member ID
         * @param       integer $new_group      New Group ID
         * @param       integer $old_group      Old Group ID
         * @return      @e void
         */
        public function onGroupChange( $id, $new_group, $old_group )
        {
                $group = $this->caches['group_cache'];
                $myGroup = array();
                $mID = intval($new_group);
                $row = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'forum_id=' .$id));

                    foreach($group as $gid => $g)
                    {
                     if($mID===$gid)
                      {
                        $myGroup = $g;
                        break;
                      }
                    }

                    $account_id = $row['account_id'];

                    /* Banned */
                if ($myGroup['syncapp_server_prem'] == 0)
                {
                    ipsRegistry::DB('appSyncWoWqqDB')->update('account', array('locked' =>  '1'), "id=".$account_id);
                    $gm_r = ipsRegistry::DB('appSyncWoWqqDB')->buildAndFetch(array('select' => '*', 'from' => 'account_access', 'where' => 'id=' .$account_id));

                        if ($gm_r['gmlevel'] >= 2){
                            ipsRegistry::DB('appSyncWoWqqDB')->delete('account_access', "id='{$account_id}'");
                        }
                    }

                    /* Member */
                if ($myGroup['syncapp_server_prem'] == 1)
                {
                    ipsRegistry::DB('appSyncWoWqqDB')->update('account', array('locked' =>  '0'), "id=".$account_id);
                    $gm_r = ipsRegistry::DB('appSyncWoWqqDB')->buildAndFetch(array('select' => '*', 'from' => 'account_access', 'where' => 'id=' .$account_id));

                        if ($gm_r['gmlevel'] >= 2){
                            ipsRegistry::DB('appSyncWoWqqDB')->delete('account_access', "id='{$account_id}'");
                        }
                    }

                    /* Moderator / Administrator */
                if ($myGroup['syncapp_server_prem'] >= 2)
                {
                    //print_r($myGroup);exit();
                    $gm_r = ipsRegistry::DB('appSyncWoWqqDB')->buildAndFetch(array('select' => '*', 'from' => 'account_access', 'where' => 'id=' .$account_id));

                    if (!$gm_r['id'] == $account_id) {

                        ipsRegistry::DB('appSyncWoWqqDB')->insert('account_access', array(
                            'id'            =>      intval($account_id),
                            'gmlevel'       =>      intval($myGroup['syncapp_server_prem']),
                            'RealmID'       =>      intval($myGroup['syncapp_realm_id'])));
                        }
                    else
                        {
                        ipsRegistry::DB('appSyncWoWqqDB')->update('account_access', array('gmlevel' => $myGroup['syncapp_server_prem']), "id=".$account_id);
                        }
                    }
        }

        /**
         * This method is run after a users display name is successfully changed
         *
         * @param       integer $id                     Member ID
         * @param       string  $new_name       New display name
         * @return      @e void
         */
        public function onNameChange( $id, $new_name )
        {
        /*      $row = ipsRegistry::DB()->buildAndFetch(array('select' => '*', 'from' => 'syncapp_members', 'where' => 'forum_id='  .$id));
                $password = ['displayPassword'];
                $username = $new_name;
                $hash = strtoupper(SHA1("".$username.":".$password.""));

                ipsRegistry::DB('appSyncWoWqqDB')->update('account', array( 'username' => $username, 'sha_pass_hash' => $hash, 'sessionkey' => '', 'v' => '', 's' => '' ), "id=".$row['account_id']);
        */
        }

        /**
         * This method is run when a user 'completes' their account, e.g. after they have validated their registration
         *
         * @param       array    $member        Array of member data
         * @return      @e void
         */
        public function onCompleteAccount( $member )
        {
                if ($this->settings['syncapp_email_vaildate'] == 1)
                {
                    ipsRegistry::DB('appSyncWoWqqDB')->update('account', array('locked' =>  '0'), "id=".$member['member_id']);
                }
        }
}