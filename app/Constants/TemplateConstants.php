<?php
namespace App\Constants;
use Illuminate\Http\Request;
use App\Models\Domain;

class TemplateConstants
{
    const DEFAULT_ADDRESS = "MDE Commerce Ltd.\n29, Triq il-Kbira\nHal-Balzan\nBZN 1259\nMalta";
    const DEFAULT_NUMBER = "(888) 743-8103";
    const DEFAULT_PHONE = "+ 44 178 245 4716";
    const DEFAULT_EMAIL = "support@maxdeals.ltd";
    
    /**
     * Get company address
     * @param type $domain
     * @param type $line_break
     * @return type
     */
    public static function getCompanyAddress(?Domain $domain = null, bool $line_break = false): string
    {
        $address = !empty($domain->address) ? $domain->address : static::DEFAULT_ADDRESS;
        $address = $line_break ? str_replace("\n", '<br>', $address) : str_replace("\n", ' - ', $address);        
        return $address;
    }
    
    /**
     * Get company email
     * @param type $domain
     */
    public static function getCompanyEmail(?Domain $domain = null): string 
    {
        $email = !empty($domain->email) ? $domain->email : static::DEFAULT_EMAIL;          
        return $email;
    }
    
    /**
     * Get company number (phone US/CA)
     * @param type $domain
     */
    public static function getCompanyNumber(?Domain $domain = null): string 
    {
        $phone = !empty($domain->phone_us) ? $domain->phone_us : static::DEFAULT_NUMBER;          
        return $phone;
    }

    /**
     * Get company phone
     * @param type $domain
     */
    public static function getCompanyPhone(?Domain $domain = null): string 
    {
        $phone = !empty($domain->phone) ? $domain->phone : static::DEFAULT_PHONE;          
        return $phone;
    }    
}
