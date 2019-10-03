<?php

namespace OpenDominion\Http\Controllers;

class LegalController extends AbstractController
{
    public function getIndex()
    {
        return view('pages.legal.index', [
            'company_name' => 'ODArena',
            'company_address' => 'Cyprus',
        ]);
    }

    public function getTermsAndConditions()
    {
      return view('pages.legal.termsandconditions', [
          'company_name' => 'ODArena',
          'company_address' => 'Cyprus',
      ]);
    }


    public function getPrivacyPolicy()
    {
      return view('pages.legal.privacypolicy', [
          'company_name' => 'ODArena',
          'website_name' => 'ODArena',
          'website_url' => 'https://odarena.com/',
      ]);
    }
}
