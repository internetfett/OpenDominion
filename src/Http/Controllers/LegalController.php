<?php

namespace OpenDominion\Http\Controllers;

class LegalController extends AbstractController
{

    public function getIndex()
    {
        return view('pages.legal.index', [
            'company_name' => 'OD Arena',
            'company_address' => 'Cyprus',
        ]);
    }

    public function getTermsAndConditions()
    {
      return view('pages.legal.termsandconditions', [
        'company_name' => 'OD Arena',
        'website_name' => 'OD Arena',
        'website_url' => 'https://odarena.com/',
        'contact_email' => 'info@odarena.com',
        'company_jurisdiction' => 'the Republic of Cyprus',
      ]);
    }


    public function getPrivacyPolicy()
    {
      return view('pages.legal.privacypolicy', [
          'company_name' => 'OD Arena',
          'website_name' => 'OD Arena',
          'website_url' => 'https://odarena.com/',
          'contact_email' => 'info@odarena.com',
      ]);
    }
}
