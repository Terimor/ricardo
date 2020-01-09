<?php
namespace App\Services;
use Illuminate\Http\Request;
use App\Models\Domain;
use App\Models\Setting;

class TemplateService
{
    /**
     * Get company address
     * @param string $settingValue
     * @param type $domain
     * @param type $line_break
     * @return type
     */
    public static function getCompanyAddress(?string $settingValue, ?Domain $domain = null, bool $line_break = false): string
    {
        $address = !empty($domain->address) ? $domain->address : $settingValue;
        $address = $line_break ? str_replace("\n", '<br>', $address) : str_replace("\n", ' - ', $address);        
        return $address;
    }
    
    /**
     * Get company email
     * @param string $settingValue
     * @param type $domain
     */
    public static function getCompanyEmail(?string $settingValue, ?Domain $domain = null): string 
    {
        $email = !empty($domain->email) ? $domain->email : $settingValue;          
        return $email;
    }
    
    /**
     * Get company number (phone US/CA)
     * @param string $settingValue
     * @param type $domain
     */
    public static function getCompanyNumber(?string $settingValue, ?Domain $domain = null): string 
    {
        $phone = !empty($domain->phone_us) ? $domain->phone_us : $settingValue;          
        return $phone;
    }

    /**
     * Get company phone
     * @param string $settingValue
     * @param type $domain
     */
    public static function getCompanyPhone(?string $settingValue, ?Domain $domain = null): string 
    {
        $phone = !empty($domain->phone) ? $domain->phone : $settingValue;          
        return $phone;
    }
    
    /**
     * Get company data
     * @return array
     */
    public static function getCompanyData(?Domain $domain = null): array
    {
        $setting = Setting::getValue([            
            'support_address',
            'support_email',
            'support_phone',
            'support_phone_us'
        ]);
        
        $data = [
            'address' => TemplateService::getCompanyAddress($setting['support_address'], $domain, true),
            'phone' => TemplateService::getCompanyPhone($setting['support_phone'], $domain),
            'number' => TemplateService::getCompanyNumber($setting['support_phone_us'], $domain),
            'email' => TemplateService::getCompanyEmail($setting['support_email'], $domain)
        ]; 
        return $data;
    }
    
}
