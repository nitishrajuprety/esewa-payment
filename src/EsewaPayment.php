<?php

namespace NitishRajUprety\EsewaPayment;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class EsewaPayment
{
    protected float $amount;
    protected float $tax_amount;
    protected float $total_amount;
    protected string $transaction_uuid;
    protected string $product_code;
    protected float $product_service_charge;
    protected float $product_delivery_charge;
    protected string $success_url;
    protected string $failure_url;
    protected string $signed_field_names;
    protected string $signature;
    protected string $secret_key;

    public function config(string $success_url, string $failure_url, float $amount, string $transaction_id, string $product_code, float $tax_amount = 0, float $product_service_charge = 0, float $product_delivery_charge = 0): void
    {
        $this->success_url = $success_url;
        $this->failure_url = $failure_url;
        $this->amount = $amount;
        $this->tax_amount = $tax_amount;
        $this->product_code = $product_code;
        $this->product_service_charge = $product_service_charge;
        $this->product_delivery_charge = $product_delivery_charge;
        $this->signed_field_names = "total_amount,transaction_uuid,product_code";
        $this->total_amount = $this->tax_amount + $this->amount + $this->product_delivery_charge + $this->product_service_charge;
        $this->secret_key = Config::get('esewa.esewa_secret_key');
        $this->transaction_uuid = $transaction_id;
        $this->signature = $this->generateHmacSignature($this->total_amount, $this->transaction_uuid, $this->product_code, $this->secret_key);
    }

    public function init(): void
    {

        $postData = [
            "amount" => $this->amount,
            "failure_url" => $this->failure_url,
            "product_delivery_charge" => $this->product_delivery_charge,
            "product_service_charge" => $this->product_service_charge,
            "product_code" => $this->product_code,
            "signature" => $this->signature,
            "signed_field_names" => "total_amount,transaction_uuid,product_code",
            "success_url" => $this->success_url,
            "tax_amount" => $this->tax_amount,
            "total_amount" => $this->total_amount,
            "transaction_uuid" => $this->transaction_uuid
        ];

        $url = Config::get('esewa.esewa_epay_base_url');

        echo "<form id='esewaForm' action='$url' method='post'>";
        foreach ($postData as $key => $value) {
            echo '<input type="hidden" name="' . $key . '" value="' . $value . '">';
        }
        echo '</form>';
        echo '<script type="text/javascript">document.getElementById("esewaForm").submit();</script>';
    }

    public function getForm(): HtmlString
    {
        $postData = [
            "amount" => $this->amount,
            "failure_url" => $this->failure_url,
            "product_delivery_charge" => $this->product_delivery_charge,
            "product_service_charge" => $this->product_service_charge,
            "product_code" => $this->product_code,
            "signature" => $this->signature,
            "signed_field_names" => "total_amount,transaction_uuid,product_code",
            "success_url" => $this->success_url,
            "tax_amount" => $this->tax_amount,
            "total_amount" => $this->total_amount,
            "transaction_uuid" => $this->transaction_uuid
        ];

        $url = Config::get('esewa.esewa_epay_base_url');

        $formHtml = "<form id='esewaForm' action='$url' method='post'>";
        foreach ($postData as $key => $value) {
            $formHtml .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
        }
        $formHtml .= '</form>';
        $formHtml .= '<script type="text/javascript">document.getElementById("esewaForm").submit();</script>';
        return new HtmlString($formHtml);
    }

    public function decode(): array|false
    {
        if (isset($_GET['data'])) {
            $data = $_GET['data'];
            $jsonString =  base64_decode($data);
            return json_decode($jsonString, true);
        }

        return false;
    }

    public function validate(string $total_amount, string $transaction_uuid, string $product_code, bool $production = false): array
    {
        $url = Config::get('esewa.esewa_epay_verify_url');

        $params = http_build_query([
            'product_code' => $product_code,
            'total_amount' => $total_amount,
            'transaction_uuid' => $transaction_uuid
        ]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . "?" . $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (!$production) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('eSewa validate transaction cURL Error at ' . now() . ' ' . curl_error($ch));
            curl_close($ch);
            return ['response' => $response, 'status' => false];
        }

        $responseJson = json_decode($response, true);

        if ($responseJson && isset($responseJson['status']) && $responseJson['status'] === 'COMPLETE') {
            curl_close($ch);
            return ['response' => $response, 'status' => true];
        }
        Log::error('eSewa validate transaction cURL Error: ' . $response);
        curl_close($ch);
        return ['response' => $response, 'status' => false];
    }

    protected function generateHmacSignature($total_amount, $transaction_uuid, $product_code, $secret_key): string
    {
        $data = "total_amount=" . $total_amount . ",transaction_uuid=" . $transaction_uuid . ",product_code=" . $product_code;
        $signature = hash_hmac('sha256', $data, $secret_key, true);
        return base64_encode($signature);
    }
}