<?php

if (!defined('_PS_VERSION_'))
	exit;

//require(dirname(__FILE__).'/classes/ProductsImports.php');

class Importer extends Module
{
	public function __construct()
	{
		$this->name = 'importer';
		$this->tab = 'quick_bulk_update';
		$this->version = '1.0.0';
		$this->author = 'noveni';
		$this->need_instance = 0; // indicates wether to load the module's class when "Modules" page is call in back-office
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Importer');
		$this->description = $this->l('This tool can import product from a XML release by wholesale-sextoys.com(Part of EDC.nl)');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	}

	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		//Ajout d'un onglet à la racine du site
		$parentTab = Tab::getIdFromClassName('AdminImporter');
		if (empty($parentTab))
			$parentTab = self::createTab(0,$this->name,'EDC feeds importer','AdminImporter');
		self::createTab($parentTab, $this->name, 'Import des produits', 'AdminImporterRunning');
		self::createTab($parentTab, $this->name, 'Configuration', 'AdminImporterConfiguration');
		self::createTab($parentTab, $this->name, 'Counter', 'AdminImporterCounter');
		// self::createTab($parentTab, $this->name, 'CRON', 'AdminImporterCron');

		Configuration::updateValue('IMPORTER_URL_FULL_FEED',	'http://graphics.edc-internet.nl/b2b_feed.php?key=[KEY]&sort=xml&type=xml&lang=[LANG]&version=2015');
		Configuration::updateValue('IMPORTER_URL_NEW_PRODUCTS',	'http://graphics.edc-internet.nl/b2b_feed.php?key=[KEY]&sort=xml&type=xml&lang=[LANG]&version=2015&new=1');
		Configuration::updateValue('IMPORTER_URL_STOCK',		'http://graphics.edc-internet.nl/xml/eg_xml_feed_stock.xml');
		Configuration::updateValue('IMPORTER_DISCONTINUED',		'http://graphics.edc-internet.nl/xml/deleted_products.xml');
		Configuration::updateValue('IMPORTER_IMPORT_CURRENT_STEP', 		0);
		Configuration::updateValue('IMPORTER_IMPORT_CURRENT_KEY_IN_XML', 0);
		Configuration::updateValue('IMPORTER_XML_FILE','');
		Configuration::updateValue('IMPORTER_XML_COUNT');

		//Créer le dossier import


		if (!parent::install() || 
			!$this->installDb()
		)
			return false;
		return true;
	}

	public function uninstall()
	{
		Configuration::deleteByName('IMPORTER_API_KEY');
		Configuration::deleteByName('IMPORTER_URL_FULL_FEED');
		Configuration::deleteByName('IMPORTER_URL_NEW_PRODUCTS');
		Configuration::deleteByName('IMPORTER_URL_STOCK');		
		Configuration::deleteByName('IMPORTER_DISCONTINUED');
		Configuration::deleteByName('IMPORTER_IMPORT_CURRENT_STEP');
		Configuration::deleteByName('IMPORTER_IMPORT_CURRENT_KEY_IN_XML');
		Configuration::deleteByName('IMPORTER_XML_FILE');
		Configuration::deleteByName('IMPORTER_XML_COUNT');

		//Vider le dossier import et le supprimer

		$this->uninstallModuleTab('AdminImporter');
		$this->uninstallModuleTab('AdminImporterRunning');
		$this->uninstallModuleTab('AdminImporterConfiguration');
		$this->uninstallModuleTab('AdminImporterCounter');
		// $this->uninstallModuleTab('AdminImporterCron');

		if (!parent::uninstall() ||
			!$this->uninstallDb()
		)
			return false;

		return true;
	}

	public function installDb()
	{
		return true;
	}

	public function uninstallDb()
	{
		return true;
	}

	static function createTab($id_parent, $module, $name, $class_name)
	{
		$Tab = new Tab();
		$Tab->module = $module;
		foreach (Language::getLanguages(true) as $languages)
		{
			$Tab->name[$languages["id_lang"]] = $name;
		}

		$Tab->id_parent = $id_parent;
		$Tab->class_name = $class_name;
		$r = $Tab->save();

		if ($r == false)
			return false;

		return $Tab->id;
	}

	private function uninstallModuleTab($tabClass)
	{
		$idTab = Tab::getIdFromClassName($tabClass);
		if ($idTab != 0)
		{
			$tab = new Tab($idTab);
			$tab->delete();
			return true;
		}
		return false;
	}

}