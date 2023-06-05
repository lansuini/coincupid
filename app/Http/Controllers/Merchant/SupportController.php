<?php

namespace App\Http\Controllers\Merchant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use DateTime;

class SupportController extends MerchantController
{
    public function apiDocumentView(Request $request)
    {
        return view('Merchant/Support/apiDocumentView', [
            'pageTitle' => $this->role->getCurrentPageTitle($request),
            'request' => $request
        ]);
    }

    public function apiDocumentList(Request $request)
    {

        $rows = [
            [
                'file_name' => __('ts.Single Money API Document'),
                'updated' => '',
                'download' => '/docs/IG-SingleMoney-API-Document.docx',
            ],
            [
                'file_name' => __('ts.Tranfer Money API Document'),
                'updated' => '',
                'download' => '/docs/IG-TransferMoney-API-Document.docx',
            ],
        ];

        foreach ($rows as $k => $v) {
            $filename = base_path('public' . $v['download']);
            $lastmodified = File::lastModified($filename);
            $lastmodified = DateTime::createFromFormat("U", $lastmodified);
            $v['updated'] = $lastmodified->format('Y-m-d H:i:s');
            $rows[$k] = $v;
        }

        return [
            'result' => [],
            'rows' => $rows,
            'success' => 1,
            'total' => count($rows),
        ];
    }
}
