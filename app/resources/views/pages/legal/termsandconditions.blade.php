@extends('layouts.master')

@section('page-header', 'Terms and Conditions')

@section('content')
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"> Terms and Conditions</h3>
    </div>
    <div class="box-body">

      <p>Last updated: October 03, 2019</p>

      <p>Please read these Terms and Conditions ("Terms", "Terms and Conditions") carefully before using the {{ $website_url }} website (the "Service") operated by {{ $website_name }} ("us", "we", or "our").</p>

      <p>Your access to and use of the Service is conditioned on your acceptance of and compliance with these Terms. These Terms apply to all visitors, users and others who access or use the Service.</p>

      <p>By accessing or using the Service you agree to be bound by these Terms. If you disagree with any part of the terms then you may not access the Service. The Terms and Conditions agreement  for {{ $website_name }} has been created with the help of <a href="https://www.termsfeed.com/">TermsFeed</a>.</p>

      <h3>Source Code</h3>

      <p>The ODArena source code is freely available and subject to GNU Affero General Public License v3.0, which can be found at the following URL: https://www.gnu.org/licenses/agpl-3.0.en.html</p>

      <p>Nothing in these Terms and Conditions shall be construed to impose any restriction on the aforementioned GNU Affero General Public License v3.0 to the source code.</p>

      <h3>Accounts</h3>

      <p>You must be at least 18 years or of legal age to register an account and play the game.</p>

      <p>When you create an account with us, you must provide us information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the Terms, which may result in immediate termination of your account on our Service.</p>

      <p>You are responsible for safeguarding the password that you use to access the Service and for any activities or actions under your password, whether your password is with our Service or a third-party service.</p>

      <p>You agree not to disclose your password to any third party. If you have any reason to believe your password is not safe, you are required to immediately change it.</p>

      <p>Your username must not be offensive or misleading. This includes profanity, slurs, and names which may cause confusion.</p>

      <p>Accounts are personal and must. One person per account: only one person is permitted to make use of an account.</p>

      <p>If your account is suspended or banned, you are not permitted to open a new account or use another account.</p>

      <p>If you have forgot your password, use the password reset function. If you do not have access to your email account, contact an administrator on Discord or via {{ $contact_email }}</p>

      <h3>Game Rules</h3>

      <p>You are only allowed to have one dominion per round per account.</p>

      <p>Until further notice, it is permitted to have multiple accounts and, as such, multiple dominions per round. However, you must not use this to give yourself or anyone specific an unfair advantage or otherwise be abusive.</p>

      <p>Your dominion name or ruler name must not be offensive or misleading.</p>

      <p>You may not use any tools, software, application, scripts, or otherwise to automate any activities in the game.</p>

      <p>You must not in any way cooperate with another realm or dominions in other realms, such as Non-Aggression Pacts (“NAP”) or alliances.</p>

      <p>You must not in any way intentionally take any action which directly or indirectly benefits another realm or a dominion in another realm.</p>

      <p>You must at all times refrain from using excessive profanity or abusive, offensive language. Banter and smack talk are allowed; just keep it mostly civil.</p>

      <p>You must not exploit any bugs in the game. A bug is a feature, mechanic, logic, or other part of the game which is not working as intended or not working at all. You are expected to have a reasonable understanding of how the game works and we will not accept ignorance as an excuse for exploiting a bug. If you find a bug, please report it immediately in Discord or by contacting an administrator.</p>

      <p>If you are negatively impacted by a bug and cannot be reasonably expected to have known about the bug, you may be compensated appropriately, at our sole discretion.</p>

      <p>If you have been negatively impacted by other players breaching the rules and this can be substantiated during the investigation, you may receive an adequate and proportionate compensation, at our sole discretion.</p>

      <h3>Links To Other Web Sites</h3>

      <p>Our Service may contain links to third-party web sites or services that are not owned or controlled by {{ $website_name }}.</p>

      <p>{{ $website_name }} has no control over, and assumes no responsibility for, the content, privacy policies, or practices of any third party web sites or services. You further acknowledge and agree that {{ $website_name }} shall not be responsible or liable, directly or indirectly, for any damage or loss caused or alleged to be caused by or in connection with use of or reliance on any such content, goods or services available on or through any such web sites or services.</p>

      <p>We strongly advise you to read the terms and conditions and privacy policies of any third-party web sites or services that you visit.</p>

      <h3>Termination</h3>

      <p>We may terminate or suspend access to our Service immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>

      <p>All provisions of the Terms which by their nature should survive termination shall survive termination, including, without limitation, ownership provisions, warranty disclaimers, indemnity and limitations of liability.</p>

      <p>We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>

      <p>Upon termination, your right to use the Service will immediately cease. If you wish to terminate your account, you may simply discontinue using the Service.</p>

      <p>All provisions of the Terms which by their nature should survive termination shall survive termination, including, without limitation, ownership provisions, warranty disclaimers, indemnity and limitations of liability.</p>

      <h3>Governing Law</h3>

      <p>These Terms shall be governed and construed in accordance with the laws of {{ $company_jurisdiction }}, without regard to its conflict of law provisions.</p>

      <p>Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights. If any provision of these Terms is held to be invalid or unenforceable by a court, the remaining provisions of these Terms will remain in effect. These Terms constitute the entire agreement between us regarding our Service, and supersede and replace any prior agreements we might have between us regarding the Service.</p>

      <h3>Changes</h3>

      <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material we will try to provide at least 30 days notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.</p>

      <p>By continuing to access or use our Service after those revisions become effective, you agree to be bound by the revised terms. If you do not agree to the new terms, please stop using the Service.</p>

      <h3>Privac Policy</h3>

      <p>These Terms also include the {{ $website_name }} Privacy Policy, available from this link: {{ route('legal.privacypolicy') }}

      <h3>Contact Us</h3>

      <p>If you have any questions about these Terms, please contact us via email by {{ $contact_email }}.</p>

      <h3>General</h3>

      <p>If you do not agree with these Terms, you may not create an account.</p>

      <p>If you have already created an account and do not agree with the Terms, you must immediately cease taking part of {{ $website_url }}.</p>

      <p>Insofar as applicable, these Terms also apply to other chat rooms, discussion boards, and other websites directly associated with {{ $website_name }}.</p>

    </div>
@endsection
