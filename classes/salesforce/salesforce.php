<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Renderable and templatable for contact us page.
 *
 * @package   block_teaching_team
 * @copyright Brendon Pretorius <bpretorius@2u.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_teaching_team\salesforce;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/moodlelib.php');
require_once(dirname( __FILE__ ) . '/../../contactuslib.php');

class salesforce {
    // Consts used when creating SF cases.
    protected const ORIGIN = 'OLC';
    protected const STATUS = 'New';
    protected const RECORD_TYPE = [
        "Name" => "Student Records Case"
    ];

    /** @var string $accesstoken */
    protected $accesstoken;
    /** @var string $authenticationurl */
    protected $authenticationurl;
    /** @var string $clientid */
    protected $clientid;
    /** @var string $clientsecret */
    protected $clientsecret;
    /** @var string $instanceurl */
    protected $instanceurl;
    /** @var string $password */
    protected $password;
    /** @var string $username */
    protected $username;
    /** @var string $parentid */
    protected $parentid;

    /**
     * @param mixed $authenticationurl
     * @param mixed $clientid
     * @param mixed $clientsecret
     * @param mixed $username
     * @param mixed $password
     * @return void
     */
    public function __construct($authenticationurl, $clientid, $clientsecret, $username, $password) {
        $this->authenticationurl = $authenticationurl;
        $this->client_id = $clientid;
        $this->client_secret = $clientsecret;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Authenticate using the settings stored on contaact us settings page
     * @return void
     */
    public function authenticate() {
        $postfields = sprintf(
            'client_id=%s&client_secret=%s&grant_type=password&password=%s&username=%s',
            $this->client_id,
            $this->client_secret,
            $this->password,
            $this->username
        );

        $curlparams = [
            CURLOPT_URL => $this->authenticationurl,
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ];

        list($result, $httpcode) = $this->doapicall($curlparams);

        if ($httpcode == 200) {
            $sfresult = json_decode($result);

            if (!empty($sfresult->access_token)) {
                $this->accesstoken = $sfresult->access_token;
            }

            if (!empty($sfresult->instance_url)) {
                $this->instanceurl = $sfresult->instance_url;
            }
        } else {
            error_log('contact_us_salesforce_api:' . $result);
        }
    }

    /**
     * Create a case on SF. MUST BE RUN AFTER authenticate()
     * @param string $type
     * @param string $description
     * @param string $useremail
     * @return void
     */
    public function createcase($type, $description, $useremail, $subject, $courseid, $file = false) {

        global $USER, $CFG;
        $olcprofilelink = $CFG->wwwroot .  '/user/view.php?id=' . $USER->id;

        $headers = [
            "Authorization: Bearer {$this->accesstoken}",
            'Content-Type: application/json'
        ];

        $data = json_encode([
            'Origin' => self::ORIGIN,
            'Status' => self::STATUS,
            'Type' => $type,
            'Subject' => $subject,
            'Account' => [
                'UUID__c' => $USER->uuid
            ],
            'Description' => $description,
            'RecordType' => self::RECORD_TYPE,
            'OLC_Profile_Link__c' => $olcprofilelink
        ]);

        $curlparams = [
            CURLOPT_URL => $this->instanceurl . '/services/data/v49.0/sobjects/Case/',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $headers
        ];

        list($result, $httpcode) = $this->doapicall($curlparams);

        if ($httpcode == 201) {
            $sfresult = json_decode($result);

            if (!empty($sfresult->id)) {
                $this->parentid = $sfresult->id;
            }

            if ($file) {
                $this->uploadattachementcase($file);
            }
        } else {
            error_log('contact_us_salesforce_api:' . $result);
            $this->send_email($subject, $type, $USER->uuid, $olcprofilelink, $description, $result, $courseid, $file);
        }
    }

    /**
     * Attach file upload to case. MUST BE RUN AFTER createcase()
     * @param array $file
     * @return void
     */
    public function uploadattachementcase($file) {

        $headers = [
            "Authorization: Bearer {$this->accesstoken}",
            'Content-Type: application/json'
        ];

        $data = json_encode([
            'records' => [
                [
                    'attributes' => [
                        'type' => 'Attachment',
                        'referenceId' => $file['name'],
                    ],
                    'contentType' => $file['type'],
                    'name' => $file['name'],
                    'body' => base64_encode(file_get_contents($file['tmp_name'])),
                    'parentId' => $this->parentid,
                ],
            ],
        ]);

        $curlparams = [
            CURLOPT_URL => $this->instanceurl . '/services/data/v49.0/composite/tree/Attachment',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => true
        ];

        list($result, $httpcode) = $this->doapicall($curlparams);

        if ($httpcode != 201) {
            error_log('contact_us_salesforce_api:' . $result);
        }
    }

    /**
     * Utility function to do curl calls
     * @param array $curlparams
     * @return array
     */
    public function doapicall($curlparams) {
        $ch = curl_init();

        foreach ($curlparams as $option => $value) {
            curl_setopt($ch, $option, $value);
        }

        // The following are common config between all te calls.
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        return [$result, $httpcode];
    }

    /**
     * Utility funciton to send email
     * @param string $subject
     * @param string $type
     * @param string $uuid
     * @param string $olcprofilelink
     * @param string $description
     * @param string $error
     * @param int $courseid
     * @param array $file
     */
    public function send_email($subject, $type, $uuid, $olcprofilelink, $description, $error, $courseid, $file = false) {
        global $DB, $CFG;
        $emailbody = sprintf(
            "Type: %s \nSubject: %s \n Account (uuid): %s \nOLC profile link: %s \nDescription: %s \nError: %s",
            $type,
            $subject,
            $uuid,
            $olcprofilelink,
            $description,
            $error
        );

        $salesforcefailoveremail = get_config('block_teaching_team', 'failover_email_address');
        $user = $DB->get_record('user', ['email' => $salesforcefailoveremail]);
        $from = get_success_manager_user($courseid);
        // Basename to prevent dir traversal attacks
        $filename = basename($file['name']);
        $uploaddir = $CFG->tempdir . '/' . $filename;

        // Copy the file to the $CFG->tempdir because the email send requires it to be there or $CFG->filedir
        if ($file) {
            move_uploaded_file($file['tmp_name'], $uploaddir);
        }

        email_to_user($user, $from, $subject, $emailbody, '', $uploaddir, $filename);
    }

}
