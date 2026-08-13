<?php

/*
    Reseller Client API — JSON
    Endpoint: https://your-domain.com/api/index.php

    Documentation reference only. Example client code for integrators.
*/

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class JsonApiReference extends Controller
{
    /**
     * Core API call helper.
     *
     * All requests are sent as application/x-www-form-urlencoded POST.
     * Order-related actions pass order fields inside a PARAMETERS XML string.
     */
    private function initJsonApi($api, $action, $parametersXml = null, array $extraFields = [])
    {
        $parsed = parse_url($api['url']);
        $url = rtrim($parsed['scheme'] . '://' . $parsed['host'] . (isset($parsed['path']) ? $parsed['path'] : ''), '/');
        if (strpos($url, '.php') === false) {
            $url .= '/api/index.php';
        }

        $fields = array_merge([
            'username'       => $api['username'],
            'apiaccesskey'   => $api['apiaccesskey'],
            'action'         => $action,
            'requestformat'  => 'JSON',
        ], $extraFields);

        if ($parametersXml !== null) {
            $fields['parameters'] = $parametersXml;
        }

        try {
            $response = Http::timeout(300)
                ->connectTimeout(15)
                ->retry(3, 2000)
                ->withHeaders([
                    'User-Agent' => request()->header('User-Agent', 'ResellerJsonClient/2.0'),
                ])
                ->withOptions([
                    'verify' => false,
                ])
                ->asForm()
                ->post($url, $fields);

            if ($response->failed()) {
                return [
                    'ERROR' => [
                        ['MESSAGE' => $response->body(), 'STATUS' => $response->status()]
                    ]
                ];
            }

            return $response->json();

        } catch (\Exception $e) {
            return [
                'ERROR' => [
                    ['MESSAGE' => $e->getMessage()]
                ]
            ];
        }
    }

    /**
     * Build PARAMETERS XML for order actions.
     * Custom fields must be base64-encoded JSON keyed by field label (spaces as underscores).
     */
    private function buildParametersXml(array $data)
    {
        $xml = '<PARAMETERS>';
        foreach ($data as $key => $value) {
            $xml .= '<' . strtoupper($key) . '>' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '</' . strtoupper($key) . '>';
        }
        $xml .= '</PARAMETERS>';
        return $xml;
    }

    // -------------------------------------------------------------------------
    // GET ACCOUNT INFORMATION
    // -------------------------------------------------------------------------
    public function getAccountInfo()
    {
        $api = [
            'username'     => 'demo',                              // replace with your API username
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',    // replace with your API access key
            'url'          => 'https://your-domain.com/',          // replace with your site URL
        ];

        $action = 'accountinfo';

        $response = $this->initJsonApi($api, $action);

        return $response;

        /* Example Result

            {
                "apiversion": "2.0.0",
                "SUCCESS": [
                    {
                        "message": "Your Account Info",
                        "AccoutInfo": {
                            "credit": "150.00",
                            "mail": "demo@example.com",
                            "currency": "USD"
                        }
                    }
                ]
            }

        */
    }

    // -------------------------------------------------------------------------
    // GET SERVICE LIST (IMEI + FILE + SERVER + REMOTE)
    // -------------------------------------------------------------------------
    public function getServiceList()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'imeiservicelist';

        $response = $this->initJsonApi($api, $action);

        return $response;

        /* ---------------------------------------------------------------------
         * API Format (per user)
         *
         * Set by the site administrator: User → API → "API Format: Old Format / New Format"
         *
         *   Old Format (default):
         *     imeiservicelist returns multiple SUCCESS blocks (IMEI, Server/File, Remote)
         *
         *   New Format:
         *     imeiservicelist returns one SUCCESS block with MESSAGE "All Services"
         *
         * This setting is applied automatically for the authenticated user.
         * It is NOT sent as a request parameter. It ONLY affects imeiservicelist.
         * All other actions are unchanged.
         *
         * Old Format:
         *   SUCCESS is an array of separate blocks — one per service type:
         *     - "IMEI Service List"   (GROUPTYPE: IMEI)
         *     - "Service List"        (GROUPTYPE: SERVER / FILE)
         *     - "Remote Service List" (GROUPTYPE: REMOTE)
         *
         * New Format:
         *   SUCCESS contains a single block:
         *     - MESSAGE: "All Services"
         *     - LIST: all groups merged (same group names have SERVICES combined)
         * ------------------------------------------------------------------- */

        /* Example Result — Old Format (default)

            {
                "apiversion": "2.0.0",
                "SUCCESS": [
                    {
                        "MESSAGE": "IMEI Service List",
                        "LIST": {
                            "Samsung Unlock": {
                                "GROUPNAME": "Samsung Unlock",
                                "GROUPTYPE": "IMEI",
                                "SERVICES": {
                                    "1": {
                                        "SERVICEID": 1,
                                        "SERVICENAME": "Samsung Carrier Unlock",
                                        "CREDIT": "5",
                                        "TIME": "1-24 hours",
                                        "INFO": "Read before order",
                                        "Requires.Network": "None",
                                        "Requires.Mobile": "None",
                                        "Requires.Provider": "None",
                                        "Requires.PIN": "None",
                                        "Requires.KBH": "None",
                                        "Requires.MEP": "None",
                                        "Requires.PRD": "None",
                                        "Requires.Type": "None",
                                        "Requires.Locks": "None",
                                        "Requires.Reference": "None"
                                    }
                                }
                            }
                        }
                    },
                    {
                        "MESSAGE": "Service List",
                        "LIST": {
                            "Server Tools": {
                                "GROUPNAME": "Server Tools",
                                "GROUPTYPE": "SERVER",
                                "SERVICES": {
                                    "10": {
                                        "SERVICEID": 10,
                                        "SERVICENAME": "Tool Activation 1 Year",
                                        "CREDIT": "36",
                                        "TIME": "1-3 days",
                                        "INFO": "",
                                        "SERVER": "1"
                                    }
                                }
                            }
                        }
                    },
                    {
                        "MESSAGE": "Remote Service List",
                        "LIST": {
                            "Remote Services": {
                                "GROUPNAME": "Remote Services",
                                "GROUPTYPE": "REMOTE",
                                "SERVICES": { }
                            }
                        }
                    }
                ]
            }

        */

        /* Example Result — New Format

            {
                "apiversion": "2.0.0",
                "SUCCESS": [
                    {
                        "MESSAGE": "All Services",
                        "LIST": {
                            "Samsung Unlock": {
                                "GROUPNAME": "Samsung Unlock",
                                "GROUPTYPE": "IMEI",
                                "SERVICES": {
                                    "1": {
                                        "SERVICEID": 1,
                                        "SERVICENAME": "Samsung Carrier Unlock",
                                        "CREDIT": "5",
                                        "TIME": "1-24 hours",
                                        "INFO": "Read before order"
                                    }
                                }
                            },
                            "Server Tools": {
                                "GROUPNAME": "Server Tools",
                                "GROUPTYPE": "SERVER",
                                "SERVICES": {
                                    "10": {
                                        "SERVICEID": 10,
                                        "SERVICENAME": "Tool Activation 1 Year",
                                        "CREDIT": "36",
                                        "TIME": "1-3 days",
                                        "INFO": "",
                                        "SERVER": "1"
                                    }
                                }
                            },
                            "Remote Services": {
                                "GROUPNAME": "Remote Services",
                                "GROUPTYPE": "REMOTE",
                                "SERVICES": { }
                            }
                        }
                    }
                ]
            }

            Client parsers should check SUCCESS[0].MESSAGE:
              - multiple blocks with distinct MESSAGE values → Old Format
              - single block with MESSAGE "All Services"      → New Format

        */
    }

    // -------------------------------------------------------------------------
    // PLACE IMEI ORDER
    // -------------------------------------------------------------------------
    public function placeImeiOrder()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'placeimeiorder';

        // Custom fields: JSON object, base64-encoded inside PARAMETERS
        $customFields = base64_encode(json_encode([
            'Email' => 'customer@example.com',
        ]));

        $parameters = $this->buildParametersXml([
            'ID'          => 1,                    // service / package id from imeiservicelist
            'IMEI'        => '356938035643809',    // 15-digit IMEI (or custom IMEI field value)
            'CUSTOMFIELD' => $customFields,
            'QNT'         => 1,
            'SERVER'      => 0,
        ]);

        // Optional: your client-side order id for deduplication (top-level POST field, NOT in parameters XML)
        $extraFields = ['orderId' => 12345];

        $response = $this->initJsonApi($api, $action, $parameters, $extraFields);

        return $response;

        /* Example Result

            {
                "apiversion": "2.0.0",
                "SUCCESS": [
                    {
                        "MESSAGE": "Order received",
                        "REFERENCEID": 10001
                    }
                ]
            }

            Save REFERENCEID — use this value when calling getimeiorder.

            Optional orderId (deduplication):
              Send orderId as a top-level POST/GET field alongside username, apiaccesskey, action.
              If you retry with the same orderId + service id, the API returns the existing order
              instead of placing a duplicate.

        */
    }

    // -------------------------------------------------------------------------
    // PLACE SERVER ORDER
    // -------------------------------------------------------------------------
    public function placeServerOrder()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'placeserverorder';

        $customFields = base64_encode(json_encode([
            'Email'    => 'customer@example.com',
            'Username' => 'unlocktool_user',
        ]));

        $parameters = $this->buildParametersXml([
            'ID'          => 10,                   // server service id from imeiservicelist
            'CUSTOMFIELD' => $customFields,
            'QNT'         => 1,
        ]);

        // Optional: orderId => your client reference for deduplication
        $response = $this->initJsonApi($api, $action, $parameters, ['orderId' => 12346]);

        return $response;

        /* Example Result

            {
                "apiversion": "2.0.0",
                "SUCCESS": [
                    {
                        "MESSAGE": "Order received",
                        "REFERENCEID": 502
                    }
                ]
            }

        */
    }

    // -------------------------------------------------------------------------
    // PLACE REMOTE ORDER
    // -------------------------------------------------------------------------
    public function placeRemoteOrder()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'placeremoteorder';

        $customFields = base64_encode(json_encode([
            'Serial' => 'ABC123456',
        ]));

        $parameters = $this->buildParametersXml([
            'ID'          => 25,
            'IMEI'        => '356938035643809',
            'CUSTOMFIELD' => $customFields,
            'QNT'         => 1,
        ]);

        // Optional: orderId => your client reference for deduplication
        $response = $this->initJsonApi($api, $action, $parameters, ['orderId' => 12347]);

        return $response;

        /* Example Result

            {
                "apiversion": "2.0.0",
                "SUCCESS": [
                    {
                        "MESSAGE": "Order received",
                        "REFERENCEID": 10002
                    }
                ]
            }

        */
    }

    // -------------------------------------------------------------------------
    // GET IMEI ORDER STATUS
    // -------------------------------------------------------------------------
    public function getImeiOrder()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'getimeiorder';

        $parameters = $this->buildParametersXml([
            'ID' => 10001,    // REFERENCEID returned from placeimeiorder
        ]);

        $response = $this->initJsonApi($api, $action, $parameters);

        return $response;

        /* Example Result

            {
                "apiversion": "2.0.0",
                "SUCCESS": [
                    {
                        "ID": 10001,
                        "DATE": "2026-07-30 14:22:00",
                        "SERVICE": "Samsung Carrier Unlock",
                        "IMEI": "356938035643809",
                        "CODE": "NCK-1234567890",
                        "STATUS": 4,
                        "COMMENTS": "Done"
                    }
                ]
            }

            STATUS values:
              1 = Pending (in progress)
              3 = Rejected
              4 = Completed

        */
    }

    // -------------------------------------------------------------------------
    // GET REMOTE ORDER STATUS
    // -------------------------------------------------------------------------
    public function getRemoteOrder()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'getremoteorder';

        $parameters = $this->buildParametersXml([
            'ID' => 10002,
        ]);

        $response = $this->initJsonApi($api, $action, $parameters);

        return $response;

        /* Example Result

            {
                "apiversion": "2.0.0",
                "SUCCESS": [
                    {
                        "ID": 10002,
                        "DATE": "2026-07-30 14:22:00",
                        "SERVICE": "Remote Check Service",
                        "IMEI": "10002",
                        "CODE": "Result data here",
                        "STATUS": 4,
                        "COMMENTS": "Completed"
                    }
                ]
            }

        */
    }

    // -------------------------------------------------------------------------
    // GET MEP LIST (legacy)
    // -------------------------------------------------------------------------
    public function getMepList()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'meplist';

        $response = $this->initJsonApi($api, $action);

        return $response;

        /* Example Result

            {
                "apiversion": "2.0.0",
                "SUCCESS": [
                    {
                        "MESSAGE": "Mep List",
                        "LIST": {
                            "2": {
                                "NAME": "xyz",
                                "ID": "1"
                            }
                        }
                    }
                ]
            }

        */
    }

    // -------------------------------------------------------------------------
    // COMMON ERROR RESPONSES
    // -------------------------------------------------------------------------

    /*
        Authentication / access errors:

            { "apiversion": "2.0.0", "ERROR": [{ "MESSAGE": "Invalid User!" }] }
            { "apiversion": "2.0.0", "ERROR": [{ "MESSAGE": "Invalid API Key!" }] }
            { "apiversion": "2.0.0", "ERROR": [{ "MESSAGE": "API access to you is not allowed!" }] }
            { "apiversion": "2.0.0", "ERROR": [{ "MESSAGE": "Invalid IP request![x.x.x.x]" }] }
            { "apiversion": "2.0.0", "ERROR": [{ "MESSAGE": "This user is not active!" }] }
            { "apiversion": "2.0.0", "ERROR": [{ "MESSAGE": "Website is in maintenance, please try again later" }] }
            { "apiversion": "2.0.0", "ERROR": [{ "MESSAGE": "Invalid Action Request" }] }

        Order errors:

            { "apiversion": "2.0.0", "ERROR": [{ "MESSAGE": "Invalid value of Network Id!" }] }
            { "apiversion": "2.0.0", "ERROR": [{ "MESSAGE": "Invalid IMEI!" }] }
            { "apiversion": "2.0.0", "ERROR": [{ "MESSAGE": "Your Credits have been finished!" }] }
            { "apiversion": "2.0.0", "ERROR": [{ "MESSAGE": "IMEI already exists!" }] }

    */
}
