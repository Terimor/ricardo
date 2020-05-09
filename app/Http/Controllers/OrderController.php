<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\{MediaAccess, OdinOrder, Domain, OdinProduct};
use App\Services\{I18nService, OrderService, ProductService, MediaService, VimeoService};

/* use com\checkout;
  use com\checkout\ApiServices; */

class OrderController extends Controller
{

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * Create a new controller instance.
     * @param OrderService $orderService
     * @return void
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     *
     */
    public function sendPostbacks()
    {
        $orders = OdinOrder::limit(1000)->orderBy('_id', 'desc')->get();
        $reduced = 0; $first_reduced = 0;
        foreach($orders as $order) {
            if (!empty($order->affiliate) && $order->total_paid_usd > 0 && $order->is_reduced === null) {
                $l = OrderService::getReducedData((string)$order->_id, $order->affiliate);
                if ($l->is_reduced) {
                    echo $order->number.'<br>';
                    $reduced++;
                }
            }
        }
        echo "<br><br>COUNT: $reduced<br>";
    }

    /**
     *
     */
    public function sendPostbacksByList()
    {
        $order = null;
        $orderNumbersFired = [];
        $orderNumbersNotFired = [];

        $list = "O2001GBJFGMAS
        O2001GBGZUPMA
        O2001GBTOXWRZ
        O2001GBTQXEUB
        O2001GBV8RALG
        O2001GBIAOX0I
        O2001GBG8IGZQ
        O2001GBCGTLDZ
        O2001GB0XTPNR
        O2001GBXWEB6M
        O2001USQ02ZRT
        O2001USXNCMOU
        O2001GBQSYBVH
        O2001USOT0JUF
        O2001GBY7GHZ2
        O2001GB50QNV6
        O2001GBIW5XIZ
        O2001GB6OL9D4
        O2001USTPFCTU
        O2001USJGXPUN
        O2001USEOPJMJ
        O2001GBONPBVB
        O2001USQ1QFMD
        O2001USUQIREF
        O2001USKUPLD3
        O2001USUTIBXH
        O2001USFOYNBW
        O2001GBMWMXNY
        O2001CAPH8TL8
        O2001USLSR0FR
        O2001CACBLEUS
        O2001USFC1QF2
        O2001USLSR0FR
        O2001USVJBF4X
        O2001USLSR0FR
        O2001US9BSYH6
        O2001GBAP04SE
        O2001CAJSDRTI
        O2001USIRTVHC
        O2001USDSGF3W
        O2001USREXDAY
        O2001USOIU0BE
        O2001US7CANG9
        O2001USALQ4TI
        O2001CANJ4HXI
        O2001CAZK0HBE
        O2001USFCKVMC
        O2001GBZLDMJT
        O2001US0JNVEW
        O2001USWCRLPB
        O2001USGOEK1J
        O2001USMQYOLL
        O2001USDGI3GT
        O2001USFNP4QC
        O2001USDZ6UVN
        O2001USZ4X3MI
        O2001USGL6IFE
        O2001GBGGBIVA
        O2001GBODRXBC
        O2001USV36K1D
        O2001USDU5VSM
        O2001USUCFXFU
        O2001USNJAUE9
        O2001US54GSHM
        O2001USH75E1Y
        O2001USHO6ZJI
        O2001CAXRXSTB
        O2001GBLP1QSC
        O2001USXUGQ0I
        O2001USYNORE6
        O2001CA6NNVTU
        O2001USXDY4DY
        O2001USSDLYY2
        O2001USWRMLCL
        O2001USLGVPJV
        O2001USDXP2DS
        O2001USZ1QWPI
        O2001USDUFXHK
        O2001GBLYC5FW
        O2001USOEJORI
        O2001US9TCEGV
        O2001USVXALQT
        O2001USUG8O8T
        O2001USN64AWA
        O2001USTOJBJV
        O2001USZYONCI
        O2001USIW5G63
        O2001USGVEAOP
        O2001USZ8G7KW
        O2001USCVCAU5
        O2001USJJWTXD
        O2001GBPJENZ7
        O2001USHSKMW7
        O2001USLYVUGB
        O2001US25D1QH
        O2001USLDNRRY
        O2001GBAF0LJK
        O2001GBGBEFPT
        O2001USZDEW2X
        O2001USUG7TIO
        O2001USZDEW2X
        O2001USLC1UZR
        O2001US5RZNIT
        O2001USIGNZMB
        O2001USCK5WV6
        O2001USFFHY4V
        O2001USWM8LJA
        O2001USIYZZDI
        O2001USDGGKM3
        O2001USOW6O1P
        O2001GBBRLO78
        O2001GBMTS8PF
        O2001GBMF0N6N
        O2001CA9HLHPE
        O2001USIYLOSA
        O2001GB6FGAUH
        O2001USSZUQZK
        O2001US2WXJOZ
        O2001CAZ0KMOH
        O2001USTWIINH
        O2001US6G8VN2
        O2001CAHYMMGZ
        O2001USVSUTFB
        O2001USXYGSLZ
        O2001USOWI1EX
        O2001CAAU6MDM
        O2001USZDRPUT
        O2001USKHLEGP
        O2001USLWFEP0
        O2001CAORTJQP
        O2001CATM4V29
        O2001USGWZBFB
        O2001USWKBXMP
        O2001USEQ73Y4
        O2001USFPNVXJ
        O2001USRCGE2A
        O2001GBUVYMAS
        O2001GBFE37YR
        O2001USCYZNIW
        O2001US7FGVF7
        O2001CAMKL8NP
        O2001CARKNU1H
        O2001GB1IIQ1W
        O2001GB684FEO
        O2001GBDVQDCW
        O2001GBM7JPFD
        O2001GB4BU9A5
        O2001GBR3BBBD
        O2001GBCWESDE
        O2001GBY5KER2
        O2001GBNNLXDW
        O2001GBK6HRWA
        O2001GBGBIBEL
        O2001GBJXI7ZU
        O2001GBYUEHQZ";

        $explodeList = explode("\n", $list);
        $c = 0;
        foreach($explodeList as $exp) {
            $number = trim($exp);
            if ($number) {
                $order = OdinOrder::getByNumber($number, false);
            }
            if (!empty($order->affiliate) && $order->is_reduced === null) {
                $l = OrderService::getReducedData((string)$order->_id, $order->affiliate);
                if ($l->is_reduced) {
                    echo $order->number . ' FIRED <br>';
                    $orderNumbersFired[] = $order->number;
                } else {
                    echo $order->number . ' NOT FIRED <br>';
                    $orderNumbersNotFired[] = $order->number;
                }
            } else {
                echo 'ignored ' . $number. '<br>';
            }
            $c++;
        }
        echo 'TOTAL: '.$c;
        logger()->info(str_repeat('*FIRE*', 5), ['orders' => $orderNumbersFired]);
        logger()->info(str_repeat('*NOT-FIRE*', 5), ['orders' => $orderNumbersNotFired]);
    }

    /**
     *
     * @param type $orderId
     */
    public function orderAmountTotal($orderId)
    {
        return $this->orderService->calculateOrderAmountTotal($orderId);
    }

    /**
     * Get order media
     * Check order and product
     * @param string $orderNumber
     * @param string $fileId
     * @param string $filename
     * @param ProductService $productService
     * @param MediaService $mediaService
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\ProductNotFoundException
     */
    public function getOrderMedia(string $orderNumber, string $fileId, string $filename, ProductService $productService, MediaService $mediaService) {
        $select = ['number', 'type', 'products'];
        $order = OdinOrder::getByNumber($orderNumber, false, $select);

        if (!$order || $order->type != OdinOrder::TYPE_VIRTUAL) {
            abort(404, 'Sorry, we couldn\'t find your order');
        }
        $sku = $order->getMainSku();
        $select = ['type', 'free_file_ids', 'sale_file_ids', 'sale_video_ids', 'logo_image_id'];

        $product = OdinProduct::getBySku($sku, false, $select);
        if (!$product) {
            abort(404, 'Sorry, we couldn\'t find your order');
        }

        $file = $productService->getMediaByProduct($product, $fileId);

        if ($file) {
            if ($file['type'] == MediaAccess::TYPE_FILE) {
                $fileData = $mediaService->getS3FileContent($file['url'], $filename);
                MediaAccess::addAccess($file, $order->number);
                return response()->download($fileData['tempFilepath'], $fileData['filename'], $fileData['headers'], 'inline')->deleteFileAfterSend();
            } else {
                /*$vimeoUrl = $mediaService->getVimeoDirectUrl($file['url']);
                if ($vimeoUrl) {
                    $fileData = $mediaService->getFileContent($vimeoUrl, $filename);
                } else {
                    abort(404, 'Video not found');
                }*/
                return Redirect::to($file['url']);
            }
        }
        abort(404, 'File not found');
    }


}
