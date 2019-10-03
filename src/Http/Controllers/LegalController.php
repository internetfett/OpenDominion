<?php

namespace OpenDominion\Http\Controllers;

class LegalController extends AbstractController
{

  $company_name = 'OD Arena';
  $website_url = 'https://odarena.com/'
  $website_name = 'OD Arena';
  $company_address = 'Cyprus';
  $company_jurisdiction = 'the Republic of Cyprus';
  $contact_email = 'info@odarena.com';

    public function getIndex()
    {
        return view('pages.legal.index', [
            'company_name' => $company_name,
            'company_address' => $company_address,
        ]);
    }

    public function getTermsAndConditions()
    {
      return view('pages.legal.termsandconditions', [
        'company_name' => $company_name,
        'website_name' => $website_name,
        'website_url' => $website_url,
        'contact_email' => $contact_email,
      ]);
    }


    public function getPrivacyPolicy()
    {
      return view('pages.legal.privacypolicy', [
          'company_name' => $company_name,
          'website_name' => $website_name,
          'website_url' => $website_url,
      ]);
    }
}
