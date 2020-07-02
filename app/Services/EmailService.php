<?php

namespace App\Services;
use GuzzleHttp\Client;
use App\Models\Setting;
use App\Models\OdinOrder;
use App\Models\OdinCustomer;
use Exception;
use App\Exceptions\BlockEmailException;
use Illuminate\Support\Str;

/**
 * Email Service class
 */
class EmailService
{

    public static $thecheckerEmailValidStatus = 'deliverable';
    public static $ipqsLowDeliverability = 'low';
    public static $NA = 'N/A';
    const TIMEOUT_IPQS = 2;

    /**
     *
     * @var type
     */
    protected $apiKey;

    /**
     *
     */
    public function __construct()
    {
        $apiKey = Setting::getValue('saga_api_access_key');
        $this->apiKey = !empty($apiKey) ? $apiKey : '';
    }

    /**
     * Validate email with thecheker.io
     * @param string $email
     * @return bool
     */
    public function validateEmailWithThechecker(string $email) : bool
    {
        $apiKey = Setting::getValue('thechecker_api_key');
        // validate email using php
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $url = "https://api.thechecker.co/v2/verify?email={$email}&api_key={$apiKey}";
            $result = file_get_contents($url);
            $res = json_decode($result);
            if ($res) {
                if (!empty($res->result) && $res->result == static::$thecheckerEmailValidStatus) {
                    $isValid = true;
                } else {
                    $isValid = false;
                }
            } else {
                $isValid = true;
                logger()->error("Validate email fail, can't decode {$url}");
            }
        } else {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Validate Email with ipqualityscore.com
     * @param string $email
     * @return type
     */
    public function validateEmailWithIPQS(string $email)
    {
        $apiKey = Setting::getValue('ipqs_private_api_key');
        $block = false; $suggest = ''; $warning = false; $valid = false; $disposable = false;
        if ($email) {
            // as first check customer for trusted
            $isTrusted = OdinCustomer::isTrustedByEmail($email);
            if ($isTrusted) {
                $valid = true;
            } else {
                $url = "https://www.ipqualityscore.com/api/json/email/{$apiKey}/{$email}?timeout=" . static::TIMEOUT_IPQS;
                $timeOut = stream_context_create(
                    ['http' => ['timeout' => 5]]
                );
                for ($i = 1; $i <= 3; $i++) {
                    try {
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
                        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
                        $json = curl_exec($curl);
                        curl_close($curl);
                        $res = json_decode($json);
                        //$result = file_get_contents($url, false, $timeOut);
                    } catch (\Exception $ex) {
                        //logger()->error("Validate email IPQS connection error", ['email' => $email,'code' => $ex->getCode(), 'message' => $ex->getMessage()]);
                    }
                    if (isset($res->success) && $res->success === true) {
                        //$res = json_decode($result);
                        // check warning
                        if (!empty($res->timed_out) || $res->deliverability == static::$ipqsLowDeliverability) {
                            $warning = true;
                        }

                        if (!empty($res->suggested_domain) && $res->suggested_domain != static::$NA) {
                            $domain = explode('@', $email)[1];
                            $suggest = str_replace($domain, $res->suggested_domain, $email);
                        }

                        if ((isset($res->overall_score) && $res->overall_score > 0)) {
                            $valid = true;
                        }

                        $disposable = $res->disposable ?? false;

                        // block if recent_abuse, leaked or overall_score = 0
                        if (!empty($res->recent_abuse) || !empty($res->leaked)) {
                            $block = true;
                            logger()->info("Blocked email", ['email' => $email, 'res' => $res]);
                            throw new BlockEmailException([
                                'block' => $block,
                                'warning' => $warning,
                                'suggest' => $suggest,
                                'valid' => $valid,
                                'disposable' => $disposable
                            ], "Email blocked {$email}, answer: " . json_encode($res));
                        }

                        break;
                    }
                }
            }
        } else {
            $block = true;
        }

        return [
            'block' => $block,
            'warning' => $warning,
            'suggest' => $suggest,
            'valid' => $valid,
            'disposable' => $disposable
        ];
    }

    /**
     * Send order email code to SAGA service
     * @param string $code
     * @param string $email
     * @param string $return_url
     * @return mixed
     */
    public function sendOrderEmailCode(string $code, string $email, string $return_url)
    {
        $client = new \GuzzleHttp\Client();
        $urlPath = Setting::getValue('saga_api_endpoint');
        $urlPath = !empty($urlPath) ? $urlPath : '';


        $url = $urlPath.'?r=odin-api/send-order-email-code';

        $request = $client->request('POST', $url, [
            'headers' => [
                'api-token' => $this->apiKey,
            ],
            'form_params' => [
                'language' => app()->getLocale(),
                'customer_email' => $email,
                'code' => $code,
                'domain'    => request()->server('SERVER_NAME'),
                'url' => $return_url
            ]
        ]);

        return json_decode($request->getBody()->getContents(), true);
    }


}
