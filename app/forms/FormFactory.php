<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

/**
 * Default Nette class
 */
class FormFactory extends Nette\Object
{

	/**
         * Creates instance of empty Form
         * 
	 * @return Form
	 */
	public function create()
	{
		return new Form;
	}

}
