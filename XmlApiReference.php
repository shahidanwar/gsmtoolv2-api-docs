<?php

/*
    Reseller Client API — XML
    Endpoint: https://your-domain.com/gsmfusion_api

    Documentation reference only. Example client code for integrators.
*/

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class XmlApiReference extends Controller
{
    /**
     * Core API call helper.
     *
     * All requests are sent as application/x-www-form-urlencoded POST or GET.
     * Response Content-Type is application/xml.
     */
    private function initXmlApi($api, $action, $extraParams = [])
    {
        $parsed = parse_url($api['url']);
        $url = rtrim($parsed['scheme'] . '://' . $parsed['host'] . (isset($parsed['path']) ? $parsed['path'] : ''), '/');
        $url .= '/gsmfusion_api';

        $fields = array_merge([
            'userId'  => $api['username'],
            'apiKey'  => $api['apiaccesskey'],
            'action'  => $action,
        ], $extraParams);

        try {
            $response = Http::timeout(300)
                ->connectTimeout(15)
                ->retry(3, 2000)
                ->withHeaders([
                    'User-Agent' => request()->header('User-Agent', 'ResellerXmlClient/1.0'),
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

            return $response->body();

        } catch (\Exception $e) {
            return [
                'ERROR' => [
                    ['MESSAGE' => $e->getMessage()]
                ]
            ];
        }
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

        $response = $this->initXmlApi($api, $action);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <Client>
                <ClientCredits>$ 150.00 USD</ClientCredits>
            </Client>

        */
    }

    // -------------------------------------------------------------------------
    // GET IMEI SERVICE LIST
    // -------------------------------------------------------------------------
    public function getImeiServices()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'imeiservices';

        $response = $this->initXmlApi($api, $action);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <Packages>
                <Package>
                    <CategoryId>1</CategoryId>
                    <Category>Samsung Unlock</Category>
                    <PackageId>1</PackageId>
                    <PackageTitle>Samsung Carrier Unlock</PackageTitle>
                    <PackagePrice>5</PackagePrice>
                    <PackagePriceWithCurrency>USD 5</PackagePriceWithCurrency>
                    <TimeTaken>1-24 hours</TimeTaken>
                    <MustRead>Read before order</MustRead>
                    <CustomFields>
                        <CustomField>
                            <FieldLabel>Email</FieldLabel>
                            <FieldName>Email</FieldName>
                            <MinQnt></MinQnt>
                            <MaxQnt></MaxQnt>
                            <MinLength>-</MinLength>
                            <MaxLength>-</MaxLength>
                            <UseAsQuantity>No</UseAsQuantity>
                            <UseAsIMEI>No</UseAsIMEI>
                            <Mandatory>Yes</Mandatory>
                            <FieldType>Text Box</FieldType>
                            <FieldInfo>-</FieldInfo>
                            <FieldOptions>-</FieldOptions>
                        </CustomField>
                    </CustomFields>
                </Package>
            </Packages>

        */
    }

    // -------------------------------------------------------------------------
    // GET SERVER SERVICE LIST
    // -------------------------------------------------------------------------
    public function getServerServices()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'serverservices';

        $response = $this->initXmlApi($api, $action);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <Packages>
                <Package>
                    <CategoryId>2</CategoryId>
                    <Category>Server Tools</Category>
                    <PackageId>10</PackageId>
                    <PackageTitle>Tool Activation 1 Year</PackageTitle>
                    <PackagePrice>36</PackagePrice>
                    <PackagePriceWithCurrency>USD 36</PackagePriceWithCurrency>
                    <TimeTaken>1-3 days</TimeTaken>
                    <MustRead></MustRead>
                    <CustomFields>...</CustomFields>
                </Package>
            </Packages>

        */
    }

    // -------------------------------------------------------------------------
    // GET FILE SERVICE LIST
    // -------------------------------------------------------------------------
    public function getFileServices()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'fileservices';

        $response = $this->initXmlApi($api, $action);

        return $response;

        /* Same Package XML structure as imeiservices. */

    }

    // -------------------------------------------------------------------------
    // GET REMOTE SERVICE LIST
    // -------------------------------------------------------------------------
    public function getRemoteServices()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'remoteservices';

        $response = $this->initXmlApi($api, $action);

        return $response;

        /* Same Package XML structure as imeiservices. */

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

        $action = 'placeorder';

        // Pass custom fields as top-level POST params keyed by FieldLabel (spaces as underscores)
        $params = [
            'networkId' => 1,                      // PackageId from imeiservices
            'IMEI'      => '356938035643809',
            'Email'     => 'customer@example.com', // custom field from service definition
            'orderId'   => 7,                      // optional: your own order id for deduplication
        ];

        $response = $this->initXmlApi($api, $action, $params);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <result>
                <imeis>
                    <id>10001</id>
                    <imei>356938035643809</imei>
                    <status>Pending</status>
                </imeis>
            </result>

            Save <id> — use this value when checking order status.

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

        $params = [
            'serviceId' => 10,                     // PackageId from serverservices
            'Email'     => 'customer@example.com',
            'Username'  => 'unlocktool_user',
            'orderId'   => 8,
        ];

        $response = $this->initXmlApi($api, $action, $params);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <result>
                <order>
                    <id>502</id>
                    <status>Pending</status>
                </order>
            </result>

        */
    }

    // -------------------------------------------------------------------------
    // PLACE FILE ORDER
    // -------------------------------------------------------------------------
    public function placeFileOrder()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'placefileorder';

        $params = [
            'networkId' => 5,                        // PackageId from fileservices
            'fileName'  => 'device.log',
            'fileData'  => base64_encode(file_get_contents('/path/to/device.log')),
        ];

        $response = $this->initXmlApi($api, $action, $params);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <result>
                <fileorder>
                    <id>301</id>
                    <status>Pending</status>
                </fileorder>
            </result>

            Accepted file extensions: log, fnx, bcl, txt, sha, ask

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

        $params = [
            'networkId' => 25,
            'IMEI'      => '356938035643809',
            'Serial'    => 'ABC123456',
            'orderId'   => 9,
        ];

        $response = $this->initXmlApi($api, $action, $params);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <result>
                <imeis>
                    <id>10002</id>
                    <imei>356938035643809</imei>
                    <status>Pending</status>
                </imeis>
            </result>

        */
    }

    // -------------------------------------------------------------------------
    // GET IMEI / REMOTE ORDERS (bulk)
    // -------------------------------------------------------------------------
    public function getImeiOrders()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'getimeis';

        // Comma-separated order ids, or omit / pass 0 for all recent orders
        $params = [
            'orderIds' => '10001,10002',
        ];

        $response = $this->initXmlApi($api, $action, $params);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <result>
                <imeis>
                    <id>10001</id>
                    <package>Samsung Carrier Unlock</package>
                    <imei>356938035643809</imei>
                    <statusId>2</statusId>
                    <status>Completed</status>
                    <code>NCK-1234567890</code>
                    <requestedat>2026-07-30 14:22:00</requestedat>
                    <repliedat>2026-07-30 16:05:00</repliedat>
                    <send></send>
                    <servicetype>imei</servicetype>
                </imeis>
            </result>

            statusId values:
              1 = Pending
              2 = Completed
              3 = Rejected
              4 = In Process

        */
    }

    // -------------------------------------------------------------------------
    // GET SERVER ORDER STATUS
    // -------------------------------------------------------------------------
    public function getServerOrder()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'getserverorder';

        $params = [
            'orderId' => 502,
        ];

        $response = $this->initXmlApi($api, $action, $params);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <result>
                <serverorder>
                    <package>Tool Activation 1 Year</package>
                    <statusId>2</statusId>
                    <status>Completed</status>
                    <code>Activation successful</code>
                    <requestedat>2026-07-30 14:22:00</requestedat>
                </serverorder>
            </result>

        */
    }

    // -------------------------------------------------------------------------
    // GET FILE ORDER STATUS
    // -------------------------------------------------------------------------
    public function getFileOrder()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'getfileorder';

        $params = [
            'orderId' => 301,
        ];

        $response = $this->initXmlApi($api, $action, $params);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <result>
                <fileorder>
                    <package>File Unlock Service</package>
                    <statusId>2</statusId>
                    <status>Completed</status>
                    <code>Unlock code here</code>
                    <requestedat>2026-07-30 14:22:00</requestedat>
                </fileorder>
            </result>

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

        $params = [
            'orderId' => 10002,
        ];

        $response = $this->initXmlApi($api, $action, $params);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <result>
                <remoteorder>
                    <id>10002</id>
                    <package>Remote Check Service</package>
                    <imei>356938035643809</imei>
                    <statusId>2</statusId>
                    <status>Completed</status>
                    <code>Result data here</code>
                    <requestedat>2026-07-30 14:22:00</requestedat>
                </remoteorder>
            </result>

        */
    }

    // -------------------------------------------------------------------------
    // GET PROVIDERS / NETWORKS
    // -------------------------------------------------------------------------
    public function getProviders()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'getproviders';

        $response = $this->initXmlApi($api, $action);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <Networks>
                <Country>
                    <ID>1</ID>
                    <Name>AT&amp;T</Name>
                    <Network>
                        <ID>10</ID>
                        <Name>USA</Name>
                    </Network>
                </Country>
            </Networks>

        */
    }

    // -------------------------------------------------------------------------
    // GET MOBILES / BRANDS
    // -------------------------------------------------------------------------
    public function getMobiles()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'getmobiles';

        $response = $this->initXmlApi($api, $action);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <Mobiles>
                <Brand>
                    <ID>1</ID>
                    <Name>Samsung</Name>
                    <Mobile>
                        <ID>100</ID>
                        <Name>Galaxy S21</Name>
                    </Mobile>
                </Brand>
            </Mobiles>

        */
    }

    // -------------------------------------------------------------------------
    // GET SERVICE TYPES / SERVICE DETAILS (legacy dropdowns)
    // -------------------------------------------------------------------------
    public function getServices()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'getservices';

        $response = $this->initXmlApi($api, $action);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <Services>
                <Service>
                    <ServiceName>Model</ServiceName>
                    <ServiceTbl>models</ServiceTbl>
                </Service>
            </Services>

        */
    }

    public function getServiceDetails()
    {
        $api = [
            'username'     => 'demo',
            'apiaccesskey' => 'X3Z6-T7V5-C8P5-Q8B9-A2A3-A2A3',
            'url'          => 'https://your-domain.com/',
        ];

        $action = 'getservicedtls';

        // st = service type key from getservices (e.g. models, providers)
        $params = [
            'st' => 'models',
        ];

        $response = $this->initXmlApi($api, $action, $params);

        return $response;

        /* Example Result

            <?xml version="1.0" encoding="utf-8"?>
            <Services>
                <Service>
                    <ServiceId>1</ServiceId>
                    <ServiceTitle>Galaxy S21</ServiceTitle>
                </Service>
            </Services>

        */
    }

    // -------------------------------------------------------------------------
    // COMMON ERROR RESPONSES
    // -------------------------------------------------------------------------

    /*
        All errors return XML:

            <?xml version="1.0" encoding="utf-8"?><error>Invalid User!</error>
            <?xml version="1.0" encoding="utf-8"?><error>Invalid API Key!</error>
            <?xml version="1.0" encoding="utf-8"?><error>API access to you is not allowed!</error>
            <?xml version="1.0" encoding="utf-8"?><error>Invalid IP request![x.x.x.x]</error>
            <?xml version="1.0" encoding="utf-8"?><error>This user is not active</error>
            <?xml version="1.0" encoding="utf-8"?><error>Website is in maintenance, please try again later</error>
            <?xml version="1.0" encoding="utf-8"?><error>Invalid value of Network Id!</error>
            <?xml version="1.0" encoding="utf-8"?><error>Invalid IMEI: 12345</error>
            <?xml version="1.0" encoding="utf-8"?><error>Credit has been finished!</error>
            <?xml version="1.0" encoding="utf-8"?><error>Invalid Order ID</error>

    */

    // -------------------------------------------------------------------------
    // ACTION REFERENCE
    // -------------------------------------------------------------------------

    /*
        | Action            | Description                          |
        |-------------------|--------------------------------------|
        | accountinfo       | Account credit balance               |
        | imeiservices      | IMEI service catalog                 |
        | fileservices      | File service catalog                 |
        | serverservices    | Server / tool service catalog        |
        | remoteservices    | Remote service catalog               |
        | getservices       | Legacy service type list             |
        | getservicedtls    | Legacy service detail dropdown       |
        | placeorder        | Place IMEI order                     |
        | placefileorder    | Place file order                     |
        | placeserverorder  | Place server order                   |
        | placeremoteorder  | Place remote order                   |
        | getimeis          | Get IMEI / remote order status       |
        | getfileorder      | Get file order status                |
        | getserverorder    | Get server order status              |
        | getremoteorder    | Get remote order status              |
        | getproviders      | Provider / network list              |
        | getmobiles        | Brand / mobile list                  |

        Auth parameters (all actions):
          userId  — API username
          apiKey  — API access key

    */
}
