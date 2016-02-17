<?php

class AdminImporterConfigurationController extends ModuleAdminController {

	public $bootstrap = true;

	public function __construct()
	{
		$this->bootstrap = true;
		parent::__construct();

		/*We are using HelperOptions right now for the configuration page of the module, 
		but the Helper seems to have a problem with the URL, he doesn't like the "&" and "&lang" character, 
		so i've check file, and i found that's the HelperOptions who retrieve the value of the URL and 
		he ask the purify method from the Tools class (Tools::purifyHTML($value)) to 'purify' the value. 
		After the execution of the method the value is broken, i show you:. 
		BEFORE: http://graphics.edc-internet.nl/b2b_feed.php?key=[KEY]&sort=xml&type=xml&lang=[LANG]&version=2015
		AFTER http://graphics.edc-internet.nl/b2b_feed.php?key=[KEY]&amp;sort=xml&amp;type=xml〈=[LANG]&amp;version=2015

		
		:TODO: SO i think the best way to handle this, is to use FormHelper instead HelperOptions 
		 */
		$this->fields_options = array(
			'general' => array(
				'title' => $this->l('Configuration générale'),
				'fields' => array(
					'IMPORTER_API_KEY' => array(
						'title' => $this->l('Votre clé API'),
						'desc' => $this->l("Votre clé permettant de vous identifier à l'API."),
						'required' => true,
						'type' => 'text'
					),
					'IMPORTER_URL_FULL_FEED' => array(
						'title' => $this->l('L\'url du full feed XML.'),
						'desc' => $this->l("C'est assez clair dans le titre, si tu sais c'est que tu ne devrais pas te trouver ici. Alors file!"),
						'required' => true,
						'type' => 'text'
					),
					'IMPORTER_URL_NEW_PRODUCTS' => array(
						'title' => $this->l("L'url du feed XML des nouveaux produits."),
						'desc' => $this->l("C'est assez clair dans le titre, si tu sais c'est que tu ne devrais pas te trouver ici. Alors file!"),
						'required' => false,
						'type' => 'text'
					),
					'IMPORTER_URL_STOCK' => array(
						'title' => $this->l("L'url du feed XML des stocks."),
						'desc' => $this->l("C'est assez clair dans le titre, si tu sais c'est que tu ne devrais pas te trouver ici. Alors file!"),
						'required' => false,
						'type' => 'text'
					),
					'IMPORTER_DISCONTINUED' => array(
						'title' => $this->l("L'url du feed XML des produits abandonner."),
						'desc' => $this->l("C'est assez clair dans le titre, si tu sais c'est que tu ne devrais pas te trouver ici. Alors file!"),
						'required' => false,
						'type' => 'text'
					)
				),
				'submit' => array(
					'title' => $this->l('Save')
				)
			)
		);
	}

	// public function renderForm()azdzadzadza
	// {

		
	// 	$this->fields_form = array(
	// 		'legend' => array(
	// 			'title' => $this->l('Configuration générale'),
	// 			'icon' => 'icon-cogs'
	// 		),
	// 		'input' => array(
	// 			array(
	// 				'type' => 'text',
	// 				'label' => $this->l('Votre clé API'),
	// 				'name' => 'importer_api_key',
	// 				'required' => true,
	// 				'desc' => $this->l("Votre clé prermetant de vous identifier via l'API.")
	// 			),
	// 			array(
	// 				'type' => 'text',
	// 				'label' => $this->l("L'url du full feed XML."),
	// 				'name' => 'importer_url_full_feed',
	// 				'required' => true,
	// 				'desc' => $this->l("C'est assez clair dans le titre, si tu ne sais pas c'est que tu ne devrais pas te trouver ici. File!")
	// 			),
	// 			array(
	// 				'type' => 'text',
	// 				'label' => $this->l("L'url du feed XML des nouveaux produits."),
	// 				'name' => 'importer_url_new_products',
	// 				'required' => false
	// 			),
	// 			array(
	// 				'type' => 'text',
	// 				'label' => $this->l("L'url du feed XML des stocks"),
	// 				'name' => 'importer_url_stock',
	// 				'required' => false,
	// 			),
	// 			array(
	// 				'type' => 'text',
	// 				'label' => $this->l("L'url du feed XML de produits abandonner"),
	// 				'name' => 'importer_url_discontinued',
	// 				'required' => false,
	// 			)
	// 		),
	// 		'submit' => array(
	// 			'title' => $this->l('Save'),
	// 			'class' => 'btn btn-default pull-right'
	// 		)
	// 	);
		
	// 	$helperForm = new HelperForm();
	// 	$helperForm->token = Tools::getValue('token');
	// 	$helperForm->default_form_language = $this->context->language->id;

	// 	$helperForm->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
	// 	$helperForm->toolbar_scroll = false;
	// 	$helperForm->toolbar_btn = array();
	// }
}