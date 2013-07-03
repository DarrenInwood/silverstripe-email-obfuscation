<?php

class GeoEmailPage extends Page {

    static $db = array(
        'EmailName' => 'Varchar',
        'EmailFrom' => 'Varchar',
        'EmailSubject' => 'Varchar',
        'EmailBody' => 'HTMLText',
        'EmailSuccessMessage' => 'HTMLText',
        'SendToAdmin' => 'Boolean',
        'AdminName' => 'Varchar',
        'AdminFrom' => 'Varchar',
        'AdminSubject' => 'Varchar',
        'AdminBody' => 'HTMLText',        
    );

    static $icon = '/geo/images/EmailIcon';

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        // Email details tab
        $fields->addFieldToTab(
            'Root.Content.EmailDetails',
            new LiteralField('email-header-0', '<h2>Sender details</h2><br/>')  
        );
        $fields->addFieldToTab(
            'Root.Content.EmailDetails',
            new TextField('EmailName', 'Sender email name')
        );
        $fields->addFieldToTab(
            'Root.Content.EmailDetails',
            new TextField('EmailFrom', 'Sender email address')
        );


        $fields->addFieldToTab(
            'Root.Content.EmailDetails',
            new LiteralField('email-header-1', '<h2>Email to site user</h2><br/>')  
        );
        $fields->addFieldToTab(
            'Root.Content.EmailDetails',
            new TextField('EmailSubject', 'Subject of email')
        );
        $fields->addFieldToTab(
            'Root.Content.EmailDetails',
            new HtmlEditorField('EmailBody', 'Email body', 20)
        );
        $fields->addFieldToTab(
            'Root.Content.EmailDetails',
            new HtmlEditorField('EmailSuccessMessage', 'Success message (replaces \'Content\' upon successful submit)', 20)
        );


        $fields->addFieldToTab(
            'Root.Content.EmailDetails',
            new LiteralField('email-header-2', '<br/><h2>Email to administrator</h2><br/>')  
        );
        $fields->addFieldToTab(
            'Root.Content.EmailDetails',
            new CheckboxField('SendToAdmin', 'Send a copy to an administrator')
        );
        $fields->addFieldToTab(
            'Root.Content.EmailDetails',
            new TextField('AdminName', 'Admin name')
        );
        $fields->addFieldToTab(
            'Root.Content.EmailDetails',
            new TextField('AdminFrom', 'Admin email address')
        );
        $fields->addFieldToTab(
            'Root.Content.EmailDetails',
            new TextField('AdminSubject', 'Subject of email')
        );
        $fields->addFieldToTab(
            'Root.Content.EmailDetails',
            new HtmlEditorField('AdminBody', 'Email body', 20)
        );
    
        return $fields;
    }

    public function requireDefaultRecords() {
        parent::requireDefaultRecords();
		$emailPage = DataObject::get_one('GeoEmailPage');
		if(!($emailPage && $emailPage->exists())) {
			$emailPage = new GeoEmailPage();
			$emailPage->Title = 'Send an email';
			$emailPage->URLSegment = 'email';
			$emailPage->Status = 'New page';
			$emailPage->ShowInMenus = false;
			$emailPage->write();
			$emailPage->publish('Stage', 'Live');

			DB::alteration_message('Graceful Email Obfuscation noscript page created', 'created');
		}
    }

}

class GeoEmailPage_Controller extends Page_Controller {

    /**
     * Outputs the form into the template.
     */
    public function GeoEmailForm() {
        // Set default values
        if ( ! array_key_exists('ToEmail', $_REQUEST) && ! array_key_exists('e', $_REQUEST) ) {
            Director::redirectBack();
            return;
        }
        $encoded = array_key_exists('e', $_REQUEST) ? $_REQUEST['e'] : '';
        $encoded = array_key_exists('ToEmail', $_REQUEST) ? $_REQUEST['ToEmail'] : $encoded;
        $geo = new Geo();
        $decoded = $geo->decodeOutput($encoded);
        $obfuscated = $geo->prepareOutput($decoded);

        $disabled = new TextField('EmailAddress', 'Email to', $obfuscated);
        $disabled->setDisabled(true);
        $fields = new Fieldset(
            $disabled,
            new HiddenField('ToEmail', 'ToEmail', $encoded),
            new TextField('FromName', 'Your name'),
            new TextField('FromEmail', 'Your email'),
            new TextAreaField('FromMessage', 'Message', 12)
        );
        $actions = new FieldSet(
            new FormAction('doSubmitEmail', 'Send email')
        );
        $emailForm = new Form(
            $this,
            'GeoEmailForm',
            $fields,
            $actions,
            new GeoEmailForm_Validator(
                'FromEmail', 'FromMessage'
            )
        );

        return $emailForm;
    }

    public function doSubmitEmail($data, $form) {
        // Get contacted email address' details
        $geo = new Geo();
        $privateEmail = $geo->decodeOutput($data['ToEmail']);

        // Get contacting user's details
        $userEmail = sprintf(
            '"%s" <%s>',
            trim($data['FromName']) ? trim($data['FromName']) : trim($data['FromEmail']),
            trim($data['FromEmail'])
        );

        // Get Sender email details
        $from = sprintf(
            '"%s" <%s>',
            trim($this->EmailName) ? trim($this->EmailName) : trim($this->EmailFrom),
            trim($this->EmailFrom)
        );

        // Send email to site user
        $subject = trim($this->EmailSubject);
        $body = trim($this->EmailBody);
        $body .= sprintf('<br/>
<strong>Email to:</strong> %s<br/>
<strong>Email from:</strong> %s<br/>
<strong>Message:</strong><br/>
%s'."\n",
            $privateEmail,
            $userEmail,
            nl2br(strip_tags($data['FromMessage']))
        );
        $email = new Email($from, $userEmail, $subject, $body);
        $email->send();

        if ( $this->SendToAdmin ) {
            // Send email to admin user
            $adminEmail = sprintf(
                '"%s" <%s>',
                trim($this->AdminName) ? trim($this->AdminName) : trim($this->AdminFrom),
                trim($this->AdminFrom)
            );
            $subject = trim($this->AdminSubject);
            $body = trim($this->AdminBody);
            $body .= sprintf('<br/>
<strong>Email to:</strong> %s<br/>
<strong>Email from:</strong> %s<br/>
<strong>Message:</strong><br/>
%s'."\n",
                $privateEmail,
                $userEmail,
                nl2br(strip_tags($data['FromMessage']))
            );
            $email = new Email($from, $adminEmail, $subject, $body);
            $email->send();
        }

        return $this->customise(array(
            'Content' => $this->EmailSuccessMessage
        ));
    }

}

/**
 * Custom validator for this form
 * @link http://ssorg.bigbird.silverstripe.com/data-model-questions/show/257241
 */
class GeoEmailForm_Validator extends RequiredFields {

    public function __construct() {
        $required = func_get_args();
        if(isset($required[0]) && is_array($required[0])) {
            $required = $required[0];
        }
        parent::__construct($required);
    }

    public function php($data) {
        $valid = parent::php($data); 

        // Check email address is valid
        if ( ! Email::validEmailAddress($data['FromEmail']) ) {
            $this->validationError('FromEmail', 'Please enter a valid email address.', 'required');
            $valid = false;
        }

        return $valid;
    }
    


}


