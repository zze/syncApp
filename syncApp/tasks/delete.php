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
*
* @class                task_item
* @brief                Task to reimport ical/webcal feeds
*
*/
class task_item
{
        /**
         * Object that stores the parent task manager class
         *
         * @var         $class
         */
        protected $class;

        /**
         * Array that stores the task data
         *
         * @var         $task
         */
        protected $task = array();

        /**
         * Registry Object Shortcuts
         *
         * @var         $registry
         * @var         $DB
         * @var         $lang
         */
        protected $registry;
        protected $DB;
        protected $lang;

        /**
         * Constructor
         *
         * @param       object          $registry               Registry object
         * @param       object          $class                  Task manager class object
         * @param       array           $task                   Array with the task data
         * @return      @e void
         */
        public function __construct( ipsRegistry $registry, $class, $task )
        {
            /* Make registry objects */
            $this->registry = $registry;
            $this->DB               = $registry->DB();
            $this->settings = ipsRegistry::fetchSettings();
            $this->lang             = $this->registry->getClass('class_localization');

            $this->class    = $class;
            $this->task             = $task;

            $this->registry->dbFunctions()->setDB( 'mysql', 'world_DB', array(
                      'sql_database'                  => $this->settings['syncapp_realm_database'],
                      'sql_user'                      => $this->settings['syncapp_mysql_user'],
                      'sql_pass'                      => $this->settings['syncapp_mysql_password'],
                      'sql_host'                      => $this->settings['syncapp_mysql_ip'],
                    )
                );
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
         * Run this task
         *
         * @return      @e void
         */
        public function runTask()
        {
                //-----------------------------------------
                // Here is where you perform your task
                //-----------------------------------------

                $members = array();
                ipsRegistry::DB()->build(array('select' => '*', 'from' => 'syncapp_members', 'where' => "deleted='1'"));
                $memdb =  ipsRegistry::DB()->execute();
                while( $mems = ipsRegistry::DB()->fetch($memdb))
                    {
                        $members[] = $mems['account_id'];
                    }
                    ipsRegistry::DB('world_DB')->freeResult($memdb);
                 if(count($members)>0)
                    {
                        $account = array();
                        ipsRegistry::DB('world_DB')->build(array('select' => 'username, id', 'from' => 'account', 'where' => "id IN('".implode("','", $members)."')"));
                        $acctdb =  ipsRegistry::DB('world_DB')->execute();

                    while( $accts = ipsRegistry::DB('world_DB')->fetch($acctdb))
                        {
                            $account[$accts['id']] = $accts['username'];
                        }
                    }
                    ipsRegistry::DB('world_DB')->freeResult($acctdb);

                    if(count($account)>0)
                    {
                        foreach($account as $id => $m)
                        {
                            //do stuff with $m
                            $cmdLineToSend = 'account delete '.$m;
                            $soap_command = $this->ExecuteSoapCommand($cmdLineToSend);

                            if(!$soap_command['sent'])
                            {
                                $pass = "Failed to delete:"."<b>(</b> ".implode(", ", $account)." <b>)</b>";
                            }
                            else
                            {
                                ipsRegistry::DB()->delete('syncapp_members',  "account_id='{$id}'");
                                $pass = "Accounts deleted:"."<b>(</b> ".implode(", ", $account)." <b>)</b>";
                            }
                        }
                    }

                //-----------------------------------------
                // Save task log
                //-----------------------------------------

                $this->class->appendTaskLog( $this->task, $pass );

                //-----------------------------------------
                // Unlock Task: REQUIRED!
                //-----------------------------------------

                $this->class->unlockTask( $this->task );
        }
}