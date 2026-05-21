<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Affiliate_Tracker extends Module
{
    public function __construct()
    {
        $this->name = 'affiliate_tracker';
        $this->tab = 'advertising_marketing';
        $this->version = '1.0.0';
        $this->author = 'Monevibe';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('Monevibe Affiliate Tracker');
        $this->description = $this->l('Automatic Click ID tracking for Monevibe affiliate partners.');
    }

    public function install()
    {
        $shopId = bin2hex(random_bytes(16));

        Configuration::updateValue('MONEVIBE_SHOP_ID', $shopId);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('actionValidateOrder') && 
            $this->registerHook('actionOrderStatusUpdate');
    }

    public function uninstall()
    {
        Configuration::deleteByName('MONEVIBE_SHOP_ID');

        return parent::uninstall();
    }

    public function hookHeader()
    {
        $this->handleClickTracking();
    }

    public function hookDisplayHeader()
    {
        $this->handleClickTracking();
    }

    private function handleClickTracking()
    {
        $clickId = Tools::getValue('clickid');

        if ($clickId) {
            setcookie(
                'monevibe_click_id',
                $clickId,
                time() + (86400 * 365 * 10),
                '/',
                '',
                true,
                true
            );
        }
    }

    public function hookActionValidateOrder($params): void
    {
        $order = $params['order'];
        $currency = $params['currency'];

        $clickId = isset($_COOKIE['monevibe_click_id']) ? $_COOKIE['monevibe_click_id'] : null;

        if ($clickId) {
            $shopId = Configuration::get('MONEVIBE_SHOP_ID');

            $data = [
                'shop_id' => $shopId,
                'external_id' => $order->id,
                'click_id' => $clickId,
                'total_amount' => $order->total_paid,
                'currency' => $currency->iso_code,
            ];

            $this->sendToBackend($data, 'create-order');
        }
    }

    public function hookActionOrderStatusUpdate($params)
    {
        $newOrderStatus = $params['newOrderStatus'];
        $orderId = (int) $params['id_order'];

        $order = new Order($orderId);
        $currency = new Currency($order->id_currency);

        $event = null;
        $isPaid = (bool) $newOrderStatus->paid;
        $statusId = (int) $newOrderStatus->id;

        if ($isPaid === true) {
            $event = 'paid';
        } elseif ($statusId === 6) {
            $event = 'cancel';
        }

        if ($event) {
            $shopId = Configuration::get('MONEVIBE_SHOP_ID');

            $this->sendToBackend([
                'shop_id' => $shopId,
                'external_id' => $orderId,
                'event' => $event,
                'total_amount' => $order->total_paid,
                'currency' => $currency->iso_code,
            ], 'webhook');
        }
    }

    private function sendToBackend($data, string $type): void
    {
        $urlPostfix = $type === 'create-order' ? 'order' : 'webhook';
        $url = 'https://addons.monevibe.com/api/prestashop/' . $urlPostfix;

        $payload = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);

        curl_exec($ch);
        curl_close($ch);
    }
}
