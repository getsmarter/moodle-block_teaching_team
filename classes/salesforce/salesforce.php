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
    public function createcase($type, $description, $useremail, $subject) {
        
        global $USER, $CFG;

        $headers = [
            "Authorization: Bearer {$this->accesstoken}",
            'Content-Type: application/json'
        ];

        $data = json_encode([
            'Origin' => self::ORIGIN,
            'Status' => self::STATUS,
            'Type' => $type,
            // Temporarily disabled until SF gets back to us.
            // 'ContactEmail' => $useremail,.
            'Subject' => $subject,
            'Description' => $description,
            'RecordType' => self::RECORD_TYPE,
            'Account' => array(
                'UUID__cc' => $USER->uuid,
                'OLC_Profile_Link__c' => $CFG->wwwroot .  '/user/view.php?id=' . $USER->id
            )
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
        } else {
            error_log('contact_us_salesforce_api:' . $result);
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

}
