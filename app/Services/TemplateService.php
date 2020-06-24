<?php
namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Domain;
use App\Models\Setting;

class TemplateService
{
    /**
     * Get company address
     * @param string $settingValue
     * @param null|Domain $domain
     * @param bool $line_break
     * @param $show_company_info
     * @return string
     */
    public static function getCompanyAddress(?string $settingValue, ?Domain $domain = null, bool $line_break = false, $show_company_info = 0): string
    {
        $address = !empty($domain->address) ? $domain->address : $settingValue;
        $address = $line_break ? str_replace("\n", '<br>', $address) : str_replace("\n", ' - ', $address);
        // if privacy = 0 address is empty
        if ($show_company_info == 0) {
            $address = '';
        }
        return $address ?? '';
    }

    /**
     * Get company email
     * @param string $settingValue
     * @param mixed $domain
     * @return string
     */
    public static function getCompanyEmail(?string $settingValue, ?Domain $domain = null): string
    {
        $email = !empty($domain->email) ? $domain->email : $settingValue;
        return $email ?? '';
    }

    /**
     * Get company number (phone US/CA)
     * @param string $settingValue
     * @param mixed $domain
     * @param $show_company_info
     * @return string
     */
    public static function getCompanyNumber(?string $settingValue, ?Domain $domain = null, $show_company_info = 0): string
    {
        $phone = !empty($domain->phone_us) ? $domain->phone_us : $settingValue;
        if ($show_company_info == 0) {
            $phone = '';
        }
        return $phone ?? '';
    }

    /**
     * Get company phone
     * @param string $settingValue
     * @param mixed $domain
     * @param $show_company_info
     * @return string
     */
    public static function getCompanyPhone(?string $settingValue, ?Domain $domain = null, $show_company_info = 0): string
    {
        $phone = !empty($domain->phone) ? $domain->phone : $settingValue;
        if ($show_company_info == 0) {
            $phone = '';
        }
        return $phone ?? '';
    }

    /**
     * Get company name
     * @param string|null $settingValue
     * @param Domain|null $domain
     * @param $show_company_info
     * @return string
     */
    public static function getCompanyName(?string $settingValue, ?Domain $domain = null, $show_company_info = 0) : string
    {
        $name = !empty($domain->company_name) ? $domain->company_name : $settingValue;
        if ($show_company_info == 0) {
            $name = $domain->getDisplayedName();
        }
        return $name ?? '';
    }

    /**
     * Get company data
     * @param mixed $domain
     * @return array
     */
    public static function getCompanyData(?Domain $domain = null): array
    {
        $setting = Setting::getValue([
            'support_address',
            'support_email',
            'support_phone',
            'support_phone_us',
            'company_name',
            'show_company_info'
        ]);

        $data = [
            'address' => TemplateService::getCompanyAddress($setting['support_address'], $domain, true, $setting['show_company_info'] ?? 0),
            'phone' => TemplateService::getCompanyPhone($setting['support_phone'], $domain, $setting['show_company_info'] ?? 0),
            'number' => TemplateService::getCompanyNumber($setting['support_phone_us'], $domain, $setting['show_company_info'] ?? 0),
            'email' => TemplateService::getCompanyEmail($setting['support_email'], $domain),
            'company' => TemplateService::getCompanyName($setting['company_name'], $domain, $setting['show_company_info'] ?? 0),
            'show_company_info' => $setting['show_company_info'] ?? 0,
        ];
        return $data;
    }

    /**
     * Get deals data for template
     * @param $product
     * @param $request
     * @return array
     */
    public static function getDealsData($product, $request): array
    {
        $is_checkout_page = Route::is('checkout') || Route::is('checkout_price_set');
        $is_vrtl_page = Route::is('checkout_vrtl') || Route::is('checkout_vrtl_price_set');

        $deals = [];
        $deals_shortlist = false;
        $deals_to_display = [1, 3, 5];
        $deals_main_quantities = [1 => 1, 2 => 2, 3 => 2, 4 => 4, 5 => 3];
        $deals_free_quantities = [1 => 0, 2 => 0, 3 => 1, 4 => 0, 5 => 2];
        $deals_sellout = array_map('intval', explode(',', $request->get('sellout') ?? ''));
        $deal_bestseller_index = -1;
        $deal_popular_index = -1;

        if ($is_checkout_page) {
            if ($request->get('tpl') === 'fmc5x') {
                $deals_to_display = [1, 2, 3, 4, 5];
                $deals_shortlist = true;
            }
        }

        if ($is_vrtl_page) {
            $deals_to_display = [1];
        }

        foreach ($product->prices as $value => $deal) {
            $value = intval($value);
            if (in_array($value, $deals_to_display)) {
                $deal['quantity'] = $value;
                $deal['sellout'] = in_array($value, $deals_sellout);
                $deals[] = $deal;
            }
        }

        foreach ($deals as $index => $deal) {
            if ($deal['is_bestseller']) {
                $deal_bestseller_index = $index;
            }
            if ($deal['is_popular']) {
                $deal_popular_index = $index;
            }
        }

        $deal_promo = $deal_bestseller_index !== -1
            ? $deals[$deal_bestseller_index]
            : ($deal_popular_index !== -1
                ? $deals[$deal_popular_index]
                : $deals[0]);

        usort($deals, function($a, $b) use ($deals_shortlist) {
            if ($deals_shortlist) {
                if ($a['is_bestseller'] || ($a['is_popular'] && !$b['is_bestseller'])) return -1;
                if ($b['is_bestseller'] || $b['is_popular']) return 1;
            }
            if ($a['quantity'] > $b['quantity']) return 1;
            if ($a['quantity'] < $b['quantity']) return -1;
            return 0;
        });

        return [
            'deals' => $deals,
            'deal_promo' => $deal_promo,
            'deals_main_quantities' => $deals_main_quantities,
            'deals_free_quantities' => $deals_free_quantities
        ];
    }

    /**
     * Get checkout page template name from get request
     * @param $request
     * @return string
     */
    public static function getCheckoutPageTemplate($request): string
    {
        $viewTemplate = 'checkout';

        if ($request->get('tpl') == 'vmp41') {
            $viewTemplate = 'vmp41';
        }
        if ($request->get('tpl') == 'fmc5x') {
            $viewTemplate = 'new.pages.checkout.templates.fmc5';
        }
        if ($request->get('tpl') == 'amc8') {
            $viewTemplate = 'new.pages.checkout.templates.amc8';
        }
        if ($request->get('tpl') == 'amc81') {
            $viewTemplate = 'new.pages.checkout.templates.amc81';
        }
        return $viewTemplate;
    }

    /**
     * Get health page template by request
     * @param $request
     * @return string
     */
    public static function getHealthPageTemplate($request): string
    {
        $viewTemplate = 'new.pages.checkout.templates.hp01';

        if ($request->get('tpl') == 'thor-power') {
            $viewTemplate = 'new.pages.checkout.templates.thor-power';
        }
        if ($request->get('tpl') == 'hydrolinx') {
            $viewTemplate = 'new.pages.checkout.templates.hydrolinx';
        }
        if ($request->get('tpl') == 'slimeazy') {
            $viewTemplate = 'new.pages.checkout.templates.slimeazy';
        }
        return $viewTemplate;
    }

    /**
     * Get virtual page template by request
     * @param $request
     * @return string
     */
    public static function getVirtualPageTemplate($request): string
    {
        $viewTemplate = 'new.pages.checkout.templates.vc1';

        if ($request->get('tpl') == 'vc1') {
            $viewTemplate = 'new.pages.checkout.templates.vc1';
        }
        if ($request->get('tpl') == 'vc2') {
            $viewTemplate = 'new.pages.checkout.templates.vc2';
        }
        return $viewTemplate;
    }

}
