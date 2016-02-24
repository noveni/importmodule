<?php

class AdminImporterLabController extends ModuleAdminController
{
	public function __construct()
	{
		parent::__construct();
		$this->bootstrap = true;
	}

	public function renderList()
	{
		$_html = "";

		$_html .= $this->listXML();

		return $_html.parent::renderList();
	}

	public function listXML()
	{
		$files = array();
		$dir = _PS_MODULE_DIR_.'importer'.DIRECTORY_SEPARATOR.'counter'.DIRECTORY_SEPARATOR;
		if ($handle = opendir($dir))
		{
			while (false !== ($entry = readdir($handle)))
			{
				if ($entry != "." && $entry != ".." && $entry != ".DS_Store")
				{
					$xml_file = simplexml_load_file($dir.$entry);
					$nproducts = count($xml_file);
					$files[] = array(
						'filename' => $entry,
						'nb_line' => $nproducts,
						'variants' => $this->checkTheVariants($xml_file,$nproducts)
					);
				}
			}
			closedir($handle);
		}

		$this->context->smarty->assign(array(
			'files' => $files
		));

		return $this->context->smarty->fetch(_PS_MODULE_DIR_.'importer/views/templates/admin/labovariants.tpl');
	}

	/**
	* checkTheVariants parse a XML of products and compile the != type of variants
	* @param array $xml_products SimpleXML object of products
	* @param int $nproducts (default false) number of product in the XML of products
	* @return array
	*/
	private function checkTheVariants($xml_products,$nproducts = false)
	{
		//Function to check the variants of an article	
		if(!$nproducts)
			$nproducts = count($xml_products);

		$type = array();
		$name_of_t = array();

		for ($i = 0; $i < $nproducts; ++$i)
		{
			if (count($xml_products->product[$i]->variants))
			{
				if ($nvariants = count($xml_products->product[$i]->variants->variant))
				{
					for ($j = 0; $j < $nvariants; ++$j)
					{
						$variant = $xml_products->product[$i]->variants->variant[$j];
						if (!empty($variant->type) && !in_array($variant->type, $type))
							$type[] = (string)$variant->type;
						if (isset($variant->title) && !empty($variant->title) && !in_array($variant->title, $name_of_t))
							$name_of_t[] = (string)$variant->title;
					}
				}
			}
		}
		return array('type'=>$type,'name_of_t' => $name_of_t);
	}
}